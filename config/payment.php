<?php

// Платежный шлюз
define('PAYMENT_API_KEY', 'your_api_key');
define('PAYMENT_SECRET_KEY', 'your_secret_key');
define('PAYMENT_API_URL', 'https://api.payment-gateway.com/v1');
define('PAYMENT_TEST_MODE', true);

// Настройки платежей
define('PAYMENT_CURRENCY', 'RUB');
define('PAYMENT_MIN_AMOUNT', 100);
define('PAYMENT_MAX_AMOUNT', 100000);

// Методы оплаты
define('PAYMENT_METHODS', [
    'card' => [
        'enabled' => true,
        'title' => 'Банковская карта',
        'commission' => 2.5
    ],
    'bank_transfer' => [
        'enabled' => true,
        'title' => 'Банковский перевод',
        'commission' => 0
    ]
]);

// Настройки уведомлений
define('PAYMENT_NOTIFICATIONS', [
    'success_email' => true,
    'failure_email' => true,
    'admin_email' => true
]);

// Тексты уведомлений
define('PAYMENT_MESSAGES', [
    'success' => 'Оплата успешно выполнена',
    'pending' => 'Ожидание подтверждения оплаты',
    'failed' => 'Ошибка при выполнении оплаты',
    'cancelled' => 'Оплата отменена'
]);

// Таймауты
define('PAYMENT_TIMEOUTS', [
    'pending' => 3600, // 1 час
    'session' => 1800  // 30 минут
]); 