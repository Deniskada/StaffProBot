<?php
namespace Spbot\models;

use Spbot\core\Model;

class Facility extends Model {
    protected $table = 'facilities';
    protected $fillable = [
        'employer_id', 'name', 'address', 'city', 
        'coordinates', 'status', 'description'
    ];
    
    public function employer() {
        return User::find($this->employer_id);
    }
    
    public function getCoordinates() {
        if (!$this->coordinates) {
            return null;
        }
        list($lat, $lng) = explode(',', $this->coordinates);
        return [
            'latitude' => trim($lat),
            'longitude' => trim($lng)
        ];
    }
    
    public function setCoordinates($lat, $lng) {
        $this->coordinates = "$lat,$lng";
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
    
    public function getFormattedStatus() {
        $envKey = 'FACILITY_STATUS_' . strtoupper($this->status);
        return $_ENV[$envKey] ?? $this->status;
    }
    
    public static function getByEmployer($employerId) {
        $facility = new static();
        return $facility->db->fetchAll(
            "SELECT *,
             DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
             DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at 
             FROM {$facility->table} 
             WHERE employer_id = ?",
            [$employerId]
        );
    }
    
    public static function searchNearby($lat, $lng, $radius = 5) {
        $facility = new static();
        // Используем формулу гаверсинусов для поиска в радиусе
        $sql = "SELECT *, 
                DATE_FORMAT(created_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as created_at,
                DATE_FORMAT(updated_at, '{$_ENV['DB_DATETIME_MYSQL_FORMAT']}') as updated_at,
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(SUBSTRING_INDEX(coordinates, ',', 1))) * 
                    cos(radians(SUBSTRING_INDEX(coordinates, ',', -1)) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(SUBSTRING_INDEX(coordinates, ',', 1)))
                )) AS distance 
                FROM {$facility->table}
                HAVING distance < ?
                ORDER BY distance";
                
        return $facility->db->fetchAll($sql, [$lat, $lng, $lat, $radius]);
    }
} 