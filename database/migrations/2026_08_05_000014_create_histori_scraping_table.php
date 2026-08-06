<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histori_scraping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraan')->nullOnDelete();
            $table->string('nopol', 20);
            $table->string('status', 30);
            $table->jsonb('payload')->nullable();
            $table->date('pkb_sebelum')->nullable();
            $table->date('pkb_sesudah')->nullable();
            $table->date('stnk_sebelum')->nullable();
            $table->date('stnk_sesudah')->nullable();
            $table->boolean('ada_perubahan')->default(false);
            $table->timestamps();

            $table->index(['kendaraan_id', 'created_at']);
            $table->index('nopol');
            $table->index('status');
        });

        DB::statement(
            "ALTER TABLE histori_scraping ADD CONSTRAINT histori_status_check
             CHECK (status IN ('Ditemukan','Tidak Ditemukan','Gagal'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('histori_scraping');
    }
};
