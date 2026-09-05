<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_types', function (Blueprint $table) {
            // Field tambahan yang harus diisi pemohon, disimpan sbg JSON:
            // [{ "name": "nama_usaha", "label": "Nama Usaha", "type": "text", "required": true }, ...]
            $table->json('required_fields')->nullable()->after('template_body');
        });
    }

    public function down(): void
    {
        Schema::table('letter_types', function (Blueprint $table) {
            $table->dropColumn('required_fields');
        });
    }
};
