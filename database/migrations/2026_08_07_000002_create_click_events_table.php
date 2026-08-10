<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('click_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->constrained('short_urls')->cascadeOnDelete();
            $table->string('status', 20)->default('success');
            $table->string('ip_hash', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('short_url_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_events');
    }
};