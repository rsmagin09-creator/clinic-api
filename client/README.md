# Клиент «Запись в поликлинику»

HTML + CSS + JavaScript (fetch), без сборки. Адрес API задаётся в `js/config.js`.

Запуск (из каталога clinic-api):
```bash
php -S 127.0.0.1:5500 -t client
```
Открыть http://127.0.0.1:5500/login.html. В `.env` сервера должен быть `CLIENT_URL=http://127.0.0.1:5500` (CORS).
