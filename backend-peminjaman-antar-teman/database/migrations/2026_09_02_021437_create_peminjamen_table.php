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
        // Tabel Utama Peminjaman
        Schema::create('peminjamen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjam_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('pemilik_id')->constrained('users')->onDelete('restrict');

            $table->date('tgl_pinjam');
            $table->date('tgl_tenggat');

            // Status disederhanakan (Status 'S' Selesai / 'T' Terlambat dihapus dari sini)
            $table->enum('status', ['M', 'Ds', 'Dt','S', 'A', 'B'])->default('M');
            // M  = Menunggu    (pengajuan baru dibuat, nunggu keputusan pemilik)
            // Ds = Disetujui   (pemilik setuju, barang belum serah terima)
            // Dt = Ditolak     (pemilik menolak pengajuan)
            // A  = Aktif       (barang sudah diserahterimakan / sedang dipinjam)
            // B  = Batal       (peminjam membatalkan sebelum disetujui)
            // S = Selesai

            $table->timestamps();
        });

        // Tabel Pivot sekaligus tempat mencatat detail & pengembalian barang
        Schema::create('barang_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamen')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            
            // Tanggal dan status dikembalikan dicatat di sini saat barang dikembalikan
            $table->date('tgl_kembali')->nullable();
            $table->enum('status_kembali', ['Selesai', 'Terlambat'])->nullable();
            
            $table->timestamps();

            $table->unique(['peminjaman_id', 'barang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_peminjaman');
        Schema::dropIfExists('peminjamen');
    }
};