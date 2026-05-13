<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Services\Academic\TranscriptService;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class TranscriptController extends Controller
{
    public function show(Result $result, TranscriptService $transcriptService): Response
    {
        return $transcriptService->generatePdfResponse($result);
    }
}
