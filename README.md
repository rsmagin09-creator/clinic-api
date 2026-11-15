# Clinic API — запись в поликлинику

Серверное приложение (REST API) на Laravel для записи пациентов на приём к врачу.
Роли: администратор, врач, пациент (у каждого свой личный кабинет).
Курсовой проект, Смагин Роман, ИС-24-23б.

## Стек
PHP 8.3, Laravel 13, MySQL 8.4, Laravel Sanctum, Composer.

## Установка
```bash
git clone <ссылка на репозиторий> clinic-api
cd clinic-api
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
# создать БД clinic_api в MySQL и указать доступ в .env (DB_*)
php artisan migrate --seed
php artisan serve
```

Проверка: http://127.0.0.1:8000/api/health — JSON с названием приложения, временем и статусом БД
(200 — БД доступна, 503 — нет подключения).
