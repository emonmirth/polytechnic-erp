<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $fillable = ['name', 'level', 'shift', 'is_active'];

    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
    public function marks(): HasMany { return $this->hasMany(Mark::class); }
    public function results(): HasMany { return $this->hasMany(Result::class); }
}
