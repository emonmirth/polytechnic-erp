<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 24px; color: #111827; }
        .card { max-width: 820px; margin: 0 auto; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 20px; }
        .ok { color: #065f46; font-weight: 700; }
        .bad { color: #991b1b; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; font-size: 14px; }
        th { background: #f9fafb; text-align: left; }
    </style>
</head>
<body>
<div class="card">
    <h1>Public Result Verification</h1>
    <p>
        Integrity Status:
        @if($isIntegrityValid)
            <span class="ok">VALID SNAPSHOT</span>
        @else
            <span class="bad">INVALID SNAPSHOT</span>
        @endif
    </p>
    <p><strong>Student:</strong> {{ $result->student?->name }} ({{ $result->student?->roll_no }})</p>
    <p><strong>Semester:</strong> {{ $result->semester?->name }} | <strong>Session:</strong> {{ $result->session?->session_year }}</p>
    <p><strong>GPA:</strong> {{ number_format((float)$result->gpa, 2) }} | <strong>CGPA:</strong> {{ number_format((float)$result->cgpa, 2) }} | <strong>Status:</strong> {{ strtoupper($result->status) }}</p>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject</th>
                <th>Total</th>
                <th>Grade</th>
                <th>GP</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($result->items as $item)
            <tr>
                <td>{{ $item->subject_code_snapshot }}</td>
                <td>{{ $item->subject_name_snapshot }}</td>
                <td>{{ $item->total_marks }}</td>
                <td>{{ $item->letter_grade }}</td>
                <td>{{ number_format((float)$item->grade_point, 2) }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
