# SPBot

Система управления сменами

## Требования

- PHP 7.4 или выше
- MySQL 5.7 или выше
- Composer
- Apache/Nginx

## Установка

1. Клонируйте репозиторий
2. Установите зависимости: `composer install`
3. Скопируйте .env.example в .env и настройте
4. Создайте базу данных
5. Запустите миграции: `php migrate.php`
6. Настройте веб-сервер:
   - Корневая директория: `/public`
   - Включите mod_rewrite для Apache или настройте rewrite rules для Nginx
7. Настройте права доступа:
   ```bash
   chmod -R 755 ./
   chmod -R 777 ./storage
   chmod -R 777 ./logs
   ```
8. Убедитесь, что composer сгенерировал autoload:
   ```bash
   composer dump-autoload -o
   ```

## Структура проекта

```
├── public/          # Публичная директория
├── src/             # Исходный код
├── storage/         # Файлы загрузок
├── logs/           # Логи
├── vendor/         # Зависимости
└── composer.json   # Конфигурация composer
``` 