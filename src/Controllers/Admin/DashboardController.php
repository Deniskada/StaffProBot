<?php
namespace Spbot\Controllers\Admin;

class DashboardController extends BaseAdminController {
    
    public function index() {
        error_log("=== Admin Dashboard Index Debug ===");
        
        // Получаем статистику через API
        $statsController = new \Spbot\Controllers\Api\Admin\StatsController();
        $stats = $statsController->getStats();
        
        error_log("Stats data: " . print_r($stats, true));
        
        // Явно указываем путь к view и layout
        return $this->view('admin/dashboard', [
            'title' => 'Панель управления',
            'stats' => $stats,
            'page' => 'dashboard',
            'layout' => 'admin/layout'  // Явно указываем layout
        ]);
    }
} 