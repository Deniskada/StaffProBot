<?php
namespace Spbot\Controllers\Api;

use Spbot\Models\Payment;
use Spbot\Models\Subscription;
use Spbot\Models\Plan;
use Spbot\Core\PaymentGateway;

class PaymentController extends ApiController {
    private $gateway;
    
    public function __construct() {
        parent::__construct();
        $this->requireRole('employer');
        $this->gateway = new PaymentGateway();
    }
    
    public function createPayment() {
        $this->validateRequest([
            'plan_id' => 'required|numeric',
            'payment_method' => 'required|in:card,bank_transfer'
        ]);
        
        $data = $this->request->getJson();
        $plan = Plan::find($data['plan_id']);
        
        if (!$plan || !$plan->isActive()) {
            return $this->jsonError('Invalid plan');
        }
        
        // Создаем платеж
        $payment = new Payment();
        $payment->fill([
            'employer_id' => $this->user->id,
            'amount' => $plan->price,
            'currency' => $_ENV['DEFAULT_CURRENCY'],
            'payment_method' => $data['payment_method'],
            'status' => 'pending'
        ]);
        
        if (!$payment->save()) {
            return $this->jsonError('Failed to create payment');
        }
        
        // Получаем платежную ссылку от платежного шлюза
        $paymentUrl = $this->gateway->createPayment([
            'amount' => $plan->price,
            'currency' => $_ENV['DEFAULT_CURRENCY'],
            'payment_id' => $payment->id,
            'description' => "Subscription to {$plan->name} plan"
        ]);
        
        if (!$paymentUrl) {
            $payment->markAsFailed('Failed to get payment URL');
            return $this->jsonError('Payment system error');
        }
        
        $this->log('payment_created', [
            'payment_id' => $payment->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price
        ]);
        
        return $this->jsonSuccess([
            'payment_id' => $payment->id,
            'payment_url' => $paymentUrl
        ]);
    }
    
    public function handleCallback() {
        $data = $this->request->getJson();
        
        if (!$this->gateway->validateCallback($data)) {
            return $this->jsonError('Invalid callback data', 400);
        }
        
        $payment = Payment::find($data['payment_id']);
        if (!$payment) {
            return $this->jsonError('Payment not found', 404);
        }
        
        if ($data['status'] === 'completed') {
            $payment->markAsCompleted($data['transaction_id']);
            
            // Создаем или продлеваем подписку
            $subscription = new Subscription();
            $subscription->fill([
                'employer_id' => $payment->employer_id,
                'plan_id' => $payment->plan_id,
                'payment_id' => $payment->id,
                'start_date' => date('Y-m-d H:i:s'),
                'end_date' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'status' => 'active'
            ])->save();
            
            $this->log('payment_completed', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id
            ]);
        } else {
            $payment->markAsFailed($data['error'] ?? null);
            $this->log('payment_failed', [
                'payment_id' => $payment->id,
                'error' => $data['error'] ?? null
            ]);
        }
        
        return $this->jsonSuccess();
    }
    
    public function getHistory() {
        $page = $this->request->get('page', 1);
        $status = $this->request->get('status');
        
        $payments = Payment::getByEmployer(
            $this->user->id,
            $status,
            $page
        );
        
        return $this->jsonSuccess(['payments' => $payments]);
    }
} 