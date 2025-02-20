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
    
    public function getFormattedStartTime() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->start_time));
    }
    
    public function getFormattedEndTime() {
        return $this->end_time ? date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->end_time)) : null;
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedStatus() {
        $envKey = 'SHIFT_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public function getFormattedAmount() {
        return number_format(
            $this->total_amount,
            intval($_ENV['PRICE_DECIMAL_PLACES']),
            $_ENV['PRICE_DECIMAL_SEPARATOR'],
            $_ENV['PRICE_THOUSAND_SEPARATOR']
        ) . ' ' . $_ENV['DEFAULT_CURRENCY'];
    }
    
    public function getFormattedHours() {
        return number_format(
            $this->total_hours,
            intval($_ENV['HOURS_DECIMAL_PLACES']),
            $_ENV['PRICE_DECIMAL_SEPARATOR'],
            $_ENV['PRICE_THOUSAND_SEPARATOR']
        ) . ' ' . $_ENV['HOURS_SUFFIX'];
    }
    
    public function getFormattedHourlyRate() {
        return number_format(
            $this->hourly_rate,
            intval($_ENV['PRICE_DECIMAL_PLACES']),
            $_ENV['PRICE_DECIMAL_SEPARATOR'],
            $_ENV['PRICE_THOUSAND_SEPARATOR']
        ) . ' ' . $_ENV['DEFAULT_CURRENCY'] . '/' . $_ENV['HOURS_SUFFIX'];
    }
    
    public function complete() {
        if (!$this->isActive()) {
            return false;
        }
        
        $this->end_time = date($_ENV['DB_DATETIME_FORMAT']);
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
            "SELECT *,
             DATE_FORMAT(start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time
             FROM {$shift->table} 
            WHERE employee_id = ? AND status = 'active' AND end_time IS NULL",
            [$employeeId]
        );
    }
    
    public static function getStatistics($employerId = null, $period = 'month') {
        $shift = new static();
        $sql = "SELECT 
                COUNT(*) as total_shifts,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
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
    
    public function getFullDescription() {
        return sprintf(
            '%s - %s (%s, %s в час)',
            $this->getFormattedStartTime(),
            $this->getFormattedEndTime() ?? 'продолжается',
            $this->getFormattedStatus(),
            $this->getFormattedHourlyRate()
        );
    }
    
    public function getShortDescription() {
        return sprintf(
            '%s, %s',
            $this->getFormattedHours(),
            $this->getFormattedAmount()
        );
    }
    
    public static function getStatisticsByDateRange($startDate, $endDate, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["status = 'completed'"];
        
        if ($startDate) {
            $where[] = "start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function formatStatistics($stats) {
        return [
            'total_shifts' => (int)$stats['total_shifts'],
            'total_employees' => isset($stats['total_employees']) ? (int)$stats['total_employees'] : null,
            'total_hours' => number_format(
                $stats['total_hours'],
                intval($_ENV['HOURS_DECIMAL_PLACES']),
                $_ENV['PRICE_DECIMAL_SEPARATOR'],
                $_ENV['PRICE_THOUSAND_SEPARATOR']
            ) . ' ' . $_ENV['HOURS_SUFFIX'],
            'total_amount' => number_format(
                $stats['total_amount'],
                intval($_ENV['PRICE_DECIMAL_PLACES']),
                $_ENV['PRICE_DECIMAL_SEPARATOR'],
                $_ENV['PRICE_THOUSAND_SEPARATOR']
            ) . ' ' . $_ENV['DEFAULT_CURRENCY'],
            'avg_hourly_rate' => number_format(
                $stats['avg_hourly_rate'],
                intval($_ENV['PRICE_DECIMAL_PLACES']),
                $_ENV['PRICE_DECIMAL_SEPARATOR'],
                $_ENV['PRICE_THOUSAND_SEPARATOR']
            ) . ' ' . $_ENV['DEFAULT_CURRENCY'] . '/' . $_ENV['HOURS_SUFFIX'],
            'period_start' => $stats['period_start'],
            'period_end' => $stats['period_end']
        ];
    }
    
    public static function getEmployeeStatistics($employeeId, $startDate = null, $endDate = null) {
        $shift = new static();
        $params = [$employeeId];
        $where = ["status = 'completed' AND employee_id = ?"];
        
        if ($startDate) {
            $where[] = "start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getFacilityStatistics($facilityId, $startDate = null, $endDate = null) {
        $shift = new static();
        $params = [$facilityId];
        $where = ["status = 'completed' AND facility_id = ?"];
        
        if ($startDate) {
            $where[] = "start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT employee_id) as total_employees,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getCurrentMonthStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["status = 'completed'"];
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "start_time >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $where[] = "start_time < DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT employee_id) as total_employees,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getCurrentWeekStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["status = 'completed'"];
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "start_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL WEEKDAY(NOW()) DAY), '%Y-%m-%d')";
        $where[] = "start_time < DATE_FORMAT(DATE_ADD(DATE_SUB(NOW(), INTERVAL WEEKDAY(NOW()) DAY), INTERVAL 7 DAY), '%Y-%m-%d')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT employee_id) as total_employees,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getTodayStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["status = 'completed'"];
        
        if ($employerId) {
            $where[] = "employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "DATE(start_time) = CURDATE()";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT employee_id) as total_employees,
                SUM(total_hours) as total_hours,
                SUM(total_amount) as total_amount,
                AVG(hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getActiveByFacility($facilityId) {
        $shift = new static();
        return $shift->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$shift->table} 
             WHERE facility_id = ? AND status = 'active' AND end_time IS NULL
             ORDER BY start_time DESC",
            [$facilityId]
        );
    }
    
    public static function findByFacility($facilityId, $startDate = null, $endDate = null) {
        $shift = new static();
        $params = [$facilityId];
        $where = ["facility_id = ?"];
        
        if ($startDate) {
            $where[] = "start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$shift->table} 
             WHERE {$whereClause}
             ORDER BY start_time DESC",
            $params
        );
    }
    
    public static function findByEmployer($employerId, $startDate = null, $endDate = null, $status = null) {
        $shift = new static();
        $params = [$employerId];
        $where = ["employer_id = ?"];
        
        if ($startDate) {
            $where[] = "start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(u.first_name, ' ', u.last_name) as employee_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users u ON s.employee_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findByEmployee($employeeId, $startDate = null, $endDate = null, $status = null) {
        $shift = new static();
        $params = [$employeeId];
        $where = ["s.employee_id = ?"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($status) {
            $where[] = "s.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function search($filters = []) {
        $shift = new static();
        $params = [];
        $where = [];
        
        if (!empty($filters['employee_id'])) {
            $where[] = "s.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        
        if (!empty($filters['employer_id'])) {
            $where[] = "s.employer_id = ?";
            $params[] = $filters['employer_id'];
        }
        
        if (!empty($filters['facility_id'])) {
            $where[] = "s.facility_id = ?";
            $params[] = $filters['facility_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "s.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($filters['start_date']));
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($filters['end_date']));
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findOverlapping($employeeId, $startTime, $endTime = null, $excludeShiftId = null) {
        $shift = new static();
        $params = [$employeeId, $startTime];
        $where = ["employee_id = ? AND status = 'active'"];
        
        if ($endTime) {
            $where[] = "(start_time < ? AND (end_time > ? OR end_time IS NULL))";
            $params[] = $endTime;
            $params[] = $startTime;
        } else {
            $where[] = "(end_time > ? OR end_time IS NULL)";
        }
        
        if ($excludeShiftId) {
            $where[] = "id != ?";
            $params[] = $excludeShiftId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
             FROM {$shift->table}
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function findByStatus($status, $startDate = null, $endDate = null) {
        $shift = new static();
        $params = [$status];
        $where = ["s.status = ?"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findByPeriod($period = 'today', $employerId = null) {
        $shift = new static();
        $params = [];
        $where = [];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        switch ($period) {
            case 'today':
                $where[] = "DATE(s.start_time) = CURDATE()";
                break;
            case 'week':
                $where[] = "s.start_time >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
                $where[] = "s.start_time < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where[] = "s.start_time >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
                $where[] = "s.start_time < DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')";
                break;
            default:
                throw new \InvalidArgumentException('Invalid period specified');
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findByEmployees($employeeIds, $startDate = null, $endDate = null, $status = null) {
        if (empty($employeeIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($employeeIds) - 1) . '?';
        $params = $employeeIds;
        $where = ["s.employee_id IN ({$placeholders})"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($status) {
            $where[] = "s.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findByFacilities($facilityIds, $startDate = null, $endDate = null, $status = null) {
        if (empty($facilityIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($facilityIds) - 1) . '?';
        $params = $facilityIds;
        $where = ["s.facility_id IN ({$placeholders})"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($status) {
            $where[] = "s.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function findByEmployers($employerIds, $startDate = null, $endDate = null, $status = null) {
        if (empty($employerIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($employerIds) - 1) . '?';
        $params = $employerIds;
        $where = ["s.employer_id IN ({$placeholders})"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        if ($status) {
            $where[] = "s.status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT s.*,
             DATE_FORMAT(s.start_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_time,
             DATE_FORMAT(s.end_time, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as end_time,
             DATE_FORMAT(s.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(s.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
             f.name as facility_name,
             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
             CONCAT(u.first_name, ' ', u.last_name) as employer_name
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             JOIN users e ON s.employee_id = e.id
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             ORDER BY s.start_time DESC",
            $params
        );
    }
    
    public static function getEmployersStatistics($employerIds, $startDate = null, $endDate = null) {
        if (empty($employerIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($employerIds) - 1) . '?';
        $params = $employerIds;
        $where = ["s.employer_id IN ({$placeholders}) AND s.status = 'completed'"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                s.employer_id,
                CONCAT(u.first_name, ' ', u.last_name) as employer_name,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             JOIN users u ON s.employer_id = u.id
             WHERE {$whereClause}
             GROUP BY s.employer_id, employer_name
             ORDER BY total_shifts DESC",
            $params
        );
    }
    
    public static function getEmployeesStatistics($employeeIds, $startDate = null, $endDate = null) {
        if (empty($employeeIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($employeeIds) - 1) . '?';
        $params = $employeeIds;
        $where = ["s.employee_id IN ({$placeholders}) AND s.status = 'completed'"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                s.employee_id,
                CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             JOIN users u ON s.employee_id = u.id
             WHERE {$whereClause}
             GROUP BY s.employee_id, employee_name
             ORDER BY total_shifts DESC",
            $params
        );
    }
    
    public static function getFacilitiesStatistics($facilityIds, $startDate = null, $endDate = null) {
        if (empty($facilityIds)) {
            return [];
        }
        
        $shift = new static();
        $placeholders = str_repeat('?,', count($facilityIds) - 1) . '?';
        $params = $facilityIds;
        $where = ["s.facility_id IN ({$placeholders}) AND s.status = 'completed'"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                s.facility_id,
                f.name as facility_name,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             JOIN facilities f ON s.facility_id = f.id
             WHERE {$whereClause}
             GROUP BY s.facility_id, facility_name
             ORDER BY total_shifts DESC",
            $params
        );
    }
    
    public static function getAllStatistics($startDate = null, $endDate = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return [
            'total' => $shift->db->fetch(
                "SELECT 
                    COUNT(*) as total_shifts,
                    COUNT(DISTINCT s.employee_id) as total_employees,
                    COUNT(DISTINCT s.employer_id) as total_employers,
                    COUNT(DISTINCT s.facility_id) as total_facilities,
                    SUM(s.total_hours) as total_hours,
                    SUM(s.total_amount) as total_amount,
                    AVG(s.hourly_rate) as avg_hourly_rate,
                    DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                    DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
                 FROM {$shift->table} s
                 WHERE {$whereClause}",
                $params
            ),
            'by_employer' => $shift->db->fetchAll(
                "SELECT 
                    s.employer_id,
                    CONCAT(u.first_name, ' ', u.last_name) as employer_name,
                    COUNT(*) as total_shifts,
                    COUNT(DISTINCT s.employee_id) as total_employees,
                    COUNT(DISTINCT s.facility_id) as total_facilities,
                    SUM(s.total_hours) as total_hours,
                    SUM(s.total_amount) as total_amount,
                    AVG(s.hourly_rate) as avg_hourly_rate
                 FROM {$shift->table} s
                 JOIN users u ON s.employer_id = u.id
                 WHERE {$whereClause}
                 GROUP BY s.employer_id, employer_name
                 ORDER BY total_shifts DESC
                 LIMIT 10",
                $params
            ),
            'by_employee' => $shift->db->fetchAll(
                "SELECT 
                    s.employee_id,
                    CONCAT(u.first_name, ' ', u.last_name) as employee_name,
                    COUNT(*) as total_shifts,
                    COUNT(DISTINCT s.employer_id) as total_employers,
                    COUNT(DISTINCT s.facility_id) as total_facilities,
                    SUM(s.total_hours) as total_hours,
                    SUM(s.total_amount) as total_amount,
                    AVG(s.hourly_rate) as avg_hourly_rate
                 FROM {$shift->table} s
                 JOIN users u ON s.employee_id = u.id
                 WHERE {$whereClause}
                 GROUP BY s.employee_id, employee_name
                 ORDER BY total_shifts DESC
                 LIMIT 10",
                $params
            ),
            'by_facility' => $shift->db->fetchAll(
                "SELECT 
                    s.facility_id,
                    f.name as facility_name,
                    COUNT(*) as total_shifts,
                    COUNT(DISTINCT s.employee_id) as total_employees,
                    COUNT(DISTINCT s.employer_id) as total_employers,
                    SUM(s.total_hours) as total_hours,
                    SUM(s.total_amount) as total_amount,
                    AVG(s.hourly_rate) as avg_hourly_rate
                 FROM {$shift->table} s
                 JOIN facilities f ON s.facility_id = f.id
                 WHERE {$whereClause}
                 GROUP BY s.facility_id, facility_name
                 ORDER BY total_shifts DESC
                 LIMIT 10",
                $params
            )
        ];
    }
    
    public static function getStatusStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = [];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        return $shift->db->fetchAll(
            "SELECT 
                s.status,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             {$whereClause}
             GROUP BY s.status
             ORDER BY total_shifts DESC",
            $params
        );
    }
    
    public static function getHoursStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                HOUR(s.start_time) as hour,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY hour
             ORDER BY hour ASC",
            $params
        );
    }
    
    public static function getWeekdayStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                WEEKDAY(s.start_time) as weekday,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY weekday
             ORDER BY weekday ASC",
            $params
        );
    }
    
    public static function getMonthlyStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                DATE_FORMAT(s.start_time, '%Y-%m') as month,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY month
             ORDER BY month DESC",
            $params
        );
    }
    
    public static function getYearlyStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                YEAR(s.start_time) as year,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY year
             ORDER BY year DESC",
            $params
        );
    }
    
    public static function getHourlyRateStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                s.hourly_rate,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY s.hourly_rate
             ORDER BY s.hourly_rate ASC",
            $params
        );
    }
    
    public static function getDurationStatistics($startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                FLOOR(s.total_hours) as duration_hours,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY duration_hours
             ORDER BY duration_hours ASC",
            $params
        );
    }
    
    public static function getPeriodStatistics($period = 'day', $startDate = null, $endDate = null, $employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        if ($startDate) {
            $where[] = "s.start_time >= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate));
        }
        
        if ($endDate) {
            $where[] = "s.start_time <= ?";
            $params[] = date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate));
        }
        
        $groupBy = match($period) {
            'day' => "DATE(s.start_time)",
            'week' => "YEARWEEK(s.start_time)",
            'month' => "DATE_FORMAT(s.start_time, '%Y-%m')",
            'year' => "YEAR(s.start_time)",
            default => throw new \InvalidArgumentException('Invalid period specified')
        };
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                {$groupBy} as period,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY period
             ORDER BY period DESC",
            $params
        );
    }
    
    public static function getLastNDaysStatistics($days = 7, $employerId = null) {
        $shift = new static();
        $params = [$days];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                DATE(s.start_time) as date,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY date
             ORDER BY date DESC",
            $params
        );
    }
    
    public static function getCurrentQuarterStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL (MONTH(NOW())-1)%3 MONTH), '%Y-%m-01')";
        $where[] = "s.start_time < DATE_FORMAT(DATE_ADD(DATE_SUB(NOW(), INTERVAL (MONTH(NOW())-1)%3 MONTH), INTERVAL 3 MONTH), '%Y-%m-01')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getLastQuarterStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 MONTH), '%Y-%m-01')";
        $where[] = "s.start_time < DATE_FORMAT(NOW(), '%Y-%m-01')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getCurrentYearStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_FORMAT(NOW(), '%Y-01-01')";
        $where[] = "s.start_time < DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 YEAR), '%Y-01-01')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getLastYearStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 YEAR), '%Y-%m-01')";
        $where[] = "s.start_time < DATE_FORMAT(NOW(), '%Y-%m-01')";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getDateRangeStatistics($startDate, $endDate, $employerId = null) {
        if (!$startDate || !$endDate) {
            throw new \InvalidArgumentException('Start date and end date are required');
        }
        
        $shift = new static();
        $params = [
            date($_ENV['DB_DATETIME_FORMAT'], strtotime($startDate)),
            date($_ENV['DB_DATETIME_FORMAT'], strtotime($endDate))
        ];
        $where = ["s.status = 'completed'", "s.start_time >= ?", "s.start_time <= ?"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }
    
    public static function getLastNMonthsStatistics($months = 12, $employerId = null) {
        $shift = new static();
        $params = [$months];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL ? MONTH)";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                DATE_FORMAT(s.start_time, '%Y-%m') as month,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY month
             ORDER BY month DESC",
            $params
        );
    }
    
    public static function getLastNWeeksStatistics($weeks = 4, $employerId = null) {
        $shift = new static();
        $params = [$weeks];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-%d'), INTERVAL ? WEEK)";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                YEARWEEK(s.start_time) as week,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY week
             ORDER BY week DESC",
            $params
        );
    }
    
    public static function getLastNQuartersStatistics($quarters = 4, $employerId = null) {
        $shift = new static();
        $params = [$quarters * 3]; // Конвертируем кварталы в месяцы
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL ? MONTH)";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                CONCAT(YEAR(s.start_time), '-Q', QUARTER(s.start_time)) as quarter,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY quarter
             ORDER BY quarter DESC",
            $params
        );
    }
    
    public static function getLastNYearsStatistics($years = 5, $employerId = null) {
        $shift = new static();
        $params = [$years];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $where[] = "s.start_time >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-01-01'), INTERVAL ? YEAR)";
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetchAll(
            "SELECT 
                YEAR(s.start_time) as year,
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}
             GROUP BY year
             ORDER BY year DESC",
            $params
        );
    }
    
    public static function getAllTimeStatistics($employerId = null) {
        $shift = new static();
        $params = [];
        $where = ["s.status = 'completed'"];
        
        if ($employerId) {
            $where[] = "s.employer_id = ?";
            $params[] = $employerId;
        }
        
        $whereClause = implode(" AND ", $where);
        
        return $shift->db->fetch(
            "SELECT 
                COUNT(*) as total_shifts,
                COUNT(DISTINCT s.employee_id) as total_employees,
                COUNT(DISTINCT s.employer_id) as total_employers,
                COUNT(DISTINCT s.facility_id) as total_facilities,
                SUM(s.total_hours) as total_hours,
                SUM(s.total_amount) as total_amount,
                AVG(s.hourly_rate) as avg_hourly_rate,
                DATE_FORMAT(MIN(s.start_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_start,
                DATE_FORMAT(MAX(s.end_time), '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as period_end
             FROM {$shift->table} s
             WHERE {$whereClause}",
            $params
        );
    }

    public static function getAll() {
        $db = static::getDB();
        $sql = "SELECT s.*, f.name as facility_name 
                FROM shifts s 
                LEFT JOIN facilities f ON s.facility_id = f.id 
                ORDER BY s.start_time DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function getAllByEmployer($employerId) {
        $db = static::getDB();
        $sql = "SELECT s.*, f.name as facility_name 
                FROM shifts s 
                LEFT JOIN facilities f ON s.facility_id = f.id 
                WHERE s.employer_id = ? 
                ORDER BY s.start_time DESC";
        return $db->query($sql, [$employerId])->fetchAll();
    }

    public static function getAllByEmployee($employeeId) {
        $db = static::getDB();
        $sql = "SELECT s.*, f.name as facility_name 
                FROM shifts s 
                LEFT JOIN facilities f ON s.facility_id = f.id 
                WHERE s.employee_id = ? 
                ORDER BY s.start_time DESC";
        return $db->query($sql, [$employeeId])->fetchAll();
    }
} 