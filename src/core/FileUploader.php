<?php
namespace Spbot\Core;

class FileUploader {
    private $uploadPath;
    private $allowedTypes;
    private $maxSize;
    private $errors = [];
    
    public function __construct($uploadPath = null) {
        $this->uploadPath = $uploadPath ?? dirname(__DIR__) . '/storage/uploads/';
        $this->allowedTypes = explode(',', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx');
        $this->maxSize = intval($_ENV['UPLOAD_MAX_SIZE'] ?? 5) * 1024 * 1024; // В МБ
        
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }
    
    public function setAllowedTypes($types) {
        $this->allowedTypes = $types;
        return $this;
    }
    
    public function setMaxSize($size) {
        $this->maxSize = $size;
        return $this;
    }
    
    public function upload($file, $customName = null) {
        if (!$this->validate($file)) {
            return false;
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $customName ? $customName . '.' . $extension : 
                   $this->generateFilename($file['name']);
                   
        $destination = $this->uploadPath . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Ошибка при загрузке файла';
            return false;
        }
        
        return $filename;
    }
    
    public function validate($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Некорректные параметры файла';
            return false;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $this->errors[] = 'Превышен максимальный размер файла';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->errors[] = 'Файл был загружен частично';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->errors[] = 'Файл не был загружен';
                    break;
                default:
                    $this->errors[] = 'Неизвестная ошибка при загрузке';
            }
            return false;
        }
        
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'Превышен максимальный размер файла';
            return false;
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $this->errors[] = 'Недопустимый тип файла';
            return false;
        }
        
        return true;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    private function generateFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    public function delete($filename) {
        $filepath = $this->uploadPath . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    public function exists($filename) {
        return file_exists($this->uploadPath . $filename);
    }
    
    public function getUrl($filename) {
        return '/storage/uploads/' . $filename;
    }
    
    public function getPath($filename) {
        return $this->uploadPath . $filename;
    }
} 