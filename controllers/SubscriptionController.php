<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\models\Plan;
use Spbot\models\Subscription;
use Spbot\models\Payment;

class SubscriptionController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'employer']);
    }
    
    public function index() {
        $subscriptions = $this->user->role === 'admin'
            ? Subscription::all()
            : Subscription::where('employer_id = ?', [$this->user->id]);
            
        $this->view->render('subscriptions/index', [
            'subscriptions' => $subscriptions
        ]);
    }
    
    public function plans() {
        $plans = Plan::where('status = ?', ['active']);
        
        if ($this->user->role === 'employer') {
            $currentSubscription = Subscription::findActiveByEmployer($this->user->id);
        }
        
        $this->view->render('subscriptions/plans', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription ?? null
        ]);
    }
    
    public function subscribe() {
        $this->requireRole('employer');
        
        $data = $this->validate($this->request->post(), [
            'plan_id' => 'required|numeric'
        ]);
        
        $plan = Plan::find($data['plan_id']);
        if (!$plan || $plan->status !== 'active') {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Тарифный план недоступен'], 400);
            }
            $_SESSION['error'] = 'Тарифный план недоступен';
            return $this->back();
        }
        
        // Проверяем, нет ли уже активной подписки
        $activeSubscription = Subscription::findActiveByEmployer($this->user->id);
        if ($activeSubscription) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'У вас уже есть активная подписка'], 400);
            }
            $_SESSION['error'] = 'У вас уже есть активная подписка';
            return $this->back();
        }
        
        // Создаем платеж
        $payment = new Payment();
        $payment->fill([
            'employer_id' => $this->user->id,
            'amount' => $plan->price,
            'payment_method' => $this->request->post('payment_method', 'card')
        ])->save();
        
        // Создаем подписку
        $subscription = new Subscription();
        $subscription->fill([
            'employer_id' => $this->user->id,
            'plan_id' => $plan->id,
            'payment_id' => $payment->id,
            'start_date' => date($_ENV['DB_DATETIME_FORMAT']),
            'end_date' => $this->calculateEndDate($plan->duration),
            'auto_renew' => $this->request->post('auto_renew', false)
        ])->save();
        
        // Перенаправляем на страницу оплаты
        if ($this->request->isAjax()) {
            return $this->json([
                'payment_id' => $payment->id,
                'redirect_url' => "/payment/{$payment->id}"
            ]);
        }
        
        $this->redirect("/payment/{$payment->id}");
    }
    
    public function cancel($id) {
        $subscription = Subscription::find($id);
        
        if (!$subscription || ($this->user->role !== 'admin' && $subscription->employer_id !== $this->user->id)) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Подписка не найдена'], 404);
            }
            return $this->view->renderError(404, 'Подписка не найдена');
        }
        
        $subscription->fill([
            'status' => 'cancelled',
            'auto_renew' => false
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Подписка отменена';
        $this->redirect('/subscriptions');
    }
    
    private function calculateEndDate($duration) {
        $date = new \DateTime();
        
        switch ($duration) {
            case 'month':
                $date->modify('+1 month');
                break;
            case 'quarter':
                $date->modify('+3 months');
                break;
            case 'year':
                $date->modify('+1 year');
                break;
        }
        
        return $date->format($_ENV['DB_DATETIME_FORMAT']);
    }
} 