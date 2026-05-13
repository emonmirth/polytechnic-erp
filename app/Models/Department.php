<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'is_active'];

    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function subjects(): HasMany { return $this->hasMany(Subject::class); }
}
