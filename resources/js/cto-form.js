// resources/js/cto-form.js

// This script contains the CTO-specific JavaScript logic,
// including form handling, date calculations, and action button functionality.

// Ensure the DOM is fully loaded before executing scripts
$(document).ready(function() {

    // These global variables are expected to be set in the blade file
    // before this script is loaded.
    // window.autocompleteRoute
    // window.ctoUpdateRoute
    // window.ctoDeleteRoute
    // window.ctoCalculateDaysRoute
    // window.csrfToken
    // window.ctoStoreActivityRoute
    // window.ctoStoreUsageRoute
    // window.customerId
    // window.ctoIndexRoute (NEWLY ADDED)

    // --- Customer Search (Common for both Leave and CTO pages) ---
    let debounceTimer;
    $('#customer-search').on('input', function() {
        clearTimeout(debounceTimer);
        const query = $(this).val();
        
        if (query.length < 2) {
            $('#suggestions').empty().hide();
            return;
        }
        
        debounceTimer = setTimeout(() => {
            $.ajax({
                url: window.autocompleteRoute,
                method: 'GET',
                data: { query: query },
                success: function(data) {
                    $('#suggestions').empty();
                    
                    if (data.length > 0) {
                        data.forEach(function(item) {
                            $('#suggestions').append(
                                `<div class="suggestion-item" data-label="${item.label}">${item.label}</div>`
                            );
                        });
                        $('#suggestions').show();
                    } else {
                        $('#suggestions').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Autocomplete AJAX error:", status, error, xhr.responseText);
                    $('#suggestions').empty().hide();
                }
            });
        }, 300);
    });

    // Handle suggestion clicks
    $(document).on('click', '.suggestion-item', function() {
        const label = $(this).data('label');
        $('#customer-search').val(label);
        $('#suggestions').empty().hide();
    });

    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-box').length) {
            $('#suggestions').empty().hide();
        }
    });

    // --- Modal functionality (Add Customer) ---
    const showAddCustomerModalBtn = document.getElementById('showAddCustomerModal');
    const closeAddCustomerModalBtn = document.getElementById('closeAddCustomerModal');
    const addCustomerModal = document.getElementById('addCustomerModal');

    if (showAddCustomerModalBtn && addCustomerModal) {
        showAddCustomerModalBtn.addEventListener('click', function() {
            addCustomerModal.style.display = 'flex';
        });
    }

    if (closeAddCustomerModalBtn && addCustomerModal) {
        closeAddCustomerModalBtn.addEventListener('click', function() {
            addCustomerModal.style.display = 'none';
        });
    }

    // --- CTO Specific JavaScript (conditional on customer existence) ---
    const singleDayActivityCheckbox = document.getElementById('single-day-activity');
    const activityEndDateField = document.getElementById('activity-end-date');
    const activityEndDateLabel = document.getElementById('end-date-label');
    const dateOfActivityStartField = document.getElementById('date_of_activity_start');
    const specialOrderField = document.getElementById('special_order');
    const activityField = document.getElementById('activity');
    const hoursEarnedField = document.getElementById('hours_earned'); 
    const submitActivityBtn = document.getElementById('submit-activity-btn');
    const cancelActivityEditBtn = document.getElementById('cancel-activity-edit-btn');
    const activityCtoIdField = document.getElementById('activity_cto_id');
    const activityFormMethodField = document.getElementById('activity_form_method');
    const activityForm = $('#activity-form'); 

    const singleDayAbsenceCheckbox = document.getElementById('single-day-absence');
    const absenceEndDateField = document.getElementById('inclusive_date_end_usage');
    const absenceEndDateLabel = document.getElementById('absence-end-date-label');
    const dateFiledUsageField = document.getElementById('usage_date_filed'); 
    const inclusiveDateStartUsageField = document.getElementById('inclusive_date_start_usage');
    const hoursAppliedUsageField = document.getElementById('hours_applied_usage'); 

    const submitUsageBtn = document.getElementById('submit-usage-btn');
    const cancelUsageEditBtn = document.getElementById('cancel-usage-edit-btn');
    const usageCtoIdField = document.getElementById('usage_cto_id');
    const usageFormMethodField = document.getElementById('usage_form_method');
    const usageForm = $('#usage-form'); 


    // Initial state for single day checkboxes (ensure end date fields are hidden/shown correctly on load)
    if (singleDayActivityCheckbox && activityEndDateField && activityEndDateLabel) {
        if (singleDayActivityCheckbox.checked) {
            activityEndDateField.style.display = 'none';
            activityEndDateField.removeAttribute('required');
            activityEndDateLabel.style.display = 'none';
        } else {
            activityEndDateField.style.display = 'block';
            activityEndDateField.setAttribute('required', 'required');
            activityEndDateLabel.style.display = 'block';
        }
        singleDayActivityCheckbox.addEventListener('change', function() {
            if (this.checked) {
                activityEndDateField.style.display = 'none';
                activityEndDateField.value = ''; 
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
        if (singleDayAbsenceCheckbox.checked) {
            absenceEndDateField.style.display = 'none';
            absenceEndDateField.removeAttribute('required');
            absenceEndDateLabel.style.display = 'none';
        } else {
            absenceEndDateField.style.display = 'block';
            absenceEndDateField.setAttribute('required', 'required');
            absenceEndDateLabel.style.display = 'block';
        }
        singleDayAbsenceCheckbox.addEventListener('change', function() {
            if (this.checked) {
                absenceEndDateField.style.display = 'none';
                absenceEndDateField.value = ''; 
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
    async function calculateWorkingDaysForUsage() {
        if (!inclusiveDateStartUsageField || !absenceEndDateField || !singleDayAbsenceCheckbox || !window.ctoCalculateDaysRoute) {
            console.warn("Missing elements or window.ctoCalculateDaysRoute. Skipping calculateWorkingDaysForUsage.");
            return;
        }

        const startDate = inclusiveDateStartUsageField.value;
        const endDate = singleDayAbsenceCheckbox.checked ? startDate : absenceEndDateField.value;

        if (startDate && endDate) {
            try {
                const response = await fetch(window.ctoCalculateDaysRoute, { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ start_date: startDate, end_date: endDate })
                });
                const data = await response.json();
                if (response.ok) {
                    console.log('Calculated working days (for usage form):', data.days);
                    // FIX: Populate the hoursAppliedUsageField with the calculated days
                    hoursAppliedUsageField.value = data.days; 
                } else {
                    console.error('Error calculating days:', data.message);
                    displayMessage('Error calculating days: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Fetch error calculating days:', error);
                displayMessage('An error occurred while calculating days.', 'error');
            }
        }
    }

    // Attach event listeners for date changes in usage form to recalculate days
    if (inclusiveDateStartUsageField) {
        inclusiveDateStartUsageField.addEventListener('change', calculateWorkingDaysForUsage);
    }
    if (absenceEndDateField) {
        absenceEndDateField.addEventListener('change', calculateWorkingDaysForUsage);
    }
    if (singleDayAbsenceCheckbox) {
        singleDayAbsenceCheckbox.addEventListener('change', calculateWorkingDaysForUsage);
    }

    // NEW: Unified edit function that accepts a data object
    window.editCtoRecordFromData = function(data) {
        console.log("editCtoRecordFromData called with data:", data);

        // Check if elements exist before trying to access their properties
        const requiredElements = [
            activityCtoIdField, activityForm, activityFormMethodField, submitActivityBtn, cancelActivityEditBtn,
            specialOrderField, activityField, hoursEarnedField, dateOfActivityStartField,
            singleDayActivityCheckbox, activityEndDateField, activityEndDateLabel,
            usageCtoIdField, usageForm, usageFormMethodField, submitUsageBtn, cancelUsageEditBtn,
            dateFiledUsageField, inclusiveDateStartUsageField, hoursAppliedUsageField,
            singleDayAbsenceCheckbox, absenceEndDateField, absenceEndDateLabel
        ];

        let missingElements = false;
        const elementNames = [
            "activityCtoIdField", "activityForm", "activityFormMethodField", "submitActivityBtn", "cancelActivityEditBtn",
            "specialOrderField", "activityField", "hoursEarnedField", "dateOfActivityStartField",
            "singleDayActivityCheckbox", "activityEndDateField", "activityEndDateLabel",
            "usageCtoIdField", "usageForm", "usageFormMethodField", "submitUsageBtn", "cancelUsageEditBtn",
            "dateFiledUsageField", "inclusiveDateStartUsageField", "hoursAppliedUsageField",
            "singleDayAbsenceCheckbox", "absenceEndDateField", "absenceEndDateLabel"
        ];

        for (let i = 0; i < requiredElements.length; i++) {
            if (!requiredElements[i]) {
                console.error(`Missing DOM element: ${elementNames[i]}. Check HTML IDs.`);
                missingElements = true;
            }
        }

        if (missingElements) {
            displayMessage("Error: Required form elements not found. Please check HTML structure and IDs.", "error");
            return; // Stop execution if elements are missing
        }


        if (data.is_activity) {
            console.log("Editing CTO Activity (earned credit)");
            // Populate Activity Form (Earned Credits)
            activityCtoIdField.value = data.id;
            activityForm.attr('action', window.ctoUpdateRoute.replace(':id', data.id));
            activityFormMethodField.value = 'PUT';
            submitActivityBtn.textContent = 'Update CTO Activity';
            cancelActivityEditBtn.style.display = 'inline-block';

            specialOrderField.value = data.special_order;
            activityField.value = data.activity;
            hoursEarnedField.value = data.hours_earned_or_applied; // This is credits_earned for activity
            dateOfActivityStartField.value = data.date_start;

            if (data.date_start === data.date_end || !data.date_end) {
                singleDayActivityCheckbox.checked = true;
                activityEndDateField.style.display = 'none';
                activityEndDateField.removeAttribute('required');
                activityEndDateField.value = '';
                activityEndDateLabel.style.display = 'none';
            } else {
                singleDayActivityCheckbox.checked = false;
                activityEndDateField.style.display = 'block';
                activityEndDateField.setAttribute('required', 'required');
                activityEndDateField.value = data.date_end;
                activityEndDateLabel.style.display = 'block';
            }

            activityForm[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

        } else {
            console.log("Editing CTO Usage (deducted credit)");
            // Populate Usage Form (Credits Deducted)
            usageCtoIdField.value = data.id;
            usageForm.attr('action', window.ctoUpdateRoute.replace(':id', data.id));
            usageFormMethodField.value = 'PUT';
            submitUsageBtn.textContent = 'Update CTO Usage';
            cancelUsageEditBtn.style.display = 'inline-block';

            dateFiledUsageField.value = data.date_filed || new Date().toISOString().slice(0, 10); 
            inclusiveDateStartUsageField.value = data.date_of_absence_start;
            hoursAppliedUsageField.value = data.hours_earned_or_applied; // This is no_of_days for usage

            if (data.date_of_absence_start === data.date_of_absence_end || !data.date_of_absence_end) {
                singleDayAbsenceCheckbox.checked = true;
                absenceEndDateField.style.display = 'none';
                absenceEndDateField.removeAttribute('required');
                absenceEndDateField.value = '';
                absenceEndDateLabel.style.display = 'none';
            } else {
                singleDayAbsenceCheckbox.checked = false;
                absenceEndDateField.style.display = 'block';
                absenceEndDateField.setAttribute('required', 'required');
                absenceEndDateField.value = data.date_of_absence_end;
                absenceEndDateLabel.style.display = 'block';
            }

            // Immediately recalculate days for usage form after populating dates
            // calculateWorkingDaysForUsage(); // This might be too early if DOM hasn't settled. Trigger manually or on next event.

            usageForm[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    // Cancel Edit functions (exposed globally)
    window.cancelCtoActivityEdit = function() {
        console.log("cancelCtoActivityEdit called.");
        activityCtoIdField.value = '';
        activityForm.attr('action', window.ctoStoreActivityRoute); 
        activityFormMethodField.value = 'POST';
        submitActivityBtn.textContent = 'Add CTO Activity';
        cancelActivityEditBtn.style.display = 'none';
        activityForm[0].reset(); 
        singleDayActivityCheckbox.checked = false;
        activityEndDateField.style.display = 'block';
        activityEndDateField.setAttribute('required', 'required');
        activityEndDateField.value = '';
        activityEndDateLabel.style.display = 'block';
    };

    window.cancelCtoUsageEdit = function() {
        console.log("cancelCtoUsageEdit called.");
        usageCtoIdField.value = '';
        usageForm.attr('action', window.ctoStoreUsageRoute); 
        usageFormMethodField.value = 'POST';
        submitUsageBtn.textContent = 'Add CTO Usage';
        cancelUsageEditBtn.style.display = 'none';
        usageForm[0].reset(); 
        singleDayAbsenceCheckbox.checked = false;
        absenceEndDateField.style.display = 'block';
        absenceEndDateField.setAttribute('required', 'required');
        absenceEndDateField.value = '';
        absenceEndDateLabel.style.display = 'block';
    };


    // Delete function for CTO records (exposed globally)
    window.deleteCtoRecord = function(id) {
        console.log("deleteCtoRecord called for ID:", id);
        showConfirmationModal('Are you sure you want to delete this CTO record? This action cannot be undone and will recalculate balances.', function() {
            const deleteUrl = window.ctoDeleteRoute.replace(':id', id); 
            console.log("Attempting to delete record with URL:", deleteUrl);
            $.ajax({
                url: deleteUrl,
                type: 'POST', 
                data: {
                    _method: 'DELETE', 
                    _token: window.csrfToken
                },
                success: function(response) {
                    console.log("Delete successful:", response);
                    if (response.success) {
                        let redirectUrl = window.ctoIndexRoute; 
                        let params = new URLSearchParams();
                        params.set('status', 'success');
                        params.set('message', response.message || 'CTO record deleted successfully!');
                        
                        const customerIdToRedirect = response.customer_id || window.customerId; 
                        if (customerIdToRedirect) {
                            params.set('customer_id', customerIdToRedirect);
                        } else {
                            console.warn("No customer_id available for redirect after delete. Check response or window.customerId.");
                        }
                        
                        window.location.href = redirectUrl + '?' + params.toString();
                    } else {
                        displayMessage(response.error || 'Failed to delete CTO record.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Delete CTO record error:", status, error, xhr.responseText);
                    displayMessage(xhr.responseJSON.error || 'Failed to delete CTO record.', 'error');
                }
            });
        });
    };

    // --- Custom Modal/Message Box Functions ---

    let messageHideTimeout; 

    function displayMessage(message, type) { 
        if (messageHideTimeout) {
            clearTimeout(messageHideTimeout);
        }

        let messageBox = document.getElementById('custom-message-box');
        if (!messageBox) {
            messageBox = document.createElement('div');
            messageBox.id = 'custom-message-box';
            messageBox.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                padding: 15px 25px;
                border-radius: 8px;
                font-weight: bold;
                color: white;
                z-index: 1000;
                display: none; 
                opacity: 0;
                transition: opacity 0.3s ease-in-out;
            `;
            document.body.appendChild(messageBox);
        }

        messageBox.textContent = message;
        messageBox.style.backgroundColor = type === 'success' ? '#28a745' : '#dc3545';
        messageBox.style.display = 'block';
        setTimeout(() => { messageBox.style.opacity = '1'; }, 10); 

        messageHideTimeout = setTimeout(() => {
            messageBox.style.opacity = '0'; 
            setTimeout(() => {
                messageBox.style.display = 'none'; 
            }, 300); 
        }, 5000); 
    }

    function showConfirmationModal(message, onConfirmCallback) {
        let modalOverlay = document.getElementById('custom-confirm-overlay');
        if (!modalOverlay) {
            modalOverlay = document.createElement('div');
            modalOverlay.id = 'custom-confirm-overlay';
            modalOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            document.body.appendChild(modalOverlay);

            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
                text-align: center;
                max-width: 400px;
                width: 90%;
            `;
            modalOverlay.appendChild(modalContent);

            const messagePara = document.createElement('p');
            messagePara.id = 'confirm-message-text';
            messagePara.style.marginBottom = '20px';
            messagePara.style.fontSize = '1.1em';
            modalContent.appendChild(messagePara);

            const buttonContainer = document.createElement('div');
            const confirmButton = document.createElement('button');
            confirmButton.textContent = 'Confirm';
            confirmButton.style.cssText = `
                background-color: #dc3545; /* Red */
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-right: 10px;
                font-size: 1em;
            `;
            confirmButton.onclick = function() {
                modalOverlay.style.display = 'none';
                if (onConfirmCallback) {
                    onConfirmCallback();
                }
            };
            buttonContainer.appendChild(confirmButton);

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancel';
            cancelButton.style.cssText = `
                background-color: #6c757d; /* Grey */
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 1em;
            `;
            cancelButton.onclick = function() {
                modalOverlay.style.display = 'none';
            };
            buttonContainer.appendChild(cancelButton);
            modalContent.appendChild(buttonContainer);
        }

        document.getElementById('confirm-message-text').textContent = message;
        modalOverlay.style.display = 'flex';
    }

}); // End of document.ready //for pull request 