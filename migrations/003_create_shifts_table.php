<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateShiftsTable extends Migration {
    protected $table = 'shifts';
    
    public function up() {
        $this->createTable([
            'id' => 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY',
            'employee_id' => 'INT UNSIGNED NOT NULL',
            'employer_id' => 'INT UNSIGNED NOT NULL',
            'facility_id' => 'INT UNSIGNED NOT NULL',
            'start_time' => 'DATETIME NOT NULL',
            'end_time' => 'DATETIME NULL',
            'status' => "ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active'",
            'payment_status' => "ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'",
            'hourly_rate' => 'DECIMAL(10,2) NOT NULL',
            'total_hours' => 'DECIMAL(10,2) NULL',
            'total_amount' => 'DECIMAL(10,2) NULL',
            'created_at' => 'DATETIME NOT NULL',
            'updated_at' => 'DATETIME NOT NULL'
        ]);
        
        $this->addIndex('shifts_employee_id_idx', 'employee_id');
        $this->addIndex('shifts_employer_id_idx', 'employer_id');
        $this->addIndex('shifts_facility_id_idx', 'facility_id');
        
        $this->addForeignKey(
            'shifts_employee_id_fk',
            'employee_id',
            'users(id) ON DELETE CASCADE'
        );
        $this->addForeignKey(
            'shifts_employer_id_fk',
            'employer_id',
            'users(id) ON DELETE CASCADE'
        );
        $this->addForeignKey(
            'shifts_facility_id_fk',
            'facility_id',
            'facilities(id) ON DELETE CASCADE'
        );
    }
    
    public function down() {
        $this->dropForeignKey('shifts_employee_id_fk');
        $this->dropForeignKey('shifts_employer_id_fk');
        $this->dropForeignKey('shifts_facility_id_fk');
        $this->dropTable();
    }
} 