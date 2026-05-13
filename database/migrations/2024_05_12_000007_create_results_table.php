<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('results')) {
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
            $table->boolean('is_locked')->default(false);
            $table->string('verification_token')->unique()->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        if (Schema::hasTable('results')) {
            Schema::dropIfExists('results');
        }
    }
};
