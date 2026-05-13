<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Services\Academic\TranscriptService;
use Illuminate\Http\Request;

class ResultVerificationController extends Controller
{
    public function show(Request $request, Result $result, string $token, TranscriptService $transcriptService)
    {
        abort_unless($request->hasValidSignature(), 403, 'Invalid or expired verification link.');
        abort_unless(hash_equals((string) $result->verification_token, $token), 403, 'Invalid token.');

        $result->load(['student.department', 'semester', 'session', 'items']);
        $isIntegrityValid = $transcriptService->verifySnapshotIntegrity($result);

        return view('verification.result', [
            'result' => $result,
            'isIntegrityValid' => $isIntegrityValid,
        ]);
    }
}
