<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bank_user', 'jumlah_topup')) {
            Schema::table('bank_user', function (Blueprint $table) {
                // Total akumulasi nominal top up
                $table->unsignedBigInteger('jumlah_topup')->default(0)->after('saldo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bank_user', 'jumlah_topup')) {
            Schema::table('bank_user', function (Blueprint $table) {
                $table->dropColumn('jumlah_topup');
            });
        }
    }
};