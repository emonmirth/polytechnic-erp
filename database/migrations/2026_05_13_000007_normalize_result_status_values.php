<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('results') || !Schema::hasColumn('results', 'status')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE results MODIFY status VARCHAR(20) NOT NULL DEFAULT 'PASSED'");
        }

        DB::table('results')->where('status', 'Passed')->update(['status' => 'PASSED']);
        DB::table('results')->where('status', 'Failed')->update(['status' => 'FAILED']);
        DB::table('results')->where('status', 'Referred')->update(['status' => 'REFERRED']);
    }

    public function down(): void
    {
        if (!Schema::hasTable('results') || !Schema::hasColumn('results', 'status')) {
            return;
        }

        DB::table('results')->where('status', 'PASSED')->update(['status' => 'Passed']);
        DB::table('results')->where('status', 'FAILED')->update(['status' => 'Failed']);
        DB::table('results')->where('status', 'REFERRED')->update(['status' => 'Referred']);
    }
};
