<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();                              // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('nim', 15)->unique();       // NIM = string, bukan angka
            $table->string('nama', 100);
            $table->string('prodi', 50);
            $table->unsignedSmallInteger('angkatan');
            $table->timestamps();                      // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
