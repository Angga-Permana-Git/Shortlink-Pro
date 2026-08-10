<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug', 100)->unique();
            $table->text('destination_url');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('password_hash')->nullable();
            $table->unsignedBigInteger('total_clicks')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};