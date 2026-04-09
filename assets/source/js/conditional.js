/**
 * Handle conditional field visibility based on other field values.
 *
 * @since 1.3.0
 */
(function () {
  /**
   * Check and apply conditional logic for all conditional fields.
   */
  /**
   * Check and apply conditional logic for all conditional fields.
   */
  function WapicFieldConditionalFields() {
    document.querySelectorAll("[data-condition-field], [data-cond-field], [data-conditions]").forEach(function (wrapper) {
      checkCondition(wrapper);
    });
  }

  /**
   * Get the value of a field by ID or name.
   *
   * @param {string} fieldId - The field ID or name
   * @returns {string|Array|null}
   */
  function getFieldValue(fieldId) {
    let trigger = document.getElementById(fieldId);

    if (!trigger) {
      trigger = document.querySelector('[name="' + fieldId + '"]');
    }

    if (!trigger) return null;

    // IF the trigger's wrapper is hidden, treat the value as empty (for cascading)
    const wrapper = trigger.closest(".wcf-field-conditional");
    if (wrapper && wrapper.hidden) {
      return "";
    }

    if (trigger.type === "checkbox") {
      return trigger.checked ? trigger.value : "";
    } else if (trigger.type === "radio") {
      const radioName = trigger.name;
      const checkedRadio = document.querySelector('input[name="' + radioName + '"]:checked');
      return checkedRadio ? checkedRadio.value : "";
    } else if (trigger.tagName === "SELECT" && trigger.multiple) {
      return Array.from(trigger.selectedOptions).map((opt) => opt.value);
    }

    return trigger.value;
  }

  /**
   * Evaluate a single condition.
   *
   * @param {Object} cond - The condition object
   * @returns {boolean}
   */
  function evaluateSingleCondition(cond) {
    const field = cond.field || cond.id;
    const condValue = cond.value;
    const operator = cond.operator || cond.compare || "==";

    const currentValue = getFieldValue(field);

    if (currentValue === null) return false;

    switch (operator) {
      case "==":
      case "=":
        return String(currentValue) === String(condValue);
      case "!=":
      case "<>":
        return String(currentValue) !== String(condValue);
      case ">":
        return parseFloat(currentValue) > parseFloat(condValue);
      case "<":
        return parseFloat(currentValue) < parseFloat(condValue);
      case ">=":
        return parseFloat(currentValue) >= parseFloat(condValue);
      case "<=":
        return parseFloat(currentValue) <= parseFloat(condValue);
      case "IN":
        return Array.isArray(condValue)
          ? condValue.map(String).includes(String(currentValue))
          : String(condValue).split(",").map(s => s.trim()).includes(String(currentValue));
      case "NOT IN":
        return Array.isArray(condValue)
          ? !condValue.map(String).includes(String(currentValue))
          : !String(condValue).split(",").map(s => s.trim()).includes(String(currentValue));
      default:
        return String(currentValue) === String(condValue);
    }
  }

  /**
   * Check condition for a single field wrapper.
   *
   * @param {HTMLElement} wrapper - The field wrapper element
   */
  function checkCondition(wrapper) {
    const conditionsData = wrapper.getAttribute("data-conditions");
    let conditions = [];
    let relation = "AND";

    if (conditionsData) {
      try {
        const parsed = JSON.parse(conditionsData);
        if (parsed.relation) {
          relation = parsed.relation.toUpperCase();
        }

        // Handle both array and object format (PHP might encode indexed array with string keys as object)
        conditions = Object.keys(parsed)
          .filter((key) => key !== "relation")
          .map((key) => parsed[key]);
      } catch (e) {
        console.error("Wapic Fields: Failed to parse conditions", e);
      }
    } else {
      const condField = wrapper.getAttribute("data-condition-field") || wrapper.getAttribute("data-cond-field");
      const condValue = wrapper.getAttribute("data-condition-value") || wrapper.getAttribute("data-cond-value");
      const operator = wrapper.getAttribute("data-condition-operator") || "==";

      if (condField) {
        conditions.push({
          field: condField,
          value: condValue,
          operator: operator,
        });
      }
    }

    if (conditions.length === 0) return;

    let isMatch = relation === "AND";
    conditions.forEach(function (cond) {
      const match = evaluateSingleCondition(cond);
      if (relation === "AND") {
        isMatch = isMatch && match;
      } else {
        isMatch = isMatch || match;
      }
    });

    if (isMatch) {
      if (wrapper.hidden) {
        wrapper.hidden = false;
        // Small delay to ensure children inputs are visible before re-running
        setTimeout(function() {
          WapicFieldConditionalFields();
        }, 10);
      }
      // Restore original input types
      wrapper.querySelectorAll("[data-original-type]").forEach(function (input) {
        if (input.tagName === 'INPUT') {
            input.type = input.getAttribute("data-original-type");
        } else {
            input.style.display = '';
        }
        input.removeAttribute("data-original-type");
        input.removeAttribute("data-is-hidden");
        input.disabled = false;

        const hiddenFlag = wrapper.querySelector('input[name="' + input.name + '_is_hidden"]');
        if (hiddenFlag) {
          hiddenFlag.remove();
        }
      });
    } else {
      if (!wrapper.hidden) {
        wrapper.hidden = true;
        // Small delay to ensure children inputs are hidden before re-running
        setTimeout(function() {
          WapicFieldConditionalFields();
        }, 10);
      }
      // Store original input type and change to hidden
      wrapper.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(function (input) {
        if (!input.hasAttribute("data-original-type")) {
          input.setAttribute("data-original-type", input.type || input.tagName.toLowerCase());
          
          if (input.tagName === 'INPUT') {
            input.type = "hidden";
          } else {
            input.style.display = 'none';
          }

          input.setAttribute("data-is-hidden", "true");
          input.disabled = true;

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

/**
 * Handle recursive display for headings and dividers inside conditional wrappers.
 */
document.addEventListener('DOMContentLoaded', function() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'hidden') {
                const wrapper = mutation.target;
                const isHidden = wrapper.hidden;
                // Find headings and dividers inside and sync their visibility if needed
                // (Though they are inside the wrapper, so they should hide anyway)
            }
        });
    });

    document.querySelectorAll('.wcf-field-conditional').forEach(function(el) {
        observer.observe(el, { attributes: true });
    });
});
