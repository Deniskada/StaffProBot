<?php
namespace Spbot\Core;

class Validator {
    private $data;
    private $rules;
    private $errors = [];
    
    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }
    
    public function validate() {
        foreach ($this->rules as $field => $rules) {
            $rules = explode('|', $rules);
            
            foreach ($rules as $rule) {
                $params = [];
                
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule);
                    $params = explode(',', $param);
                }
                
                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $params)) {
                        break;
                    }
                }
            }
        }
        
        return empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    private function validateRequired($field) {
        $value = $this->data[$field] ?? null;
        
        if ($value === null || $value === '') {
            $this->errors[$field][] = $_ENV['VALIDATION_REQUIRED'];
            return false;
        }
        return true;
    }
    
    private function validateEmail($field) {
        if (!isset($this->data[$field])) return true;
        
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $_ENV['VALIDATION_EMAIL'];
            return false;
        }
        return true;
    }
    
    private function validateMin($field, $params) {
        if (!isset($this->data[$field])) return true;
        
        $min = $params[0];
        $value = $this->data[$field];
        
        if (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->errors[$field][] = sprintf($_ENV['VALIDATION_MIN'], $min);
                return false;
            }
        } else if (is_numeric($value)) {
            if ($value < $min) {
                $this->errors[$field][] = sprintf($_ENV['VALIDATION_MIN_NUMERIC'], $min);
                return false;
            }
        }
        return true;
    }
    
    private function validateMax($field, $params) {
        if (!isset($this->data[$field])) return true;
        
        $max = $params[0];
        $value = $this->data[$field];
        
        if (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->errors[$field][] = sprintf($_ENV['VALIDATION_MAX'], $max);
                return false;
            }
        } else if (is_numeric($value)) {
            if ($value > $max) {
                $this->errors[$field][] = sprintf($_ENV['VALIDATION_MAX_NUMERIC'], $max);
                return false;
            }
        }
        return true;
    }
    
    private function validateNumeric($field) {
        if (!isset($this->data[$field])) return true;
        
        if (!is_numeric($this->data[$field])) {
            $this->errors[$field][] = $_ENV['VALIDATION_NUMERIC'];
            return false;
        }
        return true;
    }
    
    private function validateDate($field) {
        if (!isset($this->data[$field])) return true;
        
        if (!strtotime($this->data[$field])) {
            $this->errors[$field][] = $_ENV['VALIDATION_DATE'];
            return false;
        }
        return true;
    }
    
    private function validateIn($field, $params) {
        if (!isset($this->data[$field])) return true;
        
        if (!in_array($this->data[$field], $params)) {
            $this->errors[$field][] = sprintf($_ENV['VALIDATION_IN'], implode(', ', $params));
            return false;
        }
        return true;
    }
    
    private function validateUnique($field, $params) {
        if (!isset($this->data[$field])) return true;
        
        $table = $params[0];
        $exceptId = $params[1] ?? null;
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?";
        $sqlParams = [$this->data[$field]];
        
        if ($exceptId) {
            $sql .= " AND id != ?";
            $sqlParams[] = $exceptId;
        }
        
        $result = Database::getInstance()->fetch($sql, $sqlParams);
        
        if ($result['count'] > 0) {
            $this->errors[$field][] = $_ENV['VALIDATION_UNIQUE'];
            return false;
        }
        return true;
    }
}