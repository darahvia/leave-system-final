<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave</title>
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="tab-nav" style="margin-bottom: 1.5rem;">
        <a href="{{ route('leave.customer.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('leave.customer.index') ? ' active' : '' }}">Leave</a>
        <a href="{{ route('cto.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('cto.index') ? ' active' : '' }}">CTO</a>
    </div>
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
    </style>

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
                <div class="header-subtitle">Leave Credit System</div>
            </div>
            <img src="/images/deped-cadiz-logo.png" alt="Division Logo" class="header-logo">
        </div>

        <div class="search-bar-section">
            <form method="POST" action="{{ route('customer.find') }}" class="search-form" autocomplete="off">
                @csrf
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
        </div>
    </div>

    <div class="modal-bg" id="addEmpModal">
        <div class="modal-content">
            <button class="close" id="closeAddEmpModal">&times;</button>
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
                        <input type="text" name="original_appointment">
                        <label>Salary:</label>
                        <input type="number" step="0.01" name="salary" required>

                        <label>Vacation Leave Forwarded Balance:</label>
                        <input type="number" step="0.01" name="balance_forwarded_vl" required>
                        <label>Sick Leave Forwarded Balance:</label>
                        <input type="number" step="0.01" name="balance_forwarded_sl" required>
                        <div style="height: 1rem;"></div>

                        <button type="submit">Add Customer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($customer)
        @php
            $latestApp = $customer->leaveApplications->last();
        @endphp

        <div class="emp-details-table">
            <table class="customer-info-table">
                <tr>
                    <td class="label">SURNAME</td>
                    <td class="value">{{ strtoupper($customer->surname) }}</td>
                    <td class="label">DIVISION</td>
                    <td class="value">{{ strtoupper($customer->office->office) }}</td>
                    <td class="label">BASIC SALARY</td>
                    <td class="value">{{ number_format($customer->salary, 2) }}</td>
                    <td class="label"> FORCE LEAVE BALANCE </td>
                    <td class="value">{{ strtoupper($customer->fl) }}</td>
                </tr>
                <tr>
                    <td class="label">GIVEN NAME</td>
                    <td class="value">{{ strtoupper($customer->given_name) }}</td>
                    <td class="label">DESIGNATION</td>
                    <td class="value">{{ strtoupper($customer->position->position) }}</td>
                    <td class="label">VACATION LEAVE BALANCE</td>
                    <td class="value">{{ $latestApp ? $latestApp->current_vl : ($customer->balance_forwarded_vl ?? 0) }}</td>
                    <td class="label">SPECIAL PRIVILEGE LEAVE BALANCE</td>
                    <td class="value">{{ $customer->spl ?? 0 }}</td>
                </tr>
                <tr>
                    <td class="label">MIDDLE NAME</td>
                    <td class="value">{{ strtoupper($customer->middle_name) }}</td>
                    <td class="label">ORIGINAL APPOINTMENT</td>
                    <td class="value">{{ $customer->origappnt_date ?? '' }}</td>
                    <td class="label">SICK LEAVE BALANCE</td>
                    <td class="value">{{ $latestApp ? $latestApp->current_sl : ($customer->balance_forwarded_sl ?? 0) }}</td>
                    <td class="label"> VIEW OTHER LEAVE BALANCES </td>
                    <td class="value">
                        <button type="button" id="viewAllBtn" onclick="showOtherCreditsModal()">View All</button>
                    </td>
                </tr>
            </table>
        </div>

        <div class="modal-bg" id="otherCreditsModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:999;">
            <div class="modal-content" style="background:#fff; margin:5% auto; padding:20px; border-radius:8px; max-width:400px; position:relative;">
                <button class="close" onclick="closeOtherCreditsModal()" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:20px;">&times;</button>
                <h3>Other Leave Credits</h3>
                @php
                    $balances = app(\App\Services\LeaveService::class)->getCurrentBalances($customer);
                @endphp

                <ul style="list-style:none; padding:0;">
                    @foreach ($balances as $type => $value)
                        @if (!in_array($type, ['vl', 'sl', 'vawc']))
                            <li>{{ \App\Services\LeaveService::getLeaveTypes()[strtoupper($type)] ?? ucfirst(str_replace('_', ' ', $type)) }}: <strong>{{ $value }}</strong></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    @if($customer)
        <div class="bottom-section">
            <form method="POST" action="{{ route('leave.submit') }}" id="leave-form" class="leave-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <input type="hidden" name="_method" id="form_method" value="POST">
                <input type="hidden" name="is_cancellation" id="is_cancellation" value="0">
                <div class="emp-form" id="leave-form-container">
                    <label>Leave Type:</label>
                    <select name="leave_type" class="form-control" required>
                        @foreach ($leaveTypes as $code => $label)
                            <option value="{{ $code }}" {{ old('leave_type') == $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <label>Date Filed:</label>
                    <input type="date" name="date_filed" id="date_filed" required>
                    <label>Leave Start Date (Inclusive):</label>
                    <input type="date" name="inclusive_date_start" id="inclusive_date_start" required>
                    <label>Leave End Date (Inclusive):</label>
                    <input type="date" name="inclusive_date_end" id="inclusive_date_end" required>
                    <label>Working Days:</label>
                    <input type="number" name="working_days" id="working_days" readonly style="background-color: #f5f5f5;">
                    <label>Remarks:</label>
                    <input type="text" name="leave_details" id="leave_details">
                    <button type="submit" id="submit-btn">Add Leave</button>
                    <button type="button" id="cancel-edit-btn" onclick="cancelEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                </div>
            </form>
            <form method="POST" action="{{ route('leave.credits') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="emp-form">
                    <label>Earned Date:</label>
                    <input type="date" name="earned_date" required>
                    <button type="submit">Add Credits Earned</button>
                </div>
            </form>
            <form method="POST" action="{{ route('leave.otherCredits') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="emp-form">
                    <label>Leave Type:</label>
                    <select name="leave_type" class="form-control" required>
                        @foreach ($leaveTypes as $code => $label)
                            <option value="{{ $code }}" {{ old('leave_type') == $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <label>Credits to Add:</label>
                    <input type="number" name="credits" step="0.01" required>

                    <button type="submit">Add Other Credits</button>
                </div>
            </form>
            <form method="POST" action="{{ route('cto.submit') }}" id="cto-form" class="leave-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="edit_id" id="cto_edit_id" value="">
                <input type="hidden" name="_method" id="cto_form_method" value="POST">
                <div class="emp-form" id="cto-form-container">
                    <h3>Apply for CTO</h3>
                    <label>Date of CTO:</label>
                    <input type="date" name="cto_date" id="cto_date" required>
                    <label>Hours Applied:</label>
                    <input type="number" name="hours_applied" id="cto_hours_applied" step="0.01" required>
                    <label>Remarks:</label>
                    <input type="text" name="cto_details" id="cto_details">
                    <button type="submit" id="cto-submit-btn">Add CTO Application</button>
                    <button type="button" id="cto-cancel-edit-btn" onclick="cancelCtoEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                </div>
            </form>
            <form method="POST" action="{{ route('cto.credits') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="emp-form">
                    <h3>Add Earned CTO Credits</h3>
                    <label>Earned Date:</label>
                    <input type="date" name="earned_date" required>
                    <label>Hours Earned:</label>
                    <input type="number" name="hours_earned" step="0.01" required>
                    <button type="submit">Add CTO Earned</button>
                </div>
            </form>
        </div>
    @endif

    @if($customer)
        <table class="leave-table">
            <thead>
                <tr>
                    <th>PERIOD</th>
                    <th>VL EARNED</th>
                    <th>SL EARNED</th>
                    <th>CTO EARNED</th>
                    <th>DATE LEAVE FILED</th>
                    <th>DATE LEAVE INCURRED</th>
                    <th>LEAVE INCURRED</th>
                    <th>VL</th>
                    <th>SL</th>
                    <th>SPL</th>
                    <th>FL</th>
                    <th>SOLO PARENT</th>
                    <th>OTHERS</th>
                    <th>CTO Hours</th>
                    <th>REMARKS</th>
                    <th>VL BALANCE</th>
                    <th>SL BALANCE</th>
                    <th>CTO BALANCE</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td data-label="PERIOD">BALANCE FORWARDED</td>
                    <td data-label="VL EARNED"></td>
                    <td data-label="SL EARNED"></td>
                    <td data-label="CTO EARNED"></td>
                    <td data-label="DATE LEAVE FILED"></td>
                    <td data-label="DATE LEAVE INCURRED"></td>
                    <td data-label="LEAVE INCURRED"></td>
                    <td data-label="VL"></td>
                    <td data-label="SL"></td>
                    <td data-label="SPL"></td>
                    <td data-label="FL"></td>
                    <td data-label="SOLO PARENT"></td>
                    <td data-label="OTHERS"></td>
                    <td data-label="CTO Hours"></td>
                    <td data-label="REMARKS"></td>
                    <td data-label="VL BALANCE">{{ number_format($customer->balance_forwarded_vl, 3) }}</td>
                    <td data-label="SL BALANCE">{{ number_format($customer->balance_forwarded_sl, 3) }}</td>
                    <td data-label="CTO BALANCE">{{ number_format($customer->balance_forwarded_cto ?? 0, 3) }}</td>
                    <td data-label="ACTIONS"></td>
                </tr>
@if($customer->leaveApplications && $customer->leaveApplications->count())
    @php
        $sortedApplications = $customer->leaveApplications->sortBy(function($app) {
            return $app->earned_date ?? $app->date_filed ?? '1900-01-01';
        });
    @endphp
    @foreach($sortedApplications as $app)
                        <tr>
                            <td data-label="PERIOD">{{ $app->earned_date ? \Carbon\Carbon::parse($app->earned_date)->format('F j, Y') : '' }}</td>
                            <td data-label="VL EARNED">
                                @if($app->is_credit_earned)
                                    @if($app->leave_type === 'VL' || !$app->leave_type)
                                        {{ $app->earned_vl ?? '1.25' }}
                                    @endif
                                @endif
                            </td>
                            <td data-label="SL EARNED">
                                @if($app->is_credit_earned)
                                    @if($app->leave_type === 'SL' || !$app->leave_type)
                                        {{ $app->earned_sl ?? '1.25' }}
                                    @endif
                                @endif
                            </td>
                            <td data-label="CTO EARNED">
                                @if($app->is_cto_earned) {{-- Assuming 'is_cto_earned' flag --}}
                                    {{ $app->earned_cto_hours ?? '' }}
                                @endif
                            </td>
                            <td data-label="DATE LEAVE FILED">{{ $app->date_filed ? \Carbon\Carbon::parse($app->date_filed)->format('F j, Y') : '' }}</td>
                            <td data-label="DATE LEAVE INCURRED">
                                @if($app->inclusive_date_start && $app->inclusive_date_end)
                                    @if(\Carbon\Carbon::parse($app->inclusive_date_start)->isSameDay(\Carbon\Carbon::parse($app->inclusive_date_end)))
                                        {{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('F j, Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('F j, Y') }}
                                    @endif
                                @elseif($app->date_incurred)
                                    {{ \Carbon\Carbon::parse($app->date_incurred)->format('F j, Y') }}
                                @endif
                            </td>
                            <td data-label="LEAVE INCURRED">
                                @if(!$app->is_credit_earned && !$app->is_cto_application) {{-- Not earned credit and not CTO application --}}
                                    {{ \App\Services\LeaveService::getLeaveTypes()[$app->leave_type] ?? $app->leave_type }}
                                @endif
                            </td>

                            <td data-label="VL">
                                @if(!$app->is_credit_earned && !$app->is_cto_application && $app->leave_type === 'VL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SL">
                                @if(!$app->is_credit_earned && !$app->is_cto_application && $app->leave_type === 'SL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SPL">
                                @if(!$app->is_credit_earned && !$app->is_cto_application && $app->leave_type === 'SPL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="FL">
                                @if(!$app->is_credit_earned && !$app->is_cto_application && $app->leave_type === 'FL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SOLO PARENT">
                                @if(!$app->is_credit_earned && !$app->is_cto_application && $app->leave_type === 'SOLO PARENT')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            @php
                                $otherLeaveTypes = ['ML', 'PL', 'RA9710', 'RL', 'SEL', 'STUDY_LEAVE', 'ADOPT', 'VAWC'];
                            @endphp
                            <td data-label="OTHERS">
                                @if (in_array($app->leave_type, $otherLeaveTypes))
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="CTO Hours">
                                @if($app->is_cto_application) {{-- If it's a CTO application --}}
                                    {{ $app->hours_applied ?? '' }}
                                @endif
                            </td>
                            <td data-label="REMARKS">
                                @if(!$app->is_credit_earned)
                                    {{ $app->leave_details ?? $app->cto_details ?? '' }}
                                @endif
                            </td>
                            <td data-label="VL BALANCE">{{ $app->current_vl ?? '' }}</td>
                            <td data-label="SL BALANCE">{{ $app->current_sl ?? '' }}</td>
                            <td data-label="CTO BALANCE">{{ $app->current_cto ?? '' }}</td>
                            <td data-label="ACTIONS">
                                @if ($app->is_credit_earned)
                                    {{-- Credits earned → Only delete --}}
                                    <button type="button" class="delete-btn" onclick="deleteRecord({{ $app->id }}, 'credit')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="m5,6 1,14c0,1 1,2 2,2h8c1,0 2-1 2-2l1-14"></path>
                                            <path d="m10,11 0,6"></path>
                                            <path d="m14,11 0,6"></path>
                                            <path d="M8,6V4c0-1,1-2,2-2h4c0-1,1-2,2-2v2"></path>
                                        </svg>
                                    </button>

                                @elseif ($app->is_cto_earned)
                                    {{-- CTO Credits earned → Only delete --}}
                                    <button type="button" class="delete-btn" onclick="deleteRecord({{ $app->id }}, 'cto_credit')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="m5,6 1,14c0,1 1,2 2,2h8c1,0 2-1 2-2l1-14"></path>
                                            <path d="m10,11 0,6"></path>
                                            <path d="m14,11 0,6"></path>
                                            <path d="M8,6V4c0-1,1-2,2-2h4c0-1,1-2,2-2v2"></path>
                                        </svg>
                                    </button>

                                @elseif ($app->is_cancellation)
                                    {{-- Cancellation → Only edit --}}
                                    <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                        {{ $app->id }},
                                        '{{ $app->leave_type }}',
                                        '{{ \Carbon\Carbon::parse($app->date_filed)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('Y-m-d') }}',
                                        '{{ $app->working_days }}',
                                        '{{ $app->leave_details ?? '' }}'
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>

                                @elseif ($app->is_cto_application)
                                    {{-- CTO application → Edit and Delete --}}
                                    <button type="button" class="edit-btn" onclick="editCtoApplication(
                                        {{ $app->id }},
                                        '{{ \Carbon\Carbon::parse($app->date_filed)->format('Y-m-d') }}',
                                        '{{ $app->hours_applied }}',
                                        '{{ $app->cto_details ?? '' }}'
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="delete-btn" onclick="deleteRecord({{ $app->id }}, 'cto_application')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="m5,6 1,14c0,1 1,2 2,2h8c1,0 2-1 2-2l1-14"></path>
                                            <path d="m10,11 0,6"></path>
                                            <path d="m14,11 0,6"></path>
                                            <path d="M8,6V4c0-1,1-2,2-2h4c0-1,1-2,2-2v2"></path>
                                        </svg>
                                    </button>

                                @else
                                    {{-- Regular leave → Edit, Delete, Cancel --}}
                                    <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                        {{ $app->id }},
                                        '{{ $app->leave_type }}',
                                        '{{ \Carbon\Carbon::parse($app->date_filed)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('Y-m-d') }}',
                                        '{{ $app->working_days }}',
                                        '{{ $app->leave_details ?? '' }}'
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
                                        </svg>
                                    </button>

                                    <button type="button" class="delete-btn" onclick="deleteRecord({{ $app->id }}, 'leave')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3,6 5,6 21,6"></polyline>
                                            <path d="m5,6 1,14c0,1 1,2 2,2h8c1,0 2-1 2-2l1-14"></path>
                                            <path d="m10,11 0,6"></path>
                                            <path d="m14,11 0,6"></path>
                                            <path d="M8,6V4c0-1,1-2,2-2h4c0-1,1-2,2-2v2"></path>
                                        </svg>
                                    </button>

                                    <button type="button" class="cancel-btn" onclick="cancelLeaveApplication(
                                        {{ $app->id }},
                                        '{{ $app->leave_type }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('Y-m-d') }}',
                                        '{{ $app->working_days }}'
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    @endif
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Make Laravel routes available to JavaScript
        window.autocompleteRoute = '{{ route("customer.autocomplete") }}';
        window.leaveUpdateRoute = '{{ route("leave.update") }}';
        window.deleteRoute = '{{ route("leave.delete") }}';
        window.ctoUpdateRoute = '{{ route("cto.update") }}'; // New CTO update route
        window.ctoDeleteRoute = '{{ route("cto.delete") }}'; // New CTO delete route
        window.csrfToken = '{{ csrf_token() }}';

        function showOtherCreditsModal() {
            document.getElementById('otherCreditsModal').style.display = 'block';
        }

        function closeOtherCreditsModal() {
            document.getElementById('otherCreditsModal').style.display = 'none';
        }

        // Function to edit leave application
        function editLeaveApplication(id, leave_type, date_filed, inclusive_date_start, inclusive_date_end, working_days, leave_details) {
            document.getElementById('edit_id').value = id;
            document.querySelector('select[name="leave_type"]').value = leave_type;
            document.getElementById('date_filed').value = date_filed;
            document.getElementById('inclusive_date_start').value = inclusive_date_start;
            document.getElementById('inclusive_date_end').value = inclusive_date_end;
            document.getElementById('working_days').value = working_days;
            document.getElementById('leave_details').value = leave_details;

            document.getElementById('submit-btn').textContent = 'Update Leave';
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('cancel-edit-btn').style.display = 'inline-block';
            document.getElementById('is_cancellation').value = '0'; // Ensure not marked as cancellation during edit
            document.getElementById('leave-form').action = window.leaveUpdateRoute; // Set action for update
        }

        // Function to cancel a leave application
        function cancelLeaveApplication(id, leave_type, inclusive_date_start, inclusive_date_end, working_days) {
            if (confirm('Are you sure you want to cancel this leave application?')) {
                // Set the form to act as a cancellation
                document.getElementById('edit_id').value = id;
                document.querySelector('select[name="leave_type"]').value = leave_type;
                document.getElementById('date_filed').valueAsDate = new Date(); // Date of cancellation
                document.getElementById('inclusive_date_start').value = inclusive_date_start; // Original start date
                document.getElementById('inclusive_date_end').value = inclusive_date_end; // Original end date
                document.getElementById('working_days').value = working_days;
                document.getElementById('leave_details').value = 'CANCELLATION of ' + leave_type + ' from ' + inclusive_date_start + ' to ' + inclusive_date_end;
                document.getElementById('is_cancellation').value = '1';

                document.getElementById('submit-btn').textContent = 'Confirm Cancellation';
                document.getElementById('form_method').value = 'POST'; // Cancellations are new records, not updates of the original
                document.getElementById('leave-form').action = '{{ route("leave.submit") }}'; // Submit as a new record

                // Submit the form
                document.getElementById('leave-form').submit();
            }
        }

        // Function to edit CTO application
        function editCtoApplication(id, cto_date, hours_applied, cto_details) {
            document.getElementById('cto_edit_id').value = id;
            document.getElementById('cto_date').value = cto_date;
            document.getElementById('cto_hours_applied').value = hours_applied;
            document.getElementById('cto_details').value = cto_details;

            document.getElementById('cto-submit-btn').textContent = 'Update CTO';
            document.getElementById('cto_form_method').value = 'PUT';
            document.getElementById('cto-cancel-edit-btn').style.display = 'inline-block';
            document.getElementById('cto-form').action = window.ctoUpdateRoute; // Set action for update
        }

        function cancelCtoEdit() {
            document.getElementById('cto_edit_id').value = '';
            document.getElementById('cto_date').value = '';
            document.getElementById('cto_hours_applied').value = '';
            document.getElementById('cto_details').value = '';

            document.getElementById('cto-submit-btn').textContent = 'Add CTO Application';
            document.getElementById('cto_form_method').value = 'POST';
            document.getElementById('cto-cancel-edit-btn').style.display = 'none';
            document.getElementById('cto-form').action = "{{ route('cto.update', ['id' => ':id']) }}".replace(':id', id);
        }


        function deleteRecord(id, type) {
            if (confirm('Are you sure you want to delete this record?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const csrfInput = document.createElement('input');
                csrfInput.name = '_token';
                csrfInput.value = window.csrfToken;
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                const idInput = document.createElement('input');
                idInput.name = 'id';
                idInput.value = id;
                form.appendChild(idInput);

                if (type === 'credit' || type === 'leave' || type === 'cancellation') {
                    form.action = window.deleteRoute;
                } else if (type === 'cto_credit' || type === 'cto_application') {
                    form.action = window.ctoDeleteRoute;
                }

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <script src="{{ asset('js/leave-form.js') }}"></script>

</body>
</html>