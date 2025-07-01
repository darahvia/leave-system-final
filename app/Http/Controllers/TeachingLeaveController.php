<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Teaching; 
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
            $customer = Teaching::find($request->customer_id);
            if ($customer) {
                // Debug: Log what we're about to do
                \Log::info('About to query teaching_leave_applications for customer_id: ' . $customer->id);
                
                try {
                    // Get leave applications from teaching_leave_applications table
                    $query = TeachingLeaveApplications::where('customer_id', $customer->id);
                    
                    // Debug: Log the base query
                    \Log::info('Base query SQL: ' . $query->toSql());
                    
                    // Add ordering - let's be very explicit
                    $query = $query->orderBy('leave_incurred_date', 'desc')
                                   ->orderBy('created_at', 'desc');
                    
                    // Debug: Log the final query
                    \Log::info('Final query SQL: ' . $query->toSql());
                    \Log::info('Query bindings: ' . json_encode($query->getBindings()));
                    
                    $teachingLeaveApplications = $query->get();
                    
                } catch (\Exception $e) {
                    \Log::error('Error in leave applications query: ' . $e->getMessage());
                    // Try without ordering to see if that works
                    $teachingLeaveApplications = TeachingLeaveApplications::where('customer_id', $customer->id)->get();
                }

                try {
                    // Get earned credits from teaching_earned_credits table
                    $teachingEarnedCredits = TeachingEarnedCredits::where('customer_id', $customer->id)
                        ->orderBy('earned_date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                } catch (\Exception $e) {
                    \Log::error('Error in earned credits query: ' . $e->getMessage());
                    $teachingEarnedCredits = collect();
                }
            }
        }

        return view('leave.teaching.index', compact('customer', 'teachingLeaveApplications', 'teachingEarnedCredits'));
    }

public function addCustomer(Request $request)
{
    try {
        $request->validate([
            'surname' => 'required|string|max:255',
            'given_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'sex' => 'required|in:Male,Female',
            'civil_status' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'place_of_birth' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'name_of_school' => 'required|string|max:255',
            'permanency' => 'required|in:Permanent,Temporary,Contractual',
            'customer_number' => 'nullable|string|max:50|unique:teaching,customer_number',
            'salary' => 'nullable|numeric|min:0',
            'leave_credits' => 'nullable|numeric|min:0',
        ]);

        $teachingData = $request->all();
        
        if (!isset($teachingData['leave_credits'])) {
            $teachingData['leave_credits'] = 0;
        }

        $teaching = Teaching::create($teachingData);

        $fullName = "{$teaching->surname}, {$teaching->given_name} {$teaching->middle_name}";

        return redirect()->route('teaching.find', ['name' => $fullName])
            ->with('success', '✅ Teaching Customer Added Successfully!');

    } catch (ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        return back()->with('error', '❌ An error occurred while adding customer: ' . $e->getMessage())
                     ->withInput();
    }
}


    public function findCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $customer = Teaching::whereRaw("CONCAT(surname, ', ', given_name, ' ', middle_name) = ?", [$request->name])
            ->first();

        if ($customer) {
            return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id]);
        }

        return redirect()->route('leave.teaching.index')
            ->with('error', '❌ Teaching customer not found.');
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:teaching,id',
            'leave_incurred_date' => 'required|date',
            'leave_incurred_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $teaching = Teaching::findOrFail($request->customer_id);

            // Check if customer has sufficient leave credits
            if ($teaching->leave_credits < $request->leave_incurred_days) {
                return redirect()->route('leave.teaching.index', ['customer_id' => $teaching->id])
                    ->with('error', '❌ Insufficient leave credits. Available: ' . $teaching->leave_credits . ' days');
            }

            // Create leave application
            TeachingLeaveApplications::create([
                'customer_id' => $teaching->id,
                'leave_incurred_date' => $request->leave_incurred_date,
                'leave_incurred_days' => $request->leave_incurred_days,
            ]);

            // Deduct leave credits from customer
            $teaching->leave_credits -= $request->leave_incurred_days;
            $teaching->save();

            return redirect()->route('leave.teaching.index', ['customer_id' => $teaching->id])
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
                'customer_id' => 'required|integer|exists:teaching,id',
                'leave_incurred_date' => 'required|date',
                'leave_incurred_days' => 'required|integer|min:1|max:365',
            ]);

            $customer = Teaching::findOrFail($request->customer_id);
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
                'customer_id' => 'required|integer|exists:teaching,id'
            ]);

            $leaveApplication = TeachingLeaveApplications::findOrFail($request->id);
            $customer = Teaching::findOrFail($request->customer_id);

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
            'customer_id' => 'required|exists:teaching,id',
            'credits_to_add' => 'required|numeric|min:0.01|max:50',
            'earned_date' => 'required|string',
            'special_order' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            $customer = Teaching::findOrFail($request->customer_id);
            
            // Add credits to customer
            $customer->leave_credits += $request->credits_to_add;
            $customer->save();

            // Create a record for the credit addition in teaching_earned_credits table
            TeachingEarnedCredits::create([
                'customer_id' => $customer->id,
                'earned_date' => $request->earned_date,
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
                'customer_id' => 'required|integer|exists:teaching,id'
            ]);

            $earnedCredit = TeachingEarnedCredits::findOrFail($request->id);
            $customer = Teaching::findOrFail($request->customer_id);

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

    public function searchCustomer(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2'
        ]);

        $searchTerm = $request->search;
        
        $customers = Teaching::where(function($query) use ($searchTerm) {
            $query->whereRaw("CONCAT(surname, ', ', given_name, ' ', middle_name) LIKE ?", ["%{$searchTerm}%"])
                  ->orWhere('customer_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('surname', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('given_name', 'LIKE', "%{$searchTerm}%");
        })
        ->limit(10)
        ->get()
        ->map(function($customer) {
            return [
                'id' => $customer->id,
                'name' => "{$customer->surname}, {$customer->given_name} {$customer->middle_name}",
                'customer_number' => $customer->customer_number,
                'position' => $customer->position,
                'school' => $customer->name_of_school
            ];
        });

        if ($request->expectsJson()) {
            return response()->json($customers);
        }

        return view('leave.teaching.search', compact('customers', 'searchTerm'));
    }
}