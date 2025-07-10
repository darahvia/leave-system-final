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
                    
                    $query = $query->orderBy('leave_start_date', 'desc')
                                   ->orderBy('created_at', 'desc');
                    
                    $teachingLeaveApplications = $query->get();
                    
                } catch (\Exception $e) {
                    $teachingLeaveApplications = TeachingLeaveApplications::where('customer_id', $customer->id)->get();
                }

                try {
                    $teachingEarnedCredits = TeachingEarnedCredits::where('customer_id', $customer->id)
                        ->orderBy('earned_date_start', 'desc')
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
            'leave_start_date' => 'required|date',
            'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
            'working_days' => 'required|numeric|min:0.5|max:365',
            'is_leavewopay' => 'nullable|boolean',
        ]);

        try {
            $isLeaveWithoutPay = $request->has('is_leavewopay') && $request->is_leavewopay == 1;

            $customer = Customer::findOrFail($request->customer_id);
            $leaveDays = $request->working_days;
            $leaveStartDate = Carbon::parse($request->leave_start_date);
            $cutoffDate = Carbon::create(2024, 10, 1); // October 1

            // Determine available leave credits
            if (!$isLeaveWithoutPay) {
                $totalAvailableCredits = $customer->leave_credits_old + $customer->leave_credits_new;

                if ($totalAvailableCredits < $leaveDays) {
                    return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])
                        ->with('error', '❌ Insufficient leave credits. Available: ' . $totalAvailableCredits . ' days');
                }

                // Deduct from appropriate buckets
                if ($leaveStartDate->lt($cutoffDate)) {
                    // Old leave - deduct only from leave_credits_old
                    if ($customer->leave_credits_old < $leaveDays) {
                        return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])
                            ->with('error', '❌ Insufficient OLD leave credits. Available: ' . $customer->leave_credits_old . ' days');
                    }
                    $customer->leave_credits_old -= $leaveDays;

                } else {
                    // New leave - deduct from new first, then old if needed
                    if ($customer->leave_credits_new >= $leaveDays) {
                        $customer->leave_credits_new -= $leaveDays;
                    } else {
                        $remaining = $leaveDays - $customer->leave_credits_new;
                        $customer->leave_credits_new = 0;

                        if ($customer->leave_credits_old < $remaining) {
                            return redirect()->route('leave.teaching.index', ['customer_id' => $customer->id])
                                ->with('error', '❌ Not enough leave credits. Needed: ' . $leaveDays . ' (short by ' . ($remaining - $customer->leave_credits_old) . ' days)');
                        }

                        $customer->leave_credits_old -= $remaining;
                    }
                }

                $customer->save();
            }
            // Create leave application
            TeachingLeaveApplications::create([
                'customer_id' => $customer->id,
                'leave_start_date' => $request->leave_start_date,
                'leave_end_date' => $request->leave_end_date,
                'leave_incurred_date' => $request->leave_start_date,
                'working_days' => $leaveDays,
                'remarks' => $request->remarks ?? '',
                'is_leavewopay' => $isLeaveWithoutPay,
            ]);

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
            'edit_id' => 'required|integer',
            'customer_id' => 'required|integer|exists:customers,id',
            'leave_start_date' => 'required|date',
            'leave_end_date' => 'required|date|after_or_equal:leave_start_date',
            'working_days' => 'required|numeric|min:0.5|max:365',
            'remarks' => 'nullable|string|max:255',
            'is_leavewopay' => 'nullable|boolean',
        ]);

        // Find the leave application to update
        $customer = Customer::findOrFail($request->customer_id);
        $leaveApplication = TeachingLeaveApplications::findOrFail($request->edit_id);
        
        // Verify that this leave application belongs to the specified customer
        if ($leaveApplication->customer_id != $request->customer_id) {
            return back()->with('error', '❌ Unauthorized access to leave application.');
        }

        if ($leaveApplication->is_leavewopay) {
            // Just update the dates and working_days
            $leaveApplication->update([
                'leave_start_date' => $request->leave_start_date,
                'leave_end_date' => $request->leave_end_date,
                'leave_incurred_date' => $request->leave_start_date,
                'working_days' => $request->working_days,
                'remarks' => $request->remarks ?? '',
            ]);

            return back()->with('success', '✅ Leave without pay updated successfully.');
        }

        $cutoffDate = Carbon::create(2024, 10, 1); // October 1 this year

        // Step 1: Restore previous leave days
        $oldLeaveDate = Carbon::parse($leaveApplication->leave_start_date);
        $oldDays = $leaveApplication->working_days;

        if ($oldLeaveDate->lt($cutoffDate)) {
            $customer->leave_credits_old += $oldDays;
        } else {
            // Return to new first
            $customer->leave_credits_new += $oldDays;
        }

        // Step 2: Deduct new leave days
        $newLeaveDate = Carbon::parse($request->leave_start_date);
        $newDays = $request->working_days;

        if ($newLeaveDate->lt($cutoffDate)) {
            // Must deduct fully from OLD
            if ($customer->leave_credits_old < $newDays) {
                return back()->with('error', '❌ Insufficient OLD leave credits for update. Available: ' . $customer->leave_credits_old . ' days');
            }
            $customer->leave_credits_old -= $newDays;

        } else {
            // Deduct from NEW first, then OLD if needed
            if ($customer->leave_credits_new >= $newDays) {
                $customer->leave_credits_new -= $newDays;
            } else {
                $remaining = $newDays - $customer->leave_credits_new;
                $customer->leave_credits_new = 0;

                if ($customer->leave_credits_old < $remaining) {
                    return back()->with('error', '❌ Not enough leave credits for update. Needed: ' . $newDays . ', short by ' . ($remaining - $customer->leave_credits_old) . ' days');
                }

                $customer->leave_credits_old -= $remaining;
            }
        }

        $customer->save();

        // Step 3: Update the leave application
        $leaveApplication->update([
            'leave_start_date' => $request->leave_start_date,
            'leave_end_date' => $request->leave_end_date,
            'leave_incurred_date' => $request->leave_start_date,
            'working_days' => $newDays,
            'remarks' => $request->remarks ?? '',
        ]);

        return back()->with('success', '✅ Leave application updated successfully.');

    } catch (ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        return back()->with('error', '❌ An error occurred while updating: ' . $e->getMessage());
    }
}


    public function deleteLeave(Request $request)
    {
            $request->validate([
                'id' => 'required|integer',
                'customer_id' => 'required|integer|exists:customers,id',
                'type' => 'required|string|in:leave,credit'
            ]);


        try {


            $customer = Customer::findOrFail($request->customer_id);

            $cutoffDate = Carbon::create(2024, 10, 1); // October 1st of current year

            if ($request->type === 'leave') {
                $leaveApplication = TeachingLeaveApplications::findOrFail($request->id);

                if ($leaveApplication->customer_id != $request->customer_id) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized access to leave application.'
                    ], 403);
                }
                if (!$leaveApplication->is_leavewopay) {
                    $leaveDate = Carbon::parse($leaveApplication->leave_start_date);

                    // Reverse leave credits in the appropriate column
                    if ($leaveDate->lt($cutoffDate)) {
                        $customer->leave_credits_old += $leaveApplication->working_days;
                    } else {
                        $customer->leave_credits_new += $leaveApplication->working_days;
                    }

                    $customer->save();
                }
                $leaveApplication->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Leave application deleted successfully.'
                ]);

            } elseif ($request->type === 'credit') {
                $earnedCredit = TeachingEarnedCredits::findOrFail($request->id);

                if ($earnedCredit->customer_id != $request->customer_id) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Unauthorized access to earned credit.'
                    ], 403);
                }

                $earnedDate = Carbon::parse($earnedCredit->earned_date_start);

                // Reverse earned credits in the appropriate column
                if ($earnedDate->lt($cutoffDate)) {
                    $customer->leave_credits_old -= $earnedCredit->days;
                } else {
                    $customer->leave_credits_new -= $earnedCredit->days;
                }

                $customer->save();
                $earnedCredit->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Earned credit deleted successfully.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting: ' . $e->getMessage()
            ], 500);
        }
    }


    public function addCreditsEarned(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credits_to_add' => 'required|numeric|min:0.01|max:50',
            'earned_date_start' => 'required|date',
            'earned_date_end' => 'required|date',
            'event' => 'required|string|max:255',
            'special_order' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255'
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);

            // Determine the target credit column based on the earned date
            $earnedDate = Carbon::parse($request->earned_date_start);
            $cutoffDate = Carbon::create(2024, 10, 1); // October 1st of current year

            if ($earnedDate->lt($cutoffDate)) {
                $customer->leave_credits_old += $request->credits_to_add;
            } else {
                $customer->leave_credits_new += $request->credits_to_add;
            }

            $customer->save();

            // Create a record in the teaching_earned_credits table
            TeachingEarnedCredits::create([
                'customer_id' => $customer->id,
                'earned_date_start' => $request->earned_date_start,
                'earned_date_end' => $request->earned_date_end,
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