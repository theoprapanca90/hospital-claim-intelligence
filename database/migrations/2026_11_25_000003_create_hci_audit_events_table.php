<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('hospital_id')->nullable()->constrained('hospitals')->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->string('subject_type', 120)->nullable();
            // string, bukan uuid: subjek bisa berupa membership/role (bigint) maupun UUID.
            $table->string('subject_id', 64)->nullable();
            $table->string('request_id', 80)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->index(['hospital_id', 'occurred_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['actor_user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
