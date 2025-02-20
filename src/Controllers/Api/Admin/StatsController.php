<?php
namespace Spbot\Controllers\Api\Admin;

use Spbot\Controllers\Api\ApiController;
use Spbot\Models\User;
use Spbot\Models\Facility;
use Spbot\Models\Shift;
use Spbot\Models\Payment;
use Spbot\Models\SystemLog;

class StatsController extends ApiController {
    public function __construct() {
        parent::__construct();
        $this->requireRole('admin');
    }
    
    public function index() {
        $stats = [
            'total_users' => User::count(),
            'active_facilities' => Facility::where('status = ?', ['active'])->count(),
            'active_shifts' => Shift::where('status = ?', ['active'])->count(),
            'revenue' => Payment::getMonthlyRevenue(),
            'activity' => $this->getActivityStats(),
            'recent_activity' => SystemLog::getRecent(10)
        ];
        
        return $this->jsonSuccess($stats);
    }
    
    private function getActivityStats() {
        // Получаем статистику активности за последние 30 дней
        $dates = [];
        $shifts = [];
        
        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = $date;
            $shifts[] = Shift::where('DATE(created_at) = ?', [$date])->count();
        }
        
        return [
            'dates' => $dates,
            'shifts' => $shifts
        ];
    }
} 