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
        Schema::create('bantuan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminta_id')->constrained('users')->onDelete('cascade'); // User yang meminta
            $table->foreignId('target_id')->constrained('users')->onDelete('cascade');   // User yang diminta
            $table->string('tipe_request'); // Contoh: 'Tagihan Uang', 'Kembalikan Barang'
            $table->text('deskripsi');      // Rincian permintaan
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('alasan')->nullable(); // Diisi target user saat menolak/menyetujui
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bantuan_requests');
    }
};
