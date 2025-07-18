<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeachingLeaveController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CtoController;
use App\Customer;
use App\Office;
use App\Position;

// All web-related routes should be wrapped in the 'web' middleware group



Route::get('/', function () {
	return view('auth.login');
});
Auth::routes();

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/select', [CustomerController::class, 'index'])->name('leave.select');

    Route::any('/find-customer', [LeaveController::class, 'findCustomer'])->name('customer.find');


    // Customer management routes
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');  // used para mag pass data
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update'); // used para mag update
    Route::post('/customers/convert', [CustomerController::class, 'convert'])->name('customers.convert');
    Route::post('/customers/remarks', [CustomerController::class, 'updateRemarks'])->name('customers.remarks');

    // Non-teaching (customer) routes
    Route::prefix('leave/customer')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('leave.customer.index');
        Route::post('/add-customer', [LeaveController::class, 'addCustomer'])->name('customer.add');
        Route::post('/submit-leave', [LeaveController::class, 'submitLeave'])->name('leave.submit');
        Route::put('/update-leave', [LeaveController::class, 'updateLeave'])->name('leave.update');
        Route::delete('/delete-leave', [LeaveController::class, 'deleteLeave'])->name('leave.delete');
        Route::post('/add-credits', [LeaveController::class, 'addCreditsEarned'])->name('leave.credits');
        Route::post('/add-leave-row', [LeaveController::class, 'addLeaveRow'])->name('leave.row');
        Route::post('/add-otherCredits', [LeaveController::class, 'addOtherCreditsEarned'])->name('leave.otherCredits');
        Route::get('/customer-autocomplete', [LeaveController::class, 'customerAutocomplete'])->name('customer.autocomplete');
        Route::get('/nonteaching/{id}/export-pdf', [LeaveController::class, 'exportPDF'])->name('nonteaching.export-pdf');    });

    // Teaching routes
    Route::prefix('leave/teaching')->group(function () {
        Route::get('/', [TeachingLeaveController::class, 'index'])->name('leave.teaching.index');
        Route::post('/add-customer', [TeachingLeaveController::class, 'addCustomer'])->name('teaching.add');
        Route::any('/find-customer', [TeachingLeaveController::class, 'findCustomer'])->name('teaching.find');
        Route::post('/submit-leave', [TeachingLeaveController::class, 'submitLeave'])->name('teaching.leave.submit');
        Route::put('/update-leave', [TeachingLeaveController::class, 'updateLeave'])->name('teaching.leave.update');
        Route::delete('/delete-leave', [TeachingLeaveController::class, 'deleteLeave'])->name('teaching.leave.delete');
        Route::post('/add-credits', [TeachingLeaveController::class, 'addCreditsEarned'])->name('teaching.credits.add');
        Route::get('/customer-autocomplete', [TeachingLeaveController::class, 'customerAutocomplete'])->name('teaching.autocomplete');
    });

    // CTO routes - Unified around 'customer' concept
    Route::prefix('cto')->group(function () {
        // Main CTO index. This route expects a 'customer_id' query parameter to display a specific customer's CTO.
        Route::get('/', [CtoController::class, 'index'])->name('cto.index');

        // Route for adding earned CTO credits
        Route::post('/add-credits', [CtoController::class, 'addCtoCreditsEarned'])->name('cto.credits');

        // Route for submitting a new CTO application
        Route::post('/submit', [CtoController::class, 'submitCto'])->name('cto.submit');

        // Route for updating an existing CTO record (activity or usage) by ID in request body
        // Ensure the {id} placeholder is correctly used in your update route
        Route::put('/update/{id}', [CtoController::class, 'updateCtoRecord'])->name('cto.update');
        
        // Route for deleting a CTO record by ID in URL segment (Route Model Binding)
        // Corrected based on your CtoController's deleteCtoRecord(Request $request, $id) signature
        // If you were to use Route Model Binding like deleteCtoRecord(CtoApplication $ctoApplication),
        // the route would be: Route::delete('/{ctoApplication}', [CtoController::class, 'deleteCtoRecord'])->name('cto.delete');
        // But since your controller method takes $id, this is the correct route:
        Route::delete('/delete/{id}', [CtoController::class, 'deleteCtoRecord'])->name('cto.delete');

        // Route for calculating days (AJAX)
        Route::post('/calculate-days', [CtoController::class, 'calculateDays'])->name('cto.calculate-days');
    });


    Route::get('/cto/export-pdf', [CtoController::class, 'exportPdf'])->name('cto.export.pdf');


}); // End of web middleware group //for pull request 
