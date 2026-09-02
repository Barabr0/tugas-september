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
            // M  = Menunggu    (pengajuan baru dibuat, nunggu keputusan pemilik)
            // Ds = Disetujui   (pemilik setuju, barang belum serah terima)
            // Dt = Ditolak     (pemilik menolak pengajuan)
            // A  = Aktif       (barang sudah diserahterimakan, sedang dipinjam)
            // S  = Selesai     (barang sudah dikembalikan tepat/sebelum tenggat)
            // T  = Terlambat   (lewat tgl_tenggat, barang belum dikembalikan)
            // B  = Batal       (peminjam membatalkan sebelum disetujui)

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
