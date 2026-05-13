<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'report' => ['required', 'in:department_pass_percentage,semester_gpa_trend,top_failing_subjects'],
        ];
    }
}
