<?php

namespace App\Http\Controllers;

use App\Services\DiabetesScreeningService;
use Illuminate\Http\Request;

class ScreeningController extends Controller
{
    protected DiabetesScreeningService $service;

    public function __construct(DiabetesScreeningService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /screening — tampilkan halaman utama skrining.
     */
    public function index()
    {
        $metadata = $this->service->getMetadata();
        return view('screening.index', compact('metadata'));
    }

    /**
     * POST /predict — jalankan prediksi, kembalikan JSON.
     */
    public function predict(Request $request)
    {
        $mode = $request->input('mode', 'versi_b');

        if (!in_array($mode, ['versi_a', 'versi_b'], true)) {
            return response()->json(['error' => 'Mode tidak valid. Gunakan versi_a atau versi_b.'], 422);
        }

        $metadata = $this->service->getMetadata();
        $features = $metadata['models'][$mode]['features'] ?? [];

        $input = [];
        foreach ($features as $f) {
            $input[$f] = $request->input($f);
        }

        $threshold = $request->filled('threshold') ? (float) $request->input('threshold') : null;

        try {
            $result = $this->service->predict($mode, $input, $threshold);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
