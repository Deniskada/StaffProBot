<?php
namespace Spbot\Core;

class PaymentGateway {
    private $apiKey;
    private $secretKey;
    private $apiUrl;
    private $testMode;
    
    public function __construct() {
        $this->apiKey = $_ENV['STRIPE_KEY'];
        $this->secretKey = $_ENV['STRIPE_SECRET'];
        $this->apiUrl = $_ENV['PAYMENT_API_URL'] ?? 'https://api.stripe.com/v1/';
        $this->testMode = $_ENV['APP_ENV'] === 'development';
    }
    
    public function createPayment($data) {
        $params = [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'order_id' => $data['payment_id'],
            'description' => $data['description'],
            'test' => $this->testMode ? 1 : 0,
            'return_url' => $_ENV['APP_URL'] . '/payment/success',
            'cancel_url' => $_ENV['APP_URL'] . '/payment/cancel',
            'callback_url' => $_ENV['APP_URL'] . '/api/payment/callback',
            'metadata' => [
                'payment_id' => $data['payment_id']
            ]
        ];
        
        $params['signature'] = $this->generateSignature($params);
        
        $response = $this->sendRequest('POST', '/payments/create', $params);
        
        if (!$response || !isset($response['payment_url'])) {
            return false;
        }
        
        return $response['payment_url'];
    }
    
    public function validateCallback($data) {
        if (!isset($data['signature'])) {
            return false;
        }
        
        $signature = $data['signature'];
        unset($data['signature']);
        
        return $signature === $this->generateSignature($data);
    }
    
    private function generateSignature($params) {
        ksort($params);
        $str = '';
        
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $str .= $key . ':' . $value . ';';
        }
        
        return hash_hmac('sha256', $str, $this->secretKey);
    }
    
    private function sendRequest($method, $endpoint, $params = []) {
        $ch = curl_init();
        
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            error_log("Payment Gateway Error: {$error}");
            return false;
        }
        
        return json_decode($response, true);
    }

    private function initializeStripe()
    {
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);
    }
} 