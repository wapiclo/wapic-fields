/**
 * Initialize WordPress Color Picker for all color fields.
 *
 * @since 1.3.0
 */
(function () {
  function WapicFieldColorPickerInit(event) {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
      (event && event.detail && event.detail.root || document).querySelectorAll(".wcf-field-color").forEach(function (el) {
        if (el.classList.contains("wp-color-picker")) return;
        window.jQuery(el).wpColorPicker({
          showAlpha: true,
          preferredFormat: "rgba",
        });
      });
    }
  }
  document.addEventListener("DOMContentLoaded", WapicFieldColorPickerInit);
  document.addEventListener("wcf_fields_initialized", WapicFieldColorPickerInit);
})();
