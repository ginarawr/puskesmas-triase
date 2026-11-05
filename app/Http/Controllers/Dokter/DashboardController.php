<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filter = $request->query('prioritas', 'semua');
        $filter = in_array($filter, ['tinggi', 'sedang', 'rendah'], true) ? $filter : 'semua';

        $byPriority = Patient::active()
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $counts = [
            'tinggi' => (int) ($byPriority['tinggi'] ?? 0),
            'sedang' => (int) ($byPriority['sedang'] ?? 0),
            'rendah' => (int) ($byPriority['rendah'] ?? 0),
        ];
        $counts['semua'] = array_sum($counts);

        // Alur: "Ada panggilan darurat?" -> pasien berstatus "darurat" (belum ditangani dokter)
        $emergencies = Patient::with('escalator')
            ->where('status', 'darurat')
            ->orderBy('emergency_called_at')
            ->get();

        $patients = Patient::active()
            ->when($filter !== 'semua', fn ($q) => $q->where('priority', $filter))
            ->ordered()
            ->get();

        return view('dokter.dashboard', compact('patients', 'counts', 'filter', 'emergencies'));
    }
}
