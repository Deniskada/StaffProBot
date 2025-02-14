<?php
namespace Spbot\Models;

use Spbot\Core\Model;

class Shift extends Model {
    protected $table = 'shifts';
    protected $fillable = [
        'employee_id', 'employer_id', 'facility_id', 
        'start_time', 'end_time', 'status', 'payment_status',
        'hourly_rate', 'total_hours', 'total_amount'
    ];
    
    public function employee() {
        return User::find($this->employee_id);
    }
    
    public function employer() {
        return User::find($this->employer_id);
    }
    
    public function facility() {
        return Facility::find($this->facility_id);
    }
    
    public function isActive() {
        return $this->status === 'active' && $this->end_time === null;
    }
    
    public function complete() {
        if (!$this->isActive()) {
            return false;
        }
        
        $this->end_time = date('Y-m-d H:i:s');
        $this->calculateTotals();
        $this->status = 'completed';
        
        return $this->save();
    }
    
    private function calculateTotals() {
        $startTime = strtotime($this->start_time);
        $endTime = strtotime($this->end_time);
        
        $this->total_hours = round(($endTime - $startTime) / 3600, 2);
        $this->total_amount = $this->total_hours * $this->hourly_rate;
    }
    
    public static function getActiveByEmployee($employeeId) {
        $shift = new static();
        return $shift->db->fetch(
            "SELECT * FROM {$shift->table} 
            WHERE employee_id = ? AND status = 'active' AND end_time IS NULL",
            [$employeeId]
        );
    }
    
    public static function getStatistics($employerId = null, $period = 'month') {
        $shift = new static();
        $sql = "SELECT 
                COUNT(*) as total_shifts,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount
                FROM {$shift->table}
                WHERE status = 'completed'";
        
        $params = [];
        if ($employerId) {
            $sql .= " AND employer_id = ?";
            $params[] = $employerId;
        }
        
        switch ($period) {
            case 'week':
                $sql .= " AND start_time >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $sql .= " AND start_time >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $sql .= " AND start_time >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
        }
        
        return $shift->db->fetch($sql, $params);
    }
} 