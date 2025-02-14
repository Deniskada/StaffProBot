<?php
namespace Spbot\Models;

use Spbot\Core\Model;

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
    
    public static function getByEmployer($employerId) {
        $facility = new static();
        return $facility->db->fetchAll(
            "SELECT * FROM {$facility->table} WHERE employer_id = ?",
            [$employerId]
        );
    }
    
    public static function searchNearby($lat, $lng, $radius = 5) {
        $facility = new static();
        // Используем формулу гаверсинусов для поиска в радиусе
        $sql = "SELECT *, 
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