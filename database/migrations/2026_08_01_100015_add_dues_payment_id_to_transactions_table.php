<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tautan kanonik: transaksi kas yang berasal dari satu pembayaran
            // iuran. Dipakai untuk idempotensi (cegah dobel entri kas).
            $table->foreignId('dues_payment_id')
                ->nullable()
                ->after('created_by')
                ->constrained('dues_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dues_payment_id');
        });
    }
};
