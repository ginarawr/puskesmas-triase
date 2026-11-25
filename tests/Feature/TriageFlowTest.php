<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriageFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Uji',
            'nip' => strtoupper($role) . '-T',
            'role' => $role,
            'password' => 'password123',
        ]);
    }

    public function test_login_mengarahkan_sesuai_peran(): void
    {
        $this->user('perawat');
        $this->user('dokter');

        $this->post('/masuk', ['login' => 'PERAWAT-T', 'password' => 'password123'])
            ->assertRedirect(route('perawat.dashboard'));
        $this->post('/keluar');

        $this->post('/masuk', ['login' => 'DOKTER-T', 'password' => 'password123'])
            ->assertRedirect(route('dokter.dashboard'));
    }

    public function test_perawat_tidak_bisa_membuka_dashboard_dokter(): void
    {
        $this->actingAs($this->user('perawat'))
            ->get('/dokter')
            ->assertRedirect(route('perawat.dashboard'));
    }

    public function test_alur_pasien_darurat_dari_perawat_ke_dokter(): void
    {
        $nurse = $this->user('perawat');

        $this->actingAs($nurse)->post(route('perawat.pasien.store'), [
            'name' => 'Tn. Uji Coba', 'gender' => 'L', 'age' => 60,
            'medical_record_no' => 'RM-UJI-1', 'pregnancies' => 0,
            'glucose' => 210, 'diastolic_bp' => 110, 'skin_thickness' => 20,
            'insulin' => 90, 'bmi' => 27.1, 'dpf' => 0.35, 'systolic_bp' => 185,
        ])->assertRedirect(route('perawat.dashboard'));

        $patient = Patient::firstOrFail();
        $this->assertSame('tinggi', $patient->priority);
        $this->assertSame('menunggu', $patient->status);

        $this->post(route('perawat.pasien.escalate', $patient))->assertRedirect();
        $this->assertSame('darurat', $patient->fresh()->status);

        $doctor = $this->user('dokter');
        $this->actingAs($doctor)->get(route('dokter.dashboard'))
            ->assertOk()->assertSee('Tn. Uji Coba');

        $this->post(route('dokter.pasien.start', $patient))
            ->assertRedirect(route('dokter.pasien.show', $patient));

        $this->put(route('dokter.pasien.update', $patient), [
            'action' => 'selesai', 'diagnosis' => 'Uji', 'disposition' => 'rawat_jalan',
        ])->assertRedirect(route('dokter.dashboard'));

        $this->assertSame('selesai', $patient->fresh()->status);
    }

    public function test_form_perawat_menggunakan_ai_dan_mengizinkan_glukosa_insulin_nol_atau_kosong(): void
    {
        $nurse = $this->user('perawat');

        // Evaluasi Mode B (6 fitur non-lab) dengan glukosa & insulin kosong
        $this->actingAs($nurse)->postJson(route('perawat.pasien.evaluate'), [
            'screening_mode' => 'versi_b',
            'threshold' => 0.40,
            'pregnancies' => 4,
            'diastolic_bp' => 76,
            'skin_thickness' => 30,
            'bmi' => 33.6,
            'dpf' => 0.627,
            'age' => 45,
        ])->assertOk()
          ->assertJsonPath('mode', 'versi_b')
          ->assertJsonPath('outcome', 1)
          ->assertJsonPath('priority', 'sedang');

        // Simpan pasien baru dengan glukosa & insulin kosong pada Mode B
        $this->actingAs($nurse)->post(route('perawat.pasien.store'), [
            'name' => 'Ny. Uji Non-Lab',
            'gender' => 'P',
            'age' => 45,
            'medical_record_no' => 'RM-AI-001',
            'pregnancies' => 4,
            'glucose' => '',
            'diastolic_bp' => 76,
            'skin_thickness' => 30,
            'insulin' => '',
            'bmi' => 33.6,
            'dpf' => 0.627,
            'screening_mode' => 'versi_b',
            'threshold' => 0.40,
        ])->assertRedirect(route('perawat.dashboard'));

        $patient = Patient::where('medical_record_no', 'RM-AI-001')->firstOrFail();
        $this->assertSame(0, $patient->glucose);
        $this->assertSame('0.0', (string) $patient->insulin);
        $this->assertSame('sedang', $patient->priority);
        $this->assertTrue($patient->outcome);
    }
}
