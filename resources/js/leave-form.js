// Leave Management JavaScript
// This file contains all the JavaScript functionality for the leave management system

// Global variables
let isEditing = false;
let isCancelling = false;
let originalFormAction = '';
let startHalfDay = null; // null means full day, 'AM' or 'PM' means half day
let endHalfDay = null;

// DOM Content Loaded - Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeModal();
    initializeDateCalculation();
    initializeCustomerSearch();
    initializeDeleteFunctionality();
});

// Modal functionality for Add Customer
function initializeModal() {
    const showModal = document.getElementById('showAddEmpModal');
    const closeModal = document.getElementById('closeAddEmpModal');
    const modal = document.getElementById('addEmpModal');

    if (showModal) {
        showModal.onclick = function() {
            modal.classList.add('active');
        };
    }

    if (closeModal) {
        closeModal.onclick = function() {
            modal.classList.remove('active');
        };
    }

    // Close modal when clicking outside content
    if (modal) {
        modal.onclick = function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        };
    }
}

function initializeDateCalculation() {
    const startDateInput = document.getElementById('inclusive_date_start');
    const endDateInput = document.getElementById('inclusive_date_end');
    const singleDayCheckbox = document.getElementById('single-day-activity');
    const workingDaysInput = document.getElementById('working_days');
    const endDateCol = document.getElementById('end-date-col');

    // Initialize toggle buttons
    initializeToggleButtons();

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            if (singleDayCheckbox && singleDayCheckbox.checked) {
                endDateInput.value = startDateInput.value;
            }
            calculateWorkingDays();
        });

        endDateInput.addEventListener('change', calculateWorkingDays);
    }

    if (singleDayCheckbox) {
        singleDayCheckbox.addEventListener('change', function () {
            if (this.checked) {
                // Hide end date and its controls
                if (endDateCol) endDateCol.style.display = 'none';
                endDateInput.value = startDateInput.value;
                // Reset end date half day selection
                endHalfDay = null;
                updateToggleButtonState('end');
            } else {
                // Show end date and its controls
                if (endDateCol) endDateCol.style.display = 'block';
            }
            calculateWorkingDays();
        });
    }
}

function initializeToggleButtons() {
    // Start date toggle buttons
    const startAmBtn = document.getElementById('start-am-btn');
    const startPmBtn = document.getElementById('start-pm-btn');
    const endAmBtn = document.getElementById('end-am-btn');
    const endPmBtn = document.getElementById('end-pm-btn');

    if (startAmBtn) {
        startAmBtn.addEventListener('click', function() {
            toggleHalfDay('start', 'AM');
        });
    }
    
    if (startPmBtn) {
        startPmBtn.addEventListener('click', function() {
            toggleHalfDay('start', 'PM');
        });
    }

    // End date toggle buttons
    if (endAmBtn) {
        endAmBtn.addEventListener('click', function() {
            toggleHalfDay('end', 'AM');
        });
    }
    
    if (endPmBtn) {
        endPmBtn.addEventListener('click', function() {
            toggleHalfDay('end', 'PM');
        });
    }
}

function toggleHalfDay(dateType, period) {
    if (dateType === 'start') {
        if (startHalfDay === period) {
            startHalfDay = null; // Deselect if clicking the same button
        } else {
            startHalfDay = period; // Select the clicked button
        }
        updateToggleButtonState('start');
    } else if (dateType === 'end') {
        if (endHalfDay === period) {
            endHalfDay = null; // Deselect if clicking the same button
        } else {
            endHalfDay = period; // Select the clicked button
        }
        updateToggleButtonState('end');
    }
    calculateWorkingDays();
}

function updateToggleButtonState(dateType) {
    const prefix = dateType === 'start' ? 'start' : 'end';
    const currentSelection = dateType === 'start' ? startHalfDay : endHalfDay;
    
    const amBtn = document.getElementById(`${prefix}-am-btn`);
    const pmBtn = document.getElementById(`${prefix}-pm-btn`);
    
    if (amBtn && pmBtn) {
        // Reset both buttons
        amBtn.classList.remove('active');
        pmBtn.classList.remove('active');
        
        // Activate the selected button
        if (currentSelection === 'AM') {
            amBtn.classList.add('active');
        } else if (currentSelection === 'PM') {
            pmBtn.classList.add('active');
        }
    }
}

function calculateWorkingDays() {
    const startDate = document.getElementById('inclusive_date_start').value;
    const endDate = document.getElementById('inclusive_date_end').value;
    const workingDaysInput = document.getElementById('working_days');
    const singleDayCheckbox = document.getElementById('single-day-activity');
    const daysDisplay = document.getElementById('days-display');

    if (singleDayCheckbox && singleDayCheckbox.checked) {
        // Single day activity - check if start date has half day selected
        let singleDayValue = startHalfDay ? 0.5 : 1;
        if (workingDaysInput) workingDaysInput.value = singleDayValue;
        if (daysDisplay) daysDisplay.textContent = singleDayValue;
        return;
    }

    if (startDate && endDate && workingDaysInput) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) {
            workingDaysInput.value = 0;
            if (daysDisplay) daysDisplay.textContent = 0;
            return;
        }

        let workingDays = 0;
        let currentDate = new Date(start);
        let dayCount = 0;

        while (currentDate <= end) {
            const dayOfWeek = currentDate.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not weekend
                if (dayCount === 0) {
                    // First day - check start half day
                    workingDays += startHalfDay ? 0.5 : 1;
                } else if (currentDate.getTime() === end.getTime()) {
                    // Last day - check end half day
                    workingDays += endHalfDay ? 0.5 : 1;
                } else {
                    // Middle days are always full days
                    workingDays += 1;
                }
            }
            currentDate.setDate(currentDate.getDate() + 1);
            dayCount++;
        }

        workingDaysInput.value = workingDays;
        if (daysDisplay) daysDisplay.textContent = workingDays;
    } else {
        if (workingDaysInput) workingDaysInput.value = '';
        if (daysDisplay) daysDisplay.textContent = '0';
    }
}

// Edit leave application
function editLeaveApplication(id, leaveType, dateFiled, startDate, endDate, workingDays, leaveDetails = '', isLeaveWithoutPay = false, isLeaveWPay = false) {
    isEditing = true;
    
    const form = document.getElementById('leave-form');
    const editIdInput = document.getElementById('edit_id');
    const methodInput = document.getElementById('form_method');
    const leaveTypeSelect = document.querySelector('select[name="leave_type"]');
    const dateFiledInput = document.getElementById('date_filed');
    const startDateInput = document.getElementById('inclusive_date_start');
    const endDateInput = document.getElementById('inclusive_date_end');
    const workingDaysInput = document.getElementById('working_days');
    const formContainer = document.getElementById('leave-form-container');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const leaveDetailsInput = document.getElementById('leave_details');
    if (leaveDetailsInput) leaveDetailsInput.value = leaveDetails;
    
    const isLeaveWithoutPayBool = isLeaveWithoutPay === true || isLeaveWithoutPay === 'true';
    const isLeavePayBool = isLeaveWPay === true || isLeaveWPay === 'true';

    document.getElementById('is_leavewopay').checked = isLeaveWithoutPayBool;
    document.getElementById('is_leavepay').checked = isLeavePayBool;

    if (!originalFormAction && form) {
        originalFormAction = form.action;
    }
    
    // Update form for editing
    if (editIdInput) editIdInput.value = id;
    if (methodInput) methodInput.value = 'PUT';
    if (form) form.action = window.leaveUpdateRoute || form.action;

    // Populate form fields
    if (leaveTypeSelect) leaveTypeSelect.value = leaveType;
    if (dateFiledInput) dateFiledInput.value = dateFiled;
    if (startDateInput) startDateInput.value = startDate;
    if (endDateInput) endDateInput.value = endDate;
    if (workingDaysInput) workingDaysInput.value = workingDays;
    
    // Update UI
    if (formContainer) formContainer.classList.add('editing');
    if (submitBtn) submitBtn.textContent = 'Save Edits';
    if (cancelBtn) cancelBtn.style.display = 'inline-block';
    
    // Scroll to form
    if (form) {
        form.scrollIntoView({ behavior: 'smooth' });
    }
}

// Cancel edit
function cancelEdit() {
    isEditing = false;
    isCancelling = false;
    
    const form = document.getElementById('leave-form');
    const editIdInput = document.getElementById('edit_id');
    const methodInput = document.getElementById('form_method');
    const leaveTypeSelect = document.querySelector('select[name="leave_type"]');
    const dateFiledInput = document.getElementById('date_filed');
    const startDateInput = document.getElementById('inclusive_date_start');
    const endDateInput = document.getElementById('inclusive_date_end');
    const workingDaysInput = document.getElementById('working_days');
    const formContainer = document.getElementById('leave-form-container');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const isCancellationInput = document.getElementById('is_cancellation');
    
    // Reset form
    if (editIdInput) editIdInput.value = '';
    if (methodInput) methodInput.value = 'POST';
    if (form && originalFormAction) form.action = originalFormAction;
    if (isCancellationInput) isCancellationInput.value = '0';
    
    // Clear form fields
    if (leaveTypeSelect) leaveTypeSelect.selectedIndex = 0;
    if (dateFiledInput) dateFiledInput.value = '';
    if (startDateInput) startDateInput.value = '';
    if (endDateInput) endDateInput.value = '';
    if (workingDaysInput) workingDaysInput.value = '';
    
    // Reset UI
    if (formContainer) {
        formContainer.classList.remove('editing');
        formContainer.classList.remove('cancelling');
    }
    if (submitBtn) submitBtn.textContent = 'Add Leave';
    if (cancelBtn) cancelBtn.style.display = 'none';
    
    // Remove cancellation mode title
    const cancelTitle = document.getElementById('cancel-mode-title');
    if (cancelTitle) cancelTitle.remove();
}

// Customer search functionality
function initializeCustomerSearch() {
    // Wait for jQuery to be loaded
    if (typeof $ !== 'undefined') {
        setupCustomerSearch();
    } else {
        // If jQuery isn't loaded yet, wait for it
        setTimeout(initializeCustomerSearch, 100);
    }
}

function setupCustomerSearch() {
    $('#customer-search').on('input', function() {
        console.log('Input event fired');
        let query = $(this).val();
        
        if (query.length < 2) {
            $('#suggestions').hide();
            return;
        }
        
        $.ajax({
            url: window.autocompleteRoute,
            method: 'GET',
            data: { query: query },
            dataType: 'text',
            success: function(response) {
                console.log('Raw response:', response);
                
                try {
                    let data = JSON.parse(response);
                    console.log('Parsed data:', data);
                    
                    let suggestions = '';
                    if (data && data.length > 0) {
                        data.forEach(function(item) {
                            suggestions += '<div class="suggestion-item" data-id="' + item.id + '" data-name="' + item.label + '">' + item.label + '</div>';
                        });
                        $('#suggestions').html(suggestions).show();
                    } else {
                        $('#suggestions').hide();
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response was:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response Text:', xhr.responseText);
                console.error('Status Code:', xhr.status);
            }
        });
    });

    // Handle suggestion click
    $(document).on('click', '.suggestion-item', function() {
        let customerId = $(this).data('id');
        let customerName = $(this).data('name');
        
        $('#customer-search').val(customerName);
        $('#suggestions').hide();
        
        // Store the selected customer ID for form submission
        $('#customer-search').data('selected-id', customerId);
        
        // Redirect directly using the customer ID as query parameter
        window.location.href = '/leave/customer?customer_id=' + customerId;
    });

    // Handle Enter key press
    $('#customer-search').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            
            // Check if a customer was selected from suggestions
            let selectedId = $(this).data('selected-id');
            if (selectedId) {
                window.location.href = '/leave/customer?customer_id=' + selectedId;
                return;
            }
            
            // If no selection, try to find by name
            let customerName = $(this).val().trim();
            if (customerName.length > 0) {
                $.ajax({
                    url: '/find-customer',
                    method: 'POST',
                    data: {
                        name: customerName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error finding customer:', error);
                        // Handle error - maybe show a message to user
                        alert('Customer not found');
                    }
                });
            }
        }
    });

    // Clear selection when input changes
    $('#customer-search').on('input', function() {
        $(this).removeData('selected-id');
    });

    // Hide suggestions when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#customer-search, #suggestions').length) {
            $('#suggestions').hide();
        }
    });
}

// Initialize delete functionality
function initializeDeleteFunctionality() {
    // Add event listeners for delete buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            const button = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
            const id = button.dataset.id;
            const type = button.dataset.type;
            
            if (id && type) {
                deleteRecord(id, type);
            }
        }
    });
}

// CORRECTED: Delete record function using global window variables
function deleteRecord(id, type) {
    const recordType = type === 'credit' ? 'credit entry' : 'leave application';
    
    if (confirm('Are you sure you want to delete this ' + recordType + '? This action cannot be undone.')) {
        // Check if required global variables are available
        if (!window.deleteRoute || !window.csrfToken) {
            console.error('Required global variables (deleteRoute, csrfToken) are not available');
            alert('Configuration error: Unable to delete record. Please refresh the page and try again.');
            return;
        }
        
        // Option 1: Using AJAX (Recommended)
        fetch(window.deleteRoute, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                type: type
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success || data.message) {
                // Show success message and reload page
                alert(data.message || recordType + ' deleted successfully');
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete record'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the record. Please try again.');
        });
    }
}

// Alternative: Form submission method (if AJAX doesn't work)
function deleteRecordWithForm(id, type) {
    const recordType = type === 'credit' ? 'credit entry' : 'leave application';
    
    if (confirm('Are you sure you want to delete this ' + recordType + '? This action cannot be undone.')) {
        // Check if required global variables are available
        if (!window.deleteRoute || !window.csrfToken) {
            console.error('Required global variables (deleteRoute, csrfToken) are not available');
            alert('Configuration error: Unable to delete record. Please refresh the page and try again.');
            return;
        }
        
        // Create a form to submit DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.deleteRoute;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = window.csrfToken;
        form.appendChild(csrfToken);
        
        // Add DELETE method
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Add record ID
        const idField = document.createElement('input');
        idField.type = 'hidden';
        idField.name = 'id';
        idField.value = id;
        form.appendChild(idField);
        
        // Add record type
        const typeField = document.createElement('input');
        typeField.type = 'hidden';
        typeField.name = 'type';
        typeField.value = type;
        form.appendChild(typeField);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function cancelLeaveApplication(id, leaveType, startDate, endDate, workingDays, isLeaveWithoutPay = false, isLeaveWPay = false) {
    // Reset any existing edit state
    cancelEdit();
    
    // Set cancellation mode
    isCancelling = true;
    
    const leaveTypeSelect = document.querySelector('select[name="leave_type"]');
    const dateFiledInput = document.getElementById('date_filed');
    const startDateInput = document.getElementById('inclusive_date_start');
    const endDateInput = document.getElementById('inclusive_date_end');
    const workingDaysInput = document.getElementById('working_days');
    const formContainer = document.getElementById('leave-form-container');
    const submitBtn = document.getElementById('submit-btn');
    const isCancellationInput = document.getElementById('is_cancellation');
    
    // Set today's date as default cancellation date
    const today = new Date().toISOString().split('T')[0];
    
    // Populate form fields for cancellation
    if (leaveTypeSelect) leaveTypeSelect.value = leaveType;
    if (dateFiledInput) dateFiledInput.value = today; // Default to today, but editable
    if (startDateInput) startDateInput.value = startDate; // Default to today, but editable
    if (endDateInput) endDateInput.value = endDate; // Default to today, but editable
    if (workingDaysInput) workingDaysInput.value = workingDays; // Credits to add back
    if (isCancellationInput) isCancellationInput.value = '1';
    
    // Update UI to indicate cancellation mode
    if (formContainer) formContainer.classList.add('cancelling');
    if (submitBtn) submitBtn.textContent = 'Cancel Leave (Add Credits Back)';

    const isLeaveWithoutPayBool = isLeaveWithoutPay === true || isLeaveWithoutPay === 'true';
    const isLeavePayBool = isLeaveWPay === true || isLeaveWPay === 'true';

    document.getElementById('is_leavewopay').checked = isLeaveWithoutPayBool;
    document.getElementById('is_leavepay').checked = isLeavePayBool;
    
    // Make working days field editable for cancellation
    if (workingDaysInput) {
        workingDaysInput.removeAttribute('readonly');
        workingDaysInput.style.backgroundColor = '#fff';
    }
    
        

    // Add a visual indicator
    const formTitle = document.createElement('div');
    formTitle.id = 'cancel-mode-title';
    formTitle.style.cssText = 'background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold;';
    formTitle.innerHTML = '⚠️ CANCELLATION MODE: This will add back ' + leaveType + ' credits';
    
    // Remove any existing title
    const existingTitle = document.getElementById('cancel-mode-title');
    if (existingTitle) existingTitle.remove();
    
    // Add title to form
    if (formContainer) {
        formContainer.insertBefore(formTitle, formContainer.firstChild);
    }
    
    // Scroll to form
    const form = document.getElementById('leave-form');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth' });
    }
}

window.showOtherCreditsModal = function() {
    document.getElementById('otherCreditsModal').style.display = 'block';
}

window.closeOtherCreditsModal = function() {
    document.getElementById('otherCreditsModal').style.display = 'none';
}

// Make functions available globally for onclick handlers
window.editLeaveApplication = editLeaveApplication;
window.deleteRecord = deleteRecord;
window.deleteRecordWithForm = deleteRecordWithForm;
window.cancelEdit = cancelEdit;
window.cancelLeaveApplication = cancelLeaveApplication;
window.isCancelling = () => isCancelling; //for pull request