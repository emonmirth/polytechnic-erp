<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('students', 'current_semester_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['current_semester_id']);
                $table->dropUnique(['roll']);
                $table->dropUnique(['registration']);
            });

            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('current_semester_id', 'semester_id');
                $table->renameColumn('roll', 'roll_no');
                $table->renameColumn('registration', 'reg_no');
            });

            Schema::table('students', function (Blueprint $table) {
                $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
                $table->unique('roll_no');
                $table->unique('reg_no');
            });
        }

        if (! Schema::hasColumn('students', 'shift')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('shift')->default('Morning')->after('reg_no');
            });
        }

        if (Schema::hasColumn('subjects', 'code')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropUnique('dept_subject_code_unique');
            });

            Schema::table('subjects', function (Blueprint $table) {
                $table->renameColumn('code', 'subject_code');
                $table->renameColumn('theory_continuous', 'tc_marks');
                $table->renameColumn('theory_final', 'tf_marks');
                $table->renameColumn('practical_continuous', 'pc_marks');
                $table->renameColumn('practical_final', 'pf_marks');
            });

            Schema::table('subjects', function (Blueprint $table) {
                $table->unique(['department_id', 'subject_code'], 'dept_subject_code_unique');
                $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subjects', 'subject_code')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropUnique('dept_subject_code_unique');
            });

            Schema::table('subjects', function (Blueprint $table) {
                $table->renameColumn('subject_code', 'code');
                $table->renameColumn('tc_marks', 'theory_continuous');
                $table->renameColumn('tf_marks', 'theory_final');
                $table->renameColumn('pc_marks', 'practical_continuous');
                $table->renameColumn('pf_marks', 'practical_final');
            });

            Schema::table('subjects', function (Blueprint $table) {
                $table->unique(['department_id', 'code'], 'dept_subject_code_unique');
                $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('students', 'semester_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['semester_id']);
                $table->dropUnique(['roll_no']);
                $table->dropUnique(['reg_no']);
            });

            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('semester_id', 'current_semester_id');
                $table->renameColumn('roll_no', 'roll');
                $table->renameColumn('reg_no', 'registration');
            });

            Schema::table('students', function (Blueprint $table) {
                $table->foreign('current_semester_id')->references('id')->on('semesters')->restrictOnDelete();
                $table->unique('roll');
                $table->unique('registration');
            });
        }

        if (Schema::hasColumn('students', 'shift')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('shift');
            });
        }
    }
};
