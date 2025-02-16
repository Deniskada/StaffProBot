<?php
namespace Spbot\Core;

class Session {
    private static $instance = null;
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', $_ENV['SESSION_LIFETIME']);
            session_set_cookie_params($_ENV['SESSION_LIFETIME']);
            session_start();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    public function clear() {
        session_unset();
        return session_destroy();
    }
    
    public function regenerate() {
        return session_regenerate_id(true);
    }
    
    public function flash($key, $value = null) {
        if ($value === null) {
            $value = $this->get($key);
            $this->remove($key);
            return $value;
        }
        
        $this->set($key, $value);
    }
    
    public function setUser($user) {
        $this->set('user', $user);
        $this->regenerate();
    }
    
    public function getUser() {
        return $this->get('user');
    }
    
    public function isAuthenticated() {
        return $this->has('user');
    }
    
    public function logout() {
        $this->remove('user');
        $this->regenerate();
        $this->clear();
    }
} 