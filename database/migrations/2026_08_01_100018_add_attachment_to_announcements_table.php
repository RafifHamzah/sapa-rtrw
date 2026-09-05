<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('content'); // Lampiran (disk public)
            $table->index(['is_pinned', 'published_at']); // Untuk urutan feed warga
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_pinned', 'published_at']);
            $table->dropColumn('attachment_path');
        });
    }
};
