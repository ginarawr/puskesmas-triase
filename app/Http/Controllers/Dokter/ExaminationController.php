<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExaminationController extends Controller
{
    /** "Segera menuju pasien" (darurat) atau "Periksa pasien" (biasa). */
    public function start(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->status === 'selesai') {
            return redirect()->route('dokter.dashboard')->with('error', 'Pasien ini sudah selesai ditangani.');
        }

        if (in_array($patient->status, ['menunggu', 'dipanggil', 'darurat'], true)) {
            $patient->update([
                'status' => 'ditangani',
                'handling_started_at' => now(),
                'handled_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('dokter.pasien.show', $patient);
    }

    /** Alur: cek data medis pasien saat pemeriksaan. */
    public function show(Patient $patient): View|RedirectResponse
    {
        if ($patient->status === 'selesai') {
            return redirect()->route('dokter.dashboard')->with('error', 'Pasien ini sudah selesai ditangani.');
        }

        return view('dokter.examination', compact('patient'));
    }

    /** Alur: "Pasien selesai ditangani?" -> Ya (selesai) / Tidak (observasi, tetap di daftar). */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:observasi,selesai'],
            'diagnosis' => ['nullable', 'string', 'max:255', 'required_if:action,selesai'],
            'clinical_notes' => ['nullable', 'string', 'max:5000'],
            'disposition' => ['nullable', 'in:rawat_jalan,observasi,rujuk_rs', 'required_if:action,selesai'],
            'prescriptions' => ['nullable', 'array', 'max:30'],
            'prescriptions.*.name' => ['nullable', 'string', 'max:160'],
            'prescriptions.*.type' => ['nullable', 'in:tindakan_ugd,apotek'],
        ], [
            'diagnosis.required_if' => 'Isi diagnosis sebelum menandai pasien selesai.',
            'disposition.required_if' => 'Pilih disposisi akhir sebelum menandai pasien selesai.',
        ], [
            'diagnosis' => 'diagnosis',
            'disposition' => 'disposisi akhir',
            'clinical_notes' => 'catatan klinis',
        ]);

        $rx = collect($data['prescriptions'] ?? [])
            ->filter(fn ($r) => filled($r['name'] ?? null))
            ->map(fn ($r) => ['name' => trim($r['name']), 'type' => $r['type'] ?? 'apotek'])
            ->values()
            ->all();

        $finished = $data['action'] === 'selesai';

        $patient->update([
            'diagnosis' => $data['diagnosis'] ?? null,
            'clinical_notes' => $data['clinical_notes'] ?? null,
            'disposition' => $data['disposition'] ?? null,
            'prescriptions' => $rx,
            'status' => $finished ? 'selesai' : 'observasi',
            'finished_at' => $finished ? now() : null,
            'handled_by' => $patient->handled_by ?? $request->user()->id,
        ]);

        return redirect()->route('dokter.dashboard')->with(
            'status',
            $finished
                ? "{$patient->name} ditandai selesai ditangani."
                : "{$patient->name} tetap di daftar untuk penanganan lanjutan / observasi."
        );
    }
}
