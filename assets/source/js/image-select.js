(function($) {
    'use strict';

    var initImageSelect = function() {
        $('.wcf-image-select').each(function() {
            var $wrapper = $(this);
            $wrapper.find('.wcf-image-select__input:checked').closest('.wcf-image-select__item').addClass('is-selected');
        });
    };

    $(document).ready(function() {
        initImageSelect();

        $(document).on('change', '.wcf-image-select__input', function() {
            var $input = $(this);
            var $wrapper = $input.closest('.wcf-image-select');
            $wrapper.find('.is-selected').removeClass('is-selected');
            if ($input.is(':checked')) {
                $input.closest('.wcf-image-select__item').addClass('is-selected');
            }
        });
    });

    // Re-init if needed (for AJAX or other dynamic loading)
    $(document).on('wcf_fields_initialized', function() {
        initImageSelect();
    });

})(jQuery);
