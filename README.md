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
6. Настройте веб-сервер
7. Настройте права доступа на директории 