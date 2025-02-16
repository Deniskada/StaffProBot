<?php
namespace Spbot\Core;

abstract class Controller {
    protected $view;
    protected $request;
    protected $db;
    protected $user;
    
    public function __construct() {
        $this->view = new View();
        $this->request = new Request();
        $this->db = Database::getInstance();
        $this->initUser();
    }
    
    protected function initUser() {
        $token = $this->request->getAuthToken();
        if ($token) {
            $payload = JWT::decode($token);
            if ($payload && isset($payload['user_id'])) {
                $this->user = \Spbot\Models\User::find($payload['user_id']);
            }
        }
    }
    
    protected function requireAuth() {
        if (!$this->user) {
            if ($this->request->isAjax()) {
                http_response_code(401);
                echo json_encode(['error' => $_ENV['ERROR_UNAUTHORIZED']]);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
    
    protected function requireRole($roles) {
        $this->requireAuth();
        
        $roles = (array)$roles;
        if (!in_array($this->user->role, $roles)) {
            if ($this->request->isAjax()) {
                http_response_code(403);
                echo json_encode(['error' => $_ENV['ERROR_FORBIDDEN']]);
                exit;
            }
            $this->view->renderError(403, $_ENV['ERROR_ACCESS_DENIED']);
            exit;
        }
    }
    
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function back() {
        $referer = $this->request->server('HTTP_REFERER');
        header('Location: ' . ($referer ?: '/'));
        exit;
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);
            
            foreach ($fieldRules as $rule) {
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule);
                } else {
                    $param = null;
                }
                
                $value = $data[$field] ?? null;
                
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = $_ENV['VALIDATION_REQUIRED'];
                        }
                        break;
                        
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = $_ENV['VALIDATION_EMAIL'];
                        }
                        break;
                        
                    case 'min':
                        if (strlen($value) < $param) {
                            $errors[$field][] = sprintf($_ENV['VALIDATION_MIN'], $param);
                        }
                        break;
                        
                    case 'max':
                        if (strlen($value) > $param) {
                            $errors[$field][] = sprintf($_ENV['VALIDATION_MAX'], $param);
                        }
                        break;
                        
                    case 'numeric':
                        if (!is_numeric($value)) {
                            $errors[$field][] = $_ENV['VALIDATION_NUMERIC'];
                        }
                        break;
                }
            }
        }
        
        if (!empty($errors)) {
            if ($this->request->isAjax()) {
                $this->json(['errors' => $errors], 422);
            }
            
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $data;
            $this->back();
        }
        
        return $data;
    }
} 