<?php

use Illuminate\Support\Facades\Route;

// Корень сайта открывает веб-клиент (копия каталога client в public/app)
Route::redirect('/', '/app/index.html');
