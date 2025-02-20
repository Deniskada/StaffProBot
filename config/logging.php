return [
    'max_lines' => 3047,
    'enabled' => true,
    'file' => storage_path('logs/app.log'),
    'level' => 'debug',
    'filters' => [
        'line_limit' => [
            'enabled' => true,
            'start_line' => 3047
        ]
    ]
]; 