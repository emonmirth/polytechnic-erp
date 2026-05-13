<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    protected $table = 'academic_sessions';

    protected $fillable = ['session_year', 'is_active'];

    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function results(): HasMany { return $this->hasMany(Result::class); }
}
