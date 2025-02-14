<?php
namespace Spbot\Controllers\Api;

use Spbot\Models\Shift;
use Spbot\Models\Facility;

class EmployeeController extends ApiController {
    public function __construct() {
        parent::__construct();
        $this->requireRole('employee');
    }
    
    public function shifts() {
        if ($this->request->isGet()) {
            $status = $this->request->get('status');
            $shifts = Shift::getByEmployee($this->user->id, $status);
            return $this->jsonSuccess(['shifts' => $shifts]);
        }
    }
    
    public function startShift() {
        $this->validateRequest([
            'facility_id' => 'required|numeric'
        ]);
        
        // Проверяем, нет ли уже активной смены
        $activeShift = Shift::getActiveByEmployee($this->user->id);
        if ($activeShift) {
            return $this->jsonError('You already have an active shift');
        }
        
        $data = $this->request->getJson();
        $facility = Facility::find($data['facility_id']);
        
        if (!$facility || !$facility->isActive()) {
            return $this->jsonError('Invalid facility');
        }
        
        $shift = new Shift();
        $shift->fill([
            'employee_id' => $this->user->id,
            'employer_id' => $facility->employer_id,
            'facility_id' => $facility->id,
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ]);
        
        if (!$shift->save()) {
            return $this->jsonError('Failed to start shift');
        }
        
        $this->log('shift_started', ['shift_id' => $shift->id]);
        return $this->jsonSuccess(['shift' => $shift]);
    }
    
    public function endShift() {
        $activeShift = Shift::getActiveByEmployee($this->user->id);
        if (!$activeShift) {
            return $this->jsonError('No active shift found');
        }
        
        $shift = new Shift();
        $shift->fill((array)$activeShift);
        
        if (!$shift->complete()) {
            return $this->jsonError('Failed to end shift');
        }
        
        $this->log('shift_ended', ['shift_id' => $shift->id]);
        return $this->jsonSuccess([
            'shift' => $shift,
            'total_hours' => $shift->total_hours,
            'total_amount' => $shift->total_amount
        ]);
    }
    
    public function nearbyFacilities() {
        $this->validateRequest([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);
        
        $data = $this->request->getJson();
        $facilities = Facility::searchNearby(
            $data['latitude'],
            $data['longitude'],
            $this->request->get('radius', 5)
        );
        
        return $this->jsonSuccess(['facilities' => $facilities]);
    }
} 