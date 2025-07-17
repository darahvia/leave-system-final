<!DOCTYPE html>
<html>
<head>
    <title>CTO Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #eee; }
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
</body>
</html>
