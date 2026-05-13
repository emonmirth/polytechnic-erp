<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (!Schema::hasColumn('results', 'publication_status')) {
                $table->enum('publication_status', ['draft', 'published'])->default('draft')->after('status');
            }
            if (!Schema::hasColumn('results', 'referred_subject_codes')) {
                $table->json('referred_subject_codes')->nullable()->after('verification_token');
            }
            if (!Schema::hasColumn('results', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('is_locked');
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('results', 'publication_status') ? 'publication_status' : null,
                Schema::hasColumn('results', 'referred_subject_codes') ? 'referred_subject_codes' : null,
                Schema::hasColumn('results', 'locked_at') ? 'locked_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
