<?php
namespace Spbot\models;

use Spbot\core\Model;

class Plan extends Model {
    protected $table = 'plans';
    protected $fillable = [
        'name', 'description', 'price', 'duration', 
        'max_facilities', 'max_employees', 'features', 'status'
    ];
    
    public function getFeatures() {
        return json_decode($this->features, true) ?? [];
    }
    
    public function setFeatures($features) {
        $this->features = json_encode($features);
    }
    
    public function isActive() {
        return $this->status === 'active';
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedUpdatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->updated_at));
    }
    
    public function getDurationInDays() {
        switch ($this->duration) {
            case 'month':
                return intval($_ENV['SUBSCRIPTION_DURATION_MONTH']);
            case 'quarter':
                return intval($_ENV['SUBSCRIPTION_DURATION_QUARTER']);
            case 'year':
                return intval($_ENV['SUBSCRIPTION_DURATION_YEAR']);
            default:
                return (int)$this->duration;
        }
    }
    
    public static function getActive() {
        $plan = new static();
        return $plan->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$plan->table} 
             WHERE status = 'active' 
             ORDER BY price ASC"
        );
    }
    
    public static function findByEmployer($employerId) {
        $plan = new static();
        return $plan->db->fetch(
            "SELECT p.*,
             DATE_FORMAT(p.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(p.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as subscription_created_at 
             FROM {$plan->table} p
             JOIN subscriptions s ON p.id = s.plan_id
             WHERE s.employer_id = ? AND s.status = 'active'
             ORDER BY s.created_at DESC LIMIT 1",
            [$employerId]
        );
    }
    
    public function formatPrice() {
        return number_format(
            $this->price, 
            intval($_ENV['PRICE_DECIMAL_PLACES']),
            $_ENV['PRICE_DECIMAL_SEPARATOR'],
            $_ENV['PRICE_THOUSAND_SEPARATOR']
        ) . ' ' . $_ENV['DEFAULT_CURRENCY'];
    }
    
    public function getFormattedStatus() {
        $envKey = 'PLAN_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public function getFormattedDuration() {
        $envKey = 'PLAN_DURATION_' . strtoupper($this->duration);
        return $_ENV[$envKey] ?? $this->duration;
    }
    
    public function getFullDescription() {
        return sprintf(
            '%s (%s) - %s',
            $this->name,
            $this->getFormattedDuration(),
            $this->formatPrice()
        );
    }
} 