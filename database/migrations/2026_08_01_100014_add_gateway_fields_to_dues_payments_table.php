<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dues_payments', function (Blueprint $table) {
            $table->string('status')->default(PaymentStatus::Pending->value)->after('payment_method')->index();
            // Order id unik yang dikirim ke Midtrans (order_id pada Snap).
            $table->string('midtrans_order_id')->nullable()->unique()->after('status');
            // transaction_id & transaction_status yang dikembalikan Midtrans.
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_status')->nullable()->after('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('dues_payments', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_status',
            ]);
        });
    }
};
