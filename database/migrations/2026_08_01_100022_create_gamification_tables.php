<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('xp')->default(0)->after('status');
        });

        // Riwayat perolehan XP (juga jadi guard idempoten via source_key).
        Schema::create('xp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->string('source_key')->nullable();
            $table->timestamps();

            $table->index('user_id');
            // Satu sumber (mis. "dues_payment:12") hanya memberi XP sekali per user.
            $table->unique(['user_id', 'source_key']);
        });

        // Badge yang sudah diraih warga.
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('badge');
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'badge']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('xp_logs');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('xp');
        });
    }
};
