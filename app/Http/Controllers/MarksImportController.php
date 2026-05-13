<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportMarksRequest;
use App\Services\Academic\Imports\ExcelMarksImportService;
use Illuminate\Http\RedirectResponse;

class MarksImportController extends Controller
{
    public function store(ImportMarksRequest $request, ExcelMarksImportService $excelMarksImportService): RedirectResponse
    {
        $count = $excelMarksImportService->import($request->file('file'));

        return redirect()->back()->with('status', "Imported {$count} marks successfully.");
    }
}
