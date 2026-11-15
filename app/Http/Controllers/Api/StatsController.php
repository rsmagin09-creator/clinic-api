<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatsRequest;
use App\Services\StatsService;

class StatsController extends Controller
{
    public function __construct(private readonly StatsService $service)
    {
    }

    /** GET /stats/appointments?from=&to= — по умолчанию текущий месяц */
    public function appointments(StatsRequest $request)
    {
        $from = $request->validated('from') ?? now()->startOfMonth()->toDateString();
        $to = $request->validated('to') ?? now()->endOfMonth()->toDateString();

        return response()->json(['data' => $this->service->appointments($from, $to)]);
    }
}
