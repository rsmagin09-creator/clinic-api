<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorSpecialtyController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\UserController;
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

Route::prefix('v1')->name('v1.')->group(function () {

    // ---------- Гость: регистрация, вход, просмотр каталога ----------
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::apiResource('specialties', SpecialtyController::class)->only(['index', 'show']);
    Route::apiResource('doctors', DoctorController::class)->only(['index', 'show']);
    Route::get('doctors/{doctor}/schedules', [ScheduleController::class, 'index'])->name('doctors.schedules.index');
    Route::get('schedules/{schedule}', [ScheduleController::class, 'show'])->name('schedules.show');

    // ---------- Авторизованные пользователи (любая роль) ----------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('user', [AuthController::class, 'me'])->name('user');

        // Записи на приём: доступ к конкретной записи проверяет AppointmentPolicy
        Route::apiResource('appointments', AppointmentController::class);

        // Медкнижка: доступ проверяет MedicalRecordPolicy
        Route::post('appointments/{appointment}/medical-record', [MedicalRecordController::class, 'store'])
            ->middleware('role:doctor,admin')->name('medical-records.store');
        Route::apiResource('medical-records', MedicalRecordController::class)->except('store');

        // ---------- Только администратор ----------
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('specialties', SpecialtyController::class)->except(['index', 'show']);
            Route::apiResource('doctors', DoctorController::class)->except(['index', 'show']);
            Route::post('doctors/{doctor}/specialties', [DoctorSpecialtyController::class, 'store'])->name('doctors.specialties.attach');
            Route::delete('doctors/{doctor}/specialties/{specialty}', [DoctorSpecialtyController::class, 'destroy'])->name('doctors.specialties.detach');

            Route::post('doctors/{doctor}/schedules', [ScheduleController::class, 'store'])->name('doctors.schedules.store');
            Route::match(['put', 'patch'], 'schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
            Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });
    });
});
