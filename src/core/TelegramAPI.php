<?php
namespace Spbot\Core;

class TelegramAPI {
    private $token;
    private $apiUrl;
    private $chatId;
    
    public function __construct() {
        $this->token = $_ENV['TELEGRAM_BOT_TOKEN'];
        $this->chatId = $_ENV['TELEGRAM_CHAT_ID'];
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }
    
    public function setWebhook() {
        return $this->request('setWebhook', [
            'url' => $_ENV['APP_URL'] . '/webhook/telegram',
            'allowed_updates' => ['message', 'callback_query']
        ]);
    }
    
    public function sendMessage($chatId, $text, $params = []) {
        $data = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $params);
        
        return $this->request('sendMessage', $data);
    }
    
    public function sendKeyboard($chatId, $text, $buttons, $params = []) {
        $keyboard = [
            'inline_keyboard' => $buttons
        ];
        
        $data = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ], $params);
        
        return $this->request('sendMessage', $data);
    }
    
    public function editMessage($chatId, $messageId, $text, $keyboard = null) {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        if ($keyboard !== null) {
            $data['reply_markup'] = json_encode([
                'inline_keyboard' => $keyboard
            ]);
        }
        
        return $this->request('editMessageText', $data);
    }
    
    public function answerCallback($callbackId, $text = null, $alert = false) {
        $data = ['callback_query_id' => $callbackId];
        
        if ($text !== null) {
            $data['text'] = $text;
            $data['show_alert'] = $alert;
        }
        
        return $this->request('answerCallbackQuery', $data);
    }
    
    public function deleteMessage($chatId, $messageId) {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }
    
    public function sendLocation($chatId, $latitude, $longitude) {
        return $this->request('sendLocation', [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }
    
    private function request($method, $data = []) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            error_log("Telegram API Error: {$error}");
            return false;
        }
        
        $result = json_decode($response, true);
        
        if (!$result['ok']) {
            error_log("Telegram API Error: " . ($result['description'] ?? 'Unknown error'));
            return false;
        }
        
        return $result['result'];
    }
} 