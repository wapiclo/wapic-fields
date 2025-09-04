(function () {
    // Handle conditional fields
    function handleConditionalFields() {
        document.querySelectorAll('[data-condition-field], [data-cond-field]').forEach(function (wrapper) {
            var condField = wrapper.getAttribute('data-condition-field') || wrapper.getAttribute('data-cond-field');
            var condValue = wrapper.getAttribute('data-condition-value') || wrapper.getAttribute('data-cond-value');

            if (!condField) return;

            var trigger = document.getElementById(condField);
            if (!trigger) {
                trigger = document.querySelector('[name="' + condField + '"]');
            }
            if (!trigger) return;

            var currentValue = '';
            if (trigger.type === 'checkbox') {
                currentValue = trigger.checked ? trigger.value : '';
            } else if (trigger.type === 'radio') {
                var checkedRadio = document.querySelector('[name="' + condField + '"]:checked');
                currentValue = checkedRadio ? checkedRadio.value : '';
            } else {
                currentValue = trigger.value;
            }

            if (String(currentValue) === String(condValue)) {
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
        });
    }

    function conditionalFieldsInit() {
        handleConditionalFields();

        document.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.addEventListener('change', handleConditionalFields);
        });
    }

    document.addEventListener('DOMContentLoaded', conditionalFieldsInit);
})();
