<?php

namespace App\Services\Academic;

use App\Models\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResultLifecycleService
{
    public function publish(Result $result): Result
    {
        return DB::transaction(function () use ($result): Result {
            if ($result->is_locked) {
                return $result;
            }

            $result->loadMissing('items');
            if ($result->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'result' => 'Cannot publish an empty result snapshot.',
                ]);
            }

            $result->update([
                'publication_status' => 'published',
                'published_at' => now(),
                'verification_token' => $result->verification_token ?: (string) Str::uuid(),
            ]);

            app(TranscriptService::class)->ensureSnapshotHash($result->refresh());
            app(AcademicActivityLogger::class)->log('result.published', $result, [
                'publication_status' => 'published',
            ]);

            return $result->refresh();
        });
    }

    public function saveDraft(Result $result): Result
    {
        return DB::transaction(function () use ($result): Result {
            if ($result->is_locked) {
                return $result;
            }

            $result->update([
                'publication_status' => 'draft',
                'published_at' => null,
            ]);
            app(AcademicActivityLogger::class)->log('result.drafted', $result);

            return $result->refresh();
        });
    }

    public function lock(Result $result): Result
    {
        return DB::transaction(function () use ($result): Result {
            $result->update([
                'is_locked' => true,
                'locked_at' => now(),
            ]);
            app(AcademicActivityLogger::class)->log('result.locked', $result);

            return $result->refresh();
        });
    }

    public function unlock(Result $result): Result
    {
        return DB::transaction(function () use ($result): Result {
            $result->update([
                'is_locked' => false,
                'locked_at' => null,
            ]);
            app(AcademicActivityLogger::class)->log('result.unlocked', $result);

            return $result->refresh();
        });
    }
}
