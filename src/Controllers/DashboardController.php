<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Models\Shift;
use Spbot\Models\Facility;
use Spbot\Models\User;
use Spbot\Models\Payment;

class DashboardController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        $user = $this->session->getUser();
        
        try {
            error_log("=== Dashboard Stats Collection ===");
            
            // Добавляем обработку ошибок при сборе статистики
            $stats = [
                'users_count' => User::count() ?? 0,
                'active_users' => User::countActive() ?? 0,
                'new_users' => User::countNew(7) ?? 0,
                'active_facilities' => Facility::count("status = 'active'") ?? 0,
                'total_facilities' => Facility::count() ?? 0,
                'active_shifts' => Shift::count("status = 'active'") ?? 0,
                'completed_shifts' => Shift::count("status = 'completed'") ?? 0,
                'total_shifts' => Shift::count() ?? 0,
                'revenue' => Payment::sum('amount', "status = 'completed'") ?? 0
            ];
            
            error_log("Collected stats: " . print_r($stats, true));
            
            $data = [
                'title' => 'Панель управления',
                'layout' => 'admin/layout',
                'stats' => $stats,
                'user' => $user,
                'admin_sidebar' => $this->getAdminMenu(),
                'error' => null
            ];
            
        } catch (\Exception $e) {
            error_log("Error collecting stats: " . $e->getMessage());
            
            $data = [
                'title' => 'Панель управления',
                'layout' => 'admin/layout',
                'stats' => null,
                'user' => $user,
                'admin_sidebar' => $this->getAdminMenu(),
                'error' => $e->getMessage()
            ];
        }
        
        return $this->view('admin/dashboard', $data);
    }
    
    private function adminDashboard() {
        $stats = [
            'users' => [
                'total' => \Spbot\Models\User::count(),
                'employers' => \Spbot\Models\User::count("role = 'employer'"),
                'employees' => \Spbot\Models\User::count("role = 'employee'")
            ],
            'facilities' => Facility::count(),
            'shifts' => [
                'total' => Shift::count(),
                'active' => Shift::count("status = 'active'"),
                'completed' => Shift::count("status = 'completed'")
            ],
            'payments' => [
                'total' => \Spbot\Models\Payment::count(),
                'pending' => \Spbot\Models\Payment::count("status = 'pending'")
            ]
        ];
        
        $this->view->render('admin/dashboard', [
            'stats' => $stats,
            'recentUsers' => \Spbot\Models\User::getRecent(),
            'recentShifts' => Shift::getRecent()
        ]);
    }
    
    private function employerDashboard() {
        $stats = [
            'facilities' => Facility::count("employer_id = {$this->user->id}"),
            'employees' => \Spbot\Models\User::count("role = 'employee'"),
            'shifts' => [
                'active' => Shift::count("employer_id = {$this->user->id} AND status = 'active'"),
                'completed' => Shift::count("employer_id = {$this->user->id} AND status = 'completed'")
            ]
        ];
        
        $this->view->render('employer/dashboard', [
            'stats' => $stats,
            'activeShifts' => Shift::getActive($this->user->id),
            'facilities' => Facility::getByEmployer($this->user->id)
        ]);
    }
    
    private function employeeDashboard() {
        $stats = [
            'shifts' => [
                'total' => Shift::count("employee_id = {$this->user->id}"),
                'active' => Shift::count("employee_id = {$this->user->id} AND status = 'active'"),
                'completed' => Shift::count("employee_id = {$this->user->id} AND status = 'completed'")
            ]
        ];
        
        $this->view->render('employee/dashboard', [
            'stats' => $stats,
            'currentShift' => Shift::getCurrentByEmployee($this->user->id),
            'recentShifts' => Shift::getRecentByEmployee($this->user->id)
        ]);
    }

    protected function getUserStats() {
        return [
            'total' => User::count(),
            'active' => User::countActive(),
            'new' => User::countNew(7)
        ];
    }

    protected function getFacilityStats() {
        return [
            'total' => Facility::count(),
            'active' => Facility::count("status = 'active'")
        ];
    }

    protected function getShiftStats() {
        return [
            'total' => Shift::count(),
            'active' => Shift::count("status = 'active'"),
            'completed' => Shift::count("status = 'completed'")
        ];
    }

    protected function getAdminMenu() {
        return [
            ['url' => '/admin/dashboard', 'title' => 'Панель управления'],
            ['url' => '/admin/users', 'title' => 'Пользователи'],
            ['url' => '/admin/facilities', 'title' => 'Объекты'],
            ['url' => '/admin/shifts', 'title' => 'Смены'],
            ['url' => '/admin/settings', 'title' => 'Настройки']
        ];
    }
} 