<?php
namespace Spbot\models;

use Spbot\core\Model;

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
        return $this->status === 'active';
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
            "SELECT *,
             DATE_FORMAT(start_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_date,
             DATE_FORMAT(end_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_date,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at
             FROM {$subscription->table} 
            WHERE employer_id = ? AND status = 'active' 
            AND end_date > NOW()",
            [$employerId]
        );
    }
    
    public static function getExpiring($days = 7) {
        $subscription = new static();
        return $subscription->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_date,
             DATE_FORMAT(s.end_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_date,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             u.email, u.first_name, p.name as plan_name 
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
    
    public function extend() {
        $this->end_date = date(
            $_ENV['DB_DATETIME_FORMAT'], 
            strtotime($this->end_date) + (intval($_ENV['SUBSCRIPTION_DURATION_DAYS']) * 24 * 3600)
        );
        return $this->save();
    }
    
    public function getFormattedStartDate() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->start_date));
    }
    
    public function getFormattedEndDate() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->end_date));
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedUpdatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->updated_at));
    }
    
    public function getFormattedStatus() {
        $envKey = 'SUBSCRIPTION_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public static function findActiveByEmployer($employerId) {
        $subscription = new static();
        return $subscription->db->fetch(
            "SELECT *,
             DATE_FORMAT(start_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_date,
             DATE_FORMAT(end_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_date,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at
             FROM {$subscription->table} 
            WHERE employer_id = ? AND status = 'active' 
            AND end_date > NOW()",
            [$employerId]
        );
    }
} 