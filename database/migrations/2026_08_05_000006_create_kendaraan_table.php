<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $table->foreignId('jenis_id')->nullable()->constrained('jenis_kendaraan')->nullOnDelete();
            $table->foreignId('status_id')->constrained('status_kendaraan')->restrictOnDelete();
            $table->string('nopol', 20)->unique();
            $table->string('nopol_lama', 20)->nullable();
            $table->string('nama_pemilik')->nullable();
            $table->string('no_rangka')->nullable();
            $table->string('no_mesin')->nullable();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->smallInteger('tahun')->nullable();
            $table->string('warna')->nullable();
            $table->string('lokasi')->nullable();
            $table->date('masa_berlaku_pkb')->nullable();
            $table->date('masa_berlaku_stnk')->nullable();
            $table->string('pkb_status', 10)->nullable();
            $table->string('stnk_status', 10)->nullable();
            $table->decimal('nilai_pkb', 15, 2)->default(0);
            $table->decimal('nilai_swdkllj', 15, 2)->default(0);
            $table->string('sumber_data', 20)->default('manual');
            $table->boolean('is_verifikasi')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['opd_id', 'status_id']);
            $table->index('masa_berlaku_pkb');
            $table->index('masa_berlaku_stnk');
            $table->index('pkb_status');
            $table->index('stnk_status');
            $table->index('is_verifikasi');
        });

        DB::statement(
            "ALTER TABLE kendaraan ADD CONSTRAINT kendaraan_pkb_status_check
             CHECK (pkb_status IS NULL OR pkb_status IN ('AMAN','H30','H14','H7','H1','HARI_H','LEWAT'))"
        );

        DB::statement(
            "ALTER TABLE kendaraan ADD CONSTRAINT kendaraan_stnk_status_check
             CHECK (stnk_status IS NULL OR stnk_status IN ('AMAN','H30','H14','H7','H1','HARI_H','LEWAT'))"
        );

        DB::statement(
            "ALTER TABLE kendaraan ADD CONSTRAINT kendaraan_sumber_check
             CHECK (sumber_data IN ('csv','simpator','manual'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
