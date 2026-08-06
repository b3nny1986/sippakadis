<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_penetapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $table->smallInteger('tahun_pajak');
            $table->text('catatan')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->string('status', 20)->default('Menunggu');
            $table->string('nomor_penetapan', 100)->nullable();
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->index(['kendaraan_id', 'status']);
            $table->index(['opd_id', 'status']);
            $table->index('tahun_pajak');
        });

        DB::statement(
            "ALTER TABLE pengajuan_penetapan ADD CONSTRAINT pengajuan_status_check
             CHECK (status IN ('Menunggu','Diproses','Disetujui','Ditolak','Selesai'))"
        );

        // Anti duplikat: satu kendaraan hanya boleh punya satu pengajuan aktif (non-Ditolak) per tahun pajak.
        DB::statement(
            "CREATE UNIQUE INDEX pengajuan_aktif_unique
             ON pengajuan_penetapan (kendaraan_id, tahun_pajak)
             WHERE status <> 'Ditolak'"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_penetapan');
    }
};
