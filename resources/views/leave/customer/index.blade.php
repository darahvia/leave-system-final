<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave</title>
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
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

    @include('partials.header', [
        'pageTitle' => 'Leave Credit System',
        'customer' => $customer ?? null,
        'ctoService' => app(\App\Services\CtoService::class)
    ])
    <div class="tab-nav" style="margin-bottom: 1.5rem;">
        <a href="{{ route('leave.customer.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('leave.customer.index') ? ' active' : '' }}">Leave</a>
        <a href="{{ route('cto.index') }}{{ $customer ? '?customer_id=' . $customer->id : '' }}" class="tab-link{{ request()->routeIs('cto.index') ? ' active' : '' }}">CTO</a>
    </div>




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

                    <div class="single-day-check">
                        <label>
                            <input type="checkbox" name="is_single_day_activity" id="single-day-activity"> Single Day Activity
                        </label>
                    </div>
                    
                    <div class="date-row">
                        <div class="date-col">
                            <label>Leave Start Date:</label>
                            <input type="date" name="inclusive_date_start" id="inclusive_date_start" required>
                            <span class="halfday-controls" id="start-halfday-span">
                                <button type="button" class="toggle-button" id="start-am-btn" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="start-pm-btn" data-value="PM">PM</button>
                            </span>
                        </div>
                        <div class="date-col" id="end-date-col">
                            <label>Leave End Date:</label>
                            <input type="date" name="inclusive_date_end" id="inclusive_date_end" required>
                            <span class="halfday-controls" id="end-halfday-span">
                                <button type="button" class="toggle-button" id="end-am-btn" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="end-pm-btn" data-value="PM">PM</button>
                            </span>
                        </div>
                    </div>
                    <label>Working Days:</label>
                    <input type="number" name="working_days" step="0.01" id="working_days">
                    <label>Remarks:</label>
                    <input type="text" name="leave_details" id="leave_details">
                    <button type="submit" id="submit-btn">Use Leave Credits</button>
                    <button type="button" id="cancel-edit-btn" onclick="cancelEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                    <label>
                    <input type="checkbox" name="is_leavewopay" id="is_leavewopay" value="1"> Leave Without Pay
                    </label>                   <label>
                    <input type="checkbox" name="is_leavepay" id="is_leavepay" value="1"> Leave With Pay
                    </label>
                </div>
            </form>
            <form method="POST" action="{{ route('leave.credits') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="emp-form">
                    <label>Earned Date:</label>
                    <input type="date" name="earned_date" required>
                    <div class="form-group">
                        <label for="earned_vl">VL Credits:</label>
                        <input type="number" name="earned_vl" id="earned_vl" value="1.25" step="0.001" min="0" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="earned_sl">SL Credits:</label>
                        <input type="number" name="earned_sl" id="earned_sl" value="1.25" step="0.001" min="0" class="form-control" required>
                    </div>
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
                             @if (!in_array($code, ['VL', 'SL']))
                                <option value="{{ $code }}" {{ old('leave_type') == $code ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    <label>Credits to Add:</label>
                    <input type="number" name="credits" step="0.01" required>

                    <button type="submit">Add Other Credits</button>
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
                    <th>DATE FILED</th>
                    <th>DATE INCURRED</th>
                    <th>LEAVE INCURRED</th>
                    <th>VL</th>
                    <th>SL</th>
                    <th>SPL</th>
                    <th>FL</th>
                    <th>SOLO PARENT</th>
                    <th>OTHERS</th>
                    <th>REMARKS</th>
                    <th>VL BAL</th>
                    <th>SL BAL</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td data-label="PERIOD">BALANCE FORWARDED</td>
                    <td data-label="VL EARNED"></td>
                    <td data-label="SL EARNED"></td>
                    <td data-label="DATE LEAVE FILED"></td>
                    <td data-label="DATE LEAVE INCURRED"></td>
                    <td data-label="LEAVE INCURRED"></td>
                    <td data-label="VL"></td>
                    <td data-label="SL"></td>
                    <td data-label="SPL"></td>
                    <td data-label="FL"></td>
                    <td data-label="SOLO PARENT"></td>
                    <td data-label="OTHERS"></td>
                    <td data-label="REMARKS"></td>
                    <td data-label="VL BALANCE">{{ number_format($customer->balance_forwarded_vl, 3) }}</td>
                    <td data-label="SL BALANCE">{{ number_format($customer->balance_forwarded_sl, 3) }}</td>
                    <td data-label="ACTIONS"></td>
                </tr>
@if($customer->leaveApplications && $customer->leaveApplications->count())
@php
    $sortedApplications = $customer->leaveApplications->sortBy(function($app) {
        return $app->earned_date ?? $app->date_filed ?? '1900-01-01';
    });
@endphp
@foreach($sortedApplications as $app)
                        <tr class="{{ ($app->is_leavewopay) ? 'leave-without-pay' : '' }}">
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
                            <td data-label="DATE LEAVE FILED">
                                {{ $app->is_credit_earned ? '' : ($app->date_filed ? \Carbon\Carbon::parse($app->date_filed)->format('F j, Y') : '') }}
                            </td>
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
                                @if(!$app->is_credit_earned) {{-- Not earned credit and not CTO application --}}
                                    {{ \App\Services\LeaveService::getLeaveTypes()[$app->leave_type] ?? $app->leave_type }}
                                @endif
                            </td>

                            <td data-label="VL">
                                @if(!$app->is_credit_earned && $app->leave_type === 'VL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SL">
                                @if(!$app->is_credit_earned && $app->leave_type === 'SL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SPL">
                                @if(!$app->is_credit_earned && $app->leave_type === 'SPL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="FL">
                                @if(!$app->is_credit_earned && $app->leave_type === 'FL')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="SOLO PARENT">
                                @if(!$app->is_credit_earned && $app->leave_type === 'SOLO PARENT')
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            @php
                                $otherLeaveTypes = ['ML', 'PL', 'RA9710', 'RL', 'SEL', 'STUDY_LEAVE', 'ADOPT', 'VAWC', 'SOLO_PARENT'];
                            @endphp
                            <td data-label="OTHERS">
                                @if (in_array($app->leave_type, $otherLeaveTypes))
                                    {{ $app->working_days ?? '' }}
                                @endif
                            </td>
                            <td data-label="REMARKS">
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
                            <td data-label="VL BALANCE">{{ $app->current_vl ?? '' }}</td>
                            <td data-label="SL BALANCE">{{ $app->current_sl ?? '' }}</td>
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
                                @elseif ($app->is_cancellation)
                                    {{-- Cancellation → Only edit --}}
                                    <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                        {{ $app->id }},
                                        '{{ $app->leave_type }}',
                                        '{{ \Carbon\Carbon::parse($app->date_filed)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_start)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->inclusive_date_end)->format('Y-m-d') }}',
                                        '{{ $app->working_days }}',
                                        '{{ $app->leave_details ?? '' }}',
                                        @json($app->is_leavewopay),
                                        @json($app->is_leavepay)
                                        
                                    )">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M12 12l7-7 3 3-7 7-3 0 0-3z"></path>
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
                                        '{{ $app->leave_details ?? '' }}',
                                        @json($app->is_leavewopay),
                                        @json($app->is_leavepay)
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
                                        '{{ $app->working_days }}',
                                        @json($app->is_leavewopay),
                                        @json($app->is_leavepay)
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
        window.csrfToken = '{{ csrf_token() }}';
    </script>

    <script src="{{ asset('js/leave-form.js') }}"></script>

</body>
</html>