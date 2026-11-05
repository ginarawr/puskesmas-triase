<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number')->nullable()->unique();
            $table->string('medical_record_no')->unique();
            $table->string('name');
            $table->string('gender', 1); // L | P
            $table->unsignedTinyInteger('age');

            // 8 atribut dataset Pima (Age & Pregnancies di sini, sisanya di bawah)
            $table->unsignedTinyInteger('pregnancies')->default(0);
            $table->unsignedSmallInteger('glucose');
            $table->unsignedSmallInteger('diastolic_bp');
            $table->unsignedSmallInteger('skin_thickness');
            $table->decimal('insulin', 6, 1);
            $table->decimal('bmi', 4, 1);
            $table->decimal('dpf', 4, 3);

            // Tanda vital tambahan (opsional)
            $table->unsignedSmallInteger('systolic_bp')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedSmallInteger('spo2')->nullable();

            $table->text('complaint')->nullable();
            $table->string('allergies')->nullable();

            // Triase
            $table->string('priority')->index();           // tinggi | sedang | rendah
            $table->boolean('is_manual_priority')->default(false);
            $table->boolean('outcome')->default(false);    // 1 = risiko positif (Pima Outcome)
            $table->string('status')->default('menunggu')->index();
            // menunggu | dipanggil | darurat | ditangani | observasi | selesai

            $table->timestamp('called_at')->nullable();
            $table->timestamp('emergency_called_at')->nullable();
            $table->timestamp('handling_started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            // Pemeriksaan dokter
            $table->string('diagnosis')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->json('prescriptions')->nullable();
            $table->string('disposition')->nullable();     // rawat_jalan | observasi | rujuk_rs

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
