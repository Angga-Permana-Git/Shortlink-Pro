<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('action', 50);
            $table->string('resource_type', 50)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_id', 'actor_type']);
            $table->index('action');
            $table->index('resource_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};