<?php
namespace Spbot\Core;

class PaymentGateway {
    private $apiKey;
    private $secretKey;
    private $apiUrl;
    private $testMode;
    
    public function __construct() {
        $this->apiKey = PAYMENT_API_KEY;
        $this->secretKey = PAYMENT_SECRET_KEY;
        $this->apiUrl = PAYMENT_API_URL;
        $this->testMode = PAYMENT_TEST_MODE;
    }
    
    public function createPayment($data) {
        $params = [
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'order_id' => $data['payment_id'],
            'description' => $data['description'],
            'test' => $this->testMode ? 1 : 0,
            'return_url' => SITE_URL . '/payment/success',
            'cancel_url' => SITE_URL . '/payment/cancel',
            'callback_url' => SITE_URL . '/api/payment/callback',
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
} 