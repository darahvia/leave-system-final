<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeachingLeaveController;
use App\Http\Controllers\CtoController; // Ensure CtoController is imported

// Entry point selection screen
Route::get('/', function () {
    return view('leave.select');
})->name('leave.select');

// Non-teaching (customer) routes
Route::prefix('leave/customer')->group(function () {
    Route::get('/', [LeaveController::class, 'index'])->name('leave.customer.index');
    Route::post('/add-customer', [LeaveController::class, 'addCustomer'])->name('customer.add');
    Route::any('/find-customer', [LeaveController::class, 'findCustomer'])->name('customer.find');
    Route::post('/submit-leave', [LeaveController::class, 'submitLeave'])->name('leave.submit');
    Route::put('/update-leave', [LeaveController::class, 'updateLeave'])->name('leave.update');

    Route::delete('/delete-leave', [LeaveController::class, 'deleteLeave'])->name('leave.delete');
    Route::post('/add-credits', [LeaveController::class, 'addCreditsEarned'])->name('leave.credits');
    Route::post('/add-leave-row', [LeaveController::class, 'addLeaveRow'])->name('leave.row'); // Keep commented if method doesn't exist
    Route::post('/add-otherCredits', [LeaveController::class, 'addOtherCreditsEarned'])->name('leave.otherCredits');

    // Make sure this route is GET method only and comes before any catch-all routes
    Route::get('/customer-autocomplete', [LeaveController::class, 'customerAutocomplete'])->name('customer.autocomplete');
});

// Teaching routes — new controller - Grouped under 'leave/teaching' prefix
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
    
    // Teaching customer search/autocomplete
    Route::get('/search', [TeachingLeaveController::class, 'searchCustomer'])->name('teaching.search');;
    // add more routes here like /submit, /update etc., pointing to TeachingLeaveController
});

// CTO routes - Retained as they were
Route::prefix('cto')->group(function () {
    Route::get('/', [CtoController::class, 'index'])->name('cto.index');
    // Note: The addEmployee route here points to CtoController, but your LeaveController also has one.
    // If these are meant to be separate, keep them. If it's a shared function, consider a dedicated EmployeeController.
    Route::post('/add-customer', [CtoController::class, 'addCustomer'])->name('cto.customer.add');
    Route::post('/store-activity', [CtoController::class, 'storeActivity'])->name('cto.store-activity');
    Route::post('/store-usage', [CtoController::class, 'storeUsage'])->name('cto.store-usage');
    Route::get('/{ctoApplication}/edit', [CtoController::class, 'edit'])->name('cto.edit');
    // For PUT requests, Laravel typically uses POST with a hidden _method field set to PUT.
    Route::put('/{ctoApplication}', [CtoController::class, 'update'])->name('cto.update');
    // For DELETE requests, Laravel typically uses POST with a hidden _method field set to DELETE.
    Route::delete('/{ctoApplication}', [CtoController::class, 'destroy'])->name('cto.destroy');
    Route::post('/calculate-days', [CtoController::class, 'calculateDays'])->name('cto.calculate-days');
});

// Route::get('/', [LeaveController::class, 'index'])->name('leave.index');
// Route::post('/add-employee', [LeaveController::class, 'addEmployee'])->name('employee.add');
// Route::any('/find-employee', [LeaveController::class, 'findEmployee'])->name('employee.find');
// Route::post('/submit-leave', [LeaveController::class, 'submitLeave'])->name('leave.submit');
// Route::put('/update-leave', [LeaveController::class, 'updateLeave'])->name('leave.update');

// Route::delete('/delete-leave', [LeaveController::class, 'deleteLeave'])->name('leave.delete');
// Route::post('/add-credits', [LeaveController::class, 'addCreditsEarned'])->name('leave.credits');
// Route::post('/add-leave-row', [LeaveController::class, 'addLeaveRow'])->name('leave.row');
// Route::post('/add-otherCredits', [LeaveController::class, 'addOtherCreditsEarned'])->name('leave.otherCredits');


// // Make sure this route is GET method only and comes before any catch-all routes
// Route::get('/employee-autocomplete', [LeaveController::class, 'employeeAutocomplete'])->name('employee.autocomplete');
