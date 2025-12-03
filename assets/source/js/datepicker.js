/**
 * Initialize jQuery UI Datepicker for date fields.
 *
 * @since 1.3.0
 */
(function () {
  function WapicFieldDatePickerInit() {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.datepicker) {
      document.querySelectorAll(".wcf-field-date").forEach(function (el) {
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
  document.addEventListener("DOMContentLoaded", WapicFieldDatePickerInit);
})();
