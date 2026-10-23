<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat publik halaman Linktree yang dipilih pemiliknya sendiri
 * (`/u/{public_slug}`), bukan username login.
 *
 * Kolom dibiarkan nullable supaya profil lama tetap ada: halaman publiknya baru
 * bisa dibuka setelah pemilik mengisi alamatnya di kartu Profil Linktree.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('linktree_profiles', function (Blueprint $table): void {
            $table->string('public_slug', 60)->nullable()->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('linktree_profiles', function (Blueprint $table): void {
            $table->dropUnique(['public_slug']);
            $table->dropColumn('public_slug');
        });
    }
};
