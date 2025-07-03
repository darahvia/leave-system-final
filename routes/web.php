<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeachingLeaveController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CtoController;

use App\Office;
use App\Position;


Route::get('/', function () {
    $offices = Office::all();
    $positions = Position::all();
    return view('leave.select', compact('offices', 'positions'));
})->name('leave.select');

    Route::any('/find-customer', [LeaveController::class, 'findCustomer'])->name('customer.find');


// Customer management routes
Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

// Update the existing route to pass data to the view

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


    // Make sure this route is GET method only and comes before any catch-all routes
    Route::get('/customer-autocomplete', [LeaveController::class, 'customerAutocomplete'])->name('customer.autocomplete');
    
});

// Teaching routes — new controller
Route::prefix('leave/teaching')->group(function () {
     Route::get('/', [TeachingLeaveController::class, 'index'])->name('leave.teaching.index');
    
    // Add new teaching customer
    Route::post('/add-customer', [TeachingLeaveController::class, 'addCustomer'])->name('teaching.add');
    
    // Find teaching customer
    Route::any('/find-customer', [TeachingLeaveController::class, 'findCustomer'])->name('teaching.find');
    
    // Submit new leave application
    Route::post('/submit-leave', [TeachingLeaveController::class, 'submitLeave'])->name('teaching.leave.submit');
    
    // Update existing leave application
    Route::put('/update-leave', [TeachingLeaveController::class, 'updateLeave'])->name('teaching.leave.update');
    
    // Delete leave application or credit record
    Route::delete('/delete-leave', [TeachingLeaveController::class, 'deleteLeave'])->name('teaching.leave.delete');
    
    // Add credits earned
    Route::post('/add-credits', [TeachingLeaveController::class, 'addCreditsEarned'])->name('teaching.credits.add');
    Route::get('/customer-autocomplete', [TeachingLeaveController::class, 'customerAutocomplete'])->name('teaching.autocomplete');

    
});

// CTO routes - Unified around 'customer' concept
Route::prefix('cto')->group(function () {
    // Main CTO index. This route expects a 'customer_id' query parameter to display a specific customer's CTO.
    Route::get('/', [CtoController::class, 'index'])->name('cto.index');

    // Route for adding earned CTO credits (was storeActivity)
    Route::post('/add-credits', [CtoController::class, 'addCtoCreditsEarned'])->name('cto.credits');

    // Route for submitting a new CTO application (was storeUsage)
    Route::post('/submit', [CtoController::class, 'submitCto'])->name('cto.submit');

    // Route for updating an existing CTO record (activity or usage) by ID in request body
    Route::put('/update', [CtoController::class, 'updateCtoRecord'])->name('cto.update'); // Renamed method

    // Route for deleting CTO record by ID in request body
    Route::delete('/delete', [CtoController::class, 'deleteCtoRecord'])->name('cto.delete'); // Renamed method

    // Routes below should be reviewed and removed/renamed if they are duplicates or unused
    // If you explicitly need route model binding for edit/destroy, these need distinct names
    // Route::get('/{ctoApplication}/edit', [CtoController::class, 'edit'])->name('cto.edit');
    // Route::delete('/{ctoApplication}', [CtoController::class, 'destroy'])->name('cto.destroy');
    Route::post('/calculate-days', [CtoController::class, 'calculateDays'])->name('cto.calculate-days');
});

// Route::get('/', [LeaveController::class, 'index'])->name('leave.index');
// Route::post('/add-customer', [LeaveController::class, 'addCustomer'])->name('customer.add');
// Route::any('/find-customer', [LeaveController::class, 'findCustomer'])->name('customer.find');
// Route::post('/submit-leave', [LeaveController::class, 'submitLeave'])->name('leave.submit');
// Route::put('/update-leave', [LeaveController::class, 'updateLeave'])->name('leave.update');

// Route::delete('/delete-leave', [LeaveController::class, 'deleteLeave'])->name('leave.delete');
// Route::post('/add-credits', [LeaveController::class, 'addCreditsEarned'])->name('leave.credits');
// Route::post('/add-leave-row', [LeaveController::class, 'addLeaveRow'])->name('leave.row');
// Route::post('/add-otherCredits', [LeaveController::class, 'addOtherCreditsEarned'])->name('leave.otherCredits');


// // Make sure this route is GET method only and comes before any catch-all routes
// Route::get('/customer-autocomplete', [LeaveController::class, 'customerAutocomplete'])->name('customer.autocomplete');
