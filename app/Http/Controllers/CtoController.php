<?php
// app/Http/Controllers/CtoController.php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Customer;
use App\LeaveApplication; // Ensure this is needed/correct, otherwise remove.
use App\CtoApplication;
use App\CtoCreditUsage;
use App\Services\CtoService;
use App\Services\LeaveService; // Ensure this is needed/correct, otherwise remove.
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PDF; // Add at top if not yet there



class CtoController extends Controller
{
    protected $ctoService;


    public function __construct(CtoService $ctoService)
    {
        $this->ctoService = $ctoService;
    }


    /**
     * Show CTO management page for a customer.
     * Recalculates balances on load to ensure data consistency.
     */
    public function index(Request $request)
    {
        $customer = null; // Initialize customer as null
        // Ensure LeaveService::getLeaveTypes() is used correctly if it exists and is needed here.
        // Otherwise, remove $leaveTypes or its usage if it's not actually for the CTO view.
        $leaveTypes = LeaveService::getLeaveTypes();


        if ($request->has('customer_id')) {
            $customer = Customer::find($request->customer_id);


            if ($customer) {
                // Always recalculate balances on page load to ensure data integrity
                $this->ctoService->recalculateBalancesForCustomer($customer);


                // Reload the ctoApplications relationship on the Customer model
                $customer->load('ctoApplications');
            } else {
                // If customer_id is provided but customer not found, redirect with error
                return redirect()->route('leave.select')->with('error', 'Customer not found.');
            }
        }


        $ctoService = $this->ctoService;


        return view('cto.index', compact('customer', 'ctoService', 'leaveTypes'));
    }


    /**
     * Store CTO activity (credits earned).
     * Relies on CtoService for processing and recalculation.
     */
    public function addCtoCreditsEarned(Request $request)
    {
        Log::info('CTO Activity Request Data:', $request->all());


        $isSingleDayActivity = $request->has('is_single_day_activity') && ($request->input('is_single_day_activity') == '1' || $request->input('is_single_day_activity') == 'on');


        $validationRules = [
            'customer_id' => 'required|exists:customers,id',
            'special_order' => 'nullable|string|max:255',
            'activity' => 'nullable|string',
            'hours_earned' => 'required|numeric|min:0', // Matches blade form input name
            'date_of_activity_start' => 'required|date',
            'date_of_activity_end' => $isSingleDayActivity ? 'nullable|date' : 'required|date|after_or_equal:date_of_activity_start',
            'is_cto_earned' => 'required|boolean', // This field is likely not actually sent from the form but set as hidden input.
        ];


        try {
            $request->validate($validationRules);


            Log::info('Validation passed for CTO Activity.');


            $customer = Customer::findOrFail($request->customer_id);


            $endDateForStorage = $isSingleDayActivity
                ? $request->date_of_activity_start
                : $request->date_of_activity_end;


            $activityData = [
                'special_order' => $request->special_order,
                'date_of_activity_start' => $request->date_of_activity_start,
                'date_of_activity_end' => $endDateForStorage,
                'activity' => $request->activity,
                'credits_earned' => (float)$request->hours_earned,
                'is_activity' => true,
                'date_filed' => $request->date_of_activity_start, // Add date_filed for sorting/consistency
            ];


            $ctoApplication = $this->ctoService->processCtoActivity($customer, $activityData);


            Log::info('CTO Application Created and Balances Recalculated:', $ctoApplication->toArray());


            return redirect()->route('cto.index', ['customer_id' => $customer->id])
                ->with('success', 'CTO activity added successfully!');


        } catch (ValidationException $e) {
            Log::error('CTO Activity Validation Error:', ['errors' => $e->errors(), 'request' => $request->all()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed: Please check your input.');
        } catch (\Exception $e) {
            Log::error('CTO Activity General Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding CTO activity: ' . $e->getMessage());
        }
    }


    /**
     * Store CTO usage (credits deducted).
     * This method triggers the FIFO deduction and expiration logic in CtoService.
     */
    public function submitCto(Request $request)
    {
        Log::info('CTO Usage Request Data:', $request->all());


        $isSingleDayAbsence = $request->has('is_single_day_absence') && ($request->input('is_single_day_absence') == '1' || $request->input('is_single_day_absence') == 'on');


        $validationRules = [
            'customer_id' => 'required|exists:customers,id',
            'date_filed' => 'required|date',
            'inclusive_date_start' => 'required|date',
            'inclusive_date_end' => $isSingleDayAbsence ? 'nullable|date' : 'required|date|after_or_equal:inclusive_date_start',
            'hours_applied' => 'required|numeric|min:0.01',
            'cto_details' => 'nullable|string|max:255',
            'is_cto_application' => 'required|boolean', // This field is likely not actually sent from the form but set as hidden input.
        ];


        try {
            $request->validate($validationRules);


            Log::info('Validation passed for CTO Usage.');


            $customer = Customer::findOrFail($request->customer_id);


            $startDate = Carbon::parse($request->inclusive_date_start);
            $endDateForStorage = $isSingleDayAbsence
                ? $startDate->toDateString()
                : $request->inclusive_date_end;


            $hoursToDeduct = (float)$request->hours_applied;

            $sumOfDeductions = array_sum($request->input('so_deductions', []));
            if ($sumOfDeductions < $hoursToDeduct) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Total SO deductions do not match requested credits.');
            }

           


            $eligibleBalance = $this->ctoService->getEligibleCtoBalance($customer, $startDate);
            if ($eligibleBalance < $hoursToDeduct) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient eligible CTO balance. Available: ' . number_format($eligibleBalance, 2) . ' hours, Required: ' . number_format($hoursToDeduct, 2) . ' hours');
            }


            $usageData = [
                'date_of_absence_start' => $startDate->toDateString(),
                'date_of_absence_end' => $endDateForStorage,
                'no_of_days' => $hoursToDeduct,
                'cto_details' => $request->cto_details,
                'date_filed' => $request->date_filed,
                'is_activity' => false,
            ];


            $ctoApplication = $this->ctoService->processCtoUsage($customer, $usageData);


            $deductions = $request->input('so_deductions', []); // <-- Add this line


            foreach ($deductions as $activityId => $deductedHours) {
            $activity = CtoApplication::where('is_activity', true)
                ->where('customer_id', $customer->id)
                ->find($activityId);


            if (!$activity || $deductedHours <= 0) continue;


            $remaining = $activity->remaining_credits;


            if ($remaining < $deductedHours) {
                Log::warning("SO ID {$activity->id} has only {$remaining} credits, but {$deductedHours} requested. Adjusting to available.");
                $deductedHours = $remaining; // Soft fallback, only deduct what is left
            }



            \App\CtoCreditUsage::updateOrCreate(
                [
                    'cto_activity_id' => $activity->id,
                    'cto_absence_id' => $ctoApplication->id,
                ],
                [
                    'days_used' => $deductedHours,
                ]
            );

        }


            Log::info('CTO Usage Created and Balances Recalculated:', $ctoApplication->toArray());


            return redirect()->route('cto.index', ['customer_id' => $customer->id])
                ->with('success', 'CTO usage recorded successfully!');


        } catch (ValidationException $e) {
            Log::error('CTO Usage Validation Error:', ['errors' => $e->errors(), 'request' => $request->all()]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation failed: Please check your input.');
        } catch (\Exception $e) {
            Log::error('CTO Usage General Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error recording CTO usage: ' . $e->getMessage());
        }
    }


    /**
     * Update CTO record (activity or usage).
     * This method expects 'edit_id' in the request body, not route model binding.
     * Relies on CtoService for processing and recalculation.
     */
    public function updateCtoRecord(Request $request)
    {
        Log::info('CTO Update Request Data:', $request->all());


        $request->validate([
            'edit_id' => 'required|integer',
            'customer_id' => 'required|exists:customers,id',
        ]);


        $customer = Customer::findOrFail($request->customer_id);


        // Retrieve the CtoApplication record to identify its type and update
        $ctoRecord = CtoApplication::findOrFail($request->edit_id);


        // Verify record type to apply correct validation/processing
        $isCtoEarned = $ctoRecord->is_activity;
        $isCtoApplication = !$ctoRecord->is_activity;


        if ($ctoRecord->customer_id != $request->customer_id) {
            return back()->with('error', 'Unauthorized access to CTO record.');
        }


        if ($isCtoEarned) { // This is an earned credits update
            $isSingleDayActivity = $request->has('is_single_day_activity') && ($request->input('is_single_day_activity') == '1' || $request->input('is_single_day_activity') == 'on');
            $validationRules = [
                'special_order' => 'nullable|string|max:255',
                'activity' => 'nullable|string',
                'hours_earned' => 'required|numeric|min:0',
                'date_of_activity_start' => 'required|date',
                'date_of_activity_end' => $isSingleDayActivity ? 'nullable|date' : 'required|date|after_or_equal:date_of_activity_start',
            ];
            $request->validate($validationRules);


            $endDateForStorage = $isSingleDayActivity
                ? $request->date_of_activity_start
                : $request->date_of_activity_end;


            $activityData = [
                'special_order' => $request->special_order,
                'date_of_activity_start' => $request->date_of_activity_start,
                'date_of_activity_end' => $endDateForStorage,
                'activity' => $request->activity,
                'credits_earned' => (float)$request->hours_earned,
                'date_filed' => $request->date_of_activity_start,
            ];


            $this->ctoService->processCtoActivity($customer, $activityData, $ctoRecord);


        } elseif ($isCtoApplication) { // This is a usage update
            $isSingleDayAbsence = $request->has('is_single_day_absence') && ($request->input('is_single_day_absence') == '1' || $request->input('is_single_day_absence') == 'on');
            $validationRules = [
                'date_filed' => 'required|date',
                'inclusive_date_start' => 'required|date',
                'inclusive_date_end' => $isSingleDayAbsence ? 'nullable|date' : 'required|date|after_or_equal:inclusive_date_start',
                'hours_applied' => 'required|numeric|min:0.01',
                'cto_details' => 'nullable|string|max:255',
            ];
            $request->validate($validationRules);


            $startDate = Carbon::parse($request->inclusive_date_start);
            $endDateForStorage = $isSingleDayAbsence
                ? $startDate->toDateString()
                : $request->inclusive_date_end;


            $hoursToDeduct = (float)$request->hours_applied;


            $deductions = $request->input('so_deductions', []);
            $totalDeducted = array_sum($deductions);


            if ($totalDeducted != $hoursToDeduct) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Mismatch: You requested ' . number_format($hoursToDeduct, 2) . ' hrs, but selected ' . number_format($totalDeducted, 2) . ' hrs from SOs.');
            }


            $usageData = [
                'date_of_absence_start' => $startDate->toDateString(),
                'date_of_absence_end' => $endDateForStorage,
                'no_of_days' => $hoursToDeduct,
                'cto_details' => $request->cto_details,
                'date_filed' => $request->date_filed,
            ];


            $this->ctoService->processCtoUsage($customer, $usageData, $ctoRecord);
        }


        return redirect()->back()->with('success', 'CTO record updated successfully!');
    }


    /**
     * Delete CTO record.
     * This method is called via AJAX. It returns JSON response, not redirect.
     */
    public function deleteCtoRecord(Request $request, $id) // Getting $id from URL parameter
    {
        Log::info('CTO Delete Request for ID from URL:', ['id' => $id]);


        try {
            $ctoApplication = CtoApplication::findOrFail($id);
            $customerId = $ctoApplication->customer_id; // Get customer ID before deletion
            $this->ctoService->deleteCtoRecord($ctoApplication);


            // Return JSON success for AJAX call with message and customer_id
            return response()->json([
                'success' => true,
                'message' => 'CTO record deleted successfully!',
                'customer_id' => $customerId // Pass customer_id back to JS
            ]);


        } catch (\Exception $e) {
            Log::error('CTO Delete Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Always return JSON errors for AJAX calls
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Get CTO data for editing (AJAX).
     */
    public function edit(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $ctoApplication = CtoApplication::findOrFail($request->id);
        return response()->json($ctoApplication);
    }


    /**
     * Calculate days between dates (for AJAX).
     */
    public function calculateDays(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);


        $days = $this->ctoService->calculateWorkingDays($request->start_date, $request->end_date);


        return response()->json(['days' => $days]);
    }


    public function exportPdf(Request $request)
    {
        $customer = Customer::with('ctoApplications')->findOrFail($request->customer_id);

        $ctoApplications = $customer->ctoApplications->sortBy('effective_date');

        // Filter only SOs (earned entries) and group them
        $specialOrders = $ctoApplications->where('is_activity', true);

        $pdf = Pdf::loadView('cto.pdf_report', compact('customer', 'ctoApplications', 'specialOrders'));

        return $pdf->download('cto_report_' . now()->format('Ymd_His') . '.pdf');
    }



}
 // for pull request


