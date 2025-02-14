<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Models\Facility;

class FacilityController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'employer']);
    }
    
    public function index() {
        $facilities = $this->user->role === 'admin' 
            ? Facility::all()
            : Facility::where('employer_id = ?', [$this->user->id]);
            
        $this->view->render('facilities/index', [
            'facilities' => $facilities
        ]);
    }
    
    public function create() {
        $this->view->render('facilities/create');
    }
    
    public function store() {
        $data = $this->validate($this->request->post(), [
            'name' => 'required|max:255',
            'address' => 'required',
            'city' => 'required|max:100',
            'coordinates' => 'max:50'
        ]);
        
        $data['employer_id'] = $this->user->role === 'admin' 
            ? $this->request->post('employer_id')
            : $this->user->id;
            
        $facility = new Facility();
        $facility->fill($data)->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['id' => $facility->id]);
        }
        
        $_SESSION['success'] = 'Объект успешно создан';
        $this->redirect('/facilities');
    }
    
    public function edit($id) {
        $facility = Facility::find($id);
        
        if (!$facility || ($this->user->role !== 'admin' && $facility->employer_id !== $this->user->id)) {
            return $this->view->renderError(404, 'Объект не найден');
        }
        
        $this->view->render('facilities/edit', [
            'facility' => $facility
        ]);
    }
    
    public function update($id) {
        $facility = Facility::find($id);
        
        if (!$facility || ($this->user->role !== 'admin' && $facility->employer_id !== $this->user->id)) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Объект не найден'], 404);
            }
            return $this->view->renderError(404, 'Объект не найден');
        }
        
        $data = $this->validate($this->request->post(), [
            'name' => 'required|max:255',
            'address' => 'required',
            'city' => 'required|max:100',
            'coordinates' => 'max:50',
            'status' => 'required'
        ]);
        
        $facility->fill($data)->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Объект успешно обновлен';
        $this->redirect('/facilities');
    }
    
    public function delete($id) {
        $facility = Facility::find($id);
        
        if (!$facility || ($this->user->role !== 'admin' && $facility->employer_id !== $this->user->id)) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Объект не найден'], 404);
            }
            return $this->view->renderError(404, 'Объект не найден');
        }
        
        $facility->delete();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $_SESSION['success'] = 'Объект успешно удален';
        $this->redirect('/facilities');
    }
} 