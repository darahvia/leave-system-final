<!DOCTYPE html>
<html>
<head>
    <title>CTO Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            position: relative; /* Needed for absolute positioning of footer */
            min-height: 100vh; /* Ensure body is at least viewport height for footer positioning */
            margin: 0; /* Remove default body margin */
            padding-bottom: 60px; /* Space for the bottom-right footer element */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }

        /* Styles for the new footer elements */
        .report-footer {
            position: absolute;
            bottom: 20px; /* Distance from the bottom of the page */
            right: 20px; /* Distance from the right edge */
            font-size: 12px; /* Increased font size for report generated */
            text-align: right; /* Align text to the right */
        }

        /* Styles for the Certified By section (now flowing with content) */
        .certified-by-section-content {
            margin-top: 50px; /* Space below the CTO Summary table */
            margin-left: 5px; /* Left alignment, adjust as needed */
            margin-bottom: 40px;
            font-size: 15px; /* Slightly larger font */
            width: 250px; /* Ensure enough space for the underline */
            text-align: left;
        }

        .certified-by-underline {
            margin-left: 5px;
            border-bottom: 1px solid #000;
            margin-top: 15px; /* Space between underline and text below it */
            width: 40%;
            display: block;
        }

        /* The report-generated-section is now the sole content of .report-footer */
        .report-generated-section {
            /* No specific styles needed here, as it inherits from .report-footer */
        }
    </style>
</head>
<body>
    <h2>CTO Report - {{ $customer->nama }}</h2>

    <h3>Remaining Credits per Special Order</h3>
    <table>
        <thead>
            <tr>
                <th>Special Order</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Credits Earned</th>
                <th>Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($specialOrders as $so)
            <tr>
                <td>{{ $so->special_order }}</td>
                <td>{{ $so->activity }}</td>
                <td>{{ \Carbon\Carbon::parse($so->date_of_activity_start)->toDateString() }}</td>
                <td>{{ number_format($so->credits_earned, 2) }}</td>
                <td>{{ number_format($so->remaining_credits, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>CTO Summary</h3>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>SO</th>
                <th>Activity</th>
                <th>Date Start</th>
                <th>Date End</th>
                <th>Credits Earned</th>
                <th>Credits Used</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ctoApplications as $cto)
                <tr>
                    <td>{{ $cto->is_activity ? 'Earned' : 'Used' }}</td>
                    <td>{{ $cto->special_order }}</td>
                    <td>{{ $cto->activity }}</td>
                    <td>{{ \Carbon\Carbon::parse($cto->date_of_activity_start ?? $cto->date_of_absence_start)->toDateString() }}</td>
                    <td>{{ \Carbon\Carbon::parse($cto->date_of_activity_end ?? $cto->date_of_absence_end)->toDateString() }}</td>
                    <td>{{ $cto->is_activity ? number_format($cto->credits_earned, 2) : '' }}</td>
                    <td>{{ !$cto->is_activity ? number_format($cto->no_of_days, 2) : '' }}</td>
                    <td>{{ number_format($cto->balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Certified By section moved here, directly after the CTO Summary table --}}
    <div class="certified-by-section-content">
        Certified By: 
    </div>

    <div class="certified-by-underline"></div>

    {{-- Report Generated section remains at the bottom right --}}
    <div class="report-footer">
        <div class="report-generated-section">
            Report Generated: {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>
</body>
</html>