<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Models\User;
use Spbot\Models\Shift;
use Spbot\Models\SystemLog;
use Spbot\Models\Payment;

class AdminController extends Controller {
    private $user;
    private $employee;
    private $facility;
    
    public function __construct() {
        parent::__construct();
        $this->user = new User();
        $this->employee = new Employee();
        $this->facility = new Facility();
        $this->requireAuth();
        $this->requireRole('admin');
    }
    
    public function getEmployers() {
        $employers = $this->user->findByRole('employer');
        
        foreach ($employers as &$employer) {
            $employer['facilities'] = $this->facility->findByEmployer($employer['id']);
            $employer['employees_count'] = count($this->employee->findByEmployer($employer['id']));
        }
        
        return $this->json($employers);
    }
    
    public function blockUser($userId) {
        $user = $this->user->findById($userId);
        
        if (!$user) {
            return $this->json(['message' => 'Пользователь не найден'], 404);
        }
        
        if ($this->user->block($userId)) {
            return $this->json(['message' => 'Пользователь заблокирован']);
        }
        
        return $this->json(['message' => 'Ошибка при блокировке пользователя'], 500);
    }
    
    public function unblockUser($userId) {
        $user = $this->user->findById($userId);
        
        if (!$user) {
            return $this->json(['message' => 'Пользователь не найден'], 404);
        }
        
        if ($this->user->block($userId, false)) {
            return $this->json(['message' => 'Пользователь разблокирован']);
        }
        
        return $this->json(['message' => 'Ошибка при разблокировке пользователя'], 500);
    }
    
    public function getStats() {
        $stats = [
            'total_users' => $this->user->count(),
            'active_users' => $this->user->countActive(),
            'total_facilities' => $this->facility->count(),
            'active_facilities' => $this->facility->countActive(),
            'total_shifts' => $this->shift->count(),
            'active_shifts' => $this->shift->countActive(),
            'revenue' => $this->calculateRevenue(),
            'popular_locations' => $this->facility->getPopularLocations(),
            'peak_hours' => $this->shift->getPeakHours()
        ];
        
        return $this->json($stats);
    }
    
    private function calculateRevenue($period = 'month') {
        // TODO: Реализовать расчет выручки
        return 0;
    }
    
    public function dashboard() {
        $stats = [
            'users' => [
                'total' => User::count(),
                'employers' => User::countByRole('employer'),
                'employees' => User::countByRole('employee')
            ],
            'shifts' => [
                'total' => Shift::count(),
                'active' => Shift::countByStatus('active'),
                'completed' => Shift::countByStatus('completed')
            ],
            'payments' => Payment::getStatistics()
        ];
        
        $recentLogs = SystemLog::getRecent(10);
        $recentUsers = User::getRecent(5);
        
        $this->view->render('admin/dashboard', [
            'stats' => $stats,
            'logs' => $recentLogs,
            'users' => $recentUsers
        ]);
    }
    
    public function users() {
        $page = $this->request->get('page', 1);
        $search = $this->request->get('search');
        $role = $this->request->get('role');
        
        $users = User::search($search, $role, $page);
        
        if ($this->request->isAjax()) {
            return $this->jsonSuccess(['users' => $users]);
        }
        
        $this->view->render('admin/users', [
            'users' => $users,
            'search' => $search,
            'role' => $role
        ]);
    }
    
    public function userAction() {
        $this->validateRequest([
            'user_id' => 'required|numeric',
            'action' => 'required|in:block,unblock,delete'
        ]);
        
        $data = $this->request->getJson();
        $user = User::find($data['user_id']);
        
        if (!$user) {
            return $this->jsonError('User not found');
        }
        
        switch ($data['action']) {
            case 'block':
                $user->status = 'blocked';
                break;
            case 'unblock':
                $user->status = 'active';
                break;
            case 'delete':
                if ($user->role === 'admin') {
                    return $this->jsonError('Cannot delete admin user');
                }
                $user->delete();
                break;
        }
        
        if (isset($user->id)) {
            $user->save();
        }
        
        SystemLog::log('admin', "User {$data['action']}", [
            'user_id' => $user->id,
            'admin_id' => $this->session->getUser()['id']
        ]);
        
        return $this->jsonSuccess(['message' => 'Action completed successfully']);
    }
    
    public function logs() {
        $page = $this->request->get('page', 1);
        $level = $this->request->get('level');
        $from = $this->request->get('from');
        $to = $this->request->get('to');
        
        $logs = SystemLog::search($level, $from, $to, $page);
        $stats = SystemLog::getStatistics();
        
        if ($this->request->isAjax()) {
            return $this->jsonSuccess(['logs' => $logs]);
        }
        
        $this->view->render('admin/logs', [
            'logs' => $logs,
            'stats' => $stats,
            'level' => $level,
            'from' => $from,
            'to' => $to
        ]);
    }
} 