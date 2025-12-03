    // Handle conditional fields
    function WapicFieldConditionalFields() {
        // Initial check for all fields
        document.querySelectorAll('[data-condition-field], [data-cond-field]').forEach(function (wrapper) {
            checkCondition(wrapper);
        });
    }

    function checkCondition(wrapper) {
        var condField = wrapper.getAttribute('data-condition-field') || wrapper.getAttribute('data-cond-field');
        var condValue = wrapper.getAttribute('data-condition-value') || wrapper.getAttribute('data-cond-value');
        var operator = wrapper.getAttribute('data-condition-operator') || '==';

        if (!condField) return;

        // Find trigger element
        // 1. Try by ID
        var trigger = document.getElementById(condField);
        
        // 2. Try by name (more robust for repeaters)
        if (!trigger) {
            // If we are inside a repeater row, try to find the field within the same row first
            var row = wrapper.closest('.wcf-repeater-row');
            if (row) {
                // Try to find input with name ending in [condField]
                trigger = row.querySelector('[name$="[' + condField + ']"]');
                if (!trigger) {
                     // Try to find input with data-field-id
                     trigger = row.querySelector('[data-field-id="' + condField + '"] input, [data-field-id="' + condField + '"] select, [data-field-id="' + condField + '"] textarea');
                }
            }
        }

        // 3. Fallback to global query
        if (!trigger) {
            trigger = document.querySelector('[name="' + condField + '"]');
        }
        
        if (!trigger) return;

        var currentValue = '';
        if (trigger.type === 'checkbox') {
            currentValue = trigger.checked ? trigger.value : '';
            // If checkbox is unchecked, value is usually empty or 0, but let's handle "yes" convention
            if (!trigger.checked && trigger.value === 'yes') currentValue = ''; 
        } else if (trigger.type === 'radio') {
            // For radio, we need to find the checked one in the same group
            var radioName = trigger.name;
            var checkedRadio = document.querySelector('input[name="' + radioName + '"]:checked');
            currentValue = checkedRadio ? checkedRadio.value : '';
        } else {
            currentValue = trigger.value;
        }

        var isMatch = false;
        if (operator === '==') {
            isMatch = String(currentValue) === String(condValue);
        } else if (operator === '!=') {
            isMatch = String(currentValue) !== String(condValue);
        }
        // Add more operators if needed

        if (isMatch) {
            wrapper.hidden = false;
            // Restore original input types
            wrapper.querySelectorAll('input[data-original-type]').forEach(function (input) {
                input.type = input.getAttribute('data-original-type');
                input.removeAttribute('data-original-type');
                // Remove hidden flag if it exists
                const hiddenFlag = wrapper.querySelector('input[name="' + input.name + '_is_hidden"]');
                if (hiddenFlag) {
                    hiddenFlag.remove();
                }
            });
        } else {
            wrapper.hidden = true;
            // Store original input type and change to hidden
            wrapper.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
                if (!input.hasAttribute('data-original-type')) {
                    input.setAttribute('data-original-type', input.type);
                    input.type = 'hidden';
                    // Add hidden flag for the server-side
                    const hiddenFlag = document.createElement('input');
                    hiddenFlag.type = 'hidden';
                    hiddenFlag.name = input.name + '_is_hidden';
                    hiddenFlag.value = '1';
                    wrapper.appendChild(hiddenFlag);
                }
            });
        }
    }

    function WapicFieldConditionalFieldsInit() {
        WapicFieldConditionalFields();

        // Use event delegation for dynamic fields
        document.addEventListener('change', function(e) {
            var target = e.target;
            if (target.matches('input, select, textarea')) {
                // When any input changes, re-check all conditions
                // Optimization: We could try to find only dependent fields, but checking all is safer for now
                WapicFieldConditionalFields();
            }
        });
        
        // Also listen for input event for immediate feedback on text inputs if desired, 
        // but change is usually enough for conditional logic triggers (select, radio, checkbox)
    }

    document.addEventListener('DOMContentLoaded', WapicFieldConditionalFieldsInit);