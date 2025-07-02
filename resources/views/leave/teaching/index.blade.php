<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave - Teaching</title>
    <link rel="stylesheet" href="{{ asset('css/leave.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <form method="POST" action="{{ route('teaching.find') }}" class="search-form" autocomplete="off">
                @csrf
                <div class="search-box">
                    <button type="submit" class="search-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <input type="text" name="name" id="customer-search" autocomplete="off" required placeholder="Find Teaching Customer...">
                    <div id="suggestions"></div>
                </div>
            </form>
        </div>
    </div>


    <!-- Customer Details Table -->
    @if($customer)
        <div class="emp-details-table">
            <table class="customer-info-table">
                <tr>
                    <td class="label">SURNAME</td>
                    <td class="value">{{ strtoupper($customer->surname) }}</td>
                    <td class="label">SEX</td>
                    <td class="value">{{ strtoupper($customer->sex) }}</td>
                    <td class="label">POSITION</td>
                    <td class="value">{{  strtoupper($customer->position->position) ?? '' }}</td>
                    <td class="label">customer NUMBER</td>
                    <td class="value">{{ $customer->customer_number ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">GIVEN NAME</td>
                    <td class="value">{{ strtoupper($customer->given_name)?? ''  }}</td>
                    <td class="label">CIVIL STATUS</td>
                    <td class="value">{{ strtoupper($customer->civil_status ?? '') }}</td>
                    <td class="label">NAME OF SCHOOL</td>
                    <td class="value">{{ strtoupper($customer->office->office) ?? ''  }}</td>
                    <td class="label">BASIC SALARY</td>
                    <td class="value">{{ number_format($customer->salary, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">MIDDLE NAME</td>
                    <td class="value">{{ strtoupper($customer->middle_name)?? ''  }}</td>
                    <td class="label">DATE OF BIRTH</td>
                    <td class="value">{{ $customer->date_of_birth ? \Carbon\Carbon::parse($customer->date_of_birth)->format('F j, Y') : '' }}</td>
                    <td class="label">PERMANENCY</td>
                    <td class="value">{{ strtoupper($customer->permanency ?? '') }}</td>s
                    <td class="label">LEAVE CREDITS BALANCE</td>
                    <td class="value">{{ $customer->leave_credits ?? 0 }}</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Bottom: Add Leave Application and Add Credits Earned -->
    @if($customer)
        <div class="bottom-section">
            
            <!-- Add Credits Earned -->
            <form method="POST" action="{{ route('teaching.credits.add') }}">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="emp-form">
                    <label>Earned Date:</label>
                    <input type="text" name="earned_date" required>
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
            <form method="POST" action="{{ route('teaching.leave.submit') }}" id="leave-form" class="leave-form">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <input type="hidden" name="edit_id" id="edit_id" value="">
                <input type="hidden" name="_method" id="form_method" value="POST">
                <div class="emp-form" id="leave-form-container">
                    <label>Date:</label>
                    <input type="date" name="leave_incurred_date" id="leave_incurred_date" required>
                    <label>Days:</label>
                    <input type="number" name="leave_incurred_days" id="leave_incurred_days" min="1" max="365" required>
                    <button type="submit" id="submit-btn">Add Leave Application</button>
                    <button type="button" id="cancel-edit-btn" onclick="cancelEdit()" style="display: none; margin-left: 10px; background-color: #6c757d;">Cancel</button>
                </div>
            </form>

        </div>
    @endif

<!-- Leave Records Tables - Side by Side -->
@if($customer)
    <div style="display: flex; gap: 20px; align-items: flex-start;">
        
        <!-- Left Table: Credits Earned -->
        <div style="flex: 1;">
            <h3>Credits Earned</h3>
            <table class="leave-table">
                <thead>
                    <tr>
                        <th>EARNED DATE</th>
                        <th>SPECIAL ORDER</th>
                        <th>CREDITS TO ADD</th>
                        <th>REFERENCE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @if($teachingEarnedCredits && $teachingEarnedCredits->count())
                        @foreach($teachingEarnedCredits->sortBy('earned_date') as $credit)
                            <tr>
                                <td data-label="EARNED DATE">{{ $credit->earned_date }}</td>
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

        <!-- Right Table: Leave Usage -->
        <div style="flex: 1;">
            <h3>Leave Usage</h3>
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
                    <!-- INITIAL BALANCE -->
                    <tr>
                        <td data-label="DATE">INITIAL BALANCE</td>
                        <td data-label="DAYS"></td>
                        <td data-label="BALANCE">{{ $customer->leave_credits }}</td>
                        <td data-label="ACTIONS"></td>
                    </tr>
                    
                    @if($teachingLeaveApplications && $teachingLeaveApplications->count())
                        @php
                            $runningBalance = $customer->leave_credits;
                            // First, add all earned credits to initial balance
                            if($teachingEarnedCredits) {
                                foreach($teachingEarnedCredits as $creditApp) {
                                    $runningBalance += $creditApp->days;
                                }
                            }
                        @endphp
                        @foreach($teachingLeaveApplications->sortBy('leave_incurred_date') as $app)
                            @php
                                $runningBalance -= (int)$app->leave_incurred_days; // Subtract leave days
                            @endphp
                            <tr>
                                <td data-label="DATE">{{ $app->leave_incurred_date ? \Carbon\Carbon::parse($app->leave_incurred_date)->format('F j, Y') : '' }}</td>
                                <td data-label="DAYS">
                                    <span style="color: red;">-{{ $app->leave_incurred_days }}</span>
                                </td>
                                <td data-label="BALANCE"></td>
                                <td data-label="ACTIONS">
                                    <button type="button" class="edit-btn" onclick="editLeaveApplication(
                                        {{ $app->id }},
                                        '{{ $app->leave_incurred_date }}',
                                        {{ $app->leave_incurred_days }}
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

// Modal functions using jQuery
$(document).ready(function() {
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
});
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addEmpModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // FIXED: Edit leave application function with correct parameters
        function editLeaveApplication(id, leaveIncurredDate, leaveIncurredDays) {
            document.getElementById('edit_id').value = id;
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('leave_incurred_date').value = leaveIncurredDate || '';
            document.getElementById('leave_incurred_days').value = leaveIncurredDays || '';
            
            document.getElementById('submit-btn').textContent = 'Update Leave Application';
            document.getElementById('cancel-edit-btn').style.display = 'inline-block';
            
            // Change form action to update route with the ID
            const baseUrl = window.leaveUpdateRoute.replace(':id', id);
            document.getElementById('leave-form').action = baseUrl;
        }

        // Cancel edit function
        function cancelEdit() {
            document.getElementById('edit_id').value = '';
            document.getElementById('form_method').value = 'POST';
            document.getElementById('leave-form').reset();
            document.getElementById('submit-btn').textContent = 'Add Leave Application';
            document.getElementById('cancel-edit-btn').style.display = 'none';
            
            // Reset form action to submit route
            document.getElementById('leave-form').action = '{{ route("teaching.leave.submit") }}';
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
                            suggestions += '<div class="suggestion-item" data-id="' + item.id + '">' + item.label + '</div>';
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

    $(document).on('click', '.suggestion-item', function() {
        $('#customer-search').val($(this).text());
        $('#suggestions').hide();
    });

    $(document).click(function(e) {
        if (!$(e.target).closest('#customer-search, #suggestions').length) {
            $('#suggestions').hide();
        }
    });
}

// Initialize when document is ready
$(document).ready(function() {
    setupCustomerSearch();
});
    </script>

</body>
</html>