<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add New Employee</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/form.css') }}">
</head>
<body>
<div class="container">
    <h1>Select Leave Type</h1>

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

    <div class="leave-links">
        <a href="{{ url('leave/customer') }}">Non-Teaching Leave</a>
        <a href="{{ url('leave/teaching') }}">Teaching Leave</a>
    </div>

    <div class="card">
        <h2>Add New Employee</h2>

        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <h3>Contact Information</h3>
            <div class="grid">
                <div class="form-group">
                    <label for="email">Email </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" />
                </div>

                <div class="form-group">
                    <label for="telepon">Phone</label>
                    <input type="text" id="telepon" name="telepon" value="{{ old('telepon') }}" />
                </div>

                <div class="form-group">
                    <label for="employee_id">Employee ID</label>
                    <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id') }}" />
                </div>

                <div class="form-group full-width">
                    <label for="alamat">Address</label>
                    <textarea id="alamat" name="alamat">{{ old('alamat') }}</textarea>
                </div>
            </div>

            <div class="divider"></div>

            <h3>Personal Information</h3>
            <div class="grid">
                <div class="form-group">
                    <label for="surname">Surname </label>
                    <input type="text" id="surname" name="surname" value="{{ old('surname') }}" />
                </div>

                <div class="form-group">
                    <label for="given_name">Given Name </label>
                    <input type="text" id="given_name" name="given_name" value="{{ old('given_name') }}" />
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" />
                </div>

                <div class="form-group">
                    <label for="sex">Sex </label>
                    <select id="sex" name="sex">
                        <option value="">Select Sex</option>
                        <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="civil_status">Civil Status</label>
                    <select id="civil_status" name="civil_status">
                        <option value="">Select Status</option>
                        <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>
            </div>

            <div class="divider"></div>

            <h3>Employment Details</h3>
            <div class="grid">
                <div class="form-group">
                    <label for="office_id">Office </label>
                    <select id="office_id" name="office_id">
                        <option value="">Select Office</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                {{ $office->office }} — {{ $office->district }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="position_id">Position </label>
                    <select id="position_id" name="position_id">
                        <option value="">Select Position</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>
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
                    <label for="origappnt_date">Original Appointment</label>
                    <input type="date" id="origappnt_date" name="origappnt_date" value="{{ old('origappnt_date') }}" />
                </div>

                <div class="form-group">
                    <label for="lastprmtn_date">Last Promotion</label>
                    <input type="date" id="lastprmtn_date" name="lastprmtn_date" value="{{ old('lastprmtn_date') }}" />
                </div>

                <div class="form-group">
                    <label for="permanency_date">Permanency Date</label>
                    <input type="date" id="permanency_date" name="permanency_date" value="{{ old('permanency_date') }}" />
                </div>
            </div>

            <div class="divider"></div>

            <h3>Adding Credits Manual</h3>
            <div class="grid">
                <div class="form-group">
                    <h4>For Non-Teaching Employees</h4>
                    <label for="balance_forwarded_vl">Vacation Leave Forwarded Balance:</label>
                    <input type="number" id="balance_forwarded_vl" step="0.001" name="balance_forwarded_vl" value="{{ old('balance_forwarded_vl', 0) }}" />
                    <label for="balance_forwarded_sl">Sick Leave Forwarded Balance:</label>
                    <input type="number" id="balance_forwarded_sl" step="0.001" name="balance_forwarded_sl" value="{{ old('balance_forwarded_sl', 0) }}" />
                </div>
                <div class="form-group">
                    <h4>For Teaching Employees</h4>
                    <label for="leave_credits">Initial Leave Credits:</label>
                    <input type="number" id="leave_credits" step="0.001" name="leave_credits" value="{{ old('leave_credits', 0) }}" />
                </div>
            </div>

            <div class="form-action">
                <button type="submit">Create Employee</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
