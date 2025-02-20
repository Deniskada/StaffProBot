<?php
namespace Spbot\Controllers;

use Spbot\Core\Controller;
use Spbot\Core\TelegramAPI;
use Spbot\Models\User;

class ApiController extends Controller {
    private $telegram;
    
    public function __construct() {
        parent::__construct();
        $this->telegram = new TelegramAPI();
    }
    
    public function webhook() {
        $update = json_decode(file_get_contents('php://input'), true);
        
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
        
        http_response_code(200);
    }
    
    private function handleMessage($message) {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        if ($text === '/start') {
            $this->handleStart($chatId);
        } elseif ($text === '/help') {
            $this->handleHelp($chatId);
        } else {
            $this->handleUnknown($chatId);
        }
    }
    
    private function handleCallback($callback) {
        $data = json_decode($callback['data'], true);
        $chatId = $callback['message']['chat']['id'];
        
        switch ($data['action']) {
            case 'start_shift':
                $this->handleStartShift($chatId, $data);
                break;
            case 'end_shift':
                $this->handleEndShift($chatId, $data);
                break;
            default:
                $this->telegram->answerCallback($callback['id']);
        }
    }
    
    private function handleStart($chatId) {
        $user = User::findBy('telegram_id', $chatId);
        
        if (!$user) {
            $this->telegram->sendMessage($chatId, 
                "Добро пожаловать! Для начала работы необходимо привязать аккаунт.\n" .
                "Перейдите в веб-интерфейс и добавьте Telegram в настройках профиля."
            );
            return;
        }
        
        $this->telegram->sendMessage($chatId, 
            "Добро пожаловать, {$user->first_name}!\n" .
            "Используйте меню для управления сменами."
        );
    }
    
    private function handleHelp($chatId) {
        $this->telegram->sendMessage($chatId,
            "Доступные команды:\n" .
            "/start - Начать работу\n" .
            "/help - Показать справку"
        );
    }
    
    private function handleUnknown($chatId) {
        $this->telegram->sendMessage($chatId,
            "Извините, я не понимаю эту команду.\n" .
            "Используйте /help для просмотра доступных команд."
        );
    }
    
    private function handleStartShift($chatId, $data) {
        $user = User::findBy('telegram_id', $chatId);
        if (!$user || $user->role !== 'employee') {
            $this->telegram->answerCallback($data['callback_id'], 'Доступ запрещен', true);
            return;
        }
        
        // Логика начала смены
        // ...
        
        $this->telegram->answerCallback($data['callback_id'], 'Смена начата');
    }
    
    private function handleEndShift($chatId, $data) {
        $user = User::findBy('telegram_id', $chatId);
        if (!$user || $user->role !== 'employee') {
            $this->telegram->answerCallback($data['callback_id'], 'Доступ запрещен', true);
            return;
        }
        
        // Логика завершения смены
        // ...
        
        $this->telegram->answerCallback($data['callback_id'], 'Смена завершена');
    }
} 