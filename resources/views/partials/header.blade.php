

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="{{ asset('js/leave-form.js') }}"></script>
<script>
            window.autocompleteRoute = '{{ route("customer.autocomplete") }}';
                window.csrfToken = '{{ csrf_token() }}';


</script>
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

    <div class="search-bar-section">
        <a href="{{ route('leave.select') }}" class="home-button">Home</a>
        <form method="POST" action="{{ route('customer.find') }}" class="search-form" autocomplete="off">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request()->routeIs('cto.index') ? 'cto' : 'leave' }}">
            <div class="search-box">
                <button type="submit" class="search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <input type="text" name="name" id="customer-search" autocomplete="off" required placeholder="Find Employee...">
                <div id="suggestions"></div>
            </div>
        </form>
    </div>
</div>

@if($customer)
    @php
        $latestApp = $customer->leaveApplications->last();

$latestCtoApp = $customer->ctoApplications
    ->sortByDesc('date_of_activity_start')
    ->sortByDesc('date_of_absence_start')
    ->first();



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
                <td class="label">CTO BALANCE</td>
<td class="value">
    {{ $latestCtoApp ? number_format($latestCtoApp->balance, 1) : number_format($ctoService->getEligibleCtoBalance($customer), 1) }}
</td>
            </tr>
            <tr>
                <td class="label">MIDDLE NAME</td>
                <td class="value">{{ strtoupper($customer->middle_name) }}</td>
                <td class="label">ORIGINAL APPOINTMENT</td>
                <td class="value">{{ $customer->origappnt_date ? \Carbon\Carbon::parse($customer->origappnt_date)->format('F j, Y') : '' }}</td>
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
                    @if (!in_array($type, ['vl', 'sl']))
                        <li>{{ \App\Services\LeaveService::getLeaveTypes()[strtoupper($type)] ?? ucfirst(str_replace('_', ' ', $type)) }}: <strong>{{ $value }}</strong></li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
@endif