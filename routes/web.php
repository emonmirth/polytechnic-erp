<?php

use App\Http\Controllers\MarksImportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ResultVerificationController;
use App\Http\Controllers\TranscriptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', fn () => redirect('/admin/login'))->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/results/{result}/transcript', [TranscriptController::class, 'show'])
        ->name('results.transcript');
    Route::post('/marks/import', [MarksImportController::class, 'store'])->name('marks.import');
    Route::get('/reports/export', ReportExportController::class)->name('reports.export');
});

Route::get('/verification/results/{result}/{token}', [ResultVerificationController::class, 'show'])
    ->middleware('signed')
    ->name('verification.result');
