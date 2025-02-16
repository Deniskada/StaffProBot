<?php
namespace Spbot\models;

use Spbot\core\Database;

class Employee {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO employees (user_id, first_name, last_name, telegram_id, notes, created_at) 
                VALUES (:user_id, :first_name, :last_name, :telegram_id, :notes, :created_at)";
                
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'telegram_id' => $data['telegram_id'],
            'notes' => $data['notes'] ?? null,
            'created_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ]);
        
        if ($success) {
            $employeeId = $this->db->lastInsertId();
            
            // Добавляем связи с объектами
            if (!empty($data['facilities'])) {
                $this->assignFacilities($employeeId, $data['facilities']);
            }
            
            // Добавляем ставки
            if (!empty($data['rates'])) {
                $this->addRates($employeeId, $data['rates']);
            }
            
            return $employeeId;
        }
        
        return false;
    }
    
    public function findById($id) {
        $sql = "SELECT e.*, u.username, u.email, u.is_blocked,
                DATE_FORMAT(e.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
                DATE_FORMAT(e.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
                DATE_FORMAT(e.last_login, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as last_login
                FROM employees e
                JOIN users u ON e.user_id = u.id
                WHERE e.id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            $employee['facilities'] = $this->getFacilities($id);
            $employee['rates'] = $this->getRates($id);
        }
        
        return $employee;
    }
    
    public function findByTelegramId($telegramId) {
        $sql = "SELECT e.*, u.username, u.email, u.is_blocked,
                DATE_FORMAT(e.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
                DATE_FORMAT(e.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
                DATE_FORMAT(e.last_login, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as last_login
                FROM employees e
                JOIN users u ON e.user_id = u.id
                WHERE e.telegram_id = :telegram_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['telegram_id' => $telegramId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getFormattedCreatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->created_at));
    }
    
    public function getFormattedUpdatedAt() {
        return date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->updated_at));
    }
    
    public function getFormattedLastLogin() {
        return $this->last_login ? date($_ENV['DB_DATETIME_DISPLAY_FORMAT'], strtotime($this->last_login)) : null;
    }
    
    private function assignFacilities($employeeId, $facilityIds) {
        $sql = "INSERT INTO employee_facilities (employee_id, facility_id) 
                VALUES (:employee_id, :facility_id)";
                
        $stmt = $this->db->prepare($sql);
        
        foreach ($facilityIds as $facilityId) {
            $stmt->execute([
                'employee_id' => $employeeId,
                'facility_id' => $facilityId
            ]);
        }
    }
    
    private function addRates($employeeId, $rates) {
        $sql = "INSERT INTO employee_rates (employee_id, facility_id, hourly_rate, start_date) 
                VALUES (:employee_id, :facility_id, :hourly_rate, :start_date)";
                
        $stmt = $this->db->prepare($sql);
        
        foreach ($rates as $rate) {
            $stmt->execute([
                'employee_id' => $employeeId,
                'facility_id' => $rate['facility_id'] ?? null,
                'hourly_rate' => $rate['hourly_rate'],
                'start_date' => date($_ENV['DB_DATETIME_FORMAT'], strtotime($rate['start_date']))
            ]);
        }
    }
    
    private function getFacilities($employeeId) {
        $sql = "SELECT f.*,
                DATE_FORMAT(f.created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
                DATE_FORMAT(f.updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at
                FROM facilities f
                JOIN employee_facilities ef ON f.id = ef.facility_id
                WHERE ef.employee_id = :employee_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRates($employeeId) {
        $sql = "SELECT 
                  employee_id,
                  facility_id,
                  hourly_rate,
                  DATE_FORMAT(start_date, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as start_date,
                  DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at
                FROM employee_rates
                WHERE employee_id = :employee_id 
                ORDER BY start_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 