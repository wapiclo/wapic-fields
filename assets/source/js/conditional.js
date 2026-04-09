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
        // Trigger re-check for other fields that might depend on fields inside this wrapper
        WapicFieldConditionalFields();
      }
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
      if (!wrapper.hidden) {
        wrapper.hidden = true;
        // Trigger re-check for other fields that might depend on fields inside this wrapper
        WapicFieldConditionalFields();
      }
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
