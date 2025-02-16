<?php
namespace Spbot\Controllers\Api;

use Spbot\Core\Controller;
use Spbot\Core\JWT;

class ApiController extends Controller {
    protected $user;
    
    public function __construct() {
        parent::__construct();
        $this->authenticateRequest();
    }
    
    protected function authenticateRequest() {
        $token = $this->request->getAuthToken();
        
        if (!$token) {
            $this->jsonError('Unauthorized', 401);
            exit;
        }
        
        $payload = JWT::decode($token);
        if (!$payload || 
            (isset($payload['exp']) && $payload['exp'] < time()) || 
            (time() - $payload['iat'] > $_ENV['AUTH_TOKEN_LIFETIME'])) {
            $this->jsonError('Invalid or expired token', 401);
            exit;
        }
        
        $this->user = \Spbot\Models\User::find($payload['user_id']);
        if (!$this->user || !$this->user->isActive()) {
            $this->jsonError('User not found or inactive', 401);
            exit;
        }
    }
    
    protected function requireRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($this->user->role, $roles)) {
            $this->jsonError('Forbidden', 403);
            exit;
        }
    }
    
    protected function validateRequest($rules) {
        $validator = new \Spbot\Core\Validator($this->request->getJson(), $rules);
        
        if (!$validator->validate()) {
            $this->jsonError([
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 422);
            exit;
        }
    }
    
    protected function log($action, $details = []) {
        \Spbot\Models\SystemLog::log('api', $action, array_merge(
            $details,
            ['user_id' => $this->user->id, 'ip' => $this->request->getClientIp()]
        ));
    }
} 