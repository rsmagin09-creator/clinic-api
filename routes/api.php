<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Проверка работоспособности API и подключения к БД
Route::get('/health', function () {
    $response = [
        'app' => config('app.name'),
        'time' => now()->toDateTimeString(),
    ];

    try {
        DB::select('select 1');
        $response['database'] = 'ok';
        $response['db_name'] = DB::connection()->getDatabaseName();

        return response()->json($response, 200);
    } catch (\Throwable $e) {
        $response['database'] = 'error';

        return response()->json($response, 503);
    }
})->name('health');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
