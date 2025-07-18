<!DOCTYPE html>
<html>
<head>
    <title>CTO Management</title>
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .tab-nav {
            display: flex;
            gap: 1rem;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 1.5rem;
        }
        .tab-link {
            padding: 0.5rem 1.5rem;
            text-decoration: none;
            color: #333;
            border-bottom: 2px solid transparent;
            transition: border 0.2s, color 0.2s;
        }
        .tab-link.active, .tab-link:hover {
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }


        /* Styles for action buttons and table rows */
        .actions-column {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .actions-column button {
            padding: 5px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .edit-btn {
            background-color: #28a745; /* Green */
            color: white;
        }
        .delete-btn {
            background-color: #dc3545; /* Red */
            color: white;
        }
        .edit-btn:hover, .delete-btn:hover {
            opacity: 0.9;
        }
        .cto-table th, .cto-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        .cto-table thead tr {
            background-color: #90EE90;
        }
        .cto-table th {
            font-weight: bold;
        }
        .cto-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-top: 1rem;
        }


        /* Row specific colors based on CTO status */
        .cto-expired {
            background-color: #ffe0e0; /* Light Red */
        }
        .cto-valid-credit {
            background-color: #e0ffe0; /* Light Green */
        }
        .cto-fully-consumed {
            background-color: #f0f0f0; /* Light Grey */
        }
        .cto-absence {
            background-color: #fffacd; /* Lemon Chiffon (light yellow) */
        }
        .balance-positive {
            color: #28a745;
            font-weight: bold;
        }

        /* Accordion-style Expired SO Button */
        .expired-so-accordion-btn {
            display: block;
            margin: 2rem auto 0 auto; /* Top margin for spacing, auto for horizontal centering */
            float: none;
            padding: 0.6rem 2rem 0.6rem 1.2rem;
            background: #fff;
            color: #dc3545;
            border: 2px solid #dc3545;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(220,53,69,0.08);
            transition: background 0.2s, color 0.2s;
            position: relative;
        }

        .expired-so-accordion-btn:hover {
            background: #ffe0e0;
            color: #fff;
        }

        .expired-so-accordion-btn .arrow {
            position: absolute;
            top: 48%;
            transform: translateY(-50%) rotate(0deg);
            transition: transform 0.3s;
            font-size: 1.1em;
            margin-left: 8px; /* Add this line for spacing */
        }

        .expired-so-accordion-btn.active .arrow {
            transform: translateY(-50%) rotate(90deg);
        }

        /* Expired SO Table Styling */
        .expired-so-table-wrapper {
            background: #fff5f5;
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            box-shadow: 0 2px 12px rgba(220,53,69,0.07);
            transition: max-height 0.4s cubic-bezier(.4,0,.2,1), opacity 0.3s;
            opacity: 1;
        }

        .expired-so-table-wrapper[aria-hidden="true"] {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0 1.5rem;
        }

        .expired-so-table-wrapper[aria-hidden="false"] {
            max-height: 800px;
            opacity: 1;
        }

        .expired-so-table-wrapper h4 {
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .expired-so-table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .expired-so-table-wrapper th {
            background: #ffd6d6;
            color: #dc3545;
            font-weight: bold;
            border-bottom: 2px solid #dc3545;
        }

        .expired-so-table-wrapper td {
            border-bottom: 1px solid #f5c2c7;
            padding: 8px;
            text-align: center;
        }

        .expired-so-table-wrapper tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        {{-- These are for initial page load errors/successes passed via Laravel's session flashes --}}
        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif


        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif


        @include('partials.header', [
            'pageTitle' => 'Leave Credit System - CTO',
            'customer' => $customer ?? null,
            'ctoService' => app(\App\Services\CtoService::class)
        ])
        <div class="tab-nav" style="margin-bottom: 1.5rem;">
            <a href="{{ route('leave.customer.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('leave.customer.index') || request()->routeIs('leave.customer.index') ? ' active' : '' }}">Leave</a>
            <a href="{{ route('cto.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('cto.index') ? ' active' : '' }}">CTO</a>
        </div>


        {{-- Add Customer Modal --}}
        <div class="modal-bg" id="addCustomerModal">
            <div class="modal-content">
                <button class="close" id="closeAddCustomerModal">&times;</button>
                <form method="POST" action="{{ route('customer.add') }}">
                    @csrf
                    <div class="emp-form">
                        <div class="form-left">
                            <label>Surname:</label>
                            <input type="text" name="surname" required>
                            <label>Given name:</label>
                            <input type="text" name="given_name" required>
                            <label>Middle name:</label>
                            <input type="text" name="middle_name" required>
                            <label>Division:</label>
                            <input type="text" name="division" required>
                            <label>Designation:</label>
                            <input type="text" name="designation" required>
                        </div>


                        <div class="form-right">
                            <label>Original Appointment:</label>
                            <input type="date" name="original_appointment">
                            <label>Salary:</label>
                            <input type="number" step="0.01" name="salary" required>


                            <label>Vacation Leave Forwarded Balance:</label>
                            <input type="number" step="0.01" name="balance_forwarded_vl" required>
                            <label>Sick Leave Forwarded Balance:</label>
                            <input type="number" step="0.01" name="balance_forwarded_sl" required>
                            <label>CTO Forwarded Balance:</label>
                            <input type="number" step="0.01" name="balance_forwarded_cto" required>
                            <div style="height: 1rem;"></div>


                            <button type="submit">Add Customer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        {{-- Employee Details Table --}}
        @if($customer)
            <div class="bottom-section">
                <div class="form-section">
                    <h4>Add CTO Activity (Credits Earned)</h4>
                    <form method="POST" action="{{ route('cto.credits') }}" id="activity-form">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="edit_id" id="activity_cto_id" value="">
                        <input type="hidden" name="_method" id="activity_form_method" value="POST">
                        <input type="hidden" name="is_cto_earned" value="1">


                        <div class="emp-form">
                            <label>Special Order:</label>
                            <input type="text" name="special_order" id="special_order" >


                            <label>Activity:</label>
                            <input type="text" name="activity" id="activity" >


                            <label>Credits Earned:</label>
                            <input type="number" name="hours_earned" id="hours_earned" step="0.01" required>


                            <label>
                                <input type="checkbox" name="is_single_day_activity" id="single-day-activity"> Single Day Activity
                            </label>


                            <label>Date of Activity (Start):</label>
                            <input type="date" name="date_of_activity_start" id="date_of_activity_start" required>


                            <label id="end-date-label">Date of Activity (End):</label>
                            <input type="date" name="date_of_activity_end" id="activity-end-date">


                            <button type="submit" id="submit-activity-btn">Add CTO Activity</button>
                            <button type="button" id="cancel-activity-edit-btn" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                        </div>
                    </form>
                </div>


                <div class="form-section">
                    <h4>Add CTO Usage (Credits Deducted)</h4>
                    <form method="POST" action="{{ route('cto.submit') }}" id="usage-form">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="edit_id" id="usage_cto_id" value="">
                        <input type="hidden" name="_method" id="usage_form_method" value="POST">
                        <input type="hidden" name="is_cto_application" value="1">


                        <div class="emp-form">
                            <label>
                                <input type="checkbox" name="is_single_day_absence" id="single-day-absence"> Single Day Absence
                            </label>


                            <label>Date Filed:</label>
                            <input type="date" name="date_filed" id="usage_date_filed" required>




                            <label>Inclusive Date Start:</label>
                            <input type="date" id="inclusive_date_start_usage" name="inclusive_date_start" required>




                            <!-- Under Start Date -->
                            <span class="halfday-controls" id="start-halfday-span-usage">
                                <button type="button" class="toggle-button" id="start-am-btn-usage" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="start-pm-btn-usage" data-value="PM">PM</button>
                            </span>


                            <label id="absence-end-date-label">Inclusive Date End:</label>
                            <input type="date" id="inclusive_date_end_usage" name="inclusive_date_end">
                           
                            <!-- Under End Date -->
                            <span class="halfday-controls" id="end-halfday-span-usage">
                                <button type="button" class="toggle-button" id="end-am-btn-usage" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="end-pm-btn-usage" data-value="PM">PM</button>
                            </span>




                           
                            <label>Credits Used:</label>
                            <input type="number" name="hours_applied" id="hours_applied_usage" step="0.01" required>


                            {{-- Manual SO Deduction --}}
                            <div class="form-group" style="margin-top: 1rem;">
                                <label>Deduct from the following SOs:</label>
                                @foreach ($customer->ctoApplications->where('is_activity', true)->sortBy('date_of_activity_start') as $activity)
                                    @if ($activity->remaining_credits > 0 && !$activity->isExpired())
                                        <div style="margin-bottom: 4px;">
                                            <label>
                                        <input type="number"
                                            class="so-deduction-input"
                                            data-remaining="{{ $activity->remaining_credits }}"
                                            name="so_deductions[{{ $activity->id }}]"
                                            step="0.01"
                                            max="{{ $activity->remaining_credits }}"
                                            placeholder="0.00"
                                            style="width: 80px;"
                                            readonly>


                                                from SO {{ $activity->special_order ?? '(No SO)' }} —
                                                Balance: {{ number_format($activity->remaining_credits, 2) }} hrs
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>


                            <input type="hidden" name="start_halfday_usage" id="start_halfday_usage">
                            <input type="hidden" name="end_halfday_usage" id="end_halfday_usage">


                            <button type="submit" id="submit-usage-btn">Add CTO Usage</button>
                            <button type="button" id="cancel-usage-edit-btn" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="GET" action="{{ route('cto.export.pdf') }}" style="text-align: right; margin-bottom: 1rem;">
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <button type="submit" style="background:#0056b3;color:#fff;padding:6px 14px;border:none;border-radius:4px;cursor:pointer;font-weight:600;" onmouseover="this.style.background='#0069d9'" onmouseout="this.style.background='#007bff'">
                    Export as PDF
                </button>
            </form>

            <div style="text-align: center; margin: 2rem 0;">
                <h3 style="margin: 0; padding: 0.5rem; background-color: #90EE90; border: 2px solid #000; font-weight: bold;">COMPENSATORY TIME-OFF (CTO)</h3>
            </div>


            <table class="cto-table">
                <thead>
                    <tr>
                        <th>SPECIAL ORDER</th>
                        <th>DATE OF ACTIVITY</th>
                        <th>ACTIVITY</th>
                        <th>EARNED CREDITS</th>
                        <th>DATE OF ABSENCES</th>
                        <th>LEAVE INCURRED</th>
                        <th>BALANCE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through all CTO Applications for the customer, SKIP expired SOs --}}
                    @forelse($customer->ctoApplications->sortBy('effective_date') as $cto)
                        @if($cto->is_activity && $cto->isExpired())
                            @continue
                        @endif
                        @php
                            $rowClass = '';
                            if ($cto->is_activity) {
                                if ($cto->isExpired()) {
                                    $rowClass = 'cto-expired';
                                } elseif ($cto->current_balance > 0) {
                                    $rowClass = 'cto-valid-credit';
                                } else {
                                    $rowClass = 'cto-fully-consumed';
                                }
                            } else {
                                $rowClass = 'cto-absence';
                            }


                            // --- Start: More Robust Data Preparation for JSON encoding ---


                            // Sanitize string fields by explicitly casting to string and using null coalesce
                            $specialOrder = (string)($cto->special_order ?? '');
                            $activityName = (string)($cto->activity ?? '');
                            $ctoDetails = (string)($cto->cto_details ?? ''); // Ensure cto_details is handled if it exists


                            // Sanitize numeric fields by explicitly casting to float and using null coalesce
                            $creditsEarnedValue = (float)($cto->credits_earned ?? 0.00);
                            $noOfDaysUsageValue = (float)($cto->no_of_days ?? 0.00);
                            $creditsEarnedFormatted = number_format($creditsEarnedValue, 2);
                            $noOfDaysUsageFormatted = number_format($noOfDaysUsageValue, 2);


                            // Safely format dates for passing to JavaScript (empty string if null/invalid)
                            // This checks if it's already a Carbon instance, then if it's not null/empty but not Carbon, attempts to parse.
                            // Finally, defaults to empty string if anything fails or is null.
                            $dateOfActivityStartFormatted = '';
                            if ($cto->date_of_activity_start instanceof \Carbon\Carbon) {
                                $dateOfActivityStartFormatted = $cto->date_of_activity_start->format('Y-m-d');
                            } elseif ($cto->date_of_activity_start) {
                                try {
                                    $dateOfActivityStartFormatted = \Carbon\Carbon::parse($cto->date_of_activity_start)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    // Log error if date parsing fails for debugging
                                    \Illuminate\Support\Facades\Log::warning("Could not parse date_of_activity_start for CTO ID {$cto->id}: {$cto->date_of_activity_start}");
                                }
                            }


                            $dateOfActivityEndFormatted = '';
                            if ($cto->date_of_activity_end instanceof \Carbon\Carbon) {
                                $dateOfActivityEndFormatted = $cto->date_of_activity_end->format('Y-m-d');
                            } elseif ($cto->date_of_activity_end) {
                                try {
                                    $dateOfActivityEndFormatted = \Carbon\Carbon::parse($cto->date_of_activity_end)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning("Could not parse date_of_activity_end for CTO ID {$cto->id}: {$cto->date_of_activity_end}");
                                }
                            }


                            $dateOfAbsenceStartFormatted = '';
                            if ($cto->date_of_absence_start instanceof \Carbon\Carbon) {
                                $dateOfAbsenceStartFormatted = $cto->date_of_absence_start->format('Y-m-d');
                            } elseif ($cto->date_of_absence_start) {
                                try {
                                    $dateOfAbsenceStartFormatted = \Carbon\Carbon::parse($cto->date_of_absence_start)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning("Could not parse date_of_absence_start for CTO ID {$cto->id}: {$cto->date_of_absence_start}");
                                }
                            }


                            $dateOfAbsenceEndFormatted = '';
                            if ($cto->date_of_absence_end instanceof \Carbon\Carbon) {
                                $dateOfAbsenceEndFormatted = $cto->date_of_absence_end->format('Y-m-d');
                            } elseif ($cto->date_of_absence_end) {
                                try {
                                    $dateOfAbsenceEndFormatted = \Carbon\Carbon::parse($cto->date_of_absence_end)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::warning("Could not parse date_of_absence_end for CTO ID {$cto->id}: {$cto->date_of_absence_end}");
                                }
                            }
                            // --- End: More Robust Data Preparation for JSON encoding ---




                            // Create data objects for JSON encoding
                            $activityData = [
                                'id' => $cto->id,
                                'is_activity' => true,
                                'special_order' => $specialOrder,
                                'activity' => $activityName,
                                'hours_earned_or_applied' => $creditsEarnedFormatted,
                                'date_start' => $dateOfActivityStartFormatted,
                                'date_end' => $dateOfActivityEndFormatted,
                                'no_of_days_usage_unused' => '0', // Placeholder
                                'date_of_absence_start' => '', // Not applicable for activity
                                'date_of_absence_end' => '',   // Not applicable for activity
                            ];


                            $usageData = [
                                'id' => $cto->id,
                                'is_activity' => false,
                                'special_order' => '', // Not applicable for usage
                                'activity' => '', // Not applicable for usage
                                'hours_earned_or_applied' => $noOfDaysUsageFormatted, // This is hours used for usage
                                'date_start' => '', // Not applicable for usage (this was activity start)
                                'date_end' => '',   // Not applicable for usage (this was activity end)
                                'no_of_days_usage_unused' => '0', // Placeholder
                                'date_of_absence_start' => $dateOfAbsenceStartFormatted,
                                'date_of_absence_end' => $dateOfAbsenceEndFormatted,
                                'cto_details' => $ctoDetails, // Pass cto_details for usage records
                            ];
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td data-label="SPECIAL ORDER">
                                {{ $cto->is_activity ? ($cto->special_order ?? '') : '' }}
                            </td>
                            <td data-label="DATE OF ACTIVITY">
                                @if($cto->is_activity)
                                    {{ $cto->formatted_activity_date }}
                                @endif
                            </td>
                            <td data-label="ACTIVITY">
                                {{ $cto->is_activity ? ($cto->activity ?? '') : '' }}
                            </td>
                            <td data-label="EARNED HOURS">
                                @if($cto->is_activity)
                                    {{ number_format($cto->credits_earned ?? 0, 2) }}
                                @endif
                            </td>
                            <td data-label="DATE OF ABSENCES">
                                @if(!$cto->is_activity)
                                    {{ $cto->formatted_absence_date }}
                                @endif
                            </td>
                            <td data-label="HOURS USED">
                                @if(!$cto->is_activity)
                                    {{ number_format($cto->no_of_days ?? 0, 2) }}
                                @endif
                            </td>
                            <td data-label="BALANCE" style="font-weight: bold;">
                                {{ number_format($cto->balance ?? 0, 2) }}
                            </td>
                            <td data-label="ACTIONS" class="actions-column">
                                {{-- Edit Button --}}
                                @if($cto->is_activity)
                                    <button type="button" class="edit-btn" onclick='editCtoRecordFromData(@json($activityData))'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" class="edit-btn" onclick='editCtoRecordFromData(@json($usageData))'>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>
                                @endif


                                {{-- Delete Button --}}
                                <button type="button" class="delete-btn" onclick="deleteCtoRecord({{ $cto->id }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3,6 5,6 21,6"></polyline>
                                        <path d="m5,6 1,14c0,1 1,2 2,2h8c1,0 2-1 2-2l1-14"></path>
                                        <path d="m10,11 0,6"></path>
                                        <path d="m14,11 0,6"></path>
                                        <path d="M8,6V4c0-1,1-2,2-2h4c0-1,1-2,2-2v2"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                                @if(!$cto->is_activity)
                                <tr>
                                    <td colspan="8" style="padding-left: 2rem; font-size: 0.9em;">
                                        <strong>Deducted From:</strong>
                                        @php
                                            $deductions = $cto->consumedActivities;
                                        @endphp
                                    @forelse($deductions as $deduct)
                                        @php
                                            $so = $deduct->ctoActivity;
                                        @endphp
                                        SO: {{ $so->special_order ?? '(No SO)' }},
                                        {{ number_format($deduct->days_used, 2) }} hrs<br>
                                    @empty
                                        <em>No deduction records found.</em>
                                    @endforelse
                                    </td>
                                </tr>
                            @endif
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6c757d;">No CTO records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif



        @if($customer)
    <!-- View Expired S.O. History Button -->
    <button id="viewExpiredSOBtn" class="expired-so-accordion-btn">
        View Expired S.O. History
        <span class="arrow">&#9654;</span>
    </button>

    <!-- Expired SO Table (hidden by default) -->
    <div id="expiredSOTableWrapper" class="expired-so-table-wrapper" aria-hidden="true">
        <h4>Expired Special Order History</h4>
        <table>
            <thead>
                <tr>
                    <th>SPECIAL ORDER</th>
                    <th>DATE OF ACTIVITY</th>
                    <th>ACTIVITY</th>
                    <th>EARNED HOURS</th>
                    <th>BALANCE</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $expiredSOs = $customer->ctoApplications->filter(function($cto) {
                        return $cto->is_activity && $cto->isExpired();
                    });
                @endphp
                @forelse($expiredSOs as $expired)
                    <tr>
                        <td>{{ $expired->special_order }}</td>
                        <td>{{ $expired->formatted_activity_date }}</td>
                        <td>{{ $expired->activity }}</td>
                        <td>{{ number_format($expired->credits_earned ?? 0, 2) }}</td>
                        <td>{{ number_format($expired->balance ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#6c757d;">No expired SOs found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
        <!-- Make sure jQuery is loaded first -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $('#viewExpiredSOBtn').on('click', function() {
                var $wrapper = $('#expiredSOTableWrapper');
                var isHidden = $wrapper.attr('aria-hidden') === 'true';
                $wrapper.attr('aria-hidden', isHidden ? 'false' : 'true');
                $(this).toggleClass('active');
            });
        </script>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <script>
        // Make Laravel routes and customer ID available to external JavaScript
        window.autocompleteRoute = '{{ route("customer.autocomplete") }}';
        window.ctoUpdateRoute = '{{ route("cto.update", ":id") }}';
        window.ctoDeleteRoute = '{{ route("cto.delete", ":id") }}';
        window.ctoCalculateDaysRoute = '{{ route("cto.calculate-days") }}';
        window.ctoStoreActivityRoute = '{{ route("cto.credits") }}';
        window.ctoStoreUsageRoute = '{{ route("cto.submit") }}';
        window.csrfToken = '{{ csrf_token() }}';
        // FIX: Add cto.index route for JavaScript redirection
        window.ctoIndexRoute = '{{ route("cto.index") }}';
       
        @if($customer)
            window.customerId = {{ $customer->id }};
        @else
            window.customerId = null;
        @endif


        // This script block handles toast messages from URL parameters on page load
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('message');
            const customer_id_param_from_url = urlParams.get('customer_id'); // Get original customer_id from URL if present


            if (status && message) {
                displayMessage(message, status);
               
                // Optional: Clean up URL parameters after displaying message
                // This prevents the message from reappearing on subsequent manual refreshes
                urlParams.delete('status');
                urlParams.delete('message');
               
                // Reconstruct URL, preserving customer_id if it was there
                let newUrl = window.location.pathname;
                if (customer_id_param_from_url) {
                    urlParams.set('customer_id', customer_id_param_from_url);
                }
                const queryString = urlParams.toString();
                if (queryString) {
                    newUrl += '?' + queryString;
                }
                // Use history.replaceState to change URL without reloading the page again
                window.history.replaceState({}, document.title, newUrl);
            } // for pull request
        });
    </script>
    <script src="{{ asset('js/cto-form.js') }}"></script>
</body>
</html>