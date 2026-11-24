<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('storage_disk', 40);
            $table->string('storage_key', 512);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->char('sha256', 64)->nullable();
            $table->string('source_system', 40)->nullable();
            $table->string('source_document_ref', 128)->nullable();
            $table->string('processing_status', 30)->default('received');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['hospital_id', 'encounter_id']);
        });

        Schema::create('encounter_diagnoses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->string('code_system', 40)->nullable();
            $table->string('code', 40)->nullable();
            $table->text('display')->nullable();
            $table->string('diagnosis_role', 30)->default('secondary');
            $table->string('source_type', 30)->default('simrs');
            $table->string('review_status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->json('evidence_refs')->nullable();
            $table->timestamps();
            $table->index(['hospital_id', 'encounter_id', 'review_status']);
        });

        Schema::create('encounter_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->constrained('encounters')->cascadeOnDelete();
            $table->string('code_system', 40)->nullable();
            $table->string('code', 40)->nullable();
            $table->text('display')->nullable();
            $table->dateTime('performed_at')->nullable();
            $table->string('source_type', 30)->default('simrs');
            $table->string('review_status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->json('evidence_refs')->nullable();
            $table->timestamps();
            $table->index(['hospital_id', 'encounter_id', 'review_status']);
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->constrained('encounters')->restrictOnDelete();
            $table->string('payer', 40)->default('BPJS_KESEHATAN');
            $table->string('claim_number', 100)->nullable();
            $table->string('workflow_status', 30)->default('draft');
            $table->string('grouping_status', 30)->default('not_started');
            $table->string('grouping_source', 50)->nullable();
            $table->string('grouping_version', 80)->nullable();
            $table->date('service_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['hospital_id', 'workflow_status']);
            $table->unique(['hospital_id', 'claim_number'], 'claims_number_unique');
        });

        Schema::create('claim_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->string('item_type', 30);
            $table->uuid('source_item_id')->nullable();
            $table->string('code_system', 40)->nullable();
            $table->string('code', 40)->nullable();
            $table->text('display')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['claim_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_items');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('encounter_procedures');
        Schema::dropIfExists('encounter_diagnoses');
        Schema::dropIfExists('clinical_documents');
    }
};
