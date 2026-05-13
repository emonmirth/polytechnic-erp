<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('subject_code');
            $table->decimal('credit', 3, 1)->default(3.0);
            $table->integer('tc_marks')->default(0);
            $table->integer('tf_marks')->default(0);
            $table->integer('pc_marks')->default(0);
            $table->integer('pf_marks')->default(0);
            $table->unique(['department_id', 'subject_code'], 'dept_subject_code_unique');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('subjects'); }
};
