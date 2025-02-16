<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\models\Shift;
use Spbot\models\Facility;

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        switch ($this->user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'employer':
                return $this->employerDashboard();
            case 'employee':
                return $this->employeeDashboard();
        }
    }
    
    private function adminDashboard() {
        $stats = [
            'users' => [
                'total' => \Spbot\models\User::count(),
                'employers' => \Spbot\models\User::count("role = 'employer'"),
                'employees' => \Spbot\models\User::count("role = 'employee'")
            ],
            'facilities' => Facility::count(),
            'shifts' => [
                'total' => Shift::count(),
                'active' => Shift::count("status = 'active'"),
                'completed' => Shift::count("status = 'completed'")
            ],
            'payments' => [
                'total' => \Spbot\models\Payment::count(),
                'pending' => \Spbot\models\Payment::count("status = 'pending'")
            ]
        ];
        
        $this->view->render('admin/dashboard', [
            'stats' => $stats,
            'recentUsers' => \Spbot\models\User::getRecent(),
            'recentShifts' => Shift::getRecent()
        ]);
    }
    
    private function employerDashboard() {
        $stats = [
            'facilities' => Facility::count("employer_id = {$this->user->id}"),
            'employees' => \Spbot\models\User::count("role = 'employee'"),
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
} 