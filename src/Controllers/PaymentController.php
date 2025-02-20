<?php
namespace Spbot\controllers;

use Spbot\core\Controller;
use Spbot\core\PaymentGateway;
use Spbot\models\Payment;

class PaymentController extends Controller {
    private $gateway;
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->gateway = new PaymentGateway();
    }
    
    public function show($id) {
        $payment = Payment::find($id);
        
        if (!$payment || ($this->user->role !== 'admin' && $payment->employer_id !== $this->user->id)) {
            return $this->view->renderError(404, 'Платеж не найден');
        }
        
        $this->view->render('payments/show', [
            'payment' => $payment
        ]);
    }
    
    public function process($id) {
        $payment = Payment::find($id);
        
        if (!$payment || $payment->employer_id !== $this->user->id) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Платеж не найден'], 404);
            }
            return $this->view->renderError(404, 'Платеж не найден');
        }
        
        if ($payment->status !== 'pending') {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Недопустимый статус платежа'], 400);
            }
            $_SESSION['error'] = 'Недопустимый статус платежа';
            return $this->back();
        }
        
        $paymentUrl = $this->gateway->createPayment([
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'description' => "Оплата подписки #{$payment->subscription_id}"
        ]);
        
        if (!$paymentUrl) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Ошибка создания платежа'], 500);
            }
            $_SESSION['error'] = 'Ошибка создания платежа';
            return $this->back();
        }
        
        if ($this->request->isAjax()) {
            return $this->json(['redirect_url' => $paymentUrl]);
        }
        
        $this->redirect($paymentUrl);
    }
    
    public function callback() {
        if (!$this->gateway->validateCallback($_POST)) {
            http_response_code(400);
            return;
        }
        
        $paymentId = $_POST['order_id'];
        $payment = Payment::find($paymentId);
        
        if (!$payment) {
            http_response_code(404);
            return;
        }
        
        $status = $_POST['status'] === 'success' ? 'completed' : 'failed';
        
        $payment->fill([
            'status' => $status,
            'transaction_id' => $_POST['transaction_id'],
            'completed_at' => $status === 'completed' ? date($_ENV['DB_DATETIME_FORMAT']) : null,
            'payment_data' => json_encode($_POST)
        ])->save();
        
        if ($status === 'completed') {
            $subscription = \Spbot\models\Subscription::find($payment->subscription_id);
            if ($subscription) {
                $subscription->fill([
                    'status' => 'active',
                    'activated_at' => date($_ENV['DB_DATETIME_FORMAT'])
                ])->save();
            }
        }
        
        http_response_code(200);
    }
    
    public function history() {
        $startDate = $this->request->get('start_date');
        $endDate = $this->request->get('end_date');
        $employerId = $this->user->role === 'admin' ? null : $this->user->id;
        
        $payments = $this->user->role === 'admin'
            ? Payment::findByDateRange($startDate, $endDate)
            : Payment::findByDateRange($startDate, $endDate, $employerId);
        
        $stats = Payment::getStatisticsByDateRange($startDate, $endDate, $employerId);
        
        $this->view->render('payments/history', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
} 