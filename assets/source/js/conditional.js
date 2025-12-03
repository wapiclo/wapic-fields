/**
 * Handle conditional field visibility based on other field values.
 *
 * @since 1.3.0
 */
(function () {
  /**
   * Check and apply conditional logic for all conditional fields.
   */
  function WapicFieldConditionalFields() {
    document.querySelectorAll("[data-condition-field], [data-cond-field]").forEach(function (wrapper) {
      checkCondition(wrapper);
    });
  }

  /**
   * Check condition for a single field wrapper.
   *
   * @param {HTMLElement} wrapper - The field wrapper element
   */
  function checkCondition(wrapper) {
    const condField = wrapper.getAttribute("data-condition-field") || wrapper.getAttribute("data-cond-field");
    const condValue = wrapper.getAttribute("data-condition-value") || wrapper.getAttribute("data-cond-value");
    const operator = wrapper.getAttribute("data-condition-operator") || "==";

    if (!condField) return;

    // Find trigger element by ID or name
    let trigger = document.getElementById(condField);

    if (!trigger) {
      trigger = document.querySelector('[name="' + condField + '"]');
    }

    if (!trigger) return;

    let currentValue = "";
    if (trigger.type === "checkbox") {
      currentValue = trigger.checked ? trigger.value : "";
    } else if (trigger.type === "radio") {
      const radioName = trigger.name;
      const checkedRadio = document.querySelector('input[name="' + radioName + '"]:checked');
      currentValue = checkedRadio ? checkedRadio.value : "";
    } else {
      currentValue = trigger.value;
    }

    let isMatch = false;
    if (operator === "==") {
      isMatch = String(currentValue) === String(condValue);
    } else if (operator === "!=") {
      isMatch = String(currentValue) !== String(condValue);
    }

    if (isMatch) {
      wrapper.hidden = false;
      // Restore original input types
      wrapper.querySelectorAll("input[data-original-type]").forEach(function (input) {
        input.type = input.getAttribute("data-original-type");
        input.removeAttribute("data-original-type");
        const hiddenFlag = wrapper.querySelector('input[name="' + input.name + '_is_hidden"]');
        if (hiddenFlag) {
          hiddenFlag.remove();
        }
      });
    } else {
      wrapper.hidden = true;
      // Store original input type and change to hidden
      wrapper.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
        if (!input.hasAttribute("data-original-type")) {
          input.setAttribute("data-original-type", input.type);
          input.type = "hidden";
          const hiddenFlag = document.createElement("input");
          hiddenFlag.type = "hidden";
          hiddenFlag.name = input.name + "_is_hidden";
          hiddenFlag.value = "1";
          wrapper.appendChild(hiddenFlag);
        }
      });
    }
  }

  /**
   * Initialize conditional fields functionality.
   */
  function WapicFieldConditionalFieldsInit() {
    WapicFieldConditionalFields();

    // Use event delegation for dynamic fields
    document.addEventListener("change", function (e) {
      const target = e.target;
      if (target.matches("input, select, textarea")) {
        WapicFieldConditionalFields();
      }
    });
  }

  document.addEventListener("DOMContentLoaded", WapicFieldConditionalFieldsInit);
})();
