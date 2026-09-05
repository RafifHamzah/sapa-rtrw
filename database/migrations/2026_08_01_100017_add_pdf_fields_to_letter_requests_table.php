<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_requests', function (Blueprint $table) {
            // Isian field tambahan dari pemohon (key => value).
            $table->json('form_data')->nullable()->after('purpose');
            // Token acak untuk verifikasi publik surat.
            $table->string('qr_token')->nullable()->unique()->after('letter_number');
            // Lokasi file PDF hasil generate (disk public).
            $table->string('pdf_path')->nullable()->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('letter_requests', function (Blueprint $table) {
            $table->dropColumn(['form_data', 'qr_token', 'pdf_path']);
        });
    }
};
