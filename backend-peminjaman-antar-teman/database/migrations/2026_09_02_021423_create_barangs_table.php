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
       Schema::create('barangs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('restrict');

        $table->string('nama_barang');
        $table->text('deskripsi');
        $table->enum('kondisi', ['B', 'R', 'P'])->default('B');// B = baik R = rusak ringan P = diperbaiki
        $table->enum('status', ['T', 'D', 'M'])->default('T');// T = tersedia D = dipinjam M = maintenance/ 

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
