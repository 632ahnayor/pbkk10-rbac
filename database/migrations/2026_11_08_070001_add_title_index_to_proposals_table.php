<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom judul sering dicari dari tabel proposal admin.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex(['title']);
        });
    }
};
