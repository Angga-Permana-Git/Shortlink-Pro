<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('keycloak_sub')->nullable()->after('sso_subject');
            $table->boolean('is_protected')->default(false)->after('role');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('keycloak_sub', 'users_keycloak_sub_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_keycloak_sub_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['keycloak_sub', 'is_protected']);
        });
    }
};