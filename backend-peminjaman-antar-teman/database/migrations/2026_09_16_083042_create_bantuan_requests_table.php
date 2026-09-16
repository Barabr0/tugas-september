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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipe_request'); // 'barang' atau 'uang'
            $table->foreignId('referensi_id'); // ID barang atau ID peminjaman
            $table->string('aksi_diminta'); // 'edit', 'hapus', 'batalkan'
            $table->text('alasan'); // Alasan user minta bantuan
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending');
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
