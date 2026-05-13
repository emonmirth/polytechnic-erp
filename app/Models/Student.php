<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'roll_no', 'reg_no', 'department_id', 'session_id', 
        'semester_id', 'shift', 'admission_date', 'phone', 'email', 'is_active'
    ];

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function session(): BelongsTo { return $this->belongsTo(Session::class, 'session_id'); }
    public function currentSemester(): BelongsTo { return $this->belongsTo(Semester::class, 'semester_id'); }
    public function marks(): HasMany { return $this->hasMany(Mark::class); }
    public function results(): HasMany { return $this->hasMany(Result::class); }
}
