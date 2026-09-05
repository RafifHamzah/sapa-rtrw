<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_category_id')->constrained()->restrictOnDelete();
            $table->string('type')->index();
            $table->unsignedBigInteger('amount');
            $table->string('description');
            $table->date('transaction_date')->index();
            $table->string('receipt_path')->nullable(); // Foto nota (opsional)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
