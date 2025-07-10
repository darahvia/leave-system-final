<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave - Teaching</title>
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .tabs-container {
            margin: 20px 0;
            border-bottom: 2px solid #ddd;
        }
        
        .tabs {
            display: flex;
            gap: 0;
        }
        
        .tab-button {
            padding: 12px 24px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background-color: #1e40af;
            color: white;
        }
        
        .tab-button:hover:not(.active) {
            background-color: #e9ecef;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
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
                <div class="header-subtitle">Teaching Leave Credit System</div>
            </div>
            <img src="/images/deped-cadiz-logo.png" alt="Division Logo" class="header-logo">
        </div>

        <div class="search-bar-section">
        <a href="{{ route('leave.select') }}" class="home-button">Home</a>

            <form method="POST" action="{{ route('teaching.find') }}" class="search-form" autocomplete="off">
                @csrf
                <div class="search-box">
                    <button type="submit" class="search-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <input type="text" name="name" id="customer-search" autocomplete="off" required placeholder="Find Teaching Employee...">
                    <div id="suggestions"></div>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Details Table FOLLOW NONTEACHING -->
    @if($customer)
        <div class="emp-details-table">
            <table class="customer-info-table">
                <tr>
                    <td class="label">SURNAME</td>
                    <td class="value">{{ strtoupper($customer->surname) }}</td>
                    <td class="label">SCHOOL</td>
                    <td class="value">{{ strtoupper($customer->office->office) ?? ''  }}</td>
                    <td class="label">STATUS</td>
                    <td class="value">{{ $customer->status ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">GIVEN NAME</td>
                    <td class="value">{{ strtoupper($customer->given_name)?? ''  }}</td>
                    <td class="label">POSITION</td>
                    <td class="value">{{ strtoupper($customer->position->position) ?? '' }}</td>
                    <td class="label">LEAVE CREDITS BALANCE (OLD)</td>
                    <td class="value">{{ $customer->leave_credits_old ?? 0  }}</td>
                </tr>
                <tr>
                    <td class="label">MIDDLE NAME</td>
                    <td class="value">{{ strtoupper($customer->middle_name)?? ''  }}</td>
                    <td class="label">ORIGINAL APPOINTMENT</td>
                    <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
                    <td class="label">LEAVE CREDITS BALANCE (NEW)</td>
                    <td class="value">{{ $customer->leave_credits_new ?? 0 }}</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Tabs Section -->
    @if($customer)
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-button active" onclick="switchTab('old')">Old (Before October 1
                    , 2024)</button>
                <button class="tab-button" onclick="switchTab('new')">New (After October 1
                    , 2024)</button>
            </div>
        </div>
    @endif

    <!-- Tab Content for Old Records -->
    @if($customer)
        <div id="old-tab" class="tab-content active">
            <!-- Bottom: Add Leave Application and Add Credits Earned -->
            <div class="bottom-section">
                <!-- Add Credits Earned -->
                <form method="POST" action="{{ route('teaching.credits.add') }}">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <div class="emp-form">
                        <div class="date-row">
                            <div class="date-col">
                                <label>Earned Date Start:</label>
                                <input type="date" name="earned_date_start" required>
                            </div>  
                            <div class="date-col" id="end-date-col-old">
                                <label>Earned Date End:</label>
                                <input type="date" name="earned_date_end" required>
                            </div>
                        </div>
                        <label>Event:</label>
                        <input type="text" name="event" required>
                        <label>Special Order:</label>
                        <input type="text" name="special_order" id="special_order">
                        <label>Number of days:</label>
                        <input type="number" name="credits_to_add" step="0.01" min="0.01" max="50" required>
                        <label>Reference:</label>
                        <input type="text" name="reference" id="reference">
                        <button type="submit">Add Credits Earned</button>
                    </div>
                </form>
                <!-- Add Leave Application -->
                <form method="POST" action="{{ route('teaching.leave.submit') }}" id="leave-form-old" class="leave-form">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input type="hidden" name="edit_id" id="edit_id_old" value="">
                    <input type="hidden" name="_method" id="form_method_old" value="POST">
                    <div class="emp-form" id="leave-form-container-old">
                    <div class="date-row">
                        <div class="date-col">
                            <label>Leave Start Date:</label>
                            <input type="date" name="leave_start_date" id="leave_start_date_old" required>
                            <span class="halfday-controls">
                                <button type="button" class="toggle-button" id="start-am-btn-old" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="start-pm-btn-old" data-value="PM">PM</button>
                            </span>
                        </div>
                        <div class="date-col" id="end-date-col-old">
                            <label>Leave End Date:</label>
                            <input type="date" name="leave_end_date" id="leave_end_date_old" required>
                            <span class="halfday-controls">
                                <button type="button" class="toggle-button" id="end-am-btn-old" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="end-pm-btn-old" data-value="PM">PM</button>
                            </span>
                        </div>
                    </div>
                    <label>Working Days:</label>
                    <input type="number" name="working_days" step="0.01" id="working_days_old" >
                        <button type="submit" id="submit-btn-old">Add Leave Application</button>
                        <button type="button" id="cancel-edit-btn-old" onclick="cancelEdit('old')" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                        <label>
                        <input type="checkbox" name="is_leavewopay" id="is_leavewopay_old" value="1"> Leave Without Pay
                        </label>
                    </div>
                </form>
            </div>

            <!-- Leave Records Tables - Side by Side -->
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <!-- Left Table: Credits Earned (Old) -->
                <div style="flex: 1;">
                    <h3>Credits Earned (Before Oct 1, 2024)</h3>
                    <table class="leave-table">
                        <thead>
                            <tr>
                                <th>EARNED DATE</th>
                                <th>EVENT</th>
                                <th>SPECIAL ORDER</th>
                                <th>CREDITS TO ADD</th>
                                <th>REFERENCE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($teachingEarnedCredits && $teachingEarnedCredits->count())
                                @foreach($teachingEarnedCredits->filter(function($credit) {
                                    return \Carbon\Carbon::parse($credit->earned_date_start)->lt(\Carbon\Carbon::parse('2024-10-01'));
                                })->sortBy('earned_date_start') as $credit)
                                    <tr>
                                        <td data-label="EARNED DATE">
                                            @if($credit->earned_date_start && $credit->earned_date_end)
                                                @if(\Carbon\Carbon::parse($credit->earned_date_start)->isSameDay(\Carbon\Carbon::parse($credit->earned_date_end)))
                                                    {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('F j, Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($credit->earned_date_end)->format('F j, Y') }}
                                                @endif
                                            @elseif($credit->created_at)
                                                {{ \Carbon\Carbon::parse($credit->created_at)->format('F j, Y') }}
                                            @endif
                                        </td>
                                        <td data-label="EVENT">{{ $credit->event ?? '' }}</td>
                                        <td data-label="SPECIAL ORDER">{{ $credit->special_order ?? '' }}</td>
                                        <td data-label="CREDITS TO ADD">
                                            <span style="color: green;">+{{ $credit->days }}</span>
                                        </td>
                                        <td data-label="REFERENCE">{{ $credit->reference ?? '' }}</td>
                                        <td data-label="ACTIONS">
                                            <button type="button" class="delete-btn" onclick="deleteRecord({{ $credit->id }}, 'credit')">
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
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Right Table: Leave Usage (Old) -->
                <div style="flex: 1;">
                    <h3>Leave Usage (Before October 1
                        , 2024)</h3>
                    <table class="leave-table">
                        <thead>
                            <tr>
                                <th>DATE</th>
                                <th>DAYS</th>
                                <th>BALANCE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="DATE">INITIAL BALANCE</td>
                                <td data-label="DAYS"></td>
                                <td data-label="BALANCE">{{ $customer->leave_credits }}</td>
                                <td data-label="ACTIONS"></td>
                            </tr>
                            
                            @if($teachingLeaveApplications && $teachingLeaveApplications->count())
                                @foreach($teachingLeaveApplications->filter(function($app) {
                                    return \Carbon\Carbon::parse($app->leave_start_date)->lt(\Carbon\Carbon::parse('2024-10-01'));
                                }) as $app)
                                    <tr>
                                        <td data-label="DATE LEAVE INCURRED">
                                            @if($app->leave_start_date && $app->leave_end_date)
                                                @if(\Carbon\Carbon::parse($app->leave_start_date)->isSameDay(\Carbon\Carbon::parse($app->leave_end_date)))
                                                    {{ \Carbon\Carbon::parse($app->leave_start_date)->format('F j, Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($app->leave_start_date)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($app->leave_end_date)->format('F j, Y') }}
                                                @endif
                                            @elseif($app->created_at)
                                                {{ \Carbon\Carbon::parse($app->created_at)->format('F j, Y') }}
                                            @endif

                                        </td>
                                        <td data-label="DAYS">
                                        <span style="{{ $app->is_leavewopay ? '' : 'color: red;' }}">
                                            {{ $app->is_leavewopay ? $app->working_days : '-' . $app->working_days }}
                                        </span>

                                        </td>
                                        <td data-label="BALANCE"></td>
                                        <td data-label="ACTIONS">
                                            <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                                {{ $app->id }},
                                        '{{ \Carbon\Carbon::parse($app->leave_start_date)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->leave_end_date)->format('Y-m-d') }}',
                                                {{ $app->working_days }},
                                                'old',
                                                 {{ $app->is_leavewopay ? 'true' : 'false' }}
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
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Content for New Records -->
        <div id="new-tab" class="tab-content">
            <!-- Bottom: Add Leave Application and Add Credits Earned -->
            <div class="bottom-section">
                <!-- Add Credits Earned -->
                <form method="POST" action="{{ route('teaching.credits.add') }}">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <div class="emp-form">
                        <div class="date-row">
                            <div class="date-col">
                                <label>Earned Date Start:</label>
                                <input type="date" name="earned_date_start" required>
                            </div>  
                            <div class="date-col" id="end-date-col-old">
                                <label>Earned Date End:</label>
                                <input type="date" name="earned_date_end" required>
                            </div>
                        </div>
                        <label>Event:</label>
                        <input type="text" name="event" required>
                        <label>Special Order:</label>
                        <input type="text" name="special_order" id="special_order_new">
                        <label>Number of days:</label>
                        <input type="number" name="credits_to_add" step="0.01" min="0.01" max="50" required>
                        <label>Reference:</label>
                        <input type="text" name="reference" id="reference_new">
                        <button type="submit">Add Credits Earned</button>
                    </div>
                </form>
                <!-- Add Leave Application -->
                <form method="POST" action="{{ route('teaching.leave.submit') }}" id="leave-form-new" class="leave-form">
                    @csrf
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input type="hidden" name="edit_id" id="edit_id_new" value="">
                    <input type="hidden" name="_method" id="form_method_new" value="POST">
                    <div class="emp-form" id="leave-form-container-new">
                    <div class="date-row">
                        <div class="date-col">
                            <label>Leave Start Date:</label>
                            <input type="date" name="leave_start_date" id="leave_start_date_new" required>
                            <span class="halfday-controls">
                                <button type="button" class="toggle-button" id="start-am-btn-new" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="start-pm-btn-new" data-value="PM">PM</button>
                            </span>
                        </div>
                        <div class="date-col" id="end-date-col-new">
                            <label>Leave End Date:</label>
                            <input type="date" name="leave_end_date" id="leave_end_date_new" required>
                            <span class="halfday-controls">
                                <button type="button" class="toggle-button" id="end-am-btn-new" data-value="AM">AM</button>
                                <button type="button" class="toggle-button" id="end-pm-btn-new" data-value="PM">PM</button>
                            </span>
                        </div>
                    </div>
                    <label>Working Days:</label>
                    <input type="number" name="working_days" id="working_days_new" >
                        <button type="submit" id="submit-btn-new">Add Leave Application</button>
                        <button type="button" id="cancel-edit-btn-new" onclick="cancelEdit('new')" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                    </div>
                        <label>
                        <input type="checkbox" name="is_leavewopay" id="is_leavewopay_new" value="1"> Leave Without Pay
                        </label>
                </form>
            </div>

            <!-- Leave Records Tables - Side by Side -->
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <!-- Left Table: Credits Earned (New) -->
                <div style="flex: 1;">
                    <h3>Credits Earned (After October 1
                        , 2024)</h3>
                    <table class="leave-table">
                        <thead>
                            <tr>
                                <th>EARNED DATE</th>
                                <th>EVENT</th>
                                <th>SPECIAL ORDER</th>
                                <th>CREDITS TO ADD</th>
                                <th>REFERENCE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($teachingEarnedCredits && $teachingEarnedCredits->count())
                                @foreach($teachingEarnedCredits->filter(function($credit) {
                                    return \Carbon\Carbon::parse($credit->earned_date_start)->gte(\Carbon\Carbon::parse('2024-10-01'));
                                })->sortBy('earned_date_start') as $credit)
                                    <tr>
                                        <td data-label="EARNED DATE">
                                            @if($credit->earned_date_start && $credit->earned_date_end)
                                                @if(\Carbon\Carbon::parse($credit->earned_date_start)->isSameDay(\Carbon\Carbon::parse($credit->earned_date_end)))
                                                    {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('F j, Y') }}
                                                @else
                                                    {{ \Carbon\Carbon::parse($credit->earned_date_start)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($credit->earned_date_end)->format('F j, Y') }}
                                                @endif
                                            @elseif($credit->created_at)
                                                {{ \Carbon\Carbon::parse($credit->created_at)->format('F j, Y') }}
                                            @endif
                                        </td>
                                        <td data-label="EVENT">{{ $credit->event }}</td>
                                        <td data-label="SPECIAL ORDER">{{ $credit->special_order ?? '' }}</td>
                                        <td data-label="CREDITS TO ADD">
                                            <span style="color: green;">+{{ $credit->days }}</span>
                                        </td>
                                        <td data-label="REFERENCE">{{ $credit->reference ?? '' }}</td>
                                        <td data-label="ACTIONS">
                                            <button type="button" class="delete-btn" onclick="deleteRecord({{ $credit->id }}, 'credit')">
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
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Right Table: Leave Usage (New) -->
                <div style="flex: 1;">
                    <h3>Leave Usage (After October 1
                        , 2024)</h3>
                    <table class="leave-table">
                        <thead>
                            <tr>
                                <th>DATE</th>
                                <th>DAYS</th>
                                <th>BALANCE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="DATE">INITIAL BALANCE</td>
                                <td data-label="DAYS"></td>
                                <td data-label="BALANCE">{{ $customer->leave_credits }}</td>
                                <td data-label="ACTIONS"></td>
                            </tr>
                            
                            
@if($teachingLeaveApplications && $teachingLeaveApplications->count())
    @foreach($teachingLeaveApplications->filter(function($app) {
        return \Carbon\Carbon::parse($app->leave_start_date)->gte(\Carbon\Carbon::parse('2024-10-01'));
    }) as $app)
        <tr>
            <td data-label="DATE LEAVE INCURRED">
                @if($app->leave_start_date && $app->leave_end_date)
                    @if(\Carbon\Carbon::parse($app->leave_start_date)->isSameDay(\Carbon\Carbon::parse($app->leave_end_date)))
                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('F j, Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($app->leave_start_date)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($app->leave_end_date)->format('F j, Y') }}
                    @endif
                @elseif($app->created_at)
                    {{ \Carbon\Carbon::parse($app->created_at)->format('F j, Y') }}
                @endif</td>
                                        <td data-label="DAYS">
                                    <span style="{{ $app->is_leavewopay ? '' : 'color: red;' }}">
                                        {{ $app->is_leavewopay ? $app->working_days : '-' . $app->working_days }}
                                    </span>
                                    </td>
                                        <td data-label="BALANCE"></td>
                                        <td data-label="ACTIONS">
                                            <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                                {{ $app->id }},
                                        '{{ \Carbon\Carbon::parse($app->leave_start_date)->format('Y-m-d') }}',
                                        '{{ \Carbon\Carbon::parse($app->leave_end_date)->format('Y-m-d') }}',
                                                {{ $app->working_days }},
                                                'new',
                                                {{ $app->is_leavewopay ? 'true' : 'false' }}
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
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- External Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Pass Laravel routes to JavaScript -->
<script>
// Make Laravel routes available to JavaScript
window.autocompleteRoute = '{{ route("teaching.autocomplete") }}';
window.leaveUpdateRoute = '{{ route("teaching.leave.update") }}';
window.deleteRoute = '{{ route("teaching.leave.delete") }}';
window.csrfToken = '{{ csrf_token() }}';

// Global variables for half-day selections
let startHalfDayOld = null;
let endHalfDayOld = null;
let startHalfDayNew = null;
let endHalfDayNew = null;

// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Initialize date calculation for both tabs
function initializeDateCalculation() {
    // Initialize for old tab
    initializeDateCalculationForTab('old');
    // Initialize for new tab
    initializeDateCalculationForTab('new');
}

function initializeDateCalculationForTab(tab) {
    const startDateInput = document.getElementById(`leave_start_date_${tab}`);
    const endDateInput = document.getElementById(`leave_end_date_${tab}`);
    const workingDaysInput = document.getElementById(`working_days_${tab}`);
    const endDateCol = document.getElementById(`end-date-col-${tab}`);

    // Initialize toggle buttons for this tab
    initializeToggleButtonsForTab(tab);

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            calculateWorkingDaysForTab(tab);
        });

        endDateInput.addEventListener('change', function () {
            calculateWorkingDaysForTab(tab);
        });
    }
}

function initializeToggleButtonsForTab(tab) {
    // Start date toggle buttons
    const startAmBtn = document.getElementById(`start-am-btn-${tab}`);
    const startPmBtn = document.getElementById(`start-pm-btn-${tab}`);
    const endAmBtn = document.getElementById(`end-am-btn-${tab}`);
    const endPmBtn = document.getElementById(`end-pm-btn-${tab}`);

    if (startAmBtn) {
        startAmBtn.addEventListener('click', function() {
            toggleHalfDay('start', 'AM', tab);
        });
    }
    
    if (startPmBtn) {
        startPmBtn.addEventListener('click', function() {
            toggleHalfDay('start', 'PM', tab);
        });
    }

    // End date toggle buttons
    if (endAmBtn) {
        endAmBtn.addEventListener('click', function() {
            toggleHalfDay('end', 'AM', tab);
        });
    }
    
    if (endPmBtn) {
        endPmBtn.addEventListener('click', function() {
            toggleHalfDay('end', 'PM', tab);
        });
    }
}

function toggleHalfDay(dateType, period, tab) {
    const currentSelection = getCurrentHalfDaySelection(dateType, tab);
    
    if (currentSelection === period) {
        setHalfDaySelection(dateType, tab, null); // Deselect if clicking the same button
    } else {
        setHalfDaySelection(dateType, tab, period); // Select the clicked button
    }
    
    updateToggleButtonState(dateType, tab);
    calculateWorkingDaysForTab(tab);
}

function getCurrentHalfDaySelection(dateType, tab) {
    if (tab === 'old') {
        return dateType === 'start' ? startHalfDayOld : endHalfDayOld;
    } else {
        return dateType === 'start' ? startHalfDayNew : endHalfDayNew;
    }
}

function setHalfDaySelection(dateType, tab, value) {
    if (tab === 'old') {
        if (dateType === 'start') {
            startHalfDayOld = value;
        } else {
            endHalfDayOld = value;
        }
    } else {
        if (dateType === 'start') {
            startHalfDayNew = value;
        } else {
            endHalfDayNew = value;
        }
    }
}

function updateToggleButtonState(dateType, tab) {
    const prefix = dateType === 'start' ? 'start' : 'end';
    const currentSelection = getCurrentHalfDaySelection(dateType, tab);
    
    const amBtn = document.getElementById(`${prefix}-am-btn-${tab}`);
    const pmBtn = document.getElementById(`${prefix}-pm-btn-${tab}`);
    
    if (amBtn && pmBtn) {
        // Reset both buttons
        amBtn.classList.remove('active');
        pmBtn.classList.remove('active');
        
        // Activate the selected button
        if (currentSelection === 'AM') {
            amBtn.classList.add('active');
        } else if (currentSelection === 'PM') {
            pmBtn.classList.add('active');
        }
    }
}

function calculateWorkingDaysForTab(tab) {
    const startDateInput = document.getElementById(`leave_start_date_${tab}`);
    const endDateInput = document.getElementById(`leave_end_date_${tab}`);
    const workingDaysInput = document.getElementById(`working_days_${tab}`);
    
    if (!startDateInput || !endDateInput || !workingDaysInput) return;
    
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    if (!startDate || !endDate) {
        workingDaysInput.value = '';
        return;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    if (start > end) {
        workingDaysInput.value = '';
        return;
    }
    
    // Get half day selections
    const startHalfDay = getCurrentHalfDaySelection('start', tab);
    const endHalfDay = getCurrentHalfDaySelection('end', tab);
    
    // If same date and both half days selected, set to 0.5
    if (startDate === endDate && startHalfDay && endHalfDay) {
        workingDaysInput.value = 0.5;
        return;
    }
    
    // If same date and one half day selected, set to 0.5
    if (startDate === endDate && (startHalfDay || endHalfDay)) {
        workingDaysInput.value = 0.5;
        return;
    }
    
    // Calculate working days (excluding weekends)
    let workingDays = 0;
    const currentDate = new Date(start);
    
    while (currentDate <= end) {
        // Check if it's a weekday (Monday = 1, Sunday = 0)
        const dayOfWeek = currentDate.getDay();
        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday or Saturday
            workingDays++;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }

    if (startHalfDay === 'PM' && workingDays > 0) {
        workingDays -= 0.5;
    }
    if (startHalfDay === 'AM' && workingDays > 0) {

        workingDays -= 0.5;
    }
    if (endHalfDay === 'AM' && workingDays > 0) {

        workingDays -= 0.5;
    }
    if (endHalfDay === 'PM' && workingDays > 0) {

        workingDays -= 0.5;
    }
    // Ensure minimum is 0
    workingDays = Math.max(0, workingDays);
    
    workingDaysInput.value = workingDays;
}

// Modal functions using jQuery
$(document).ready(function() {
    // Initialize date calculation
    initializeDateCalculation();
    
    $('#showAddEmpModal').on('click', function() {
        $('#addEmpModal').show();
    });

    $('#closeAddEmpModal').on('click', function() {
        $('#addEmpModal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).is('#addEmpModal')) {
            $('#addEmpModal').hide();
        }
    });
    
    // Setup customer search
    setupCustomerSearch();
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('addEmpModal');
    if (event.target === modal && modal) {
        modal.style.display = 'none';
    }
});

function editLeaveApplication(id, leaveStartDate, leaveEndDate, workingDays, tab = 'old', isLeaveWOPay = false) {
    document.getElementById('is_leavewopay_' + tab).checked = !!isLeaveWOPay;

    document.getElementById('edit_id_' + tab).value = id;
    document.getElementById('form_method_' + tab).value = 'PUT';
    document.getElementById('leave_start_date_' + tab).value = leaveStartDate || '';
    document.getElementById('leave_end_date_' + tab).value = leaveEndDate || '';
    document.getElementById('working_days_' + tab).value = workingDays || '';

    
    document.getElementById('submit-btn-' + tab).textContent = 'Update Leave Application';
    document.getElementById('cancel-edit-btn-' + tab).style.display = 'inline-block';
    
    document.getElementById('leave-form-' + tab).action = window.leaveUpdateRoute;
}

// Updated cancel edit function with tab parameter
function cancelEdit(tab) {
    document.getElementById('edit_id_' + tab).value = '';
    document.getElementById('form_method_' + tab).value = 'POST';
    document.getElementById('leave-form-' + tab).reset();
    document.getElementById('submit-btn-' + tab).textContent = 'Add Leave Application';
    document.getElementById('cancel-edit-btn-' + tab).style.display = 'none';
    
    // Reset half day selections
    setHalfDaySelection('start', tab, null);
    setHalfDaySelection('end', tab, null);
    updateToggleButtonState('start', tab);
    updateToggleButtonState('end', tab);
    
    // Reset form action to submit route
    document.getElementById('leave-form-' + tab).action = '{{ route("teaching.leave.submit") }}';
}

// Delete record function
function deleteRecord(id, type) {
    if (confirm('Are you sure you want to delete this record?')) {
        fetch(window.deleteRoute, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                customer_id: {{ $customer->id ?? 0 }},
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete record'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the record');
        });
    }
}

// Auto-complete functionality using jQuery
function setupCustomerSearch() {
    $('#customer-search').on('input', function() {
        console.log('Input event fired');
        let query = $(this).val();
        
        if (query.length < 2) {
            $('#suggestions').hide();
            return;
        }
        
        $.ajax({
            url: window.autocompleteRoute,
            method: 'GET',
            data: { query: query },
            dataType: 'text',
            success: function(response) {
                console.log('Raw response:', response);
                
                try {
                    let data = JSON.parse(response);
                    console.log('Parsed data:', data);
                    
                    let suggestions = '';
                    if (data && data.length > 0) {
                        data.forEach(function(item) {
                            suggestions += '<div class="suggestion-item" data-id="' + item.id + '" data-name="' + item.label + '">' + item.label + '</div>';
                        });
                        $('#suggestions').html(suggestions).show();
                    } else {
                        $('#suggestions').hide();
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response was:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response Text:', xhr.responseText);
                console.error('Status Code:', xhr.status);
            }
        });
    });

    // Handle suggestion click
    $(document).on('click', '.suggestion-item', function() {
        let customerId = $(this).data('id');
        let customerName = $(this).data('name');
        
        $('#customer-search').val(customerName);
        $('#suggestions').hide();
        
        // Store the selected customer ID for form submission
        $('#customer-search').data('selected-id', customerId);
        
        // Redirect directly using the customer ID as query parameter
        window.location.href = '/leave/teaching?customer_id=' + customerId;
    });

    // Handle Enter key press
    $('#customer-search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            
            // Check if a customer was selected from suggestions
            let selectedId = $(this).data('selected-id');
            if (selectedId) {
                window.location.href = '/leave/teaching?customer_id=' + selectedId;
                return;
            }
            
            // If no selection, try to find by name
            let customerName = $(this).val().trim();
            if (customerName.length > 0) {
                $.ajax({
                    url: '/find-customer',
                    method: 'POST',
                    data: {
                        name: customerName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error finding customer:', error);
                        // Handle error - maybe show a message to user
                        alert('Customer not found');
                    }
                });
            }
        }
    });

    // Clear selection when input changes
    $('#customer-search').on('input', function() {
        $(this).removeData('selected-id');
    });

    // Hide suggestions when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#customer-search, #suggestions').length) {
            $('#suggestions').hide();
        }
    });
}
</script>

</body>
</html>