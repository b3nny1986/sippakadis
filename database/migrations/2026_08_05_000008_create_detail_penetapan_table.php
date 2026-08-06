<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penetapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penetapan_id')->constrained('pengajuan_penetapan')->cascadeOnDelete();
            $table->string('uraian');
            $table->decimal('volume', 15, 2)->default(1);
            $table->string('satuan', 30)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penetapan');
    }
};
