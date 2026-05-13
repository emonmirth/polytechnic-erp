<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'name', 'subject_code', 'department_id', 'semester_id', 'credit',
        'tc_marks', 'tf_marks', 'pc_marks', 'pf_marks'
    ];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }

    public function getTotalMarks(): int
    {
        return $this->tc_marks + $this->tf_marks + $this->pc_marks + $this->pf_marks;
    }
}
