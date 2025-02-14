<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateFacilitiesTable extends Migration {
    protected $table = 'facilities';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'employer_id' => 'INT UNSIGNED NOT NULL',
            'name' => 'VARCHAR(255) NOT NULL',
            'address' => 'TEXT NOT NULL',
            'city' => 'VARCHAR(100) NOT NULL',
            'coordinates' => 'VARCHAR(50) NULL',
            'description' => 'TEXT NULL',
            'status' => "ENUM('active', 'inactive') NOT NULL DEFAULT 'active'",
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('facilities_employer_id_idx', 'employer_id');
        $this->addIndex('facilities_city_idx', 'city');
        $this->addForeignKey(
            'facilities_employer_id_fk',
            'employer_id',
            'users(id) ON DELETE CASCADE'
        );
    }
    
    public function down() {
        $this->dropForeignKey('facilities_employer_id_fk');
        $this->dropTable();
    }
} 