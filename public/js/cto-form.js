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
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/cto-form.js":
/*!**********************************!*\
  !*** ./resources/js/cto-form.js ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { if (r) i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n;else { var o = function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); }; o("next", 0), o("throw", 1), o("return", 2); } }, _regeneratorDefine2(e, r, n, t); }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
// resources/js/cto-form.js

// This script contains the CTO-specific JavaScript logic,
// including form handling, date calculations, and action button functionality.

// Ensure the DOM is fully loaded before executing scripts
$(document).ready(function () {
  // These global variables are expected to be set in the blade file
  // before this script is loaded.
  // window.autocompleteRoute
  // window.ctoEditRoute
  // window.ctoUpdateRoute
  // window.ctoDeleteRoute
  // window.ctoCalculateDaysRoute
  // window.csrfToken
  // window.ctoStoreActivityRoute (NEWLY ADDED IN BLADE FOR EXTERNAL JS)
  // window.ctoStoreUsageRoute (NEWLY ADDED IN BLADE FOR EXTERNAL JS)

  // --- Customer Search (Common for both Leave and CTO pages) ---
  var debounceTimer;
  $('#customer-search').on('input', function () {
    clearTimeout(debounceTimer);
    var query = $(this).val();
    if (query.length < 2) {
      $('#suggestions').empty().hide();
      return;
    }
    debounceTimer = setTimeout(function () {
      $.ajax({
        url: window.autocompleteRoute,
        method: 'GET',
        data: {
          query: query
        },
        success: function success(data) {
          $('#suggestions').empty();
          if (data.length > 0) {
            data.forEach(function (item) {
              $('#suggestions').append("<div class=\"suggestion-item\" data-label=\"".concat(item.label, "\">").concat(item.label, "</div>"));
            });
            $('#suggestions').show();
          } else {
            $('#suggestions').hide();
          }
        },
        error: function error(xhr, status, _error) {
          console.error("Autocomplete AJAX error:", status, _error, xhr.responseText);
          $('#suggestions').empty().hide();
        }
      });
    }, 300);
  });

  // Handle suggestion clicks
  $(document).on('click', '.suggestion-item', function () {
    var label = $(this).data('label');
    $('#customer-search').val(label);
    $('#suggestions').empty().hide();
  });

  // Hide suggestions when clicking outside
  $(document).on('click', function (e) {
    if (!$(e.target).closest('.search-box').length) {
      $('#suggestions').empty().hide();
    }
  });

  // --- Modal functionality (Add Customer) ---
  // These elements always exist regardless of whether an customer is loaded.
  var showAddEmpModalBtn = document.getElementById('showAddEmpModal');
  var closeAddEmpModalBtn = document.getElementById('closeAddEmpModal');
  var addEmpModal = document.getElementById('addEmpModal');
  if (showAddEmpModalBtn && addEmpModal) {
    showAddEmpModalBtn.addEventListener('click', function () {
      addEmpModal.style.display = 'flex';
    });
  }
  if (closeAddEmpModalBtn && addEmpModal) {
    closeAddEmpModalBtn.addEventListener('click', function () {
      addEmpModal.style.display = 'none';
    });
  }

  // --- CTO Specific JavaScript (conditional on customer existence) ---
  // These elements and functions only apply if an customer is loaded,
  // meaning the forms are actually present in the HTML.
  // The presence check is done implicitly by trying to get the element.
  var singleDayActivityCheckbox = document.getElementById('single-day-activity');
  var activityEndDateField = document.getElementById('activity-end-date');
  var activityEndDateLabel = document.getElementById('end-date-label');
  var dateOfActivityStartField = document.getElementById('date_of_activity_start');
  var specialOrderField = document.getElementById('special_order');
  var activityField = document.getElementById('activity');
  var creditsEarnedField = document.getElementById('credits_earned');
  var submitActivityBtn = document.getElementById('submit-activity-btn');
  var cancelActivityEditBtn = document.getElementById('cancel-activity-edit-btn');
  var activityCtoIdField = document.getElementById('activity_cto_id');
  var activityFormMethodField = document.getElementById('activity_form_method');
  var activityForm = $('#activity-form'); // Using jQuery for form submission handling

  var singleDayAbsenceCheckbox = document.getElementById('single-day-absence');
  var absenceEndDateField = document.getElementById('absence-end-date');
  var absenceEndDateLabel = document.getElementById('absence-end-date-label');
  var dateOfAbsenceStartField = document.getElementById('date_of_absence_start');
  var submitUsageBtn = document.getElementById('submit-usage-btn');
  var cancelUsageEditBtn = document.getElementById('cancel-usage-edit-btn');
  var usageCtoIdField = document.getElementById('usage_cto_id');
  var usageFormMethodField = document.getElementById('usage_form_method');
  var usageForm = $('#usage-form'); // Using jQuery for form submission handling

  // Initial state for single day checkboxes (ensure end date fields are hidden/shown correctly on load)
  if (singleDayActivityCheckbox && activityEndDateField && activityEndDateLabel) {
    // Set initial state based on checkbox
    if (singleDayActivityCheckbox.checked) {
      activityEndDateField.style.display = 'none';
      activityEndDateField.removeAttribute('required');
      activityEndDateLabel.style.display = 'none';
    } else {
      activityEndDateField.style.display = 'block';
      activityEndDateField.setAttribute('required', 'required');
      activityEndDateLabel.style.display = 'block';
    }
    // Add event listener
    singleDayActivityCheckbox.addEventListener('change', function () {
      if (this.checked) {
        activityEndDateField.style.display = 'none';
        activityEndDateField.value = ''; // Clear value when hidden
        activityEndDateField.removeAttribute('required');
        activityEndDateLabel.style.display = 'none';
      } else {
        activityEndDateField.style.display = 'block';
        activityEndDateField.setAttribute('required', 'required');
        activityEndDateLabel.style.display = 'block';
      }
    });
  }
  if (singleDayAbsenceCheckbox && absenceEndDateField && absenceEndDateLabel) {
    // Set initial state based on checkbox
    if (singleDayAbsenceCheckbox.checked) {
      absenceEndDateField.style.display = 'none';
      absenceEndDateField.removeAttribute('required');
      absenceEndDateLabel.style.display = 'none';
    } else {
      absenceEndDateField.style.display = 'block';
      absenceEndDateField.setAttribute('required', 'required');
      absenceEndDateLabel.style.display = 'block';
    }
    // Add event listener
    singleDayAbsenceCheckbox.addEventListener('change', function () {
      if (this.checked) {
        absenceEndDateField.style.display = 'none';
        absenceEndDateField.value = ''; // Clear value when hidden
        absenceEndDateField.removeAttribute('required');
        absenceEndDateLabel.style.display = 'none';
      } else {
        absenceEndDateField.style.display = 'block';
        absenceEndDateField.setAttribute('required', 'required');
        absenceEndDateLabel.style.display = 'block';
      }
    });
  }

  // Function to calculate working days (for CTO usage)
  function calculateWorkingDaysForUsage() {
    return _calculateWorkingDaysForUsage.apply(this, arguments);
  } // Attach event listeners for date changes in usage form to recalculate days
  function _calculateWorkingDaysForUsage() {
    _calculateWorkingDaysForUsage = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
      var startDate, endDate, response, data, _t;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            if (!(!dateOfAbsenceStartField || !absenceEndDateField || !singleDayAbsenceCheckbox)) {
              _context.n = 1;
              break;
            }
            return _context.a(2);
          case 1:
            startDate = dateOfAbsenceStartField.value; // If single day, end date is same as start date for calculation
            endDate = singleDayAbsenceCheckbox.checked ? startDate : absenceEndDateField.value;
            if (!(startDate && endDate)) {
              _context.n = 6;
              break;
            }
            _context.p = 2;
            _context.n = 3;
            return fetch(window.ctoCalculateDaysRoute, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
              },
              body: JSON.stringify({
                start_date: startDate,
                end_date: endDate
              })
            });
          case 3:
            response = _context.v;
            _context.n = 4;
            return response.json();
          case 4:
            data = _context.v;
            if (response.ok) {
              // console.log('Calculated working days:', data.days);
              // You might want to update a hidden field or display this to the user
              // For now, it's just logged or used internally by the server.
            } else {
              console.error('Error calculating days:', data.message);
              // Using a custom modal/div for error messages instead of alert()
              displayMessage('Error calculating days: ' + data.message, 'error');
            }
            _context.n = 6;
            break;
          case 5:
            _context.p = 5;
            _t = _context.v;
            console.error('Fetch error calculating days:', _t);
            displayMessage('An error occurred while calculating days.', 'error');
          case 6:
            return _context.a(2);
        }
      }, _callee, null, [[2, 5]]);
    }));
    return _calculateWorkingDaysForUsage.apply(this, arguments);
  }
  if (dateOfAbsenceStartField) {
    dateOfAbsenceStartField.addEventListener('change', calculateWorkingDaysForUsage);
  }
  if (absenceEndDateField) {
    absenceEndDateField.addEventListener('change', calculateWorkingDaysForUsage);
  }
  if (singleDayAbsenceCheckbox) {
    singleDayAbsenceCheckbox.addEventListener('change', calculateWorkingDaysForUsage);
  }

  // Edit function for CTO records (exposed globally)
  window.editCtoRecord = function (id, is_activity, special_order, activity, credits_earned, date_of_activity_start, date_of_activity_end, no_of_days, date_of_absence_start, date_of_absence_end) {
    if (is_activity) {
      // Populate Activity Form
      activityCtoIdField.value = id;
      activityForm.attr('action', window.ctoUpdateRoute.replace(':id', id));
      activityFormMethodField.value = 'PUT'; // Set to PUT for update
      submitActivityBtn.textContent = 'Update CTO Activity';
      cancelActivityEditBtn.style.display = 'inline-block';
      specialOrderField.value = special_order;
      activityField.value = activity;
      creditsEarnedField.value = credits_earned;
      dateOfActivityStartField.value = date_of_activity_start;
      if (date_of_activity_start && date_of_activity_end && date_of_activity_start === date_of_activity_end) {
        singleDayActivityCheckbox.checked = true;
        activityEndDateField.style.display = 'none';
        activityEndDateField.removeAttribute('required');
        activityEndDateLabel.style.display = 'none';
      } else {
        singleDayActivityCheckbox.checked = false;
        activityEndDateField.style.display = 'block';
        activityEndDateField.setAttribute('required', 'required');
        activityEndDateField.value = date_of_activity_end;
        activityEndDateLabel.style.display = 'block';
      }

      // Scroll to the activity form
      activityForm[0].scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    } else {
      // Populate Usage Form
      usageCtoIdField.value = id;
      usageForm.attr('action', window.ctoUpdateRoute.replace(':id', id));
      usageFormMethodField.value = 'PUT'; // Set to PUT for update
      submitUsageBtn.textContent = 'Update CTO Usage';
      cancelUsageEditBtn.style.display = 'inline-block';
      dateOfAbsenceStartField.value = date_of_absence_start;
      if (date_of_absence_start && date_of_absence_end && date_of_absence_start === date_of_absence_end) {
        singleDayAbsenceCheckbox.checked = true;
        absenceEndDateField.style.display = 'none';
        absenceEndDateField.removeAttribute('required');
        absenceEndDateLabel.style.display = 'none';
      } else {
        singleDayAbsenceCheckbox.checked = false;
        absenceEndDateField.style.display = 'block';
        absenceEndDateField.setAttribute('required', 'required');
        absenceEndDateField.value = date_of_absence_end;
        absenceEndDateLabel.style.display = 'block';
      }

      // Scroll to the usage form
      usageForm[0].scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }
  };

  // Cancel Edit functions (exposed globally)
  window.cancelCtoActivityEdit = function () {
    activityCtoIdField.value = '';
    // CORRECTED: Use the window variable for the route
    activityForm.attr('action', window.ctoStoreActivityRoute);
    activityFormMethodField.value = 'POST';
    submitActivityBtn.textContent = 'Add CTO Activity';
    cancelActivityEditBtn.style.display = 'none';
    activityForm[0].reset(); // Reset form fields
    // Ensure end date visibility is reset
    singleDayActivityCheckbox.checked = false; // Reset checkbox state
    activityEndDateField.style.display = 'block';
    activityEndDateField.setAttribute('required', 'required');
    activityEndDateField.value = ''; // Clear date value
    activityEndDateLabel.style.display = 'block';
  };
  window.cancelCtoUsageEdit = function () {
    usageCtoIdField.value = '';
    // CORRECTED: Use the window variable for the route
    usageForm.attr('action', window.ctoStoreUsageRoute);
    usageFormMethodField.value = 'POST';
    submitUsageBtn.textContent = 'Add CTO Usage';
    cancelUsageEditBtn.style.display = 'none';
    usageForm[0].reset(); // Reset form fields
    // Ensure end date visibility is reset
    singleDayAbsenceCheckbox.checked = false; // Reset checkbox state
    absenceEndDateField.style.display = 'block';
    absenceEndDateField.setAttribute('required', 'required');
    absenceEndDateField.value = ''; // Clear date value
    absenceEndDateLabel.style.display = 'block';
  };

  // Delete function for CTO records (exposed globally)
  window.deleteCtoRecord = function (id) {
    // Using a custom modal/div for confirmation instead of alert()
    showConfirmationModal('Are you sure you want to delete this CTO record? This action cannot be undone and will recalculate balances.', function () {
      var deleteUrl = window.ctoDeleteRoute.replace(':id', id);
      $.ajax({
        url: deleteUrl,
        type: 'POST',
        // Laravel uses POST for DELETE via _method spoofing
        data: {
          _method: 'DELETE',
          _token: window.csrfToken
        },
        success: function success(response) {
          displayMessage(response.success || 'CTO record deleted successfully.', 'success');
          location.reload(); // Reload the page to reflect changes
        },
        error: function error(xhr, status, _error2) {
          console.error("Delete CTO record error:", status, _error2, xhr.responseText);
          displayMessage(xhr.responseJSON.error || 'Failed to delete CTO record.', 'error');
        }
      });
    });
  };

  // --- Custom Modal/Message Box Functions (replacing alert/confirm) ---

  // Function to display messages (success/error)
  function displayMessage(message, type) {
    var messageBox = document.getElementById('custom-message-box');
    if (!messageBox) {
      messageBox = document.createElement('div');
      messageBox.id = 'custom-message-box';
      messageBox.style.cssText = "\n                position: fixed;\n                top: 20px;\n                left: 50%;\n                transform: translateX(-50%);\n                padding: 15px 25px;\n                border-radius: 8px;\n                font-weight: bold;\n                color: white;\n                z-index: 1000;\n                box-shadow: 0 4px 8px rgba(0,0,0,0.2);\n                display: none; /* Hidden by default */\n                opacity: 0;\n                transition: opacity 0.3s ease-in-out;\n            ";
      document.body.appendChild(messageBox);
    }
    messageBox.textContent = message;
    messageBox.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
    messageBox.style.display = 'block';
    setTimeout(function () {
      messageBox.style.opacity = '1';
    }, 10); // Fade in

    setTimeout(function () {
      messageBox.style.opacity = '0';
      setTimeout(function () {
        messageBox.style.display = 'none';
      }, 300); // Fade out and hide
    }, 3000); // Display for 3 seconds
  }

  // Function to show a custom confirmation modal
  function showConfirmationModal(message, onConfirmCallback) {
    var modalOverlay = document.getElementById('custom-confirm-overlay');
    if (!modalOverlay) {
      modalOverlay = document.createElement('div');
      modalOverlay.id = 'custom-confirm-overlay';
      modalOverlay.style.cssText = "\n                position: fixed;\n                top: 0;\n                left: 0;\n                width: 100%;\n                height: 100%;\n                background: rgba(0, 0, 0, 0.6);\n                display: flex;\n                justify-content: center;\n                align-items: center;\n                z-index: 1000;\n            ";
      document.body.appendChild(modalOverlay);
      var modalContent = document.createElement('div');
      modalContent.style.cssText = "\n                background: white;\n                padding: 30px;\n                border-radius: 10px;\n                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);\n                text-align: center;\n                max-width: 400px;\n                width: 90%;\n            ";
      modalOverlay.appendChild(modalContent);
      var messagePara = document.createElement('p');
      messagePara.id = 'confirm-message-text';
      messagePara.style.marginBottom = '20px';
      messagePara.style.fontSize = '1.1em';
      modalContent.appendChild(messagePara);
      var buttonContainer = document.createElement('div');
      var confirmButton = document.createElement('button');
      confirmButton.textContent = 'Confirm';
      confirmButton.style.cssText = "\n                background-color: #dc3545; /* Red */\n                color: white;\n                padding: 10px 20px;\n                border: none;\n                border-radius: 5px;\n                cursor: pointer;\n                margin-right: 10px;\n                font-size: 1em;\n            ";
      confirmButton.onclick = function () {
        modalOverlay.style.display = 'none';
        if (onConfirmCallback) {
          onConfirmCallback();
        }
      };
      buttonContainer.appendChild(confirmButton);
      var cancelButton = document.createElement('button');
      cancelButton.textContent = 'Cancel';
      cancelButton.style.cssText = "\n                background-color: #6c757d; /* Grey */\n                color: white;\n                padding: 10px 20px;\n                border: none;\n                border-radius: 5px;\n                cursor: pointer;\n                font-size: 1em;\n            ";
      cancelButton.onclick = function () {
        modalOverlay.style.display = 'none';
      };
      buttonContainer.appendChild(cancelButton);
      modalContent.appendChild(buttonContainer);
    }
    document.getElementById('confirm-message-text').textContent = message;
    modalOverlay.style.display = 'flex';
  }
}); // End of document.ready

/***/ }),

/***/ 2:
/*!****************************************!*\
  !*** multi ./resources/js/cto-form.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/darahvia/leave-system-final/resources/js/cto-form.js */"./resources/js/cto-form.js");


/***/ })

/******/ });