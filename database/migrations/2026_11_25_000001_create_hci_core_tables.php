<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('external_patient_ref_hash', 128)->nullable();
            $table->string('source_system', 40)->nullable();
            $table->string('medical_record_no_hash', 128)->nullable();
            $table->string('identity_status', 20)->default('unverified');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['hospital_id', 'source_system']);
            $table->unique(['hospital_id', 'source_system', 'external_patient_ref_hash'], 'patients_source_ref_unique');
        });

        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('source_system', 40)->nullable();
            $table->string('source_encounter_ref', 128)->nullable();
            $table->string('encounter_type', 30);
            $table->string('status', 30)->default('open');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->string('care_unit', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['hospital_id', 'started_at']);
            $table->unique(['hospital_id', 'source_system', 'source_encounter_ref'], 'encounters_source_ref_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('hospitals');
    }
};
