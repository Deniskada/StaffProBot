<?php
namespace Spbot\Core;

class Session {
    public function __construct() {
        error_log("=== Session Constructor Debug ===");
        error_log("Session status: " . session_status());
        error_log("Session ID: " . session_id());
        
        if (session_status() === PHP_SESSION_NONE) {
            error_log("Starting new session");
            session_start();
        }
        
        error_log("Session data after init: " . print_r($_SESSION, true));
    }
    
    public function get($key, $default = null) {
        error_log("=== Session::get Debug ===");
        error_log("Getting key: " . $key);
        error_log("Value: " . print_r($_SESSION[$key] ?? $default, true));
        return $_SESSION[$key] ?? $default;
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function clear() {
        $_SESSION = [];
    }
    
    public function destroy() {
        session_destroy();
        $this->clear();
    }
    
    public function setUser($user) {
        error_log("=== Session::setUser Debug ===");
        error_log("Setting user data: " . print_r($user, true));
        $this->set('user', $user);
        error_log("Session after setUser: " . print_r($_SESSION, true));
    }
    
    public function getUser() {
        error_log("=== Session::getUser Debug ===");
        error_log("Raw session data: " . print_r($_SESSION, true));
        error_log("User data in session: " . print_r($_SESSION['user'] ?? null, true));
        return $this->get('user');
    }
    
    public function removeUser() {
        $this->remove('user');
    }
    
    public function regenerate() {
        session_regenerate_id(true);
    }
    
    public function flash($key, $value) {
        $_SESSION['_flash'][$key] = $value;
    }
    
    public function getFlash($key, $default = null) {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
} 