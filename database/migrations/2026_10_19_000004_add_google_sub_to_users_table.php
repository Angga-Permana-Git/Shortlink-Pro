<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan penanda akun Google pada tabel pengguna.
 *
 * Akun lokal yang sudah ada boleh ditautkan ke Google: kolom ini yang diisi,
 * sedangkan auth_provider dan kata sandinya dibiarkan supaya kedua jalur masuk
 * tetap bisa dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_sub')->nullable()->after('keycloak_sub');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('google_sub', 'users_google_sub_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_google_sub_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_sub');
        });
    }
};
