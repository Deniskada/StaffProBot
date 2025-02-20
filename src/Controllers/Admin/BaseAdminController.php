<?php
namespace Spbot\Controllers\Admin;

use Spbot\Controllers\BaseController;

class BaseAdminController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
    }
    
    protected function requireAdmin() {
        if (!$this->isAuthenticated()) {
            return $this->redirect('/login');
        }
        
        $user = $this->session->getUser();
        if ($user['role'] !== 'admin') {
            return $this->redirect('/dashboard');
        }
    }
    
    protected function view($name, $data = []) {
        error_log("=== Admin View Debug ===");
        error_log("Original view name: " . $name);
        
        // Добавляем sidebar для админ-панели
        $data['admin_sidebar'] = $this->renderAdminSidebar();
        
        // Не модифицируем имя view, передаем как есть
        return parent::view($name, $data);
    }
    
    private function renderAdminSidebar() {
        ob_start();
        include dirname(dirname(dirname(__DIR__))) . '/resources/views/admin/partials/sidebar.php';
        return ob_get_clean();
    }
} 