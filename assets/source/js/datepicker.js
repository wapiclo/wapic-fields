// Function to initialize datepicker (using jQuery)
function wapicFieldDatePickerInit() {
  if (window.jQuery && window.jQuery.fn && window.jQuery.fn.datepicker) {
    document
      .querySelectorAll(".wcf-field-date")
      .forEach(function (el) {
        if (el.closest(".wcf-repeater-template")) return;
        jQuery(el).datepicker({
          dateFormat: "yy-mm-dd",
          changeMonth: true,
          changeYear: true,
          showButtonPanel: true,
          beforeShow: function (input, inst) {
            setTimeout(function () {
              jQuery(inst.dpDiv).addClass("wcf-datepicker-theme");
            }, 0);
          },
        });
      });
  }
}
// Initialize the datepicker
document.addEventListener("DOMContentLoaded", wapicFieldDatePickerInit);
