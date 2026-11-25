<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\TriageClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(private TriageClassifier $classifier)
    {
    }

    /** Alur: "Ada pasien baru?" -> Ya -> isi form data kesehatan pasien (hasil tes). */
    public function create(): View
    {
        $nextRm = 'RM-' . date('Y') . '-' . str_pad((string) ((Patient::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

        return view('perawat.patient-form', ['nextRm' => $nextRm]);
    }

    /** Evaluasi langsung (dipanggil JS saat form diisi). Tidak menyimpan apa pun. */
    public function evaluate(Request $request): JsonResponse
    {
        return response()->json($this->classifier->evaluate($request->all()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:L,P'],
            'age' => ['required', 'integer', 'between:0,120'],
            'medical_record_no' => ['required', 'string', 'max:30', 'unique:patients,medical_record_no'],
            'pregnancies' => ['required', 'integer', 'between:0,20'],
            'glucose' => ['nullable', 'integer', 'between:0,600'],
            'diastolic_bp' => ['required', 'integer', 'between:0,200'],
            'skin_thickness' => ['required', 'integer', 'between:0,99'],
            'insulin' => ['nullable', 'numeric', 'between:0,900'],
            'bmi' => ['required', 'numeric', 'between:10,70'],
            'dpf' => ['required', 'numeric', 'between:0,3'],
            'screening_mode' => ['nullable', 'in:versi_a,versi_b'],
            'threshold' => ['nullable', 'numeric', 'between:0.1,0.9'],
            'systolic_bp' => ['nullable', 'integer', 'between:50,300'],
            'heart_rate' => ['nullable', 'integer', 'between:20,250'],
            'spo2' => ['nullable', 'integer', 'between:50,100'],
            'complaint' => ['nullable', 'string', 'max:2000'],
            'allergies' => ['nullable', 'string', 'max:255'],
            'priority_override' => ['nullable', 'in:tinggi,sedang,rendah'],
        ], [], [
            'name' => 'nama pasien',
            'gender' => 'jenis kelamin',
            'age' => 'usia',
            'medical_record_no' => 'nomor rekam medis',
            'pregnancies' => 'riwayat kehamilan',
            'glucose' => 'glukosa',
            'diastolic_bp' => 'tekanan darah diastolik',
            'skin_thickness' => 'ketebalan lipatan kulit',
            'insulin' => 'insulin',
            'bmi' => 'BMI',
            'dpf' => 'DPF',
            'systolic_bp' => 'tekanan sistolik',
            'heart_rate' => 'nadi',
            'spo2' => 'SpO2',
            'complaint' => 'keluhan',
            'allergies' => 'alergi',
        ]);

        $eval = $this->classifier->evaluate($data);
        $manual = ! empty($data['priority_override']);
        $priority = $manual ? $data['priority_override'] : $eval['priority'];

        $patient = Patient::create([
            'medical_record_no' => $data['medical_record_no'],
            'name' => $data['name'],
            'gender' => $data['gender'],
            'age' => $data['age'],
            'pregnancies' => $data['pregnancies'],
            'glucose' => (int) ($data['glucose'] ?? 0),
            'diastolic_bp' => $data['diastolic_bp'],
            'skin_thickness' => $data['skin_thickness'],
            'insulin' => (float) ($data['insulin'] ?? 0),
            'bmi' => $data['bmi'],
            'dpf' => $data['dpf'],
            'systolic_bp' => $data['systolic_bp'] ?? null,
            'heart_rate' => $data['heart_rate'] ?? null,
            'spo2' => $data['spo2'] ?? null,
            'complaint' => $data['complaint'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'priority' => $priority,
            'is_manual_priority' => $manual,
            'outcome' => $eval['outcome'] === 1,
            'status' => 'menunggu',
            'created_by' => $request->user()->id,
        ]);

        $patient->update(['queue_number' => 'TR-' . str_pad((string) $patient->id, 3, '0', STR_PAD_LEFT)]);

        return redirect()->route('perawat.dashboard')->with(
            'status',
            "{$patient->name} masuk antrean {$patient->queue_number} dengan prioritas " . strtolower($patient->priority_label) . '.'
        );
    }

    /** Fitur panggil pasien. */
    public function call(Patient $patient): RedirectResponse
    {
        if ($patient->status === 'selesai') {
            return back()->with('error', 'Pasien ini sudah selesai ditangani.');
        }

        $patient->update([
            'status' => $patient->status === 'menunggu' ? 'dipanggil' : $patient->status,
            'called_at' => now(),
        ]);

        return back()
            ->with('status', "Memanggil {$patient->queue_number} – {$patient->name}.")
            ->with('announce', "Nomor antrean {$patient->queue_number}, {$patient->name}, silakan menuju ruang pemeriksaan.");
    }

    /** Alur: prioritas tinggi -> segera hubungi dokter & dibawa ke UGD. */
    public function escalate(Request $request, Patient $patient): RedirectResponse
    {
        if (! in_array($patient->status, ['menunggu', 'dipanggil', 'darurat'], true)) {
            return back()->with('error', 'Pasien ini sudah ditangani dokter.');
        }

        if ($patient->status !== 'darurat') {
            $patient->update([
                'status' => 'darurat',
                'emergency_called_at' => now(),
                'escalated_by' => $request->user()->id,
            ]);
        }

        return back()->with('status', "Dokter jaga sudah dihubungi untuk {$patient->queue_number}. Bawa pasien ke UGD.");
    }
}
