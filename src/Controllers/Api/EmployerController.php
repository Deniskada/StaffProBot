<?php
namespace Spbot\Controllers\Api;

use Spbot\Models\Facility;
use Spbot\Models\Shift;
use Spbot\Models\User;

class EmployerController extends ApiController {
    public function __construct() {
        parent::__construct();
        $this->requireRole('employer');
    }
    
    public function facilities() {
        if ($this->request->isGet()) {
            $facilities = Facility::getByEmployer($this->user->id);
            return $this->jsonSuccess(['facilities' => $facilities]);
        }
        
        if ($this->request->isPost()) {
            $this->validateRequest([
                'name' => 'required',
                'address' => 'required',
                'city' => 'required',
                'coordinates' => 'required'
            ]);
            
            $data = $this->request->getJson();
            $facility = new Facility();
            $data['employer_id'] = $this->user->id;
            $data['status'] = 'active';
            
            if (!$facility->fill($data)->save()) {
                return $this->jsonError('Failed to create facility');
            }
            
            $this->log('facility_created', ['facility_id' => $facility->id]);
            return $this->jsonSuccess(['facility' => $facility]);
        }
    }
    
    public function shifts() {
        if ($this->request->isGet()) {
            $status = $this->request->get('status');
            $facilityId = $this->request->get('facility_id');
            
            $shifts = Shift::getByEmployer($this->user->id, $status, $facilityId);
            return $this->jsonSuccess(['shifts' => $shifts]);
        }
        
        if ($this->request->isPost()) {
            $this->validateRequest([
                'facility_id' => 'required|numeric',
                'employee_id' => 'required|numeric',
                'start_time' => 'required|date',
                'hourly_rate' => 'required|numeric'
            ]);
            
            $data = $this->request->getJson();
            $shift = new Shift();
            
            // Проверяем, что объект принадлежит работодателю
            $facility = Facility::find($data['facility_id']);
            if (!$facility || $facility->employer_id !== $this->user->id) {
                return $this->jsonError('Invalid facility');
            }
            
            // Проверяем существование сотрудника
            $employee = User::find($data['employee_id']);
            if (!$employee || $employee->role !== 'employee') {
                return $this->jsonError('Invalid employee');
            }
            
            $data['employer_id'] = $this->user->id;
            $data['status'] = 'active';
            $data['payment_status'] = 'pending';
            
            if (!$shift->fill($data)->save()) {
                return $this->jsonError('Failed to create shift');
            }
            
            $this->log('shift_created', ['shift_id' => $shift->id]);
            return $this->jsonSuccess(['shift' => $shift]);
        }
    }
    
    public function statistics() {
        $period = $this->request->get('period', 'month');
        $stats = Shift::getStatistics($this->user->id, $period);
        
        return $this->jsonSuccess([
            'statistics' => $stats,
            'period' => $period
        ]);
    }
    
    public function employees() {
        $search = $this->request->get('search');
        $status = $this->request->get('status', 'active');
        
        $employees = User::searchEmployees($search, $status);
        return $this->jsonSuccess(['employees' => $employees]);
    }
} 