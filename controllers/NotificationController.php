<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\models\Notification;

class NotificationController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function index() {
        $notifications = Notification::where(
            'user_id = ? ORDER BY created_at DESC',
            [$this->user->id]
        );
        
        $this->view->render('notifications/index', [
            'notifications' => $notifications
        ]);
    }
    
    public function read($id) {
        $notification = Notification::find($id);
        
        if (!$notification || $notification->user_id !== $this->user->id) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Уведомление не найдено'], 404);
            }
            return $this->view->renderError(404, 'Уведомление не найдено');
        }
        
        $notification->fill([
            'status' => 'read',
            'read_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ])->save();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $this->redirect('/notifications');
    }
    
    public function readAll() {
        Notification::where('user_id = ? AND status = ?', [
            $this->user->id,
            'unread'
        ])->update([
            'status' => 'read',
            'read_at' => date($_ENV['DB_DATETIME_FORMAT'])
        ]);
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $this->redirect('/notifications');
    }
    
    public function delete($id) {
        $notification = Notification::find($id);
        
        if (!$notification || $notification->user_id !== $this->user->id) {
            if ($this->request->isAjax()) {
                return $this->json(['error' => 'Уведомление не найдено'], 404);
            }
            return $this->view->renderError(404, 'Уведомление не найдено');
        }
        
        $notification->delete();
        
        if ($this->request->isAjax()) {
            return $this->json(['success' => true]);
        }
        
        $this->redirect('/notifications');
    }
} 