<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            if (!Schema::hasColumn('results', 'snapshot_hash')) {
                $table->string('snapshot_hash', 64)->nullable()->after('referred_subject_codes');
            }
            if (!Schema::hasColumn('results', 'transcript_generated_at')) {
                $table->timestamp('transcript_generated_at')->nullable()->after('snapshot_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('results', 'snapshot_hash') ? 'snapshot_hash' : null,
                Schema::hasColumn('results', 'transcript_generated_at') ? 'transcript_generated_at' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
