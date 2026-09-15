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
        Schema::create('peminjaman_uangs', function (Blueprint $table) {
             $table->id();
            $table->foreignId('pemberi_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('peminjam_id')->constrained('users')->onDelete('restrict');

            $table->decimal('nominal', 15, 2);
            $table->date('tgl_pinjam');
            $table->date('tgl_tenggat');
            $table->date('tgl_lunas')->nullable();

            $table->enum('status', ['M', 'Ds', 'Dt', 'A', 'S', 'B'])->default('M');
            // M  = Menunggu     (pengajuan baru, nunggu keputusan pemberi)
            // Ds = Disetujui    (pemberi setuju, uang belum diserahkan)
            // Dt = Ditolak      (pemberi menolak pengajuan)
            // A  = Aktif        (uang sudah diserahkan, sedang berjalan)
            // S  = Selesai      (sudah lunas dibayar)
            // B  = Batal        (peminjam batalkan sebelum disetujui)

            $table->timestamps();

            $table->index(['peminjam_id', 'status']);
            $table->index(['pemberi_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_uangs');
    }
};
