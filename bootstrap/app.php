<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Псевдоним middleware проверки роли: ->middleware('role:admin')
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Для всех запросов к API ошибки возвращаются только в JSON
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        $isApi = fn (Request $request) => $request->is('api/*');

        // 422 — ошибка валидации: message + errors по полям
        $exceptions->render(function (ValidationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Ошибка валидации', 'errors' => $e->errors()], 422);
            }
        });

        // 401 — нет токена или он недействителен
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Требуется авторизация'], 401);
            }
        });

        // 403 — недостаточно прав
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Недостаточно прав для выполнения действия'], 403);
            }
        });

        // 404 — запись или маршрут не найдены
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if (! $isApi($request)) {
                return null;
            }
            $previous = $e->getPrevious();
            if ($previous instanceof ModelNotFoundException) {
                $message = match ($previous->getModel()) {
                    Specialty::class => 'Специальность не найдена',
                    Doctor::class => 'Врач не найден',
                    Schedule::class => 'Рабочий день не найден',
                    Appointment::class => 'Запись на приём не найдена',
                    MedicalRecord::class => 'Запись медкнижки не найдена',
                    User::class => 'Пользователь не найден',
                    default => 'Запись не найдена',
                };

                return response()->json(['message' => $message], 404);
            }

            return response()->json(['message' => 'Ресурс не найден'], 404);
        });

        // 405 — метод не поддерживается
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Метод не поддерживается для этого адреса'], 405);
            }
        });

        // 409 — удаление записи, на которую ссылаются другие (FOREIGN KEY ... RESTRICT)
        $exceptions->render(function (QueryException $e, Request $request) use ($isApi) {
            if ($isApi($request) && ($e->errorInfo[1] ?? null) === 1451) {
                return response()->json([
                    'message' => 'Нельзя удалить запись: на неё ссылаются другие данные (например, записи на приём или медкнижка)',
                ], 409);
            }
        });

        // Остальные HTTP-ошибки (abort(...)) — с их кодом
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => $e->getMessage() ?: 'Ошибка запроса'], $e->getStatusCode());
            }
        });

        // 500 — непредвиденная ошибка: без стека вызовов и SQL (подробности только в журнале)
        $exceptions->render(function (\Throwable $e, Request $request) use ($isApi) {
            if ($isApi($request) && ! config('app.debug')) {
                return response()->json(['message' => 'Внутренняя ошибка сервера. Попробуйте позже'], 500);
            }
        });
    })->create();
