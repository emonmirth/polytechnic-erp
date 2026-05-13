<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Transcript</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .sheet { border: 2px solid #111827; padding: 18px; }
        .header { text-align: center; border-bottom: 1px solid #9ca3af; padding-bottom: 12px; margin-bottom: 12px; }
        .title { font-size: 20px; font-weight: 700; letter-spacing: 1px; }
        .subtitle { font-size: 13px; margin-top: 4px; }
        .meta { width: 100%; margin-bottom: 10px; }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta .label { font-weight: 700; width: 140px; }
        table.marks { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .marks th, .marks td { border: 1px solid #6b7280; padding: 6px; text-align: center; }
        .marks th { background: #f3f4f6; }
        .left { text-align: left !important; }
        .summary { width: 100%; margin-top: 12px; }
        .summary td { padding: 4px; }
        .badge { display: inline-block; padding: 2px 8px; border: 1px solid #111827; font-weight: 700; }
        .footer { margin-top: 16px; border-top: 1px solid #9ca3af; padding-top: 10px; font-size: 10px; }
        .qr-wrap { margin-top: 10px; }
        .small { font-size: 10px; word-break: break-all; }
    </style>
</head>
<body>
<div class="sheet">
    <div class="header">
        <div class="title">BTEB INSTITUTIONAL MARKSHEET</div>
        <div class="subtitle">Official Academic Transcript</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Student Name</td><td>: {{ $result->student?->name }}</td>
            <td class="label">Roll No</td><td>: {{ $result->student?->roll_no }}</td>
        </tr>
        <tr>
            <td class="label">Registration No</td><td>: {{ $result->student?->reg_no }}</td>
            <td class="label">Department</td><td>: {{ $result->student?->department?->name }}</td>
        </tr>
        <tr>
            <td class="label">Session</td><td>: {{ $result->session?->session_year }}</td>
            <td class="label">Semester</td><td>: {{ $result->semester?->name }}</td>
        </tr>
    </table>

    <table class="marks">
        <thead>
        <tr>
            <th>Code</th>
            <th class="left">Subject</th>
            <th>Credit</th>
            <th>TC</th>
            <th>TF</th>
            <th>PC</th>
            <th>PF</th>
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
                <td class="left">{{ $item->subject_name_snapshot }}</td>
                <td>{{ number_format((float)$item->credit_snapshot, 1) }}</td>
                <td>{{ $item->tc_mark }}</td>
                <td>{{ $item->tf_mark }}</td>
                <td>{{ $item->pc_mark }}</td>
                <td>{{ $item->pf_mark }}</td>
                <td>{{ $item->total_marks }}</td>
                <td>{{ $item->letter_grade }}</td>
                <td>{{ number_format((float)$item->grade_point, 2) }}</td>
                <td>{{ $item->status }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td><strong>Total Marks:</strong> {{ $result->total_marks }}</td>
            <td><strong>GPA:</strong> {{ number_format((float)$result->gpa, 2) }}</td>
            <td><strong>CGPA:</strong> {{ number_format((float)$result->cgpa, 2) }}</td>
            <td><span class="badge">{{ strtoupper($result->status) }}</span></td>
        </tr>
    </table>

    <div class="footer">
        <div><strong>Verification URL:</strong> {{ $verificationUrl }}</div>
        <div><strong>Snapshot Hash:</strong> {{ $snapshotHash }}</div>
        <div class="qr-wrap">{!! $qrSvg !!}</div>
        <div class="small">Scan QR to verify transcript authenticity via signed verification URL.</div>
    </div>
</div>
</body>
</html>
