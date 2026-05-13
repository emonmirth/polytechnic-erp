<?php

namespace App\Services\Academic;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AcademicActivityLogger
{
    /**
     * @param  array<string,mixed>  $properties
     */
    public function log(string $event, ?Model $subject = null, array $properties = []): void
    {
        AuditLog::query()->create([
            'actor_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $subject ? $subject::class : 'system',
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
