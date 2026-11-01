<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename Linktree -> Bio (tabel + kolom FK).
 *
 * Migrasi lama membuat `linktree_profiles/links/events`; model dan test
 * sekarang memakai `bio_profiles/links/events` dengan kolom
 * `bio_profile_id` / `bio_link_id`. Migrasi ini menyamakan DB lama
 * (server/produksi) tanpa menghapus data, dan install baru tetap
 * berakhir di nama bio_* karena migrasi lama + migrasi ini jalan berurutan.
 *
 * Ditulis tanpa doctrine/dbal: rename kolom memakai SQL RENAME COLUMN yang
 * didukung MySQL 8 dan SQLite 3.25+.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sudah dalam keadaan baru -> tidak ada kerjaan.
        if (! Schema::hasTable('linktree_profiles') && Schema::hasTable('bio_profiles')) {
            return;
        }

        $driver = DB::getDriverName();

        // 1. Lepas FK lama (hanya MySQL yang menegakkannya saat rename).
        if ($driver === 'mysql') {
            if (Schema::hasTable('linktree_events')) {
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->dropForeign(['linktree_profile_id']);
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->dropForeign(['linktree_link_id']);
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->dropIndex(['linktree_profile_id', 'jenis', 'created_at']);
                    });
                } catch (Throwable) {
                }
            }

            if (Schema::hasTable('linktree_links')) {
                try {
                    Schema::table('linktree_links', function ($table): void {
                        $table->dropForeign(['profile_id']);
                    });
                } catch (Throwable) {
                }
            }
        }

        // 2. Matikan FK sementara supaya SQLite/MySQL tidak menolak rename.
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } catch (Throwable) {
            }
        }

        try {
            // 3. Rename tabel.
            if (Schema::hasTable('linktree_profiles')) {
                Schema::rename('linktree_profiles', 'bio_profiles');
            }
            if (Schema::hasTable('linktree_links')) {
                Schema::rename('linktree_links', 'bio_links');
            }
            if (Schema::hasTable('linktree_events')) {
                Schema::rename('linktree_events', 'bio_events');
            }

            // 4. Rename kolom FK di bio_events (tanpa DBAL).
            if (Schema::hasTable('bio_events')) {
                $kolom = Schema::getColumnListing('bio_events');
                if (in_array('linktree_profile_id', $kolom, true) && ! in_array('bio_profile_id', $kolom, true)) {
                    DB::statement('ALTER TABLE bio_events RENAME COLUMN linktree_profile_id TO bio_profile_id');
                }
                if (in_array('linktree_link_id', $kolom, true) && ! in_array('bio_link_id', $kolom, true)) {
                    DB::statement('ALTER TABLE bio_events RENAME COLUMN linktree_link_id TO bio_link_id');
                }
            }
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } else {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (Throwable) {
                }
            }
        }

        // 5. Pasang kembali FK + indeks (MySQL saja; SQLite mengandalkan
        // skema dari migrasi saat fresh, enforcement via PRAGMA).
        if ($driver === 'mysql') {
            if (Schema::hasTable('bio_links')) {
                try {
                    Schema::table('bio_links', function ($table): void {
                        $table->foreign('profile_id')->references('id')->on('bio_profiles')->cascadeOnDelete();
                    });
                } catch (Throwable) {
                }
            }

            if (Schema::hasTable('bio_events')) {
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->foreign('bio_profile_id')->references('id')->on('bio_profiles')->cascadeOnDelete();
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->foreign('bio_link_id')->references('id')->on('bio_links')->nullOnDelete();
                    });
                } catch (Throwable) {
                }
                // Indeks dibuat bila belum ada (hindari duplikat saat migrate:refresh).
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->index(['bio_profile_id', 'jenis', 'created_at']);
                    });
                } catch (Throwable) {
                }
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasTable('bio_events')) {
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->dropForeign(['bio_profile_id']);
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->dropForeign(['bio_link_id']);
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('bio_events', function ($table): void {
                        $table->dropIndex(['bio_profile_id', 'jenis', 'created_at']);
                    });
                } catch (Throwable) {
                }
            }

            if (Schema::hasTable('bio_links')) {
                try {
                    Schema::table('bio_links', function ($table): void {
                        $table->dropForeign(['profile_id']);
                    });
                } catch (Throwable) {
                }
            }
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } catch (Throwable) {
            }
        }

        try {
            if (Schema::hasTable('bio_events')) {
                $kolom = Schema::getColumnListing('bio_events');
                if (in_array('bio_profile_id', $kolom, true) && ! in_array('linktree_profile_id', $kolom, true)) {
                    DB::statement('ALTER TABLE bio_events RENAME COLUMN bio_profile_id TO linktree_profile_id');
                }
                if (in_array('bio_link_id', $kolom, true) && ! in_array('linktree_link_id', $kolom, true)) {
                    DB::statement('ALTER TABLE bio_events RENAME COLUMN bio_link_id TO linktree_link_id');
                }
            }

            if (Schema::hasTable('bio_profiles')) {
                Schema::rename('bio_profiles', 'linktree_profiles');
            }
            if (Schema::hasTable('bio_links')) {
                Schema::rename('bio_links', 'linktree_links');
            }
            if (Schema::hasTable('bio_events')) {
                Schema::rename('bio_events', 'linktree_events');
            }
        } finally {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } else {
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } catch (Throwable) {
                }
            }
        }

        if ($driver === 'mysql') {
            if (Schema::hasTable('linktree_links')) {
                try {
                    Schema::table('linktree_links', function ($table): void {
                        $table->foreign('profile_id')->references('id')->on('linktree_profiles')->cascadeOnDelete();
                    });
                } catch (Throwable) {
                }
            }

            if (Schema::hasTable('linktree_events')) {
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->foreign('linktree_profile_id')->references('id')->on('linktree_profiles')->cascadeOnDelete();
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->foreign('linktree_link_id')->references('id')->on('linktree_links')->nullOnDelete();
                    });
                } catch (Throwable) {
                }
                try {
                    Schema::table('linktree_events', function ($table): void {
                        $table->index(['linktree_profile_id', 'jenis', 'created_at']);
                    });
                } catch (Throwable) {
                }
            }
        }
    }
};
