<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peran pengguna kini disimpan di tabel otorisasi Spatie (model_has_roles).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('mahasiswa')->after('password');
        });
    }
};
