// Initialize Select2
(function () {
    function select2Init() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') return;

        jQuery('.wcf-field-select2').each(function () {
            const $select = jQuery(this);
            const $field = $select.closest('.wcf-field');
            const $hidden = $field.find('input[type="hidden"]');
            const placeholder = $select.data('placeholder') || 'Select an option';
            const allowClear = $select.data('allow-clear') === true || $select.data('allow-clear') === 'true';
            const width = $select.data('width') || '100%';
            const isMultiple = $select.attr('multiple') === 'multiple';

            // Basic configuration for Select2 4.0.13
            const options = {
                placeholder: placeholder,
                allowClear: allowClear,
                width: 'style', // Use style width instead of fixed width
                dropdownParent: $select.closest('.wcf-field')
            };

            // Apply the width directly to the element
            $select.css('width', width);

            // Initialize Select2
            $select.select2(options);

            // Handle multiple select values
            if (isMultiple) {
                // Ensure the select has a name attribute for form submission
                const originalName = $select.attr('name');
                if (originalName && !originalName.endsWith('[]')) {
                    $select.attr('name', originalName + '[]');
                }

                // Set initial value from hidden input
                if ($hidden.length) {
                    const initialValue = $hidden.val();
                    if (initialValue) {
                        const values = initialValue.split(',').map(v => v.trim()).filter(Boolean);
                        $select.val(values).trigger('change');
                    }
                }
            }

            // Update hidden field when selection changes
            $select.on('change', function () {
                const values = $select.val() || [];
                const valueString = Array.isArray(values) ? values.join(',') : values;
                $hidden.val(valueString);
            });
        });

        // Ensure form submission handles Select2 fields correctly
        jQuery(document).on('submit', 'form', function () {
            jQuery('.wcf-field-select2[multiple]').each(function () {
                const $select = jQuery(this);
                const $field = $select.closest('.wcf-field');
                const $hidden = $field.find('input[type="hidden"]');
                const values = $select.val() || [];
                $hidden.val(Array.isArray(values) ? values.join(',') : values);
            });
            return true;
        });
    }
    document.addEventListener('DOMContentLoaded', select2Init);
})();
