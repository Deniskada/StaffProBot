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
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedCompletedAt() {
        return $this->completed_at ? date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->completed_at)) : null;
    }
    
    public function getFormattedAmount() {
        return number_format(
            $this->amount,
            intval($_ENV['PRICE_DECIMAL_PLACES']),
            $_ENV['PRICE_DECIMAL_SEPARATOR'],
            $_ENV['PRICE_THOUSAND_SEPARATOR']
        ) . ' ' . $this->currency;
    }
    
    public function markAsCompleted($transactionId = null) {
        $this->status = 'completed';
        $this->completed_at = date($_ENV['DB_DATETIME_FORMAT']);
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
                status,
                DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
                DATE_FORMAT(completed_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as completed_at,
                DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
                FROM {$payment->table}
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 {$period})
                GROUP BY payment_method, status";
                
        return $payment->db->fetchAll($sql);
    }

    public function create($amount, $currency = null) {
        $this->amount = $amount;
        $this->currency = $currency ?? $_ENV['DEFAULT_CURRENCY'];
        $this->created_at = date($_ENV['DB_DATETIME_FORMAT']);
        // ...
    }

    public static function findByEmployer($employerId) {
        $payment = new static();
        return $payment->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(completed_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as completed_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$payment->table}
             WHERE employer_id = ?
             ORDER BY created_at DESC",
            [$employerId]
        );
    }

    public static function all() {
        $payment = new static();
        return $payment->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(completed_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as completed_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$payment->table}
             ORDER BY created_at DESC"
        );
    }

    public static function find($id) {
        $payment = new static();
        return $payment->db->fetch(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(completed_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as completed_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$payment->table}
             WHERE id = ?",
            [$id]
        );
    }

    public static function findByDateRange($startDate, $endDate, $employerId = null) {
        $payment = new static();
        $params = [];
        $where = [];
        
        if ($startDate) {
            $where[] = "created_at >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "created_at <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        return $payment->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(completed_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as completed_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$payment->table}
             {$whereClause}
             ORDER BY created_at DESC",
            $params
        );
    }

    public static function getStatisticsByDateRange($startDate, $endDate, $employerId = null) {
        $payment = new static();
        $params = [];
        $where = [];
        
        if ($startDate) {
            $where[] = "created_at >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "created_at <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        return $payment->db->fetch(
            "SELECT 
                COUNT(*) as total_payments,
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments,
                DATE_FORMAT(MIN(created_at), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(created_at), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$payment->table}
             {$whereClause}",
            $params
        );
    }

    public function getFormattedStatus() {
        $envKey = 'PAYMENT_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public function getFormattedPaymentMethod() {
        $envKey = 'PAYMENT_METHOD_' . strtoupper(str_replace('-', '_', $this->payment_method));
        return $_ENV[$envKey] ?? $this->payment_method;
    }
} 