<?php
namespace Spbot\Controllers\Api;

use Spbot\Models\Subscription;
use Spbot\Models\Plan;

class SubscriptionController extends ApiController {
    public function __construct() {
        parent::__construct();
        $this->requireRole('employer');
    }
    
    public function getPlans() {
        $plans = Plan::getActive();
        return $this->jsonSuccess(['plans' => $plans]);
    }
    
    public function getCurrentPlan() {
        $subscription = Subscription::getActive($this->user->id);
        
        if (!$subscription) {
            return $this->jsonSuccess(['subscription' => null]);
        }
        
        $plan = Plan::find($subscription->plan_id);
        return $this->jsonSuccess([
            'subscription' => $subscription,
            'plan' => $plan,
            'days_left' => $subscription->getDaysLeft()
        ]);
    }
    
    public function cancel() {
        $subscription = Subscription::getActive($this->user->id);
        
        if (!$subscription) {
            return $this->jsonError('No active subscription found');
        }
        
        if ($subscription->cancel()) {
            $this->log('subscription_cancelled', [
                'subscription_id' => $subscription->id
            ]);
            
            return $this->jsonSuccess([
                'message' => 'Subscription cancelled successfully'
            ]);
        }
        
        return $this->jsonError('Failed to cancel subscription');
    }
    
    public function getHistory() {
        $page = $this->request->get('page', 1);
        $subscriptions = Subscription::getHistory(
            $this->user->id,
            $page
        );
        
        foreach ($subscriptions as &$subscription) {
            $subscription['plan'] = Plan::find($subscription['plan_id']);
        }
        
        return $this->jsonSuccess(['subscriptions' => $subscriptions]);
    }
} 