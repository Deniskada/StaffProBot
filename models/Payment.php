<?php
namespace Spbot\Models;

use Spbot\Core\Model;

class Payment extends Model {
    protected $table = 'payments';
    protected $fillable = [
        'employer_id', 'subscription_id', 'amount', 'currency',
        'payment_method', 'status', 'transaction_id', 'payment_data'
    ];
    
    public function employer() {
        return User::find($this->employer_id);
    }
    
    public function subscription() {
        return Subscription::find($this->subscription_id);
    }
    
    public function getPaymentData() {
        return json_decode($this->payment_data, true) ?? [];
    }
    
    public function setPaymentData($data) {
        $this->payment_data = json_encode($data);
    }
    
    public function isSuccessful() {
        return $this->status === 'completed';
    }
    
    public function markAsCompleted($transactionId = null) {
        $this->status = 'completed';
        if ($transactionId) {
            $this->transaction_id = $transactionId;
        }
        return $this->save();
    }
    
    public function markAsFailed($reason = null) {
        $this->status = 'failed';
        if ($reason) {
            $data = $this->getPaymentData();
            $data['failure_reason'] = $reason;
            $this->setPaymentData($data);
        }
        return $this->save();
    }
    
    public static function getStatistics($period = 'month') {
        $payment = new static();
        $sql = "SELECT 
                COUNT(*) as total_payments,
                SUM(amount) as total_amount,
                payment_method,
                status
                FROM {$payment->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 {$period})
                GROUP BY payment_method, status";
                
        return $payment->db->fetchAll($sql);
    }
} 