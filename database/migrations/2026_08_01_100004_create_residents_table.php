<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // NIK dienkripsi di level aplikasi (cast 'encrypted'), sehingga tidak
            // bisa di-unique-kan di DB. Panjang text untuk menampung ciphertext.
            $table->text('nik');
            $table->string('full_name');
            $table->string('gender')->index();
            $table->string('birth_place')->nullable();
            $table->date('birth_date');
            $table->string('relationship')->index();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('occupation')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
