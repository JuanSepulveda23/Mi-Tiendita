<?php

namespace App\Http\Controllers;

use App\Services\ProfitReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ProfitReportService $profitReportService
    ) {}

    public function profits(): View
    {
        return view('reports.profits');
    }

    /**
     * API: Datos de ganancias diarias consolidadas para los últimos N días.
     */
    public function profitsData(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30);
        $data = $this->profitReportService->getProfitsData($days);

        return response()->json($data);
    }

    /**
     * API: Ganancias por producto (Top N).
     */
    public function profitsByProduct(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 30);
        $products = $this->profitReportService->getProfitsByProduct($days);

        return response()->json($products);
    }
}
