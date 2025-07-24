@auth
    <span style="font-family: 'Consolas', monospace; font-size: 14px; color: green;">
        Logged in as {{ auth()->user()->name }}
    </span>
@endauth

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add New Employee</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
</head>
<body>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="header-wrapper">
        <div class="header-container">
            <img src="/images/deped-logo.png" alt="DepEd Logo" class="header-logo">
            <div class="header-text">
                <div class="header-title">
                    <span class="dep">Dep</span><span class="ed">Ed</span> Cadiz City
                </div>
                <div class="header-subtitle">{{ $pageTitle ?? 'Leave Credit System' }}</div>
            </div>
            <img src="/images/deped-cadiz-logo.png" alt="Division Logo" class="header-logo">
        </div>
    </div>
    


    <div class="homepage">
    <div class="left-content">
        @php
        $nonTeachingCustomers = $customers->where('position_id', '>=', 0)->where('position_id', '<=', 39);
        $teachingCustomers = $customers->where('position_id', '>=', 40)->where('position_id', '<=', 52);
        
        $nonTeachingByOffice = $nonTeachingCustomers->groupBy('office_id');
        $teachingByOffice = $teachingCustomers->groupBy('office_id');
        @endphp
        
        <h3>Non-Teaching Employees</h3>
        <div class="accordion-container">
            @foreach($nonTeachingByOffice as $officeId => $officeCustomers)
                @php
                    $office = $offices->find($officeId);
                @endphp
                <div class="accordion-item">
                    <div class="accordion-header" onclick="toggleAccordion('nonteaching-{{ $officeId }}')">
                        <p class="office-name">{{ $office ? $office->office : 'No Office Assigned' }}</p>
                        <span class="accordion-icon" id="icon-nonteaching-{{ $officeId }}">▼</span>
                    </div>
                    
                    <div class="accordion-content" id="content-nonteaching-{{ $officeId }}" style="display: none;">
                        <ul class="employee-list">
                            @foreach($officeCustomers as $customer)
                                <li>
                                    <a href="{{ route('leave.customer.index', ['customer_id' => $customer->id]) }}">
                                        {{ $customer->surname }}, {{ $customer->given_name }} {{ $customer->middle_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            @endforeach
        </div>
        
        <h3>Teaching Employees</h3>
        <div class="accordion-container">
            @foreach($teachingByOffice as $officeId => $officeCustomers)
                @php
                    $office = $offices->find($officeId);
                @endphp
                
                <div class="accordion-item">
                    <div class="accordion-header" onclick="toggleAccordion('teaching-{{ $officeId }}')">
                        <p class="office-name">{{ $office ? $office->office : 'No Office Assigned' }}</p>
                        <span class="accordion-icon" id="icon-teaching-{{ $officeId }}">▼</span>
                    </div>
                    
                    <div class="accordion-content" id="content-teaching-{{ $officeId }}" style="display: none;">
                        <ul class="employee-list">
                            @foreach($officeCustomers as $customer)
                                <li>
                                    <a href="{{ route('leave.teaching.index', ['customer_id' => $customer->id]) }}">
                                        {{ $customer->surname }}, {{ $customer->given_name }} {{ $customer->middle_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
        <div class="right-content">
            <h1>Select Leave Type</h1>
            <div class="leave-links">
                <a href="{{ url('leave/customer') }}">Non-Teaching Leave</a>
                <a href="{{ url('leave/teaching') }}">Teaching Leave</a>
            </div>
            <h2>{{ $editingCustomer ? 'Edit Employee' : 'Add New Employee' }}</h2>
            
            <form method="POST" action="{{$editingCustomer ? route('customers.update', $editingCustomer->id) : route('customers.store') }}">
                @csrf
                @if(isset($editingCustomer))
                    @method('PUT')
                @endif

                <h3>Personal Information</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="surname">Surname <span style="color: red;">*</span></label>
                        <input type="text" id="surname" name="surname" value="{{ old('surname', $editingCustomer->surname ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="given_name">Given Name <span style="color: red;">*</span></label>
                        <input type="text" id="given_name" name="given_name" value="{{ old('given_name', $editingCustomer->given_name ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name<span style="color: red;">*</span></label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $editingCustomer->middle_name ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="sex">Sex </label>
                        <select id="sex" name="sex">
                            <option value="">Select Sex</option>
                            <option value="Male" {{ old('sex', $editingCustomer->sex ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('sex', $editingCustomer->sex ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="civil_status">Civil Status</label>
                        <select id="civil_status" name="civil_status">
                            <option value="">Select Status</option>
                            <option value="Single" {{ old('civil_status', $editingCustomer->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status', $editingCustomer->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Divorced" {{ old('civil_status', $editingCustomer->civil_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="Widowed" {{ old('civil_status', $editingCustomer->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                    </div>
                </div>
                <div class="divider"></div>
                <h3>Contact Information</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="email">Email </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $editingCustomer->email ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="telepon">Phone</label>
                        <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $editingCustomer->telepon ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $editingCustomer->employee_id ?? '') }}" />
                    </div>

                    <div class="form-group full-width">
                        <label for="alamat">Address</label>
                        <textarea id="alamat" name="alamat">{{ old('alamat', $editingCustomer->alamat ?? '') }}</textarea>
                    </div>
                </div>

                <div class="divider"></div>

                <h3>Employment Details</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="office_id">Division/Unit/School<span style="color: red;">*</span></label>
                        <select id="office_id" name="office_id">
                            <option value="">Select Office</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ old('office_id', $editingCustomer->office_id ?? '') == $office->id ? 'selected' : '' }}>
                                    {{ $office->office }} — {{ $office->district }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="position_id">Position <span style="color: red;">*</span></label>
                        <select id="position_id" name="position_id">
                            <option value="">Select Position</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" {{ old('position_id', $editingCustomer->position_id ?? '') == $position->id ? 'selected' : '' }}>
                                    {{ $position->position }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="divider"></div>

                <h3>Important Dates</h3>
                <div class="grid">
                    <div class="form-group">
                        <label for="origappnt_date">Original Appointment<span style="color: red;">*</span></label>
                        <input type="date" id="origappnt_date" name="origappnt_date" value="{{ old('origappnt_date', $editingCustomer->origappnt_date ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="lastprmtn_date">Last Promotion</label>
                        <input type="date" id="lastprmtn_date" name="lastprmtn_date" value="{{ old('lastprmtn_date', $editingCustomer->lastprmtn_date ?? '') }}" />
                    </div>

                    <div class="form-group">
                        <label for="status">Status<span style="color: red;">*</span></label>
                        <select id="status" name="status">
                            <option value="">Select Status</option>
                            <option value="Casual" {{ old('status', $editingCustomer->status ?? '') == 'Casual' ? 'selected' : '' }}>Casual</option>
                            <option value="Permanent" {{ old('status', $editingCustomer->status ?? '') == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                            <option value="Retired" {{ old('status', $editingCustomer->status ?? '') == 'Retired' ? 'selected' : '' }}>Retired</option>
                            <option value="Resigned" {{ old('status', $editingCustomer->status ?? '') == 'Resigned' ? 'selected' : '' }}>Resigned</option>
                                                        <option value="Transferred" {{ old('status', $editingCustomer->status ?? '') == 'Transferred' ? 'selected' : '' }}>Transferred</option>

                        </select>
                    </div>
                </div>

                <div class="divider"></div>

                <h3>Adding Credits Manual</h3>
                <div class="grid">
                    <div class="form-group">
                        <h4>For Non-Teaching Employees</h4>
                        <label for="balance_forwarded_vl">Vacation Leave Forwarded Balance:</label>
                        <input type="number" id="balance_forwarded_vl" step="0.001" name="balance_forwarded_vl" value="{{ old('balance_forwarded_vl', $editingCustomer->balance_forwarded_vl ?? 0) }}" />
                        <label for="balance_forwarded_sl">Sick Leave Forwarded Balance:</label>
                        <input type="number" id="balance_forwarded_sl" step="0.001" name="balance_forwarded_sl" value="{{ old('balance_forwarded_sl', $editingCustomer->balance_forwarded_sl ?? 0) }}" />
                        <button
                            type="button"
                            class="collapsible-btn"
                            onclick="toggleCreditsSection()"
                            style="
                                margin: 10px 0px;
                                padding: 6px 12px;
                                font-size: 14px;
                                background-color: #f3f4f6;
                                border: 1px solid #ccc;
                                border-radius: 4px;
                                cursor: pointer;
                                font-weight: 500;
                            "
                        >
                            <span id="credits-toggle-icon">▼</span> Show/Hide Other Leave Credits
                        </button>

                        <div id="other-credits-section" style="display: none; margin-bottom: 20px; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <label for="spl">Special Leave (SPL):</label>
                                <input type="number" id="spl" step="0.001" name="spl" value="{{ old('spl', $editingCustomer->spl ?? 0) }}" />

                                <label for="fl">Force Leave (FL):</label>
                                <input type="number" id="fl" step="0.001" name="fl" value="{{ old('fl', $editingCustomer->fl ?? 0) }}" />

                                <label for="solo_parent">Solo Parent:</label>
                                <input type="number" id="solo_parent" step="0.001" name="solo_parent" value="{{ old('solo_parent', $editingCustomer->solo_parent ?? 0) }}" />

                                <label for="ml">Maternity Leave (ML):</label>
                                <input type="number" id="ml" step="0.001" name="ml" value="{{ old('ml', $editingCustomer->ml ?? 0) }}" />

                                <label for="pl">Paternity Leave (PL):</label>
                                <input type="number" id="pl" step="0.001" name="pl" value="{{ old('pl', $editingCustomer->pl ?? 0) }}" />

                                <label for="ra9710">RA 9710 (Magna Carta of Women):</label>
                                <input type="number" id="ra9710" step="0.001" name="ra9710" value="{{ old('ra9710', $editingCustomer->ra9710 ?? 0) }}" />

                                <label for="rl">Rehabilitation Leave (RL):</label>
                                <input type="number" id="rl" step="0.001" name="rl" value="{{ old('rl', $editingCustomer->rl ?? 0) }}" />

                                <label for="sel">Special Emergency Leave (SEL):</label>
                                <input type="number" id="sel" step="0.001" name="sel" value="{{ old('sel', $editingCustomer->sel ?? 0) }}" />

                                <label for="study_leave">Study Leave:</label>
                                <input type="number" id="study_leave" step="0.001" name="study_leave" value="{{ old('study_leave', $editingCustomer->study_leave ?? 0) }}" />

                                <label for="vawc">VAWC Leave:</label>
                                <input type="number" id="vawc" step="0.001" name="vawc" value="{{ old('vawc', $editingCustomer->vawc ?? 0) }}" />

                                <label for="adopt">Adoption Leave:</label>
                                <input type="number" id="adopt" step="0.001" name="adopt" value="{{ old('adopt', $editingCustomer->adopt ?? 0) }}" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <h4>For Teaching Employees</h4>
                        <label for="leave_credits_old">Initial Vacation Service Credits (old):</label>
                        <input type="number" id="leave_credits_old" step="0.001" name="leave_credits_old" value="{{ old('leave_credits_old', $editingCustomer->leave_credits_old ?? 0) }}" />
                        <label for="leave_credits_new">Initial Vacation Service Credits (new):</label>
                        <input type="number" id="leave_credits_new" step="0.001" name="leave_credits_new" value="{{ old('leave_credits_new', $editingCustomer->leave_credits_new ?? 0) }}" />
                    </div>
                    <h3>Remarks</h3>
                    <div class="grid">
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <input type="text" id="remarks" name="remarks" value="{{ old('remarks', $editingCustomer->remarks ?? '') }}" />
                        </div>
                    </div>
                </div>

                <div class="form-action">
                    <button type="submit">{{ isset($editingCustomer) ? 'Update Employee' : 'Create Employee' }}</button>
                    @if(isset($editingCustomer))
                            <button type="button" id="cancelBtn" onclick="window.location.href='{{ route('leave.select', $editingCustomer->id) }}'" class="cancel-button">Cancel</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</body>
<script>
    function toggleCreditsSection() {
        var section = document.getElementById('other-credits-section');
        var icon = document.getElementById('credits-toggle-icon');
        if (section.style.display === 'none') {
            section.style.display = 'block';
            icon.textContent = '▲';
        } else {
            section.style.display = 'none';
            icon.textContent = '▼';
        }
    }
    function toggleAccordion(accordionId) {
        const content = document.getElementById(`content-${accordionId}`);
        const icon = document.getElementById(`icon-${accordionId}`);
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            content.classList.add('active');
            icon.classList.add('rotated');
        } else {
            content.style.display = 'none';
            content.classList.remove('active');
            icon.classList.remove('rotated');
        }
    }
    </script>
</html>