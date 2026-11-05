<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriageFlowTest extends TestCase
{
    use RefreshDatabase;

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
}
