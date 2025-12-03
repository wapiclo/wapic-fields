/**
 * Initialize WordPress Color Picker for all color fields.
 *
 * @since 1.3.0
 */
(function () {
  function WapicFieldColorPickerInit() {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
      document.querySelectorAll(".wcf-field-color").forEach(function (el) {
        window.jQuery(el).wpColorPicker({
          showAlpha: true,
          preferredFormat: "rgba",
        });
      });
    }
  }
  document.addEventListener("DOMContentLoaded", WapicFieldColorPickerInit);
})();
