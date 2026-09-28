<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laporan / bantuan antar user terkait peminjaman:
     * peminjam mengajukan "batal pinjam" atau "ganti barang" ke pemilik barang.
     *
     * Pastikan migration ini berjalan SETELAH tabel users, barangs, dan peminjamen.
     */
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');   // Pelapor = peminjam
            $table->foreignId('target_id')->constrained('users')->onDelete('cascade'); // Target = pemilik barang

            $table->string('tipe_laporan'); // batal_pinjam | ganti_barang

            $table->foreignId('peminjaman_id')->constrained('peminjamen')->onDelete('cascade');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->nullOnDelete();      // Barang lama (ganti_barang)
            $table->foreignId('barang_baru_id')->nullable()->constrained('barangs')->nullOnDelete(); // Barang pengganti

            $table->text('deskripsi'); // Alasan dari pelapor
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('alasan_respons')->nullable(); // Catatan dari target
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};