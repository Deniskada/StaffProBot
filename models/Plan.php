<?php
namespace Spbot\Models;

use Spbot\Core\Model;

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
    
    public function getDurationInDays() {
        switch ($this->duration) {
            case 'month':
                return 30;
            case 'quarter':
                return 90;
            case 'year':
                return 365;
            default:
                return (int)$this->duration;
        }
    }
    
    public static function getActive() {
        $plan = new static();
        return $plan->db->fetchAll(
            "SELECT * FROM {$plan->table} WHERE status = 'active' ORDER BY price ASC"
        );
    }
    
    public static function findByEmployer($employerId) {
        $plan = new static();
        return $plan->db->fetch(
            "SELECT p.* FROM {$plan->table} p
            JOIN subscriptions s ON p.id = s.plan_id
            WHERE s.employer_id = ? AND s.status = 'active'
            ORDER BY s.created_at DESC LIMIT 1",
            [$employerId]
        );
    }
} 