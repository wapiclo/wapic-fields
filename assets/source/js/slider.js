jQuery(document).ready(function($) {
    $(document).on('input', '.wcf-slider', function() {
        var $slider = $(this);
        var $value = $slider.siblings('.wcf-slider-value');
        $value.text($slider.val());
    });
});
