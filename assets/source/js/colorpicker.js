function WapicFieldColorPickerInit() {
  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
    document.querySelectorAll(".wcf-field-color").forEach(function (el) {
      if (el.closest(".wcf-repeater-template")) return;

      window.jQuery(el).wpColorPicker({
        showAlpha: true,
        preferredFormat: "rgba",
      });
    });
  }
}
document.addEventListener("DOMContentLoaded", WapicFieldColorPickerInit);
