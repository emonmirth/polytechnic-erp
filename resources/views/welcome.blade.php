<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Polytechnic ERP</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
            background: #f8fafc;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        main {
            width: min(920px, calc(100% - 32px));
            padding: 48px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin: 0 0 12px;
            font-size: 40px;
            line-height: 1.1;
        }

        p {
            margin: 0 0 28px;
            max-width: 680px;
            color: #4b5563;
            font-size: 17px;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 6px;
            background: #b45309;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
        }

        dl {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 32px 0 0;
        }

        div {
            padding: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fffbeb;
        }

        dt {
            font-weight: 700;
            margin-bottom: 6px;
        }

        dd {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 720px) {
            main {
                padding: 28px;
            }

            h1 {
                font-size: 30px;
            }

            dl {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main>
        <h1>Polytechnic ERP</h1>
        <p>
            Academic result management for BTEB polytechnic workflows: student records,
            marks entry, GPA calculation, result publication, transcript snapshots, and
            QR-based verification.
        </p>

        <a href="{{ url('/admin') }}">Open Admin Panel</a>

        <dl>
            <div>
                <dt>Marks</dt>
                <dd>Single and bulk entry with subject-wise mark validation.</dd>
            </div>
            <div>
                <dt>Results</dt>
                <dd>Generated semester snapshots with GPA and referred-subject tracking.</dd>
            </div>
            <div>
                <dt>Verification</dt>
                <dd>Published transcripts include signed QR verification links.</dd>
            </div>
        </dl>
    </main>
</body>
</html>
