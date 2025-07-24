<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Credit Report</title>
    <style>
    @page { 
            size: A4 landscape; 
            margin: 0.5in; 
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 10px;
            }
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            line-height: 1.2; 
            margin: 0;
            padding: 0;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header-subtitle {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .header-main {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
        p
        .employee-info {
            margin-bottom: 15px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .info-table td {
            padding: 4px 6px;
            border: 1px solid #000;
            font-size: 9px;
            vertical-align: top;
        }
        
        .info-table .label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 15%;
            text-align: left;
        }
        
        .info-table .value {
            width: 35%;
            text-align: left;
        }
        
        .leave-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-top: 10px;
        }
        
        .leave-table th, .leave-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .leave-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 7px;
        }
        
        .leave-table .period-col {
            width: 8%;
        }
        
        .leave-table .earned-col {
            width: 6%;
        }
        
        .leave-table .date-col {
            width: 8%;
        }
        
        .leave-table .leave-type-col {
            width: 8%;
        }
        
        .leave-table .days-col {
            width: 5%;
        }
        
        .leave-table .remarks-col {
            width: 12%;
        }
        
        .leave-table .balance-col {
            width: 6%;
        }
        
        .balance-forwarded {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        .leave-without-pay {
            background-color: #ffe6e6;
        }
        
        .credit-earned {
            background-color: #e6f3ff;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .summary-section {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .summary-table td {
            padding: 3px 5px;
            border: 1px solid #000;
            font-size: 9px;
        }
        
        .summary-table .summary-label {
            background-color: #e0e0e0;
            font-weight: bold;
            width: 30%;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        
        .other-credits {
            margin-top: 10px;
            font-size: 8px;
        }
        
        .other-credits ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .other-credits li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <img src="file://{{ public_path('/images/deped-logo.png') }}" class="footer-logo-deped" style="height: 60px; width: auto;">
        <div class="header-title">Republic of the Philippines</div>
        <div class="header-subtitle">Department of Education</div>
        <div class="header-subtitle">NEGROS ISLAND REGION</div>
        <div class="header-subtitle">SCHOOLS DIVISION OF CADIZ CITY</div>
        <div class="header-main">LEAVE CREDIT REPORT</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <table class="info-table">
            <tr>
                <td class="label">SURNAME</td>
                <td class="value">{{ strtoupper($customer->surname) }}</td>
                <td class="label">DIVISION/UNIT/SCHOOL</td>
                <td class="value">{{ strtoupper($customer->office->office ?? '') }}</td>
                <td class="label">STATUS</td>
                <td class="value">{{ strtoupper($customer->status ?? '') }}</td>
            </tr>
            <tr>
                <td class="label">GIVEN NAME</td>
                <td class="value">{{ strtoupper($customer->given_name) }}</td>
                <td class="label">POSITION</td>
                <td class="value">{{ strtoupper($customer->position->position ?? '') }}</td>
                <td class="label">ORIGINAL APPOINTMENT</td>
                <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
            </tr>
            <tr>
                <td class="label">MIDDLE NAME</td>
                <td class="value">{{ strtoupper($customer->middle_name) }}</td>
                <td class="label">REPORT GENERATED</td>
                <td class="value">{{ \Carbon\Carbon::now()->format('F j, Y - g:i A') }}</td>
                <td class="label">EMPLOYEE ID</td>
                <td class="value">{{ $customer->id }}</td>
            </tr>
        </table>
    </div>

    <!-- Leave Records Table -->
    <table class="leave-table">
        <thead>
            <tr>
                <th class="period-col">PERIOD</th>
                <th class="earned-col">VL EARNED</th>
                <th class="earned-col">SL EARNED</th>
                <th class="date-col">DATE FILED</th>
                <th class="date-col">DATE INCURRED</th>
                <th class="leave-type-col">LEAVE INCURRED</th>
                <th class="days-col">VL</th>
                <th class="days-col">SL</th>
                <th class="days-col">SPL</th>
                <th class="days-col">FL</th>
                <th class="days-col">SOLO PARENT</th>
                <th class="days-col">OTHERS</th>
                <th class="remarks-col">REMARKS</th>
                <th class="balance-col">VL BAL</th>
                <th class="balance-col">SL BAL</th>
            </tr>
        </thead>
        <tbody>
            <!-- Balance Forwarded Row -->
            <tr class="balance-forwarded">
                <td>BALANCE FORWARDED</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>{{ number_format($customer->balance_forwarded_vl, 3) }}</td>
                <td>{{ number_format($customer->balance_forwarded_sl, 3) }}</td>
            </tr>

            @if($customer->leaveApplications && $customer->leaveApplications->count())
                @php
                    $sortedApplications = $customer->leaveApplications->sortBy(function($app) {
                        return $app->earned_date ?? $app->date_filed ?? '1900-01-01';
                    });
                @endphp
                @foreach($sortedApplications as $app)
                    <tr class="{{ ($app->is_leavewopay) ? 'leave-without-pay' : '' }} {{ ($app->is_credit_earned) ? 'credit-earned' : '' }}">
                        <td>{{ $app->earned_date ? \Carbon\Carbon::parse($app->earned_date)->format('M j, Y') : '' }}</td>
                        <td>
                            @if($app->is_credit_earned)
                                @if($app->leave_type === 'VL' || !$app->leave_type)
                                    {{ $app->earned_vl ?? '1.25' }}
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($app->is_credit_earned)
                                @if($app->leave_type === 'SL' || !$app->leave_type)
                                    {{ $app->earned_sl ?? '1.25' }}
                                @endif
                            @endif
                        </td>
                        <td>
                            {{ $app->is_credit_earned ? '' : ($app->date_filed ? \Carbon\Carbon::parse($app->date_filed)->format('M j, Y') : '') }}
                        </td>
                        <td>
                            @if($app->inclusive_date_start && $app->inclusive_date_end)
                                @if(\Carbon\Carbon::parse($app->inclusive_date_start)->isSameDay(\Carbon\Carbon::parse($app->inclusive_date_end)))
                                    {{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('M j, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('M j') }} - {{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('M j, Y') }}
                                @endif
                            @elseif($app->date_incurred)
                                {{ \Carbon\Carbon::parse($app->date_incurred)->format('M j, Y') }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned)
                                {{ \App\Services\LeaveService::getLeaveTypes()[$app->leave_type] ?? $app->leave_type }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned && $app->leave_type === 'VL')
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned && $app->leave_type === 'SL')
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned && $app->leave_type === 'SPL')
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned && $app->leave_type === 'FL')
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned && $app->leave_type === 'SOLO PARENT')
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        @php
                            $otherLeaveTypes = ['ML', 'PL', 'RA9710', 'RL', 'SEL', 'STUDY_LEAVE', 'ADOPT', 'VAWC', 'SOLO_PARENT'];
                        @endphp
                        <td>
                            @if (in_array($app->leave_type, $otherLeaveTypes))
                                {{ $app->working_days ?? '' }}
                            @endif
                        </td>
                        <td>
                            @if(!$app->is_credit_earned)
                                @if($app->is_leavewopay || $app->is_leavepay)
                                    Leave {{ $app->is_leavewopay ? 'Without' : 'With' }} Pay
                                    @if($app->leave_details)
                                        - {{ $app->leave_details }}
                                    @endif
                                @else
                                    {{ $app->leave_details ?? '' }}
                                @endif
                            @endif
                        </td>
                        <td>{{ $app->current_vl ?? '' }}</td>
                        <td>{{ $app->current_sl ?? '' }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <strong>CURRENT LEAVE BALANCES SUMMARY</strong>
        <table class="summary-table">
            <tr>
                <td class="summary-label">VACATION LEAVE (VL)</td>
                <td>{{ $latestApp ? $latestApp->current_vl : ($customer->balance_forwarded_vl ?? 0) }}</td>
                <td class="summary-label">SICK LEAVE (SL)</td>
                <td>{{ $latestApp ? $latestApp->current_sl : ($customer->balance_forwarded_sl ?? 0) }}</td>
            </tr>
            <tr>
                <td class="summary-label">COMPENSATORY TIME OFF (CTO)</td>
                <td>{{ $latestCtoApp ? number_format($latestCtoApp->balance, 1) : (isset($ctoService) ? number_format($ctoService->getEligibleCtoBalance($customer), 1) : '0.0') }}</td>
                <td class="summary-label"></td>
                <td></td>
            </tr>
        </table>

        @if(isset($ctoService))
            <div class="other-credits">
                <strong>OTHER LEAVE CREDITS:</strong>
                @php
                    $balances = $leaveService->getCurrentBalances($customer);
                @endphp
                <ul>
                    @foreach ($balances as $type => $value)
                        @if (!in_array($type, ['vl', 'sl']) && $value > 0)
                            <li>{{ \App\Services\LeaveService::getLeaveTypes()[strtoupper($type)] ?? ucfirst(str_replace('_', ' ', $type)) }}: <strong>{{ $value }}</strong></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <!-- Footer -->
        <p style="font-size: 12px; text-align:left;">Certified correct by:</p>
        <p style="font-size: 12px; margin-top: 50px; text-align: left;">__________________________</p>
<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Arial", "normal");
            $size = 8;
            $y = 570; // Adjust this for vertical position
            $x = 780; // Adjust this for horizontal position (landscape A4 is about 842px wide)
            $pdf->text($x, $y, "Page $PAGE_NUM of $PAGE_COUNT", $font, $size);
        ');
    }
</script>
</body>
</html>