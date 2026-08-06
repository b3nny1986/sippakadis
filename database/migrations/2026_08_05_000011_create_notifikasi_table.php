<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opd_id')->nullable()->constrained('opd')->cascadeOnDelete();
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraan')->cascadeOnDelete();
            $table->string('tipe', 20)->default('Sistem');
            $table->string('kategori', 30)->nullable();
            $table->string('judul');
            $table->text('pesan');
            $table->jsonb('data')->nullable();
            $table->string('channel', 20)->default('Database');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['opd_id', 'is_read']);
            $table->index(['kendaraan_id', 'tipe', 'kategori', 'created_at']);
        });

        DB::statement(
            "ALTER TABLE notifikasi ADD CONSTRAINT notifikasi_tipe_check
             CHECK (tipe IN ('PKB','STNK','Status','Sistem'))"
        );

        DB::statement(
            "ALTER TABLE notifikasi ADD CONSTRAINT notifikasi_channel_check
             CHECK (channel IN ('Database','WhatsApp','Telegram','Email'))"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
