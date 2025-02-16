<?php
namespace Spbot\Core;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->Port = $_ENV['MAIL_PORT'];
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }
    
    public function send($to, $subject, $body, $params = []) {
        $headers = $this->buildHeaders($params);
        
        if ($this->mailer->SMTPSecure === 'tls') {
            $this->startTLS();
        }
        
        $result = $this->mailer->send();
        
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
        $headers[] = "From: {$this->mailer->FromName} <{$this->mailer->Username}>";
        $headers[] = "Reply-To: {$this->mailer->Username}";
        
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
        $socket = fsockopen($this->mailer->Host, $this->mailer->Port, $errno, $errstr, 30);
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