<?php
namespace Spbot\Core;

class Cache {
    private static $instance = null;
    private $path;
    
    private function __construct() {
        $this->path = dirname(__DIR__) . '/' . ($_ENV['CACHE_PATH'] ?? 'storage/cache/');
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get($key, $default = null) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }
        
        $data = file_get_contents($filename);
        $cached = json_decode($data, true);
        
        if (!$cached || !isset($cached['expiry']) || !isset($cached['data'])) {
            return $default;
        }
        
        if ($cached['expiry'] !== 0 && $cached['expiry'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $cached['data'];
    }
    
    public function set($key, $value, $minutes = 0) {
        $filename = $this->getFilename($key);
        
        $data = [
            'expiry' => $minutes ? time() + ($minutes * 60) : 0,
            'data' => $value
        ];
        
        return file_put_contents($filename, json_encode($data)) !== false;
    }
    
    public function delete($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    public function clear() {
        $files = glob($this->path . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    public function remember($key, $minutes, $callback) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $minutes);
        
        return $value;
    }
    
    private function getFilename($key) {
        return $this->path . md5($key) . '.cache';
    }
    
    public function getSize() {
        $size = 0;
        $files = glob($this->path . '*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }
        
        return $size;
    }
} 