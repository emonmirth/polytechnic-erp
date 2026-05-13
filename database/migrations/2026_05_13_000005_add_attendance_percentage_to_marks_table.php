<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('marks', function (Blueprint $table): void {
            if (!Schema::hasColumn('marks', 'attendance_percentage')) {
                $table->decimal('attendance_percentage', 5, 2)->nullable()->after('exam_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marks', function (Blueprint $table): void {
            if (Schema::hasColumn('marks', 'attendance_percentage')) {
                $table->dropColumn('attendance_percentage');
            }
        });
    }
};
