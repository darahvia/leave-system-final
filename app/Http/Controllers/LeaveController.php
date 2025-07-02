<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer; 
use App\LeaveApplication; 
use App\Services\LeaveService;
use Carbon\Carbon;


class LeaveController extends Controller
{
    protected $leaveService;
    
    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

        public function index(Request $request)
        {
            $customer = null;
            $leaveTypes = LeaveService::getLeaveTypes();
            $message = '';

            if ($request->has('customer_id')) {
                $customer = Customer::with('office')->find($request->customer_id);
            }

            return view('leave.customer.index', [
                'customer' => $customer,
                'leaveTypes' => $leaveTypes,
                'message' => $message

            ]);
        }


    public function addCustomer(Request $request)
    {
        $request->validate([
            'surname' => 'required|string',
            'given_name' => 'required|string',
            'middle_name' => 'required|string',
            'division' => 'required|string',
            'designation' => 'required|string',
            'origappnt_date' => 'required|string',
            'balance_forwarded_vl' => 'nullable|numeric',
            'balance_forwarded_sl' => 'nullable|numeric',
        ]);

        $customerData = $request->all();
        $customerData['vl'] = $customerData['vl'] ?? 0;
        $customerData['sl'] = $customerData['sl'] ?? 0;
        $customerData['spl'] = $customerData['spl'] ?? 3;
        $customerData['fl'] = $customerData['fl'] ?? 0;
        $customerData['solo_parent'] = $customerData['solo_parent'] ?? 7;
        $customerData['ml'] = $customerData['ml'] ?? 105;
        $customerData['pl'] = $customerData['pl'] ?? 7;
        $customerData['ra9710'] = $customerData['ra9710'] ?? 0;
        $customerData['rl'] = $customerData['rl'] ?? 0;
        $customerData['sel'] = $customerData['sel'] ?? 0;
        $customerData['study_leave'] = $customerData['study_leave'] ?? 0;
        $customerData['vawc'] = $customerData['vawc'] ?? 0;
        $customerData['adopt'] = $customerData['adopt'] ?? 0;

        $customer = Customer::create($customerData);

        $fullName = "{$customer->surname}, {$customer->given_name} {$customer->middle_name}";

        return redirect()->route('customer.find', ['name' => $fullName])
            ->with('success', '✅ Customer Added!');
        }

        public function findCustomer(Request $request)
        {
            $customer = Customer::whereRaw("CONCAT(surname, ', ', given_name, ' ', middle_name) = ?", [$request->name])
                ->first();

            if ($customer) { // Keep the $customer variable from main
                        // Preserving the dynamic redirect based on 'redirect_to' input from your branch
                        $redirectTo = $request->input('redirect_to', 'leave');

                        if ($redirectTo === 'cto') {
                            // Update employee->id to customer->id
                            return redirect()->route('cto.index', ['customer_id' => $customer->id]);
                        } else {
                            // Update employee->id to customer->id and use the new customer route
                            return redirect()->route('leave.customer.index', ['customer_id' => $customer->id]);
                        }
                    }

                    // If customer not found, redirect back to the appropriate page
                    // Preserve the dynamic redirect logic but use customer route for 'leave'
                    $redirectTo = $request->input('redirect_to', 'leave');
                    $routeName = $redirectTo === 'cto' ? 'cto.index' : 'leave.customer.index'; // Use leave.customer.index here

                    return redirect()->route($routeName)
                        ->with('error', '❌ Customer not found.'); // Update message for customer
        }


        public function submitLeave(Request $request)
        {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'leave_type' => 'required|string',
                'working_days' => 'required|integer|min:1',
                'date_filed' => 'required|date',
                'inclusive_date_start' => 'required|date',
                'inclusive_date_end' => 'required|date|after_or_equal:inclusive_date_start',
                'is_cancellation' => 'sometimes|boolean',
            ]);

            try {
                $customer = Customer::find($request->customer_id);
                $isCancellation = $request->input('is_cancellation', false);
                
                if ($isCancellation) {
                    // Handle leave cancellation (credit restoration)
                    $leaveApplication = $this->leaveService->processCancellation(
                        $customer,
                        $request->all()
                    );
                    
                    $leaveTypeName = LeaveService::getLeaveTypes()[$request->leave_type] ?? $request->leave_type;
                    
                    return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                        ->with('success', "✅ {$leaveTypeName} cancellation processed! {$request->working_days} credits restored.");
                } else {
                    // Handle regular leave application
                    $leaveApplication = $this->leaveService->processLeaveApplication(
                        $customer,
                        $request->all()
                    );

                    $leaveTypeName = LeaveService::getLeaveTypes()[$request->leave_type] ?? $request->leave_type;
                    
                    return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                        ->with('success', "✅ {$leaveTypeName} application submitted successfully!");
                }

            } catch (\Exception $e) {
                return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                    ->with('error', '❌ ' . $e->getMessage());
            }
        }

        public function updateLeave(Request $request)
        {
            try {
                $request->validate([
                    'edit_id' => 'required|integer',
                    'customer_id' => 'required|integer',
                    'leave_type' => 'required|string',
                    'date_filed' => 'required|date',
                    'inclusive_date_start' => 'required|date',
                    'inclusive_date_end' => 'required|date',
                    'working_days' => 'required|numeric',
                ]);

                // Find the leave application to update
                $customer = Customer::findOrFail($request->customer_id);
                $leaveApplication = LeaveApplication::findOrFail($request->edit_id);
                
                // Verify that this leave application belongs to the specified customer
                if ($leaveApplication->customer_id != $request->customer_id) {
                    return back()->with('error', 'Unauthorized access to leave application.');
                }
                $this->leaveService->processLeaveApplication(
                    $customer,
                    $request->all(),
                    $leaveApplication // <- update mode
                );

                return back()->with('success', 'Leave application updated successfully.');
                
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                return back()->with('error', 'An error occurred while updating the leave application: ' . $e->getMessage());
            }
        }
        public function deleteLeave(Request $request)
        {
            try {
                $request->validate([
                    'id' => 'required|integer',
                    'type' => 'required|in:leave,credit'
                ]);

                $leaveApplication = LeaveApplication::findOrFail($request->id);
                $leaveApplication->delete();

                $this->leaveService->deleteLeaveApplication($leaveApplication);

                $recordType = $request->type === 'credit' ? 'credit entry' : 'leave application';
                
                // Return JSON for AJAX requests
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => ucfirst($recordType) . ' deleted successfully.'
                    ]);
                }

                return back()->with('success', ucfirst($recordType) . ' deleted successfully.');
                
            } catch (\Exception $e) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'An error occurred while deleting the record: ' . $e->getMessage()
                    ], 500);
                }
                
                return back()->with('error', 'An error occurred while deleting the record: ' . $e->getMessage());
            }
        }
        public function addCreditsEarned(Request $request)
        {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'earned_date' => 'required|date',
            ]);

            try {
                $customer = Customer::find($request->customer_id);
                
                $this->leaveService->addCreditsEarned(
                    $customer,
                    $request->earned_date,
                    1.25, // VL credits
                    1.25  // SL credits
                );

                return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                    ->with('success', '✅ Leave credits added successfully!');

            } catch (\Exception $e) {
                return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                    ->with('error', '❌ ' . $e->getMessage());
            }
        }

        public function addOtherCreditsEarned(Request $request)
        {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'leave_type' => 'required|string',
                'credits' => 'required|numeric|min:0',
            ]);

            try {
                $customer = Customer::findOrFail($request->customer_id);
                $leaveType = strtolower($request->leave_type);
                $credits = $request->credits;

                if (!in_array($leaveType, [
                    'spl', 'fl', 'solo_parent', 'ml', 'pl', 'ra9710', 'rl', 'sel', 'study_leave', 'vawc', 'adopt'
                ])) {
                    throw new \Exception('Invalid leave type.');
                }

                $customer->{$leaveType} += $credits;
                $customer->save();

                return redirect()->route('leave.customer.index', ['customer_id' => $customer->id])
                    ->with('success', '✅ Other leave credits added successfully!');
                    
            } catch (\Exception $e) {
                return redirect()->route('leave.customer.index', ['customer_id' => $request->customer_id])
                    ->with('error', '❌ ' . $e->getMessage());
            }
        }

    // unused
    // public function getCustomerLeaveBalances($customerId)
    // {
    //     $customer = Customer::find($customerId);
        
    //     if (!$customer) {
    //         return response()->json(['error' => 'Customer not found'], 404);
    //     }

    //      $balances = [];
    //      $leaveTypes = ['vl', 'sl', 'spl', 'fl', 'solo_parent', 'ml', 'pl', 'ra9710', 'rl', 'sel', 'study_leave', 'adopt'];
        
    //     foreach ($leaveTypes as $type) {
    //         $balances[$type] = $customer->getCurrentLeaveBalance($type);
    //     }

    //      return response()->json($balances);
    // }

    public function customerAutocomplete(Request $request)
    {
        if (ob_get_level()) {
            ob_clean();
        }

        $search = $request->get('query');

        if (empty($search) || strlen($search) < 2) {
            return response()->json([]);
        }

        try {
            $results = Customer::where(function ($query) use ($search) {
                    $query->where('surname', 'LIKE', "%{$search}%")
                        ->orWhere('given_name', 'LIKE', "%{$search}%")
                        ->orWhere('middle_name', 'LIKE', "%{$search}%");
                })
                ->limit(10)
                ->get(['surname', 'given_name', 'middle_name', 'id'])
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'label' => trim("{$customer->surname}, {$customer->given_name} {$customer->middle_name}"),
                    ];
                })
                ->values()
                ->toArray();

            return response()->json($results, 200, [
                'Content-Type' => 'application/json'
            ]);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
}