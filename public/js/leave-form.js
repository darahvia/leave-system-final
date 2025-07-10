/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/leave-form.js":
/*!************************************!*\
  !*** ./resources/js/leave-form.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// Leave Management JavaScript
// This file contains all the JavaScript functionality for the leave management system

// Global variables
var isEditing = false;
var isCancelling = false;
var originalFormAction = '';
var startHalfDay = null; // null means full day, 'AM' or 'PM' means half day
var endHalfDay = null;

// DOM Content Loaded - Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function () {
  initializeModal();
  initializeDateCalculation();
  initializeCustomerSearch();
  initializeDeleteFunctionality();
});

// Modal functionality for Add Customer
function initializeModal() {
  var showModal = document.getElementById('showAddEmpModal');
  var closeModal = document.getElementById('closeAddEmpModal');
  var modal = document.getElementById('addEmpModal');
  if (showModal) {
    showModal.onclick = function () {
      modal.classList.add('active');
    };
  }
  if (closeModal) {
    closeModal.onclick = function () {
      modal.classList.remove('active');
    };
  }

  // Close modal when clicking outside content
  if (modal) {
    modal.onclick = function (e) {
      if (e.target === this) {
        this.classList.remove('active');
      }
    };
  }
}
function initializeDateCalculation() {
  var startDateInput = document.getElementById('inclusive_date_start');
  var endDateInput = document.getElementById('inclusive_date_end');
  var singleDayCheckbox = document.getElementById('single-day-activity');
  var workingDaysInput = document.getElementById('working_days');
  var endDateCol = document.getElementById('end-date-col');

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
  var startAmBtn = document.getElementById('start-am-btn');
  var startPmBtn = document.getElementById('start-pm-btn');
  var endAmBtn = document.getElementById('end-am-btn');
  var endPmBtn = document.getElementById('end-pm-btn');
  if (startAmBtn) {
    startAmBtn.addEventListener('click', function () {
      toggleHalfDay('start', 'AM');
    });
  }
  if (startPmBtn) {
    startPmBtn.addEventListener('click', function () {
      toggleHalfDay('start', 'PM');
    });
  }

  // End date toggle buttons
  if (endAmBtn) {
    endAmBtn.addEventListener('click', function () {
      toggleHalfDay('end', 'AM');
    });
  }
  if (endPmBtn) {
    endPmBtn.addEventListener('click', function () {
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
  var prefix = dateType === 'start' ? 'start' : 'end';
  var currentSelection = dateType === 'start' ? startHalfDay : endHalfDay;
  var amBtn = document.getElementById("".concat(prefix, "-am-btn"));
  var pmBtn = document.getElementById("".concat(prefix, "-pm-btn"));
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
  var startDate = document.getElementById('inclusive_date_start').value;
  var endDate = document.getElementById('inclusive_date_end').value;
  var workingDaysInput = document.getElementById('working_days');
  var singleDayCheckbox = document.getElementById('single-day-activity');
  var daysDisplay = document.getElementById('days-display');
  if (singleDayCheckbox && singleDayCheckbox.checked) {
    // Single day activity - check if start date has half day selected
    var singleDayValue = startHalfDay ? 0.5 : 1;
    if (workingDaysInput) workingDaysInput.value = singleDayValue;
    if (daysDisplay) daysDisplay.textContent = singleDayValue;
    return;
  }
  if (startDate && endDate && workingDaysInput) {
    var start = new Date(startDate);
    var end = new Date(endDate);
    if (end < start) {
      workingDaysInput.value = 0;
      if (daysDisplay) daysDisplay.textContent = 0;
      return;
    }
    var workingDays = 0;
    var currentDate = new Date(start);
    var dayCount = 0;
    while (currentDate <= end) {
      var dayOfWeek = currentDate.getDay();
      if (dayOfWeek !== 0 && dayOfWeek !== 6) {
        // Not weekend
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
function editLeaveApplication(id, leaveType, dateFiled, startDate, endDate, workingDays) {
  var leaveDetails = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : '';
  var isLeaveWithoutPay = arguments.length > 7 && arguments[7] !== undefined ? arguments[7] : false;
  isEditing = true;
  var form = document.getElementById('leave-form');
  var editIdInput = document.getElementById('edit_id');
  var methodInput = document.getElementById('form_method');
  var leaveTypeSelect = document.querySelector('select[name="leave_type"]');
  var dateFiledInput = document.getElementById('date_filed');
  var startDateInput = document.getElementById('inclusive_date_start');
  var endDateInput = document.getElementById('inclusive_date_end');
  var workingDaysInput = document.getElementById('working_days');
  var formContainer = document.getElementById('leave-form-container');
  var submitBtn = document.getElementById('submit-btn');
  var cancelBtn = document.getElementById('cancel-edit-btn');
  var leaveDetailsInput = document.getElementById('leave_details');
  if (leaveDetailsInput) leaveDetailsInput.value = leaveDetails;
  document.getElementById('is_leavewopay').checked = !!isLeaveWithoutPay; // Store original form action if not already stored
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
    form.scrollIntoView({
      behavior: 'smooth'
    });
  }
}

// Cancel edit
function cancelEdit() {
  isEditing = false;
  isCancelling = false;
  var form = document.getElementById('leave-form');
  var editIdInput = document.getElementById('edit_id');
  var methodInput = document.getElementById('form_method');
  var leaveTypeSelect = document.querySelector('select[name="leave_type"]');
  var dateFiledInput = document.getElementById('date_filed');
  var startDateInput = document.getElementById('inclusive_date_start');
  var endDateInput = document.getElementById('inclusive_date_end');
  var workingDaysInput = document.getElementById('working_days');
  var formContainer = document.getElementById('leave-form-container');
  var submitBtn = document.getElementById('submit-btn');
  var cancelBtn = document.getElementById('cancel-edit-btn');
  var isCancellationInput = document.getElementById('is_cancellation');

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
  var cancelTitle = document.getElementById('cancel-mode-title');
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
  $('#customer-search').on('input', function () {
    console.log('Input event fired');
    var query = $(this).val();
    if (query.length < 2) {
      $('#suggestions').hide();
      return;
    }
    $.ajax({
      url: window.autocompleteRoute,
      method: 'GET',
      data: {
        query: query
      },
      dataType: 'text',
      success: function success(response) {
        console.log('Raw response:', response);
        try {
          var data = JSON.parse(response);
          console.log('Parsed data:', data);
          var suggestions = '';
          if (data && data.length > 0) {
            data.forEach(function (item) {
              suggestions += '<div class="suggestion-item" data-id="' + item.id + '">' + item.label + '</div>';
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
      error: function error(xhr, status, _error) {
        console.error('AJAX Error:', _error);
        console.error('Status:', status);
        console.error('Response Text:', xhr.responseText);
        console.error('Status Code:', xhr.status);
      }
    });
  });
  $(document).on('click', '.suggestion-item', function () {
    $('#customer-search').val($(this).text());
    $('#suggestions').hide();
  });
  $(document).click(function (e) {
    if (!$(e.target).closest('#customer-search, #suggestions').length) {
      $('#suggestions').hide();
    }
  });
}

// Initialize delete functionality
function initializeDeleteFunctionality() {
  // Add event listeners for delete buttons
  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
      var button = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
      var id = button.dataset.id;
      var type = button.dataset.type;
      if (id && type) {
        deleteRecord(id, type);
      }
    }
  });
}

// CORRECTED: Delete record function using global window variables
function deleteRecord(id, type) {
  var recordType = type === 'credit' ? 'credit entry' : 'leave application';
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
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    }).then(function (data) {
      if (data.success || data.message) {
        // Show success message and reload page
        alert(data.message || recordType + ' deleted successfully');
        window.location.reload();
      } else {
        alert('Error: ' + (data.error || 'Failed to delete record'));
      }
    })["catch"](function (error) {
      console.error('Error:', error);
      alert('An error occurred while deleting the record. Please try again.');
    });
  }
}

// Alternative: Form submission method (if AJAX doesn't work)
function deleteRecordWithForm(id, type) {
  var recordType = type === 'credit' ? 'credit entry' : 'leave application';
  if (confirm('Are you sure you want to delete this ' + recordType + '? This action cannot be undone.')) {
    // Check if required global variables are available
    if (!window.deleteRoute || !window.csrfToken) {
      console.error('Required global variables (deleteRoute, csrfToken) are not available');
      alert('Configuration error: Unable to delete record. Please refresh the page and try again.');
      return;
    }

    // Create a form to submit DELETE request
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = window.deleteRoute;

    // Add CSRF token
    var csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = window.csrfToken;
    form.appendChild(csrfToken);

    // Add DELETE method
    var methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);

    // Add record ID
    var idField = document.createElement('input');
    idField.type = 'hidden';
    idField.name = 'id';
    idField.value = id;
    form.appendChild(idField);

    // Add record type
    var typeField = document.createElement('input');
    typeField.type = 'hidden';
    typeField.name = 'type';
    typeField.value = type;
    form.appendChild(typeField);
    document.body.appendChild(form);
    form.submit();
  }
}
function cancelLeaveApplication(id, leaveType, startDate, endDate, workingDays) {
  // Reset any existing edit state
  cancelEdit();

  // Set cancellation mode
  isCancelling = true;
  var leaveTypeSelect = document.querySelector('select[name="leave_type"]');
  var dateFiledInput = document.getElementById('date_filed');
  var startDateInput = document.getElementById('inclusive_date_start');
  var endDateInput = document.getElementById('inclusive_date_end');
  var workingDaysInput = document.getElementById('working_days');
  var formContainer = document.getElementById('leave-form-container');
  var submitBtn = document.getElementById('submit-btn');
  var isCancellationInput = document.getElementById('is_cancellation');

  // Set today's date as default cancellation date
  var today = new Date().toISOString().split('T')[0];

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

  // Make working days field editable for cancellation
  if (workingDaysInput) {
    workingDaysInput.removeAttribute('readonly');
    workingDaysInput.style.backgroundColor = '#fff';
  }

  // Add a visual indicator
  var formTitle = document.createElement('div');
  formTitle.id = 'cancel-mode-title';
  formTitle.style.cssText = 'background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold;';
  formTitle.innerHTML = '⚠️ CANCELLATION MODE: This will add back ' + workingDays + ' ' + leaveType + ' credits';

  // Remove any existing title
  var existingTitle = document.getElementById('cancel-mode-title');
  if (existingTitle) existingTitle.remove();

  // Add title to form
  if (formContainer) {
    formContainer.insertBefore(formTitle, formContainer.firstChild);
  }

  // Scroll to form
  var form = document.getElementById('leave-form');
  if (form) {
    form.scrollIntoView({
      behavior: 'smooth'
    });
  }
}
window.showOtherCreditsModal = function () {
  document.getElementById('otherCreditsModal').style.display = 'block';
};
window.closeOtherCreditsModal = function () {
  document.getElementById('otherCreditsModal').style.display = 'none';
};

// Make functions available globally for onclick handlers
window.editLeaveApplication = editLeaveApplication;
window.deleteRecord = deleteRecord;
window.deleteRecordWithForm = deleteRecordWithForm;
window.cancelEdit = cancelEdit;
window.cancelLeaveApplication = cancelLeaveApplication;
window.isCancelling = function () {
  return isCancelling;
}; //for pull request

/***/ }),

/***/ 1:
/*!******************************************!*\
  !*** multi ./resources/js/leave-form.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! C:\xampp\htdocs\leave-system-final\resources\js\leave-form.js */"./resources/js/leave-form.js");


/***/ })

/******/ });