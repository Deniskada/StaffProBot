<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Models\Shift;
use Spbot\Models\Facility;

class ShiftController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        if (!$this->session->get('user')) {
            return $this->redirect('/login');
        }
        
        $user = $this->session->get('user');
        
        // Получаем смены в зависимости от роли пользователя
        $shifts = [];
        if ($user['role'] === 'admin') {
            $shifts = Shift::getAll(); // Для админа показываем все смены
        } elseif ($user['role'] === 'employer') {
            $shifts = Shift::getAllByEmployer($user['id']); // Для работодателя только его смены
        } else {
            $shifts = Shift::getAllByEmployee($user['id']); // Для работника только его смены
        }
        
        return $this->view('shifts/index', [
            'title' => 'Смены',
            'user' => $user,
            'shifts' => $shifts ?? []
        ]);
    }
    
    public function start() {
        $this->requireRole('employee');
        
        $data = $this->validate($this->request->post(), [
            'facility_id' => 'required|numeric'
        ]);
        
        $facility = Facility::find($data['facility_id']);
        if (!$facility || $facility->status !== 'active') {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Объект недоступен'], 400);
            }
            $_SESSION['error'] = 'Объект недоступен';
            return $this->back();
        }
        
        // Проверяем, нет ли уже активной смены
        $activeShift = Shift::findActiveByEmployee($this->user->id);
        if ($activeShift) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'У вас уже есть активная смена'], 400);
            }
            $_SESSION['error'] = 'У вас уже есть активная смена';
            return $this->back();
        }
        
        $shift = new Shift();
        $shift->fill([
            'employee_id' => $this->user->id,
            'employer_id' => $facility->employer_id,
            'facility_id' => $facility->id,
            'start_time' => date($_ENV['DB_DATETIME_FORMAT']),
            'hourly_rate' => $this->getHourlyRate($facility)
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['id' => $shift->id]);
        }
        
        $_SESSION['success'] = 'Смена успешно начата';
        $this->redirect('/shifts/current');
    }
    
    public function end($id) {
        $shift = Shift::find($id);
        
        if (!$shift || $shift->employee_id !== $this->user->id || $shift->status !== 'active') {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Смена не найдена'], 404);
            }
            return $this->view->renderError(404, 'Смена не найдена');
        }
        
        $endTime = date($_ENV['DB_DATETIME_FORMAT']);
        $totalHours = round((strtotime($endTime) - strtotime($shift->start_time)) / 3600, 2);
        $totalAmount = round($totalHours * $shift->hourly_rate, 2);
        
        $shift->fill([
            'end_time' => $endTime,
            'status' => 'completed',
            'total_hours' => $totalHours,
            'total_amount' => $totalAmount
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json([
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount
            ]);
        }
        
        $_SESSION['success'] = 'Смена успешно завершена';
        $this->redirect('/shifts');
    }
    
    public function cancel($id) {
        $shift = Shift::find($id);
        
        if (!$shift || ($this->user->role === 'employee' && $shift->employee_id !== $this->user->id)) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Смена не найдена'], 404);
            }
            return $this->view->renderError(404, 'Смена не найдена');
        }
        
        $shift->fill([
            'status' => 'cancelled',
            'payment_status' => 'cancelled'
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Смена отменена';
        $this->redirect('/shifts');
    }
    
    private function getHourlyRate($facility) {
        // Здесь может быть логика расчета почасовой ставки
        // в зависимости от объекта, времени и других факторов
        return 500.00; // Базовая ставка
    }
} 