<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Записывает в журнал storage/logs/api-ГГГГ-ММ-ДД.log каждый запрос к API:
 * метод, URL, id пользователя, IP, код ответа, время выполнения.
 * Тело запроса, пароли и токены (заголовок Authorization) в журнал не попадают.
 */
class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $status = $response->getStatusCode();
        $context = [
            'method' => $request->method(),
            'url' => '/'.$request->path().($request->getQueryString() ? '?'.$request->getQueryString() : ''),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'status' => $status,
            'time_ms' => round((microtime(true) - $start) * 1000, 1),
        ];

        $level = match (true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info',
        };

        Log::channel('api')->log($level, sprintf('%s %s -> %d', $context['method'], $context['url'], $status), $context);

        return $response;
    }
}
