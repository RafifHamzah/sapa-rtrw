<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('rw_number');
            $table->string('name')->nullable();
            $table->string('village');
            $table->string('district');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code')->nullable();
            $table->text('address')->nullable();
            $table->string('chairman_name')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rts');
    }
};
