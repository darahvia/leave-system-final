<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer; 
use App\TeachingLeaveApplications; 
use App\TeachingEarnedCredits;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TeachingLeaveController extends Controller
{
    public function index(Request $request)
    {
        $customer = null;
        $teachingLeaveApplications = collect();
        $teachingEarnedCredits = collect();

        if ($request->has('customer_id')) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                
                try {
                    $query = TeachingLeaveApplications::where('customer_id', $customer->id);
                    
                    $query = $query->orderBy('leave_incurred_date', 'desc')
                                   ->orderBy('created_at', 'desc');
                    
                    $teachingLeaveApplications = $query->get();
                    
                } catch (\Exception $e) {
                    $teachingLeaveApplications = TeachingLeaveApplications::where('customer_id', $customer->id)->get();
                }

                try {
                    $teachingEarnedCredits = TeachingEarnedCredits::where('customer_id', $customer->id)
                        ->orderBy('earned_date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                } catch (\Exception $e) {
                    $teachingEarnedCredits = collect();
                }
            }
        }

        return view('leave.teaching.index', compact('customer', 'teachingLeaveApplications', 'teachingEarnedCredits'));
    }




    public function findCustomer(Request $request)
    {
        $customer = Customer::whereRaw("CONCAT(surname, ', ', given_name, ' ', middle_name) = ?", [$request->name])
            ->first();

        if ($customer) {
            return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id]);
        }

        return redirect()->route('leave.teaching.index')
            ->with('error', '❌ Customer not found.');
    }
    

    public function submitLeave(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'leave_incurred_date' => 'required|date',
            'leave_incurred_days' => 'required|numeric|min:1|max:365',
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);

            // Check if customer has sufficient leave credits
            if ($customer->leave_credits < $request->leave_incurred_days) {
                return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])
                    ->with('error', '❌ Insufficient leave credits. Available: ' . $customer->leave_credits . ' days');
            }

            // Create leave application
            TeachingLeaveApplications::create([
                'customer_id' => $customer->id,
                'leave_incurred_date' => $request->leave_incurred_date,
                'leave_incurred_days' => $request->leave_incurred_days,
            ]);

            // Deduct leave credits from customer
            $customer->leave_credits -= $request->leave_incurred_days;
            $customer->save();

            return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])
                ->with('success', '✅ Teaching leave application submitted successfully!');

        } catch (\Exception $e) {
            return redirect()->route('leave.teaching.index', ['customer_id' => $request->customer_id])
                ->with('error', '❌ An error occurred: ' . $e->getMessage());
        }
    }

    public function updateLeave(Request $request)
    {
        try {
            $request->validate([
                'edit_id' => 'required|integer|exists:teaching_leave_applications,id',
                'customer_id' => 'required|integer|exists:customers,id',
                'leave_incurred_date' => 'required|date',
                'leave_incurred_days' => 'required|numeric|min:1|max:365',
            ]);

            $customer = Customer::findOrFail($request->customer_id);
            $leaveApplication = TeachingLeaveApplications::findOrFail($request->edit_id);
            
            // Verify that this leave application belongs to the specified customer
            if ($leaveApplication->customer_id != $request->customer_id) {
                return back()->with('error', '❌ Unauthorized access to leave application.');
            }

            // Calculate the difference in days
            $oldDays = $leaveApplication->leave_incurred_days;
            $newDays = $request->leave_incurred_days;
            $daysDifference = $newDays - $oldDays;

            // Check if customer has sufficient credits for additional days
            if ($daysDifference > 0 && $customer->leave_credits < $daysDifference) {
                return back()->with('error', '❌ Insufficient leave credits for update. Available: ' . $customer->leave_credits . ' days');
            }

            // Update the leave application
            $leaveApplication->update([
                'leave_incurred_date' => $request->leave_incurred_date,
                'leave_incurred_days' => $request->leave_incurred_days,
            ]);

            // Adjust customer's leave credits
            $customer->leave_credits -= $daysDifference;
            $customer->save();

            return back()->with('success', '✅ Leave application updated successfully.');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', '❌ An error occurred while updating: ' . $e->getMessage());
        }
    }

    public function deleteLeave(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:teaching_leave_applications,id',
                'customer_id' => 'required|integer|exists:customers,id'
            ]);

            $leaveApplication = TeachingLeaveApplications::findOrFail($request->id);
            $customer = Customer::findOrFail($request->customer_id);

            // Verify that this leave application belongs to the specified customer
            if ($leaveApplication->customer_id != $request->customer_id) {
                return back()->with('error', '❌ Unauthorized access to leave application.');
            }

            // Restore leave credits to customer
            $customer->leave_credits += $leaveApplication->leave_incurred_days;
            $customer->save();

            // Delete the leave application
            $leaveApplication->delete();
            
            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave application deleted successfully.'
                ]);
            }

            return back()->with('success', '✅ Leave application deleted successfully.');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'An error occurred while deleting: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', '❌ An error occurred while deleting: ' . $e->getMessage());
        }
    }

    public function addCreditsEarned(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credits_to_add' => 'required|numeric|min:0.01|max:50',
            'earned_date' => 'required|string',
            'event' => 'nullable|string|max:255',
            'special_order' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);
            
            // Add credits to customer
            $customer->leave_credits += $request->credits_to_add;
            $customer->save();

            // Create a record for the credit addition in teaching_earned_credits table
            TeachingEarnedCredits::create([
                'customer_id' => $customer->id,
                'earned_date' => $request->earned_date,
                'event' => $request->event,
                'days' => $request->credits_to_add,
                'reference' => $request->reference ?? 'CREDIT_EARNED',
                'special_order' => $request->special_order ?? 'Leave credits earned',
            ]);

            return redirect()->route('leave.teaching.index', ['customer_id' => $request->customer_id])
                ->with('success', '✅ Leave credits added successfully! Added: ' . $request->credits_to_add . ' days');

        } catch (\Exception $e) {
            return redirect()->route('leave.teaching.index', ['customer_id' => $request->customer_id])
                ->with('error', '❌ An error occurred: ' . $e->getMessage());
        }
    }

    public function deleteCredit(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:teaching_earned_credits,id',
                'customer_id' => 'required|integer|exists:customers,id'
            ]);

            $earnedCredit = TeachingEarnedCredits::findOrFail($request->id);
            $customer = Customer::findOrFail($request->customer_id);

            // Verify that this earned credit belongs to the specified customer
            if ($earnedCredit->customer_id != $request->customer_id) {
                return back()->with('error', '❌ Unauthorized access to earned credit.');
            }

            // Deduct credits from customer (reverse the credit addition)
            $customer->leave_credits -= $earnedCredit->days;
            $customer->save();

            // Delete the earned credit record
            $earnedCredit->delete();
            
            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Earned credit deleted successfully.'
                ]);
            }

            return back()->with('success', '✅ Earned credit deleted successfully.');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'An error occurred while deleting: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', '❌ An error occurred while deleting: ' . $e->getMessage());
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
                ->whereNotBetween('office_id', [1, 14])
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