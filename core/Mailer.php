<?php
namespace Spbot\Core;

class Mailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption;
    private $fromName;
    
    public function __construct() {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        $this->password = MAIL_PASSWORD;
        $this->encryption = MAIL_ENCRYPTION;
        $this->fromName = MAIL_FROM_NAME;
    }
    
    public function send($to, $subject, $body, $params = []) {
        $headers = $this->buildHeaders($params);
        
        if ($this->encryption === 'tls') {
            $this->startTLS();
        }
        
        $result = mail($to, $subject, $body, $headers);
        
        if (!$result) {
            error_log("Failed to send email to: {$to}");
            return false;
        }
        
        return true;
    }
    
    public function sendTemplate($to, $template, $data = [], $subject = null) {
        $templatePath = VIEWS_PATH . '/emails/' . $template . '.php';
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template not found: {$template}");
        }
        
        extract($data);
        ob_start();
        include $templatePath;
        $body = ob_get_clean();
        
        if ($subject === null && isset($data['subject'])) {
            $subject = $data['subject'];
        }
        
        return $this->send($to, $subject, $body, [
            'isHtml' => true
        ]);
    }
    
    private function buildHeaders($params = []) {
        $headers = [];
        
        // From
        $headers[] = "From: {$this->fromName} <{$this->username}>";
        $headers[] = "Reply-To: {$this->username}";
        
        // Content type
        if (!empty($params['isHtml'])) {
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
        }
        
        // Additional headers
        if (!empty($params['cc'])) {
            $headers[] = "Cc: {$params['cc']}";
        }
        
        if (!empty($params['bcc'])) {
            $headers[] = "Bcc: {$params['bcc']}";
        }
        
        return implode("\r\n", $headers);
    }
    
    private function startTLS() {
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
        if (!$socket) {
            throw new \Exception("Could not connect to mail server: {$errstr}");
        }
        
        $response = fgets($socket);
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket);
        
        if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            return true;
        }
        
        throw new \Exception("Failed to enable TLS");
    }
} 