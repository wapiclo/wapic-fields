(function () {
    function colorPickerInit() {
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
            document.querySelectorAll('.wcf-field .wcf-field-color').forEach(function (el) {
                window.jQuery(el).wpColorPicker({
                    showAlpha: true,
                    preferredFormat: 'rgba'
                });
            });
        }
    }
    document.addEventListener('DOMContentLoaded', colorPickerInit);
})();