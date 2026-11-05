<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use App\Services\TriageClassifier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $nurse = User::create([
            'name' => 'Ns. Siti Rahayu',
            'nip' => 'PRW-001',
            'email' => 'perawat@puskesmas.test',
            'role' => 'perawat',
            'password' => 'password123',
        ]);

        User::create([
            'name' => 'dr. Hendra Wijaya, Sp.PD',
            'nip' => 'DR-001',
            'email' => 'dokter@puskesmas.test',
            'role' => 'dokter',
            'password' => 'password123',
        ]);

        $classifier = new TriageClassifier();

        $rows = [
            ['Tn. Rahmat Hidayat', 'L', 62, 0, 210, 110, 22, 90.0, 27.1, 0.350, 185, 118, 89, 'Nyeri dada kiri, keringat dingin', 40],
            ['Tn. Budi Santoso', 'L', 54, 0, 340, 90, 30, 160.0, 29.4, 0.420, 140, 88, 95, 'Lemas hebat & riwayat diabetes', 30],
            ['Ny. Kartini Suwandi', 'P', 48, 3, 110, 80, 25, 85.0, 24.0, 0.250, 120, 76, 98, 'Batuk pilek ringan 3 hari', 20],
            ['Ny. Aminah Dahlan', 'P', 69, 4, 125, 85, 28, 110.0, 26.5, 0.310, 135, 72, 97, 'Kontrol rutin hipertensi tanpa keluhan', 10],
        ];

        foreach ($rows as $i => [$name, $g, $age, $preg, $glu, $dia, $skin, $ins, $bmi, $dpf, $sys, $hr, $spo2, $complaint, $minAgo]) {
            $eval = $classifier->evaluate([
                'age' => $age, 'glucose' => $glu, 'diastolic_bp' => $dia, 'systolic_bp' => $sys,
                'heart_rate' => $hr, 'spo2' => $spo2, 'bmi' => $bmi, 'dpf' => $dpf,
            ]);

            $p = Patient::create([
                'medical_record_no' => 'RM-2026-' . str_pad((string) (8800 + $i * 17), 5, '0', STR_PAD_LEFT),
                'name' => $name, 'gender' => $g, 'age' => $age, 'pregnancies' => $preg,
                'glucose' => $glu, 'diastolic_bp' => $dia, 'skin_thickness' => $skin,
                'insulin' => $ins, 'bmi' => $bmi, 'dpf' => $dpf,
                'systolic_bp' => $sys, 'heart_rate' => $hr, 'spo2' => $spo2,
                'complaint' => $complaint,
                'priority' => $eval['priority'], 'outcome' => $eval['outcome'] === 1,
                'status' => 'menunggu', 'created_by' => $nurse->id,
                'created_at' => now()->subMinutes($minAgo), 'updated_at' => now()->subMinutes($minAgo),
            ]);
            $p->update(['queue_number' => 'TR-' . str_pad((string) $p->id, 3, '0', STR_PAD_LEFT)]);
        }
    }
}
