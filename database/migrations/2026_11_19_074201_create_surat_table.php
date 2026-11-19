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
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable()->unique();

            // FOREIGN KEY. Nama tabel ditulis eksplisit karena
            // Laravel akan menebak "mahasiswas" (jamak bahasa Inggris).
            $table->foreignId('mahasiswa_id')
                ->constrained('mahasiswa')
                ->cascadeOnDelete();                 // mahasiswa dihapus -> suratnya ikut terhapus
            $table->foreignId('jenis_surat_id')
                ->constrained('jenis_surat')
                ->restrictOnDelete();                // jenis surat yang masih dipakai TIDAK boleh dihapus

            $table->text('keperluan');
            $table->string('status', 20)->default('diajukan')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
