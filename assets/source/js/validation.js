(function () {
  class WapicFieldValidation {
    constructor() {
      this.init();
    }

    /*------------------------------------------------
    | Helpers
    ------------------------------------------------*/
    showError(input, message) {
      const field = input.closest(".wcf-field");
      const error = document.getElementById(`${input.id}_error`);
      if (!error) return;

      field.classList.add("has-field-error");
      error.textContent = message;
      error.style.display = "block";
    }

    clearError(input) {
      const field = input.closest(".wcf-field");
      const error = document.getElementById(`${input.id}_error`);
      if (!error) return;

      field.classList.remove("has-field-error");
      error.textContent = "";
      error.style.display = "none";
    }

    isNumber(val) {
      return /^\d+(\.\d+)?$/.test(val.trim());
    }

    isEmail(val) {
      return /^\S+@\S+\.\S+$/.test(val.trim());
    }

    isURL(val) {
      return /^https?:\/\//i.test(val) || /^#[\w-]*$/.test(val);
    }

    /*------------------------------------------------
    | Field Validation
    ------------------------------------------------*/
    validateField(input) {
      const val = input.value.trim();
      const required = input.hasAttribute("required") || input.hasAttribute("data-required");

      // Required check
      if (required && val === "") {
        this.showError(input, wapic_field.validation.requiredMessage);
        return false;
      }

      // Skip if empty and not required
      if (val === "") {
        this.clearError(input);
        return true;
      }

      // Email
      if (input.classList.contains("wcf-field-email") && !this.isEmail(val)) {
        this.showError(input, wapic_field.validation.validEmail);
        return false;
      }

      // Number / Phone
      if (input.classList.contains("wcf-field-number") || input.classList.contains("wcf-field-phone")) {
        if (!this.isNumber(val)) {
          this.showError(input, wapic_field.validation.validNumber);
          return false;
        }
        const min = input.getAttribute("min");
        const max = input.getAttribute("max");
        const numVal = parseFloat(val);
        if (min !== null && numVal < parseFloat(min)) {
          this.showError(input, wapic_field.validation.minNumber.replace("%s", min));
          return false;
        }
        if (max !== null && numVal > parseFloat(max)) {
          this.showError(input, wapic_field.validation.maxNumber.replace("%s", max));
          return false;
        }
      }

      // URL
      if (input.classList.contains("wcf-field-url") && !this.isURL(val)) {
        this.showError(input, wapic_field.validation.validUrl);
        return false;
      }

      // File URL
      if (input.classList.contains("wcf-field-file-url") && !this.isURL(val)) {
        this.showError(input, wapic_field.validation.validUrl);
        return false;
      }

      // Price compare
      const isRegular = input.closest(".wcf-field").classList.contains("regular-price");
      const isSale = input.closest(".wcf-field").classList.contains("sale-price");

      if (isRegular || isSale) {
        const regularInput = document.querySelector(".regular-price input");
        const saleInput = document.querySelector(".sale-price input");

        this.showError(regularInput, wapic_field.validation.compareRegularPrice);
        this.showError(saleInput, wapic_field.validation.compareSalePrice);

        if (regularInput && saleInput && this.isNumber(regularInput.value) && this.isNumber(saleInput.value)) {
          if (parseFloat(saleInput.value) > parseFloat(regularInput.value)) {
            this.showError(regularInput, wapic_field.validation.compareRegularPrice);
            this.showError(saleInput, wapic_field.validation.compareSalePrice);
            return false;
          } else {
            this.clearError(regularInput);
            this.clearError(saleInput);
          }
        }
      }

      this.clearError(input);
      return true;
    }

    /**
     * Handle form submission validation.
     *
     * @param {Event} e - Submit event
     */
    handleFormSubmit(e) {
      const form = e.target;
      const inputs = form.querySelectorAll(".wcf-field input, .wcf-field textarea, .wcf-field select");
      let allValid = true;
      let errorMessages = [];

      inputs.forEach((input) => {
        if (!this.validateField(input)) {
          allValid = false;

          const errorElement = document.getElementById(`${input.id}_error`);
          const errorText = errorElement ? errorElement.textContent.trim() : "";

          const labelEl = document.querySelector(`label[for="${input.id}"]`);
          const labelText = labelEl ? labelEl.textContent.trim() : input.name || input.id;

          if (errorText !== "") {
            errorMessages.push(`${labelText}: ${errorText}`);
          }
        }
      });

      if (!allValid) {
        e.preventDefault();

        // Remove duplicate messages
        errorMessages = [...new Set(errorMessages)];

        const globalMessage = `${wapic_field.validation.submitFailed}\n\n${errorMessages.map((msg, i) => `${i + 1}. ${msg}`).join("\n")}`;

        alert(globalMessage);

        const firstError = form.querySelector(".has-field-error");
        if (firstError) {
          firstError.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      }
    }

    /*------------------------------------------------
    | Event Handlers
    ------------------------------------------------*/
    onInputChange(e) {
      if (e.target.matches(".wcf-field input, .wcf-field textarea, .wcf-field select")) {
        this.validateField(e.target);
      }
    }

    onFormSubmit(e) {
      this.handleFormSubmit(e);
    }

    /*------------------------------------------------
    | Initialization
    ------------------------------------------------*/
    init() {
      // Event delegation for input changes
      document.addEventListener("input", this.onInputChange.bind(this));

      // Handle submit validation
      document.querySelectorAll("form#post, form#option, form#addtag, form#edittag").forEach((form) => {
        form.addEventListener("submit", (e) => this.onFormSubmit(e));
      });

      // Initial validation on page load
      document.querySelectorAll(".wcf-field input, .wcf-field textarea, .wcf-field select").forEach((input) => {
        this.validateField(input);
      });
    }
  }

  // Initialize on DOM ready
  document.addEventListener("DOMContentLoaded", () => {
    new WapicFieldValidation();
  });
})();
