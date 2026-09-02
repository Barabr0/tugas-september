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
            Schema::create('peminjamen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            $table->foreignId('peminjam_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('pemilik_id')->constrained('users')->onDelete('restrict');

            $table->date('tgl_pinjam');
            $table->date('tgl_tenggat');
            $table->date('tgl_kembali')->nullable();

            $table->enum('status', ['M', 'Ds', 'Dt', 'A', 'S', 'T', 'B'])->default('M');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamen');
    }
};
