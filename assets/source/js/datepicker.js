/**
 * Initialize jQuery UI Datepicker for date fields.
 *
 * @since 1.3.0
 */
(function () {
  function WapicFieldDatePickerInit(event) {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.datepicker) {
      (event && event.detail && event.detail.root || document).querySelectorAll(".wcf-field-date").forEach(function (el) {
        if (el.classList.contains("hasDatepicker")) return;
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
  document.addEventListener("wcf_fields_initialized", WapicFieldDatePickerInit);
})();
