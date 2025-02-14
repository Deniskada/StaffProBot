<?php
require_once 'bootstrap.php';

use Spbot\Core\MigrationManager;

try {
    $manager = new MigrationManager();
    
    $action = $argv[1] ?? 'migrate';
    
    switch ($action) {
        case 'migrate':
            $count = $manager->runMigrations();
            echo "Migrations completed. {$count} migrations executed.\n";
            break;
            
        case 'rollback':
            $count = $manager->rollback();
            echo "Rollback completed. {$count} migrations rolled back.\n";
            break;
            
        default:
            echo "Unknown action: {$action}\n";
            echo "Available actions: migrate, rollback\n";
            exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 