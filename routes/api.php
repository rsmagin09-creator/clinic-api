<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorSpecialtyController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\UserController;
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

Route::prefix('v1')->name('v1.')->group(function () {
    // Справочники
    Route::apiResource('specialties', SpecialtyController::class);
    Route::apiResource('doctors', DoctorController::class);

    // Связь врач ↔ специальность (многие ко многим)
    Route::post('doctors/{doctor}/specialties', [DoctorSpecialtyController::class, 'store'])->name('doctors.specialties.attach');
    Route::delete('doctors/{doctor}/specialties/{specialty}', [DoctorSpecialtyController::class, 'destroy'])->name('doctors.specialties.detach');

    // График работы врача (вложенный ресурс, shallow)
    Route::apiResource('doctors.schedules', ScheduleController::class)->shallow();

    // Записи на приём
    Route::apiResource('appointments', AppointmentController::class);

    // Медицинская книжка
    Route::post('appointments/{appointment}/medical-record', [MedicalRecordController::class, 'store'])->name('medical-records.store');
    Route::apiResource('medical-records', MedicalRecordController::class)->except('store');

    // Пользователи (администрирование)
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
