<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 60);
            $table->string('entitas_tipe', 60)->nullable();
            $table->unsignedBigInteger('entitas_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->jsonb('data_lama')->nullable();
            $table->jsonb('data_baru')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['entitas_tipe', 'entitas_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
