<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dues_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dues_id')->constrained('dues')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('payment_method')->index();
            // Terisi saat pembayaran benar-benar lunas (settlement). Untuk
            // pembayaran online, null sampai callback Midtrans dikonfirmasi.
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dues_payments');
    }
};
