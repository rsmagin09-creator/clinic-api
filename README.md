# Clinic API — запись в поликлинику

Серверное приложение (REST API) на Laravel для записи пациентов на приём к врачу.
Роли: администратор, врач, пациент — у каждого свой личный кабинет.
Курсовой проект, Смагин Роман, ИС-24-23б.

## Стек
PHP 8.3, Laravel 13, MySQL 8.4, Laravel Sanctum, Composer, PHPUnit.

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
Проверка: http://127.0.0.1:8000/api/health

## Тестовые учётные записи (пароль у всех: `password`)
| Роль | Email |
|---|---|
| Администратор | admin@clinic.ru |
| Врач | doctor@clinic.ru |
| Пациент | patient@clinic.ru |

## Описание API
Базовый адрес: `http://127.0.0.1:8000/api/v1`. Все запросы с заголовком `Accept: application/json`,
авторизация — `Authorization: Bearer <token>` (токен из `/login` или `/register`, живёт 24 часа).

| Раздел | Основные эндпоинты |
|---|---|
| Аутентификация | POST /register, POST /login, POST /logout, GET /user |
| Специальности | GET/POST /specialties, GET/PUT/DELETE /specialties/{id} |
| Врачи | GET /doctors (search, specialty_id, sort, order, page, per_page), GET /doctors/{id}/slots?date= |
| График | GET/POST /doctors/{id}/schedules, PUT/DELETE /schedules/{id} |
| Записи на приём | GET/POST /appointments, POST /appointments/{id}/cancel, PATCH /appointments/{id}/status |
| Медкнижка | GET /medical-records, POST /appointments/{id}/medical-record |
| Администрирование | GET /users, PATCH /users/{id}/role, GET /stats/appointments |

Ошибки: `{"message": "..."}`, ошибки валидации — дополнительно `errors`. Коды: 401, 403, 404, 409 (бизнес-правило), 422.

## Документация API
- HTML: http://127.0.0.1:8000/docs/index.html (файл `public/docs/index.html`)
- Коллекция Postman с описанием и примерами ответов: `docs/Clinic_API_docs.postman_collection.json`
- Коллекция Bruno для ручной проверки: папка `Clinic API` (Bruno → Open Collection)

## Тесты
Функциональные тесты используют SQLite в памяти (`phpunit.xml`) и трейт RefreshDatabase:
```bash
php artisan test
```

## Журналы
- `storage/logs/laravel.log` — события бизнес-логики (регистрация, вход, запись на приём, медкнижка, нарушения правил)
- `storage/logs/api-ГГГГ-ММ-ДД.log` — каждый запрос к API (метод, URL, пользователь, код ответа, время). Пароли и токены не пишутся.
