<?php
namespace Spbot\Models;

use Spbot\Core\Model;

class Subscription extends Model {
    protected $table = 'subscriptions';
    protected $fillable = [
        'employer_id', 'plan_id', 'start_date', 'end_date',
        'status', 'payment_id', 'auto_renew'
    ];
    
    public function employer() {
        return User::find($this->employer_id);
    }
    
    public function plan() {
        return Plan::find($this->plan_id);
    }
    
    public function isActive() {
        return $this->status === 'active' && 
               strtotime($this->end_date) > time();
    }
    
    public function isExpired() {
        return strtotime($this->end_date) < time();
    }
    
    public function getDaysLeft() {
        $endTime = strtotime($this->end_date);
        $now = time();
        return max(0, ceil(($endTime - $now) / 86400));
    }
    
    public function cancel() {
        $this->auto_renew = false;
        return $this->save();
    }
    
    public static function getActive($employerId) {
        $subscription = new static();
        return $subscription->db->fetch(
            "SELECT * FROM {$subscription->table} 
            WHERE employer_id = ? AND status = 'active' 
            AND end_date > NOW()",
            [$employerId]
        );
    }
    
    public static function getExpiring($days = 7) {
        $subscription = new static();
        return $subscription->db->fetchAll(
            "SELECT s.*, u.email, u.first_name, p.name as plan_name 
            FROM {$subscription->table} s
            JOIN users u ON s.employer_id = u.id
            JOIN plans p ON s.plan_id = p.id
            WHERE s.status = 'active' 
            AND s.end_date BETWEEN NOW() 
            AND DATE_ADD(NOW(), INTERVAL ? DAY)
            AND s.auto_renew = 0",
            [$days]
        );
    }
} 