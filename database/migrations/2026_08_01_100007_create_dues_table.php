<?php

use App\Enums\DuesStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedBigInteger('amount');
            $table->string('status')->default(DuesStatus::Unpaid->value)->index();
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Satu tagihan iuran per keluarga per periode (bulan+tahun).
            $table->unique(['family_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dues');
    }
};
