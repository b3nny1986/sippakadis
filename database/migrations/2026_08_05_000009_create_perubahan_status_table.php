<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perubahan_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();
            $table->foreignId('status_lama_id')->constrained('status_kendaraan')->restrictOnDelete();
            $table->foreignId('status_baru_id')->constrained('status_kendaraan')->restrictOnDelete();
            $table->text('alasan');
            $table->string('lampiran_path')->nullable();
            $table->string('status', 20)->default('Menunggu');
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->index(['kendaraan_id', 'status']);
            $table->index('status_baru_id');
        });

        DB::statement(
            "ALTER TABLE perubahan_status ADD CONSTRAINT perubahan_status_status_check
             CHECK (status IN ('Menunggu','Disetujui','Ditolak'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('perubahan_status');
    }
};
