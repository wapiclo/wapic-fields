function wapiFieldColorPickerInit() {
  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.wpColorPicker) {
    document.querySelectorAll(".wcf-field-color").forEach(function (el) {
      var fieldWrap = el.closest(".wcf-field");
      if (fieldWrap && fieldWrap.classList.contains("wcf-repeater-field"))
        return;

      window.jQuery(el).wpColorPicker({
        showAlpha: true,
        preferredFormat: "rgba",
      });
    });
  }
}
document.addEventListener("DOMContentLoaded", wapiFieldColorPickerInit);
