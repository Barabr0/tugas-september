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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User yang membuat laporan
            $table->string('tipe_laporan'); // Contoh: 'Kendala Transaksi', 'Barang Rusak'
            $table->text('deskripsi'); // Penjelasan dari user
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending'); // Status awal pending
            $table->text('alasan_admin')->nullable(); // Diisi admin saat menyetujui/menolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
