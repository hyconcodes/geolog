<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SIWES Final Report - {{ $user->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a365d;
            font-size: 24px;
        }
        .student-info {
            margin-bottom: 30px;
        }
        .student-info p {
            margin: 5px 0;
        }
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .activity-table th, .activity-table td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        .activity-table th {
            background-color: #f2f2f2;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin: 50px auto 10px;
            width: 80%;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STUDENT INDUSTRIAL WORK EXPERIENCE SCHEME (SIWES)</h1>
        <h2>FINAL TRAINING REPORT</h2>
    </div>

    <div class="student-info">
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Matriculation Number:</strong> {{ $user->matric_no }}</p>
        <p><strong>Institution:</strong> Bamidele Olumilua University of Education, Science and Technology, Ikere-Ekiti</p>
        <p><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</p>
        <p><strong>Company/Organization:</strong> {{ $user->ppa_company_name ?? 'N/A' }}</p>
        <p><strong>Training Period:</strong> 
            {{ $user->siwes_start_date ? $user->siwes_start_date->format('F j, Y') : 'N/A' }} to 
            {{ $user->siwes_end_date ? $user->siwes_end_date->format('F j, Y') : 'N/A' }}
        </p>
    </div>

    <h3>WEEKLY ACTIVITY SUMMARY</h3>
    <table class="activity-table">
        <thead>
            <tr>
                <th>Week</th>
                <th>Date</th>
                <th>Activity Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities as $activity)
                <tr>
                    <td>Week {{ $activity->week_number }}</td>
                    <td>{{ $activity->activity_date->format('M j, Y') }}</td>
                    <td>{{ $activity->activity_description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>OVERALL EXPERIENCE</h3>
    <div style="margin-bottom: 20px;">
        [Student should provide a detailed summary of their overall experience, skills gained, challenges faced, and how the SIWES program has contributed to their professional development.]
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Student's Signature</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Supervisor's Signature & Date</p>
        </div>
    </div>

    <div class="signature-section" style="margin-top: 20px;">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Company Stamp</p>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>SIWES Coordinator's Signature & Date</p>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }} | LogX - Smart Digital Logbook System</p>
    </div>
</body>
</html>
