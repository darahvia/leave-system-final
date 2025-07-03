<!DOCTYPE html>
<html>
<head>
    <title>CTO Management</title>
    {{-- @vite(['resources/css/leave.css', 'resources/js/cto-form.js']) --}}
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
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="tab-nav" style="margin-bottom: 1.5rem;">
            <a href="{{ route('leave.customer.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('leave.customer.index') || request()->routeIs('leave.customer.index') ? ' active' : '' }}">Leave</a>
            <a href="{{ route('cto.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('cto.index') ? ' active' : '' }}">CTO</a>
        </div>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <div class="header-wrapper">
            <div class="header-container">
                <img src="/images/deped-logo.png" alt="DepEd Logo" class="header-logo">
                <div class="header-text">
                    <div class="header-title">
                        <span class="dep">Dep</span><span class="ed">Ed</span> Cadiz City
                    </div>
                    <div class="header-subtitle">CTO Management System</div>
                </div>
                <img src="/images/deped-cadiz-logo.png" alt="Division Logo" class="header-logo">
            </div>

            <div class="search-bar-section">
                {{-- Changed route to customer.find and input/button labels --}}
                <form method="POST" action="{{ route('customer.find') }}" class="search-form" autocomplete="off">
                    @csrf
                    <input type="hidden" name="redirect_to" value="cto">
                    <div class="search-box">
                        <button type="submit" class="search-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                        <input type="text" name="name" id="customer-search" autocomplete="off" required placeholder="Find Customer...">
                        <div id="suggestions"></div>
                    </div>
                </form>
                {{-- Changed button labels and IDs --}}
                <button class="add-customer-btn" id="showAddCustomerModal">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    <span>Add Customer</span>
                </button>
            </div>
        </div>

        {{-- Add Customer Modal - Changed ID and route --}}
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
                            <input type="date" name="original_appointment"> {{-- Changed type to date --}}
                            <label>Salary:</label>
                            <input type="number" step="0.01" name="salary" required>

                            <label>Vacation Leave Forwarded Balance:</label>
                            <input type="number" step="0.01" name="balance_forwarded_vl" required>
                            <label>Sick Leave Forwarded Balance:</label>
                            <input type="number" step="0.01" name="balance_forwarded_sl" required>
                            <label>CTO Forwarded Balance:</label> {{-- Added CTO forwarded balance --}}
                            <input type="number" step="0.01" name="balance_forwarded_cto" required>
                            <div style="height: 1rem;"></div>

                            <button type="submit">Add Customer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Employee Details Table - Changed variable to $customer --}}
        @if($customer)
            <div class="emp-details-table">
                <table class="employee-info-table"> {{-- Class name can remain, or change to customer-info-table --}}
                    <tr>
                        <td class="label">SURNAME</td>
                        <td class="value">{{ strtoupper($customer->surname) }}</td>
                        <td class="label">DIVISION</td>
                        {{-- Added fallback for division/designation if 'office' and 'position' relationships aren't loaded or exist --}}
                        <td class="value">{{ strtoupper($customer->office->office ?? $customer->division) }}</td>
                        <td class="label">BASIC SALARY</td>
                        <td class="value">{{ number_format($customer->salary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">GIVEN NAME</td>
                        <td class="value">{{ strtoupper($customer->given_name) }}</td>
                        <td class="label">DESIGNATION</td>
                        <td class="value">{{ strtoupper($customer->position->position ?? $customer->designation) }}</td>
                        <td class="label">CTO BALANCE</td>
                        {{-- Display customer's current eligible CTO balance for new deductions. Assumes ctoService is injected into CtoController --}}
                        <td class="value balance-positive">{{ number_format($ctoService->getEligibleCtoBalance($customer), 1) }}</td>
                    </tr>
                    <tr>
                        <td class="label">MIDDLE NAME</td>
                        <td class="value">{{ strtoupper($customer->middle_name) }}</td>
                        <td class="label">ORIGINAL APPOINTMENT</td>
                        <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
                        <td class="label"></td>
                        <td class="value"></td>
                    </tr>
                </table>
            </div>

            <div class="bottom-section">
                <div class="form-section">
                    <h4>Add CTO Activity (Credits Earned)</h4>
                    {{-- Updated action route to cto.credits --}}
                    <form method="POST" action="{{ route('cto.credits') }}" id="activity-form">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="edit_id" id="activity_cto_id" value="">
                        <input type="hidden" name="_method" id="activity_form_method" value="POST">
                        <input type="hidden" name="is_cto_earned" value="1"> {{-- Flag to identify as earned credit --}}

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
                            <button type="button" id="cancel-activity-edit-btn" onclick="cancelCtoActivityEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="form-section">
                    <h4>Add CTO Usage (Credits Deducted)</h4>
                    {{-- Updated action route to cto.submit --}}
                    <form method="POST" action="{{ route('cto.submit') }}" id="usage-form">
                        @csrf
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                        <input type="hidden" name="edit_id" id="usage_cto_id" value="">
                        <input type="hidden" name="_method" id="usage_form_method" value="POST">
                        <input type="hidden" name="is_cto_application" value="1"> {{-- Flag to identify as CTO application --}}

                        <div class="emp-form">
                            <label>
                                <input type="checkbox" name="is_single_day_absence" id="single-day-absence"> Single Day Absence
                            </label>

                            <label>Date Filed:</label>
                            <input type="date" name="date_filed" id="usage_date_filed" required>

                            <label>Leave Date (Inclusive Start):</label>
                            <input type="date" name="inclusive_date_start" id="inclusive_date_start_usage" required>

                            <label id="absence-end-date-label">CTO Date (Inclusive End):</label>
                            <input type="date" name="inclusive_date_end" id="inclusive_date_end_usage">

                            <label>Credits Used:</label>
                            <input type="number" name="hours_applied" id="hours_applied_usage" step="0.01" required>

                            <label>Remarks:</label>
                            <input type="text" name="cto_details" id="cto_details_usage">

                            <button type="submit" id="submit-usage-btn">Add CTO Usage</button>
                            <button type="button" id="cancel-usage-edit-btn" onclick="cancelCtoUsageEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div style="text-align: center; margin: 2rem 0;">
                <h3 style="margin: 0; padding: 0.5rem; background-color: #90EE90; border: 2px solid #000; font-weight: bold;">COMPENSATORY TIME-OFF (CTO)</h3>
            </div>

            <table class="cto-table">
                <thead>
                    <tr>
                        <th>SPECIAL ORDER</th>
                        <th>DATE OF ACTIVITY</th>
                        <th>ACTIVITY</th>
                        <th>EARNED HOURS</th>
                        <th>DATE OF ABSENCES</th>
                        <th>HOURS USED</th>
                        <th>BALANCE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through all CTO Applications for the customer --}}
                    @forelse($customer->ctoApplications->sortBy('effective_date') as $cto)
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
                                    <button type="button" class="edit-btn" onclick="editCtoRecord(
                                        {{ $cto->id }},
                                        true, {{-- is_activity flag --}}
                                        '{{ $cto->special_order ?? '' }}',
                                        '{{ $cto->activity ?? '' }}',
                                        '{{ number_format($cto->credits_earned ?? 0, 2) }}', {{-- credits_earned for earned --}}
                                        '{{ $cto->date_of_activity_start ? \Carbon\Carbon::parse($cto->date_of_activity_start)->format('Y-m-d') : '' }}',
                                        '{{ $cto->date_of_activity_end ? \Carbon\Carbon::parse($cto->date_of_activity_end)->format('Y-m-d') : '' }}',
                                        '' {{-- cto_details (not applicable for earned) --}}
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" class="edit-btn" onclick="editCtoRecord(
                                        {{ $cto->id }},
                                        false, {{-- is_activity flag --}}
                                        '', {{-- special_order (not applicable for usage) --}}
                                        '', {{-- activity (not applicable for usage) --}}
                                        '{{ number_format($cto->no_of_days ?? 0, 2) }}', {{-- no_of_days for usage (hours applied) --}}
                                        '{{ $cto->date_of_absence_start ? \Carbon\Carbon::parse($cto->date_of_absence_start)->format('Y-m-d') : '' }}',
                                        '{{ $cto->date_of_absence_end ? \Carbon\Carbon::parse($cto->date_of_absence_end)->format('Y-m-d') : '' }}',
                                        '{{ $cto->cto_details ?? '' }}' {{-- CTO details for usage --}}
                                    )">
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
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #6c757d;">No CTO records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Make Laravel routes available to JavaScript
            window.autocompleteRoute = '{{ route("customer.autocomplete") }}';
            window.ctoUpdateRoute = '{{ route("cto.update") }}';
            window.ctoDeleteRoute = '{{ route("cto.delete") }}';
            window.ctoCalculateDaysRoute = '{{ route("cto.calculate-days") }}';
            window.ctoStoreActivityRoute = '{{ route("cto.credits") }}';
            window.ctoStoreUsageRoute = '{{ route("cto.submit") }}';
            window.csrfToken = '{{ csrf_token() }}';

            // Modal functionality (Add Customer)
            document.getElementById('showAddCustomerModal').addEventListener('click', function() {
                document.getElementById('addCustomerModal').style.display = 'flex';
            });

            document.getElementById('closeAddCustomerModal').addEventListener('click', function() {
                document.getElementById('addCustomerModal').style.display = 'none';
            });

            // Customer search autocomplete
            let debounceTimer;
            $('#customer-search').on('input', function() {
                clearTimeout(debounceTimer);
                const query = $(this).val();

                if (query.length < 2) {
                    $('#suggestions').empty().hide();
                    return;
                }

                debounceTimer = setTimeout(() => {
                    $.ajax({
                        url: window.autocompleteRoute,
                        method: 'GET',
                        data: { query: query },
                        success: function(data) {
                            $('#suggestions').empty();

                            if (data.length > 0) {
                                data.forEach(function(item) {
                                    $('#suggestions').append(
                                        `<div class="suggestion-item" data-label="${item.label}">${item.label}</div>`
                                    );
                                });
                                $('#suggestions').show();
                            } else {
                                $('#suggestions').hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Autocomplete AJAX error:", status, error, xhr.responseText);
                            $('#suggestions').empty().hide();
                        }
                    });
                }, 300);
            });

            // Handle suggestion clicks
            $(document).on('click', '.suggestion-item', function() {
                const label = $(this).data('label');
                $('#customer-search').val(label);
                $('#suggestions').empty().hide();
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-box').length) {
                    $('#suggestions').empty().hide();
                }
            });

            // --- CTO Specific JavaScript conditional on customer existence ---
            @if($customer)
                // Activity form elements
                const singleDayActivityCheckbox = document.getElementById('single-day-activity');
                const activityEndDateField = document.getElementById('activity-end-date');
                const activityEndDateLabel = document.getElementById('end-date-label');
                const dateOfActivityStartField = document.getElementById('date_of_activity_start');
                const specialOrderField = document.getElementById('special_order');
                const activityField = document.getElementById('activity');
                const hoursEarnedField = document.getElementById('hours_earned');
                const submitActivityBtn = document.getElementById('submit-activity-btn');
                const cancelActivityEditBtn = document.getElementById('cancel-activity-edit-btn');
                const activityCtoIdField = document.getElementById('activity_cto_id');
                const activityFormMethodField = document.getElementById('activity_form_method');
                const activityForm = $('#activity-form');


                // Usage form elements
                const singleDayAbsenceCheckbox = document.getElementById('single-day-absence');
                const absenceEndDateField = document.getElementById('inclusive_date_end_usage');
                const absenceEndDateLabel = document.getElementById('absence-end-date-label');
                const dateFiledUsageField = document.getElementById('usage_date_filed');
                const inclusiveDateStartUsageField = document.getElementById('inclusive_date_start_usage');
                const hoursAppliedUsageField = document.getElementById('hours_applied_usage');
                const ctoDetailsUsageField = document.getElementById('cto_details_usage');
                const submitUsageBtn = document.getElementById('submit-usage-btn');
                const cancelUsageEditBtn = document.getElementById('cancel-usage-edit-btn');
                const usageCtoIdField = document.getElementById('usage_cto_id');
                const usageFormMethodField = document.getElementById('usage_form_method');
                const usageForm = $('#usage-form');


                // Initial state for single day checkboxes
                if (singleDayActivityCheckbox && activityEndDateField && activityEndDateLabel) {
                    if (singleDayActivityCheckbox.checked) {
                        activityEndDateField.style.display = 'none';
                        activityEndDateField.removeAttribute('required');
                        activityEndDateField.value = '';
                        activityEndDateLabel.style.display = 'none';
                    } else {
                        activityEndDateField.style.display = 'block';
                        activityEndDateField.setAttribute('required', 'required');
                        activityEndDateLabel.style.display = 'block';
                    }
                    singleDayActivityCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            activityEndDateField.style.display = 'none';
                            activityEndDateField.value = '';
                            activityEndDateField.removeAttribute('required');
                            activityEndDateLabel.style.display = 'none';
                        } else {
                            activityEndDateField.style.display = 'block';
                            activityEndDateField.setAttribute('required', 'required');
                            activityEndDateLabel.style.display = 'block';
                        }
                    });
                }

                if (singleDayAbsenceCheckbox && absenceEndDateField && absenceEndDateLabel) {
                    if (singleDayAbsenceCheckbox.checked) {
                        absenceEndDateField.style.display = 'none';
                        absenceEndDateField.removeAttribute('required');
                        absenceEndDateField.value = '';
                        absenceEndDateLabel.style.display = 'none';
                    } else {
                        absenceEndDateField.style.display = 'block';
                        absenceEndDateField.setAttribute('required', 'required');
                        absenceEndDateLabel.style.display = 'block';
                    }
                    singleDayAbsenceCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            absenceEndDateField.style.display = 'none';
                            absenceEndDateField.value = '';
                            absenceEndDateField.removeAttribute('required');
                            absenceEndDateLabel.style.display = 'none';
                        } else {
                            absenceEndDateField.style.display = 'block';
                            absenceEndDateField.setAttribute('required', 'required');
                            absenceEndDateLabel.style.display = 'block';
                        }
                    });
                }

                // Function to calculate working days (for CTO usage)
                async function calculateWorkingDaysForUsage() {
                    const startDate = inclusiveDateStartUsageField.value;
                    const endDate = singleDayAbsenceCheckbox.checked ? startDate : absenceEndDateField.value;

                    if (startDate) {
                        try {
                            const response = await fetch(window.ctoCalculateDaysRoute, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.csrfToken
                                },
                                body: JSON.stringify({ start_date: startDate, end_date: endDate })
                            });
                            const data = await response.json();
                            if (response.ok) {
                                // For CTO usage, we often don't display working days, but hours.
                                // If you need to populate a 'total_hours' field based on working days, do it here.
                                // e.g., hoursAppliedUsageField.value = data.days * 8; (assuming 8 hours per day)
                            } else {
                                console.error('Error calculating days:', data.message);
                                displayMessage('Error calculating days: ' + data.message, 'error');
                            }
                        } catch (error) {
                            console.error('Fetch error calculating days:', error);
                            displayMessage('An error occurred while calculating days.', 'error');
                        }
                    }
                }

                // Attach event listeners for date changes in usage form to recalculate days
                if (inclusiveDateStartUsageField) {
                    inclusiveDateStartUsageField.addEventListener('change', calculateWorkingDaysForUsage);
                }
                if (absenceEndDateField) {
                    absenceEndDateField.addEventListener('change', calculateWorkingDaysForUsage);
                }
                if (singleDayAbsenceCheckbox) {
                    singleDayAbsenceCheckbox.addEventListener('change', calculateWorkingDaysForUsage);
                }


                // Edit function for CTO records
                window.editCtoRecord = function(id, is_activity, special_order, activity, hours_earned_or_applied, date_start, date_end, cto_details_usage = '') {
                    if (is_activity) {
                        // Populate Activity Form (Earned Credits)
                        activityCtoIdField.value = id;
                        activityForm.attr('action', window.ctoUpdateRoute); // Use cto.update for PUT
                        activityFormMethodField.value = 'PUT'; // Set to PUT for update
                        submitActivityBtn.textContent = 'Update CTO Activity';
                        cancelActivityEditBtn.style.display = 'inline-block';

                        specialOrderField.value = special_order;
                        activityField.value = activity;
                        hoursEarnedField.value = hours_earned_or_applied;
                        dateOfActivityStartField.value = date_start;

                        if (date_start === date_end) {
                            singleDayActivityCheckbox.checked = true;
                            activityEndDateField.style.display = 'none';
                            activityEndDateField.removeAttribute('required');
                            activityEndDateField.value = '';
                            activityEndDateLabel.style.display = 'none';
                        } else {
                            singleDayActivityCheckbox.checked = false;
                            activityEndDateField.style.display = 'block';
                            activityEndDateField.setAttribute('required', 'required');
                            activityEndDateField.value = date_end;
                            activityEndDateLabel.style.display = 'block';
                        }

                        // Scroll to the activity form
                        activityForm[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

                    } else {
                        // Populate Usage Form (Credits Deducted)
                        usageCtoIdField.value = id;
                        usageForm.attr('action', window.ctoUpdateRoute); // Use cto.update for PUT
                        usageFormMethodField.value = 'PUT'; // Set to PUT for update
                        submitUsageBtn.textContent = 'Update CTO Usage';
                        cancelUsageEditBtn.style.display = 'inline-block';

                        dateFiledUsageField.valueAsDate = new Date(); // Or get from record if stored
                        inclusiveDateStartUsageField.value = date_start;
                        inclusiveDateEndUsageField.value = date_end; // Make sure to set this
                        hoursAppliedUsageField.value = hours_earned_or_applied;
                        ctoDetailsUsageField.value = cto_details_usage;

                        if (date_start === date_end) {
                            singleDayAbsenceCheckbox.checked = true;
                            absenceEndDateField.style.display = 'none';
                            absenceEndDateField.removeAttribute('required');
                            absenceEndDateField.value = '';
                            absenceEndDateLabel.style.display = 'none';
                        } else {
                            singleDayAbsenceCheckbox.checked = false;
                            absenceEndDateField.style.display = 'block';
                            absenceEndDateField.setAttribute('required', 'required');
                            absenceEndDateField.value = date_end;
                            absenceEndDateLabel.style.display = 'block';
                        }

                        // Scroll to the usage form
                        usageForm[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                };

                // Cancel Edit functions (Activity Form)
                window.cancelCtoActivityEdit = function() {
                    activityCtoIdField.value = '';
                    activityForm.attr('action', window.ctoStoreActivityRoute);
                    activityFormMethodField.value = 'POST';
                    submitActivityBtn.textContent = 'Add CTO Activity';
                    cancelActivityEditBtn.style.display = 'none';
                    activityForm[0].reset();
                    singleDayActivityCheckbox.checked = false;
                    activityEndDateField.style.display = 'block';
                    activityEndDateField.setAttribute('required', 'required');
                    activityEndDateLabel.style.display = 'block';
                };

                // Cancel Edit functions (Usage Form)
                window.cancelCtoUsageEdit = function() {
                    usageCtoIdField.value = '';
                    usageForm.attr('action', window.ctoStoreUsageRoute);
                    usageFormMethodField.value = 'POST';
                    submitUsageBtn.textContent = 'Add CTO Usage';
                    cancelUsageEditBtn.style.display = 'none';
                    usageForm[0].reset();
                    singleDayAbsenceCheckbox.checked = false;
                    absenceEndDateField.style.display = 'block';
                    absenceEndDateField.setAttribute('required', 'required');
                    absenceEndDateLabel.style.display = 'block';
                };


                // Delete function for CTO records
                window.deleteCtoRecord = function(id) {
                    showConfirmationModal('Are you sure you want to delete this CTO record? This action cannot be undone and will recalculate balances.', function() {
                        const deleteUrl = window.ctoDeleteRoute;
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: window.csrfToken,
                                id: id
                            },
                            success: function(response) {
                                displayMessage(response.message || 'CTO record deleted successfully.', 'success');
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Delete CTO record error:", status, error, xhr.responseText);
                                displayMessage(xhr.responseJSON.error || 'Failed to delete CTO record.', 'error');
                            }
                        });
                    });
                };

                // --- Custom Modal/Message Box Functions ---
                function displayMessage(message, type) {
                    let messageBox = document.getElementById('custom-message-box');
                    if (!messageBox) {
                        messageBox = document.createElement('div');
                        messageBox.id = 'custom-message-box';
                        messageBox.style.cssText = `
                            position: fixed;
                            top: 20px;
                            left: 50%;
                            transform: translateX(-50%);
                            padding: 15px 25px;
                            border-radius: 8px;
                            font-weight: bold;
                            color: white;
                            z-index: 1000;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                            display: none;
                            opacity: 0;
                            transition: opacity 0.3s ease-in-out;
                        `;
                        document.body.appendChild(messageBox);
                    }

                    messageBox.textContent = message;
                    messageBox.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
                    messageBox.style.display = 'block';
                    setTimeout(() => { messageBox.style.opacity = '1'; }, 10);

                    setTimeout(() => {
                        messageBox.style.opacity = '0';
                        setTimeout(() => { messageBox.style.display = 'none'; }, 300);
                    }, 3000);
                }

                function showConfirmationModal(message, onConfirmCallback) {
                    let modalOverlay = document.getElementById('custom-confirm-overlay');
                    if (!modalOverlay) {
                        modalOverlay = document.createElement('div');
                        modalOverlay.id = 'custom-confirm-overlay';
                        modalOverlay.style.cssText = `
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.6);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            z-index: 1000;
                        `;
                        document.body.appendChild(modalOverlay);

                        const modalContent = document.createElement('div');
                        modalContent.style.cssText = `
                            background: white;
                            padding: 30px;
                            border-radius: 10px;
                            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                            text-align: center;
                            max-width: 400px;
                            width: 90%;
                        `;
                        modalOverlay.appendChild(modalContent);

                        const messagePara = document.createElement('p');
                        messagePara.id = 'confirm-message-text';
                        messagePara.style.marginBottom = '20px';
                        messagePara.style.fontSize = '1.1em';
                        modalContent.appendChild(messagePara);

                        const buttonContainer = document.createElement('div');
                        const confirmButton = document.createElement('button');
                        confirmButton.textContent = 'Confirm';
                        confirmButton.style.cssText = `
                            background-color: #dc3545; /* Red */
                            color: white;
                            padding: 10px 20px;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            margin-right: 10px;
                            font-size: 1em;
                        `;
                        confirmButton.onclick = function() {
                            modalOverlay.style.display = 'none';
                            if (onConfirmCallback) {
                                onConfirmCallback();
                            }
                        };
                        buttonContainer.appendChild(confirmButton);

                        const cancelButton = document.createElement('button');
                        cancelButton.textContent = 'Cancel';
                        cancelButton.style.cssText = `
                            background-color: #6c757d; /* Grey */
                            color: white;
                            padding: 10px 20px;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 1em;
                        `;
                        cancelButton.onclick = function() {
                            modalOverlay.style.display = 'none';
                        };
                        buttonContainer.appendChild(cancelButton);
                        modalContent.appendChild(buttonContainer);
                    }

                    document.getElementById('confirm-message-text').textContent = message;
                    modalOverlay.style.display = 'flex';
                }
            @endif {{-- End of @if($customer) conditional for JS --}}

        }); {{-- End of $(document).ready() --}}
    </script>
</body>
</html>