<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->string('exam_type')->default('Regular');
            $table->foreignId('session_id')->constrained('academic_sessions')->restrictOnDelete();
            $table->integer('tc_mark')->default(0);
            $table->integer('tf_mark')->default(0);
            $table->integer('pc_mark')->default(0);
            $table->integer('pf_mark')->default(0);
            $table->integer('total_marks')->default(0);
            $table->decimal('grade_point', 3, 2)->default(0.00);
            $table->string('letter_grade', 2)->default('F');
            $table->string('exam_year');
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unique(['student_id', 'subject_id', 'semester_id', 'exam_year'], 'student_subject_exam_unique');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('marks'); }
};
