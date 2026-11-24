<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_memberships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['hospital_id', 'user_id'], 'hospital_membership_unique');
            $table->index(['user_id', 'status']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignUuid('hospital_id')->nullable()->constrained('hospitals')->cascadeOnDelete();
            $table->string('slug', 60);
            $table->string('name', 100);
            $table->string('scope', 20)->default('hospital');
            $table->text('description')->nullable();
            $table->boolean('is_assignable')->default(true);
            $table->timestamps();
            $table->unique(['hospital_id', 'slug'], 'roles_hospital_slug_unique');
            $table->index(['scope', 'is_assignable']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug', 100)->unique();
            $table->string('description', 180);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'permission_id'], 'role_permission_unique');
        });

        Schema::create('membership_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('hospital_membership_id')->constrained('hospital_memberships')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['hospital_membership_id', 'role_id'], 'membership_role_unique');
            $table->index(['role_id', 'hospital_membership_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('hospital_memberships');
    }
};
