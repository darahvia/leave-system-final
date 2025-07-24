<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teaching Leave Credit Report</title>
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
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            text-align: center;
            background-color: #e0e0e0;
            padding: 5px;
            border: 1px solid #000;
        }
        
        .leave-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-bottom: 20px;
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
        
        .leave-table .date-col {
            width: 12%;
        }
        
        .leave-table .event-col {
            width: 15%;
        }
        
        .leave-table .order-col {
            width: 10%;
        }
        
        .leave-table .days-col {
            width: 8%;
        }
        
        .leave-table .reference-col {
            width: 10%;
        }
        
        .leave-table .balance-col {
            width: 8%;
        }
        
        .leave-table .remarks-col {
            width: 15%;
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
        
        .credits-section {
            margin-bottom: 15px;
        }
        
        .usage-section {
            margin-bottom: 15px;
        }
        
        .positive-credit {
            color: green;
            font-weight: bold;
        }
        
        .negative-credit {
            color: red;
            font-weight: bold;
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
        <div class="header-main">TEACHING LEAVE CREDIT REPORT</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <table class="info-table">
            <tr>
                <td class="label">SURNAME</td>
                <td class="value">{{ strtoupper($customer->surname) }}</td>
                <td class="label">SCHOOL</td>
                <td class="value">{{ strtoupper($customer->office->office ?? '') }}</td>
                <td class="label">STATUS</td>
                <td class="value">{{ strtoupper($customer->status ?? '') }}</td>
            </tr>
            <tr>
                <td class="label">GIVEN NAME</td>
                <td class="value">{{ strtoupper($customer->given_name ?? '') }}</td>
                <td class="label">POSITION</td>
                <td class="value">{{ strtoupper($customer->position->position ?? '') }}</td>
                <td class="label">LEAVE CREDITS (OLD)</td>
                <td class="value">{{ $customer->leave_credits_old ?? 0 }}</td>
            </tr>
            <tr>
                <td class="label">MIDDLE NAME</td>
                <td class="value">{{ strtoupper($customer->middle_name ?? '') }}</td>
                <td class="label">ORIGINAL APPOINTMENT</td>
                <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
                <td class="label">LEAVE CREDITS (NEW)</td>
                <td class="value">{{ $customer->leave_credits_new ?? 0 }}</td>
            </tr>
            <tr>
                <td class="label">EMPLOYEE ID</td>
                <td class="value">{{ $customer->id }}</td>
                <td class="label">REPORT GENERATED</td>
                <td class="value">{{ \Carbon\Carbon::now()->format('F j, Y - g:i A') }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
        </table>
    </div>

    <!-- OLD LEAVE CREDITS SECTION (Before October 1, 2024) -->
    <div class="section-title">LEAVE CREDITS - BEFORE OCTOBER 1, 2024</div>
    
    <!-- Credits Earned (Old) -->
    <div class="credits-section">
        <h4 style="margin: 10px 0 5px 0; font-size: 10px;">Credits Earned (Before Oct 1, 2024)</h4>
        <table class="leave-table">
            <thead>
                <tr>
                    <th class="date-col">EARNED DATE</th>
                    <th class="event-col">EVENT</th>
                    <th class="order-col">SPECIAL ORDER</th>
                    <th class="days-col">CREDITS TO ADD</th>
                    <th class="reference-col">REFERENCE</th>
                </tr>
            </thead>
            <tbody>
                @if($teachingEarnedCredits && $teachingEarnedCredits->count())
                    @foreach($teachingEarnedCredits->filter(function($credit) {
                        return \Carbon\Carbon::parse($credit->earned_date_start)->lt(\Carbon\Carbon::parse('2024-10-01'));
                    })->sortByDesc('earned_date_start') as $credit)
                        <tr class="credit-earned">
                            <td>
                                @if($credit->earned_date_start && $credit->earned_date_end)
                                    @if(\Carbon\Carbon::parse($credit->earned_date_start)->isSameDay(\Carbon\Carbon::parse($credit->earned_date_end)))
                                        {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('M j, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('M j') }} - {{ \Carbon\Carbon::parse($credit->earned_date_end)->format('M j, Y') }}
                                    @endif
                                @elseif($credit->created_at)
                                    {{ \Carbon\Carbon::parse($credit->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td>{{ $credit->event ?? '' }}</td>
                            <td>{{ $credit->special_order ?? '' }}</td>
                            <td class="positive-credit">+{{ $credit->days }}</td>
                            <td>{{ $credit->reference ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic;">No credits earned before October 1, 2024</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Leave Usage (Old) -->
    <div class="usage-section">
        <h4 style="margin: 10px 0 5px 0; font-size: 10px;">Leave Usage (Before October 1, 2024)</h4>
        <table class="leave-table">
            <thead>
                <tr>
                    <th class="date-col">DATE FILED</th>
                    <th class="date-col">DATE INCURRED</th>
                    <th class="days-col">DAYS</th>
                    <th class="balance-col">BALANCE</th>
                    <th class="remarks-col">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                <tr class="balance-forwarded">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $customer->leave_credits_old ?? 0 }}</td>
                    <td></td>
                </tr>
                
                @if($teachingLeaveApplications && $teachingLeaveApplications->count())
                    @foreach($teachingLeaveApplications->filter(function($app) {
                        return \Carbon\Carbon::parse($app->date_filed)->lt(\Carbon\Carbon::parse('2024-10-01'));
                    })->sortByDesc(function($app) {
                        return $app->date_filed ?: $app->created_at;
                    }) as $app)
                        <tr class="{{ ($app->is_leavewopay) ? 'leave-without-pay' : '' }}">
                            <td>
                                @if($app->date_filed)
                                    {{ \Carbon\Carbon::parse($app->date_filed)->format('M j, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($app->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td>
                                @if($app->leave_start_date && $app->leave_end_date)
                                    @if(\Carbon\Carbon::parse($app->leave_start_date)->isSameDay(\Carbon\Carbon::parse($app->leave_end_date)))
                                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('M j, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($app->leave_end_date)->format('M j, Y') }}
                                    @endif
                                @elseif($app->created_at)
                                    {{ \Carbon\Carbon::parse($app->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td class="{{ $app->is_leavewopay || $app->is_leavepay ? '' : 'negative-credit' }}">
                                {{ $app->is_leavewopay || $app->is_leavepay ? $app->working_days : '-' . $app->working_days }}
                            </td>
                            <td></td>
                            <td>
                                @if(!$app->is_credit_earned)
                                    @if($app->is_leavewopay || $app->is_leavepay)
                                        Leave {{ $app->is_leavewopay ? 'Without' : 'With' }} Pay
                                        @if($app->remarks)
                                            - {{ $app->remarks }}
                                        @endif
                                    @else
                                        {{ $app->remarks ?? '' }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic;">No leave usage before October 1, 2024</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
        <p class="generated">This report was generated on {{ \Carbon\Carbon::now()->format('F j, Y \a\t g:i A') }}</p>

    <!-- NEW LEAVE CREDITS SECTION (After October 1, 2024) -->
    <div class="page-break"></div>
    <div class="header-section">
        <img src="file://{{ public_path('/images/deped-logo.png') }}" class="footer-logo-deped" style="height: 60px; width: auto;">
        <div class="header-title">Republic of the Philippines</div>
        <div class="header-subtitle">Department of Education</div>
        <div class="header-subtitle">NEGROS ISLAND REGION</div>
        <div class="header-subtitle">SCHOOLS DIVISION OF CADIZ CITY</div>
        <div class="header-main">TEACHING LEAVE CREDIT REPORT</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <table class="info-table">
            <tr>
                <td class="label">SURNAME</td>
                <td class="value">{{ strtoupper($customer->surname) }}</td>
                <td class="label">SCHOOL</td>
                <td class="value">{{ strtoupper($customer->office->office ?? '') }}</td>
                <td class="label">STATUS</td>
                <td class="value">{{ strtoupper($customer->status ?? '') }}</td>
            </tr>
            <tr>
                <td class="label">GIVEN NAME</td>
                <td class="value">{{ strtoupper($customer->given_name ?? '') }}</td>
                <td class="label">POSITION</td>
                <td class="value">{{ strtoupper($customer->position->position ?? '') }}</td>
                <td class="label">LEAVE CREDITS (OLD)</td>
                <td class="value">{{ $customer->leave_credits_old ?? 0 }}</td>
            </tr>
            <tr>
                <td class="label">MIDDLE NAME</td>
                <td class="value">{{ strtoupper($customer->middle_name ?? '') }}</td>
                <td class="label">ORIGINAL APPOINTMENT</td>
                <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
                <td class="label">LEAVE CREDITS (NEW)</td>
                <td class="value">{{ $customer->leave_credits_new ?? 0 }}</td>
            </tr>
            <tr>
                <td class="label">EMPLOYEE ID</td>
                <td class="value">{{ $customer->id }}</td>
                <td class="label">REPORT GENERATED</td>
                <td class="value">{{ \Carbon\Carbon::now()->format('F j, Y - g:i A') }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
        </table>
    </div>
    <div class="section-title">LEAVE CREDITS - AFTER OCTOBER 1, 2024</div>
    
    <!-- Credits Earned (New) -->
    <div class="credits-section">
        <h4 style="margin: 10px 0 5px 0; font-size: 10px;">Credits Earned (After October 1, 2024)</h4>
        <table class="leave-table">
            <thead>
                <tr>
                    <th class="date-col">EARNED DATE</th>
                    <th class="event-col">EVENT</th>
                    <th class="order-col">SPECIAL ORDER</th>
                    <th class="days-col">CREDITS TO ADD</th>
                    <th class="reference-col">REFERENCE</th>
                </tr>
            </thead>
            <tbody>
                @if($teachingEarnedCredits && $teachingEarnedCredits->count())
                    @foreach($teachingEarnedCredits->filter(function($credit) {
                        return \Carbon\Carbon::parse($credit->earned_date_start)->gte(\Carbon\Carbon::parse('2024-10-01'));
                    })->sortByDesc('earned_date_start') as $credit)
                        <tr class="credit-earned">
                            <td>
                                @if($credit->earned_date_start && $credit->earned_date_end)
                                    @if(\Carbon\Carbon::parse($credit->earned_date_start)->isSameDay(\Carbon\Carbon::parse($credit->earned_date_end)))
                                        {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('M j, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('M j') }} - {{ \Carbon\Carbon::parse($credit->earned_date_end)->format('M j, Y') }}
                                    @endif
                                @elseif($credit->created_at)
                                    {{ \Carbon\Carbon::parse($credit->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td>{{ $credit->event }}</td>
                            <td>{{ $credit->special_order ?? '' }}</td>
                            <td class="positive-credit">+{{ $credit->days }}</td>
                            <td>{{ $credit->reference ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic;">No credits earned after October 1, 2024</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Leave Usage (New) -->
    <div class="usage-section">
        <h4 style="margin: 10px 0 5px 0; font-size: 10px;">Leave Usage (After October 1, 2024)</h4>
        <table class="leave-table">
            <thead>
                <tr>
                    <th class="date-col">DATE FILED</th>
                    <th class="date-col">DATE INCURRED</th>
                    <th class="days-col">DAYS</th>
                    <th class="balance-col">BALANCE</th>
                    <th class="remarks-col">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                <tr class="balance-forwarded">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $customer->leave_credits_new ?? 0 }}</td>
                    <td></td>
                </tr>
                
                @if($teachingLeaveApplications && $teachingLeaveApplications->count())
                    @foreach($teachingLeaveApplications->filter(function($app) {
                        return \Carbon\Carbon::parse($app->date_filed)->gte(\Carbon\Carbon::parse('2024-10-01'));
                    })->sortByDesc(function($app) {
                        return $app->date_filed ?: $app->created_at;
                    }) as $app)
                        <tr class="{{ ($app->is_leavewopay) ? 'leave-without-pay' : '' }}">
                            <td>
                                @if($app->date_filed)
                                    {{ \Carbon\Carbon::parse($app->date_filed)->format('M j, Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($app->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td>
                                @if($app->leave_start_date && $app->leave_end_date)
                                    @if(\Carbon\Carbon::parse($app->leave_start_date)->isSameDay(\Carbon\Carbon::parse($app->leave_end_date)))
                                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('M j, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($app->leave_end_date)->format('M j, Y') }}
                                    @endif
                                @elseif($app->created_at)
                                    {{ \Carbon\Carbon::parse($app->created_at)->format('M j, Y') }}
                                @endif
                            </td>
                            <td class="{{ $app->is_leavewopay || $app->is_leavepay ? '' : 'negative-credit' }}">
                                {{ $app->is_leavewopay || $app->is_leavepay ? $app->working_days : '-' . $app->working_days }}
                            </td>
                            <td></td>
                            <td>
                                @if(!$app->is_credit_earned)
                                    @if($app->is_leavewopay || $app->is_leavepay)
                                        Leave {{ $app->is_leavewopay ? 'Without' : 'With' }} Pay
                                        @if($app->remarks)
                                            - {{ $app->remarks }}
                                        @endif
                                    @else
                                        {{ $app->remarks ?? '' }}
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center; font-style: italic;">No leave usage after October 1, 2024</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <strong>TEACHING LEAVE CREDITS SUMMARY</strong>
        <table class="summary-table">
            <tr>
                <td class="summary-label">OLD LEAVE CREDITS (Before Oct 1, 2024)</td>
                <td>{{ $customer->leave_credits_old ?? 0 }}</td>
                <td class="summary-label">NEW LEAVE CREDITS (After Oct 1, 2024)</td>
                <td>{{ $customer->leave_credits_new ?? 0 }}</td>
            </tr>
            <tr>
                <td class="summary-label">TOTAL LEAVE APPLICATIONS (OLD)</td>
                <td>
                    @if($teachingLeaveApplications)
                        {{ $teachingLeaveApplications->filter(function($app) {
                            return \Carbon\Carbon::parse($app->date_filed)->lt(\Carbon\Carbon::parse('2024-10-01'));
                        })->count() }}
                    @else
                        0
                    @endif
                </td>
            </tr>
        </table>
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