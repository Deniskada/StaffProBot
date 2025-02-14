<?php
class EmployeeController extends Controller {
    private $employee;
    private $shift;
    
    public function __construct() {
        parent::__construct();
        $this->employee = new Employee();
        $this->shift = new Shift();
        $this->requireAuth();
    }
    
    public function profile() {
        if ($_SESSION['user']['role'] !== 'employee') {
            return $this->json(['message' => 'Доступ запрещен'], 403);
        }
        
        $employee = $this->employee->findById($_SESSION['user']['employee_id']);
        return $this->json($employee);
    }
    
    public function startShift() {
        if ($_SESSION['user']['role'] !== 'employee') {
            return $this->json(['message' => 'Доступ запрещен'], 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['facility_id']) || empty($data['coordinates'])) {
            return $this->json(['message' => 'Неверные данные'], 400);
        }
        
        // Проверяем, нет ли уже активной смены
        $activeShift = $this->shift->findActive($_SESSION['user']['employee_id']);
        if ($activeShift) {
            return $this->json(['message' => 'У вас уже есть активная смена'], 400);
        }
        
        // Получаем актуальную ставку для объекта
        $rate = $this->employee->getCurrentRate(
            $_SESSION['user']['employee_id'], 
            $data['facility_id']
        );
        
        if (!$rate) {
            return $this->json(['message' => 'Ставка не найдена'], 400);
        }
        
        $shiftData = [
            'employee_id' => $_SESSION['user']['employee_id'],
            'facility_id' => $data['facility_id'],
            'start_time' => date('Y-m-d H:i:s'),
            'hourly_rate' => $rate['hourly_rate']
        ];
        
        if ($this->shift->create($shiftData)) {
            return $this->json(['message' => 'Смена начата']);
        }
        
        return $this->json(['message' => 'Ошибка при начале смены'], 500);
    }
    
    public function endShift() {
        if ($_SESSION['user']['role'] !== 'employee') {
            return $this->json(['message' => 'Доступ запрещен'], 403);
        }
        
        $activeShift = $this->shift->findActive($_SESSION['user']['employee_id']);
        
        if (!$activeShift) {
            return $this->json(['message' => 'Активная смена не найдена'], 404);
        }
        
        if ($this->shift->end($activeShift['id'])) {
            return $this->json(['message' => 'Смена завершена']);
        }
        
        return $this->json(['message' => 'Ошибка при завершении смены'], 500);
    }
    
    public function getShifts() {
        if ($_SESSION['user']['role'] !== 'employee') {
            return $this->json(['message' => 'Доступ запрещен'], 403);
        }
        
        $filters = [
            'employee_id' => $_SESSION['user']['employee_id'],
            'start_date' => $_GET['start_date'] ?? null,
            'end_date' => $_GET['end_date'] ?? null
        ];
        
        $shifts = $this->shift->getHistory($filters);
        return $this->json($shifts);
    }
} 