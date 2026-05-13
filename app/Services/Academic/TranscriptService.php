<?php

namespace App\Services\Academic;

use App\Models\Result;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TranscriptService
{
    public function buildVerificationUrl(Result $result): string
    {
        return URL::temporarySignedRoute(
            'verification.result',
            now()->addYears(3),
            [
                'result' => $result->id,
                'token' => $result->verification_token,
            ],
        );
    }

    public function ensureSnapshotHash(Result $result): string
    {
        $result->loadMissing(['student.department', 'semester', 'session', 'items']);
        $currentHash = $this->calculateSnapshotHash($result);

        if ($result->snapshot_hash !== null && $result->snapshot_hash !== $currentHash) {
            throw ValidationException::withMessages([
                'result' => 'Result snapshot integrity check failed.',
            ]);
        }

        if ($result->snapshot_hash === null) {
            $result->update([
                'snapshot_hash' => $currentHash,
                'transcript_generated_at' => now(),
                'verification_token' => $result->verification_token ?: (string) str()->uuid(),
            ]);
        }

        return $currentHash;
    }

    public function generatePdfResponse(Result $result): Response
    {
        $validator = Validator::make([
            'publication_status' => $result->publication_status,
        ], [
            'publication_status' => ['required', 'in:published'],
        ], [
            'publication_status.in' => 'Transcript can be generated only for published results.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($result): Response {
            $result->refresh()->loadMissing(['student.department', 'semester', 'session', 'items']);

            $hash = $this->calculateSnapshotHash($result);

            if ($result->is_locked && $result->snapshot_hash !== null && $result->snapshot_hash !== $hash) {
                throw ValidationException::withMessages([
                    'result' => 'Locked result integrity mismatch. Transcript generation blocked.',
                ]);
            }

            if (!$result->is_locked && ($result->snapshot_hash === null || $result->snapshot_hash !== $hash)) {
                $result->update([
                    'snapshot_hash' => $hash,
                    'transcript_generated_at' => now(),
                    'verification_token' => $result->verification_token ?: (string) str()->uuid(),
                ]);
            }

            $verificationUrl = $this->buildVerificationUrl($result);
            $qrSvg = app('qrcode')->format('svg')->size(120)->margin(1)->generate($verificationUrl);

            $pdf = Pdf::loadView('pdf.marksheet', [
                'result' => $result,
                'verificationUrl' => $verificationUrl,
                'qrSvg' => $qrSvg,
                'snapshotHash' => $result->snapshot_hash,
            ])->setPaper('a4');

            app(AcademicActivityLogger::class)->log('transcript.generated', $result, [
                'verification_url' => $verificationUrl,
            ]);

            $filename = sprintf(
                'transcript_%s_semester_%s.pdf',
                $result->student->roll_no,
                str_replace(' ', '_', strtolower($result->semester->name)),
            );

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        });
    }

    public function verifySnapshotIntegrity(Result $result): bool
    {
        if (!$result->snapshot_hash) {
            return false;
        }

        $result->loadMissing(['student.department', 'semester', 'session', 'items']);

        return hash_equals($result->snapshot_hash, $this->calculateSnapshotHash($result));
    }

    private function calculateSnapshotHash(Result $result): string
    {
        $result->loadMissing(['student.department', 'semester', 'session', 'items']);

        $items = $result->items
            ->sortBy('subject_code_snapshot')
            ->values()
            ->map(static fn ($item) => [
                'subject_code' => $item->subject_code_snapshot,
                'subject_name' => $item->subject_name_snapshot,
                'credit' => (float) $item->credit_snapshot,
                'tc_mark' => (int) $item->tc_mark,
                'tf_mark' => (int) $item->tf_mark,
                'pc_mark' => (int) $item->pc_mark,
                'pf_mark' => (int) $item->pf_mark,
                'total_marks' => (int) $item->total_marks,
                'grade_point' => (float) $item->grade_point,
                'letter_grade' => $item->letter_grade,
                'status' => $item->status,
            ])->all();

        $payload = [
            'result_id' => $result->id,
            'student' => [
                'name' => $result->student?->name,
                'roll_no' => $result->student?->roll_no,
                'reg_no' => $result->student?->reg_no,
                'department' => $result->student?->department?->name,
            ],
            'semester' => $result->semester?->name,
            'session' => $result->session?->session_year,
            'gpa' => (float) $result->gpa,
            'cgpa' => (float) $result->cgpa,
            'status' => $result->status,
            'publication_status' => $result->publication_status,
            'total_marks' => (int) $result->total_marks,
            'items' => $items,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
