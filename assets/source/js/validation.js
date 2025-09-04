(function () {
    /*------------------------------------------------
   | Helpers
   ------------------------------------------------*/
    function showError(input, message) {
        var field = input.closest('.wcf-field');
        var error = document.getElementById(input.id + '_error');
        if (!error) return;

        field.classList.add('has-field-error');
        error.textContent = message;
        error.style.display = 'block';
    }

    function clearError(input) {
        var field = input.closest('.wcf-field');
        var error = document.getElementById(input.id + '_error');
        if (!error) return;

        field.classList.remove('has-field-error');
        error.textContent = '';
        error.style.display = 'none';
    }

    function isNumber(val) {
        return /^\d+(\.\d+)?$/.test(val.trim());
    }

    function isEmail(val) {
        return /^\S+@\S+\.\S+$/.test(val.trim());
    }

    function isURL(val) {
        return /^https?:\/\//i.test(val) || /^#[\w-]*$/.test(val);
    }

    /*------------------------------------------------
    | Field Validation
    ------------------------------------------------*/
    function validateField(input) {
        var val = input.value.trim();
        var required = input.hasAttribute('required') || input.hasAttribute('data-required');

        // Required check
        if (required && val === '') {
            showError(input, wapic_field.validation.requiredMessage);
            return false;
        }

        // Skip if empty and not required
        if (val === '') {
            clearError(input);
            return true;
        }

        // Email
        if (input.classList.contains('wcf-field-email') && !isEmail(val)) {
            showError(input, wapic_field.validation.validEmail);
            return false;
        }

        // Number / Phone
        if (input.classList.contains('wcf-field-number') || input.classList.contains('wcf-field-phone')) {
            if (!isNumber(val)) {
                showError(input, wapic_field.validation.validNumber);
                return false;
            }
            const min = input.getAttribute('min');
            const max = input.getAttribute('max');
            const numVal = parseFloat(val);
            if (min !== null && numVal < parseFloat(min)) {
                showError(input, wapic_field.validation.minNumber.replace('%s', min));
                return false;
            }
            if (max !== null && numVal > parseFloat(max)) {
                showError(input, wapic_field.validation.maxNumber.replace('%s', max));
                return false;
            }
        }

        // URL
        if (input.classList.contains('wcf-field-url') && !isURL(val)) {
            showError(input, wapic_field.validation.validUrl);
            return false;
        }

        // File URL
        if (input.classList.contains('wcf-field-file-url') && !isURL(val)) {
            showError(input, wapic_field.validation.validUrl);
            return false;
        }

        // Price compare
        var isRegular = input.closest('.wcf-field').classList.contains('regular-price');
        var isSale = input.closest('.wcf-field').classList.contains('sale-price');

        if (isRegular || isSale) {
            var regularInput = document.querySelector('.regular-price input');
            var saleInput = document.querySelector('.sale-price input');

            showError(regularInput, wapic_field.validation.compareRegularPrice);
            showError(saleInput, wapic_field.validation.compareSalePrice);

            if (regularInput && saleInput && isNumber(regularInput.value) && isNumber(saleInput.value)) {
                if (parseFloat(saleInput.value) > parseFloat(regularInput.value)) {
                    showError(regularInput, wapic_field.validation.compareRegularPrice);
                    showError(saleInput, wapic_field.validation.compareSalePrice);
                    return false;
                } else {
                    clearError(regularInput);
                    clearError(saleInput);
                }
            }
        }

        clearError(input);
        return true;
    }

    /*------------------------------------------------
    | Form Submit Handler
    ------------------------------------------------*/
    function handleFormSubmit(e) {
        var form = e.target;
        var inputs = form.querySelectorAll('.wcf-field input');
        var allValid = true;
        var errorMessages = [];

        inputs.forEach(function (input) {
            if (!validateField(input)) {
                allValid = false;

                var errorElement = document.getElementById(input.id + '_error');
                var errorText = errorElement ? errorElement.textContent.trim() : '';

                var labelEl = document.querySelector('label[for="' + input.id + '"]');
                var labelText = labelEl ? labelEl.textContent.trim() : input.name || input.id;

                if (errorText !== '') {
                    errorMessages.push(labelText + ': ' + errorText);
                }
            }
        });

        if (!allValid) {
            e.preventDefault();

            // remove duplicate messages
            errorMessages = [...new Set(errorMessages)];

            var globalMessage = wapic_field.validation.submitFailed + '\n\n' +
                errorMessages.map(function (msg, i) {
                    return (i + 1) + '. ' + msg;
                }).join('\n');

            alert(globalMessage);

            var firstError = form.querySelector('.has-field-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    /*------------------------------------------------
    | Init
    ------------------------------------------------*/
    function formValidationInit() {
        // Event delegation for input changes
        document.addEventListener('input', function (e) {
            if (e.target.matches('.wcf-field input')) {
                validateField(e.target);
            }
        });

        // Handle submit validation
        document.querySelectorAll('form#post, form#option, form#addtag, form#edittag').forEach(function (form) {
            form.addEventListener('submit', handleFormSubmit);
        });

        // Initial validation on page load
        document.querySelectorAll('.wcf-field input').forEach(validateField);
    }

    document.addEventListener('DOMContentLoaded', formValidationInit);
})();
