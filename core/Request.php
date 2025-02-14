<?php
namespace Spbot\Core;

class Request {
    private $get;
    private $post;
    private $server;
    private $files;
    private $headers;
    
    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->headers = $this->getHeaders();
    }
    
    public function get($key = null, $default = null) {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }
    
    public function post($key = null, $default = null) {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }
    
    public function file($key) {
        return $this->files[$key] ?? null;
    }
    
    public function server($key) {
        return $this->server[$key] ?? null;
    }
    
    public function header($key) {
        return $this->headers[$key] ?? null;
    }
    
    public function method() {
        return $this->server('REQUEST_METHOD');
    }
    
    public function isGet() {
        return $this->method() === 'GET';
    }
    
    public function isPost() {
        return $this->method() === 'POST';
    }
    
    public function isPut() {
        return $this->method() === 'PUT';
    }
    
    public function isDelete() {
        return $this->method() === 'DELETE';
    }
    
    public function isAjax() {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    public function isJson() {
        return strpos($this->header('Content-Type'), 'application/json') !== false;
    }
    
    public function getJson() {
        if (!$this->isJson()) {
            return [];
        }
        
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }
    
    public function getAuthToken() {
        $header = $this->header('Authorization');
        if (!$header) {
            return null;
        }
        
        if (preg_match('/Bearer\s+(.+)/', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    public function getClientIp() {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($keys as $key) {
            if ($ip = $this->server($key)) {
                foreach (explode(',', $ip) as $addr) {
                    $addr = trim($addr);
                    if (filter_var($addr, FILTER_VALIDATE_IP)) {
                        return $addr;
                    }
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    private function getHeaders() {
        $headers = [];
        
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            }
        }
        
        return $headers;
    }
} 