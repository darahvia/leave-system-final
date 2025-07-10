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
            $ctoService = app(\App\Services\CtoService::class);
            return view('leave.customer.index', [
                'customer' => $customer,
                'leaveTypes' => $leaveTypes,
                'message' => $message,
                'ctoService' => $ctoService,
            ]);
        }


public function findCustomer(Request $request)
{
    $customer = Customer::whereRaw("CONCAT(surname, ', ', given_name, CASE WHEN middle_name IS NOT NULL AND middle_name != '' THEN CONCAT(' ', middle_name) ELSE '' END) = ?", [$request->name])
        ->first();

    if ($customer) {
        return response()->json([
            'redirect_url' => route('leave.customer.index', ['customer_id' => $customer->id])
        ]);
    }

    return response()->json([
        'error' => '❌ Customer not found.'
    ], 404);
}


        public function submitLeave(Request $request)
        {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'leave_type' => 'required|string',
                'working_days' => 'required|numeric|min:0',
                'date_filed' => 'required|date',
                'inclusive_date_start' => 'required|date',
                'inclusive_date_end' => 'required|date|after_or_equal:inclusive_date_start',
                'is_cancellation' => 'sometimes|boolean',
                'is_leavewopay' => 'sometimes|boolean',
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
                    'is_leavewopay' => 'sometimes|boolean',
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
                'earned_vl' => 'required|numeric|min:0',
                'earned_sl' => 'required|numeric|min:0',
            ]);

            try {
                $customer = Customer::find($request->customer_id);
                $this->leaveService->addCreditsEarned(
                    $customer,
                    $request->earned_date,
                    $request->earned_vl, // Use input value
                    $request->earned_sl  // Use input value
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
