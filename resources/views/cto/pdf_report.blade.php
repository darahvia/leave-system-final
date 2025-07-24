<!DOCTYPE html>
<html>
<head>
    <title>CTO Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #eee; }
        body {
            font-family: sans-serif;
            font-size: 12px;
            position: relative;
            min-height: 100vh;
            margin: 0;
            padding-bottom: 60px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #eee;
            text-align: center;
        }

        th.so-col, td.so-col {
            width: 250px;
        }

        th.activity-col, td.activity-col {
            width: 220px;
        }

        th.date-col, td.date-col {
            width: 80px;
            text-align: center;
        }

        th.type-col, td.type-col {
            width: 50px;
            text-align: center;
        }

        th.credits-col, td.credits-col,
        th.balance-col, td.balance-col,
        th.credits-earned-col, td.credits-earned-col {
            width: 30px;
            max-width: 30px;
            overflow: hidden;
            white-space: nowrap;
            text-align: center;
        }

        .report-footer {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            text-align: right;
        }

        .certified-by-section-content {
            margin-top: 50px;
            margin-left: 5px;
            margin-bottom: 40px;
            font-size: 15px;
            width: 250px;
            text-align: left;
        }

        .certified-by-underline {
            margin-left: 5px;
            border-bottom: 1px solid #000;
            margin-top: 15px;
            width: 40%;
            display: block;
        }
    </style>
</head>
<body>
    <h2>CTO Report - {{ $customer->nama }}</h2>

    <h3>Remaining Credits per Special Order</h3>
    <table>
        <thead>
            <tr>
                <th class="so-col">Special Order</th>
                <th class="activity-col">Activity</th>
                <th class="date-col">Date</th>
                <th class="credits-earned-col">Credits<br>Earned</th>
                <th class="balance-col">Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($specialOrders as $so)
            <tr>
                <td class="so-col">{{ $so->special_order }}</td>
                <td class="activity-col">{{ $so->activity }}</td>
                <td class="date-col">{{ \Carbon\Carbon::parse($so->date_of_activity_start)->toDateString() }}</td>
                <td class="credits-earned-col">{{ number_format($so->credits_earned, 2) }}</td>
                <td class="balance-col">{{ number_format($so->remaining_credits, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>CTO Summary</h3>
    <table>
        <thead>
            <tr>
                <th class="type-col">Type</th>
                <th class="so-col">SO</th>
                <th class="activity-col">Activity</th>
                <th class="date-col">Date Start</th>
                <th class="date-col">Date End</th>
                <th class="credits-col">Credits<br>Earned</th>
                <th class="credits-col">Credits<br>Used</th>
                <th class="balance-col">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ctoApplications as $cto)
            <tr>
                <td class="type-col">{{ $cto->is_activity ? 'Earned' : 'Used' }}</td>
                <td class="so-col">
                    @if ($cto->is_activity)
                        {{ $cto->special_order }}
                    @else
                        @php $deductions = $cto->consumedActivities; @endphp
                        @forelse ($deductions as $deduct)
                            @php $so = $deduct->ctoActivity; @endphp
                            {{ $so->special_order ?? '(No SO)' }},
                            {{ number_format($deduct->days_used, 2) }} hrs<br>
                        @empty
                            No deduction records
                        @endforelse
                    @endif
                </td>
                <td class="activity-col">{{ $cto->activity }}</td>
                <td class="date-col">{{ \Carbon\Carbon::parse($cto->date_of_activity_start ?? $cto->date_of_absence_start)->toDateString() }}</td>
                <td class="date-col">{{ \Carbon\Carbon::parse($cto->date_of_activity_end ?? $cto->date_of_absence_end)->toDateString() }}</td>
                <td class="credits-col">{{ $cto->is_activity ? number_format($cto->credits_earned, 2) : '' }}</td>
                <td class="credits-col">{{ !$cto->is_activity ? number_format($cto->no_of_days, 2) : '' }}</td>
                <td class="balance-col">{{ number_format($cto->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="certified-by-section-content">
        Certified By: 
    </div>

    <div class="certified-by-underline"></div>

    <div class="report-footer">
        <div class="report-generated-section">
            Report Generated: {{ now()->format('Y-m-d H:i:s') }}
        </div>
    </div>
</body>
</html>
