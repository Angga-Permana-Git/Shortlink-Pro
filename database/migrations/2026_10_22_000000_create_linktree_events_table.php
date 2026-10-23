<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linktree_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('linktree_profile_id')->constrained('linktree_profiles')->cascadeOnDelete();
            $table->foreignId('linktree_link_id')->nullable()->constrained('linktree_links')->nullOnDelete();
            $table->string('jenis', 20);
            $table->string('ip_hash', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['linktree_profile_id', 'jenis', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linktree_events');
    }
};
