<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultItem extends Model
{
    protected $fillable = [
        'result_id', 'subject_id', 'subject_name_snapshot', 'subject_code_snapshot',
        'credit_snapshot', 'tc_mark', 'tf_mark', 'pc_mark', 'pf_mark',
        'total_marks', 'letter_grade', 'grade_point', 'status'
    ];

    protected $casts = [
        'credit_snapshot' => 'decimal:1',
        'grade_point' => 'decimal:2',
    ];

    public function result(): BelongsTo { return $this->belongsTo(Result::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
}
