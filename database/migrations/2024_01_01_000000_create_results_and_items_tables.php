<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('results') || Schema::hasTable('result_items')) {
            return;
        }

        if (
            !Schema::hasTable('students') ||
            !Schema::hasTable('semesters') ||
            !Schema::hasTable('academic_sessions') ||
            !Schema::hasTable('subjects')
        ) {
            return;
        }

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->foreignId('session_id')->constrained('academic_sessions')->restrictOnDelete();
            $table->decimal('gpa', 3, 2)->default(0.00);
            $table->decimal('cgpa', 3, 2)->default(0.00);
            $table->integer('total_marks')->default(0);
            $table->string('status', 20)->default('PASSED');
            $table->enum('publication_status', ['draft', 'published'])->default('draft');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->string('verification_token')->unique()->nullable();
            $table->json('referred_subject_codes')->nullable();
            $table->string('snapshot_hash', 64)->nullable();
            $table->timestamp('transcript_generated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('subject_name_snapshot');
            $table->string('subject_code_snapshot');
            $table->decimal('credit_snapshot', 3, 1);
            $table->integer('tc_mark')->default(0);
            $table->integer('tf_mark')->default(0);
            $table->integer('pc_mark')->default(0);
            $table->integer('pf_mark')->default(0);
            $table->integer('total_marks');
            $table->string('letter_grade', 2);
            $table->decimal('grade_point', 3, 2);
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('result_items')) {
            Schema::dropIfExists('result_items');
        }

        if (Schema::hasTable('results')) {
            Schema::dropIfExists('results');
        }
    }
};
