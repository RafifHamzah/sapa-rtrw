<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained()->cascadeOnDelete();
            $table->string('kk_number')->unique();
            // Kepala keluarga. Tidak diberi FK constraint di level DB karena
            // relasi families <-> residents saling merujuk (circular); integritas
            // dijaga di level aplikasi. Kolom di-index agar lookup tetap cepat.
            $table->unsignedBigInteger('head_resident_id')->nullable()->index();
            $table->text('address');
            $table->string('house_number')->nullable();
            $table->string('rt_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
