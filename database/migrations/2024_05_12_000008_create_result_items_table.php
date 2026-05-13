<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('result_items')) {
            return;
        }

        Schema::create('result_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->cascadeOnDelete();
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
    }
};
