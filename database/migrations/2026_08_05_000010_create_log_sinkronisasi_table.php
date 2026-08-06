<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_sinkronisasi', function (Blueprint $table) {
            $table->id();
            $table->string('tipe', 20)->default('scraping');
            $table->string('nopol', 20)->nullable();
            $table->string('status', 30);
            $table->jsonb('request_json')->nullable();
            $table->jsonb('response_json')->nullable();
            $table->text('pesan')->nullable();
            $table->integer('durasi_ms')->nullable();
            $table->foreignId('dijalankan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('nopol');
            $table->index('tipe');
        });

        DB::statement(
            "ALTER TABLE log_sinkronisasi ADD CONSTRAINT log_tipe_check
             CHECK (tipe IN ('scraping','import'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('log_sinkronisasi');
    }
};
