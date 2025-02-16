<?php
namespace Spbot\Migrations;

use Spbot\Core\Migration;

class CreateFacilitiesTable extends Migration {
    protected $table = 'facilities';
    
    public function up() {
        $this->createTable([
            'id' => "{$_ENV['DB_TYPE_PRIMARY_KEY']} AUTO_INCREMENT PRIMARY KEY",
            'employer_id' => "{$_ENV['DB_TYPE_FOREIGN_KEY']} NOT NULL",
            'name' => "VARCHAR({$_ENV['DB_FIELD_NAME_LENGTH']}) NOT NULL",
            'address' => "VARCHAR({$_ENV['DB_FIELD_ADDRESS_LENGTH']}) NOT NULL",
            'city' => "VARCHAR({$_ENV['DB_FIELD_CITY_LENGTH']}) NOT NULL",
            'state' => "VARCHAR({$_ENV['DB_FIELD_STATE_LENGTH']}) NOT NULL",
            'zip' => "VARCHAR({$_ENV['DB_FIELD_ZIP_LENGTH']}) NOT NULL",
            'latitude' => "DECIMAL({$_ENV['DB_FIELD_COORDINATES_SCALE']},{$_ENV['DB_FIELD_COORDINATES_PRECISION']}) NOT NULL",
            'longitude' => "DECIMAL({$_ENV['DB_FIELD_COORDINATES_SCALE']},{$_ENV['DB_FIELD_COORDINATES_PRECISION']}) NOT NULL",
            'status' => "ENUM({$_ENV['DB_ENUM_FACILITY_STATUSES']}) NOT NULL DEFAULT '{$_ENV['DB_ENUM_FACILITY_DEFAULT_STATUS']}'",
            'created_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL",
            'updated_at' => "{$_ENV['DB_TYPE_TIMESTAMP']} NOT NULL"
        ]);
        
        $this->addIndex("facilities_employer_id_{$_ENV['DB_INDEX_PREFIX']}", 'employer_id');
        $this->addIndex("facilities_city_{$_ENV['DB_INDEX_PREFIX']}", 'city');
        $this->addForeignKey(
            "facilities_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}",
            'employer_id',
            "users(id) ON DELETE {$_ENV['DB_FOREIGN_KEY_ACTION_DELETE']}"
        );
    }
    
    public function down() {
        $this->dropForeignKey("facilities_employer_id_{$_ENV['DB_FOREIGN_KEY_PREFIX']}");
        $this->dropTable();
    }
} 