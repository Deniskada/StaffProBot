<?php
class Employee {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO employees (user_id, first_name, last_name, telegram_id, notes) 
                VALUES (:user_id, :first_name, :last_name, :telegram_id, :notes)";
                
        $stmt = $this->db->prepare($sql);
        
        $success = $stmt->execute([
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'telegram_id' => $data['telegram_id'],
            'notes' => $data['notes'] ?? null
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
        $sql = "SELECT e.*, u.username, u.email, u.is_blocked 
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
        $sql = "SELECT e.*, u.username, u.email, u.is_blocked 
                FROM employees e
                JOIN users u ON e.user_id = u.id
                WHERE e.telegram_id = :telegram_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['telegram_id' => $telegramId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                'start_date' => $rate['start_date']
            ]);
        }
    }
    
    private function getFacilities($employeeId) {
        $sql = "SELECT f.* FROM facilities f
                JOIN employee_facilities ef ON f.id = ef.facility_id
                WHERE ef.employee_id = :employee_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRates($employeeId) {
        $sql = "SELECT * FROM employee_rates 
                WHERE employee_id = :employee_id 
                ORDER BY start_date DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 