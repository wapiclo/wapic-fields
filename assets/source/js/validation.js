(function () {
  class WapicFieldValidation {
    constructor() {
      this.submitNotices = new WeakMap();
      this.invalidInputs = new WeakMap();
      this.init();
    }

    /*------------------------------------------------
    | Helpers
    ------------------------------------------------*/
    updateTabWarnings(form, invalidInputs) {
      const activeErrors = Array.from(invalidInputs).filter((input) => input.isConnected && !input.disabled && !input.closest('[hidden]'));
      let firstInvalidLink = null;
      form.querySelectorAll('.wcf-tabs-nav a').forEach((link) => {
        const container = link.closest('.wcf-tabs');
        const targetId = (link.getAttribute('href') || '').slice(1);
        const panel = Array.from(container.querySelectorAll('.wcf-tab-content')).find((element) => element.id === targetId && element.closest('.wcf-tabs') === container);
        const count = panel ? activeErrors.filter((input) => panel.contains(input)).length : 0;
        let badge = link.querySelector('.wcf-tab-warning');
        link.parentElement.classList.toggle('has-tab-error', count > 0);
        if (!count) {
          if (badge) badge.remove();
          return;
        }
        if (!firstInvalidLink) firstInvalidLink = link;
        if (!badge) {
          badge = document.createElement('span');
          badge.className = 'wcf-tab-warning';
          const icon = document.createElement('span');
          icon.className = 'dashicons dashicons-warning';
          icon.setAttribute('aria-hidden', 'true');
          const number = document.createElement('span');
          number.className = 'wcf-tab-error-count';
          number.setAttribute('aria-hidden', 'true');
          const description = document.createElement('span');
          description.className = 'screen-reader-text';
          badge.append(icon, number, description);
          link.appendChild(badge);
        }
        const message = wapic_field.validation.tabErrors.replace('%s', count);
        badge.querySelector('.wcf-tab-error-count').textContent = count;
        badge.querySelector('.screen-reader-text').textContent = message;
        badge.title = message;
      });
      return firstInvalidLink;
    }

    clearSubmitNotice(form) {
      const notice = this.submitNotices.get(form);
      if (notice) notice.remove();
      this.submitNotices.delete(form);
    }

    focusErrorField(input) {
      if (!input.isConnected || input.disabled || input.closest('[hidden]')) return;
      const ancestors = [];
      for (let parent = input.parentElement; parent; parent = parent.parentElement) ancestors.unshift(parent);
      ancestors.forEach((element) => {
        if (element.classList.contains('wcf-tab-content')) {
          const container = element.closest('.wcf-tabs');
          const link = container && Array.from(container.querySelectorAll('.wcf-tabs-nav a')).find((item) => item.closest('.wcf-tabs') === container && item.getAttribute('href') === '#' + element.id);
          if (link) link.click();
        }
        if (element.matches('.wcf-repeater-row.is-collapsed')) {
          const toggle = element.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-collapse');
          if (toggle) toggle.click();
        }
      });
      requestAnimationFrame(() => {
        const field = input.closest('.wcf-field');
        const editor = window.tinymce && window.tinymce.get(input.id);
        const code = field.querySelector('.CodeMirror');
        if (input.classList.contains('select2-hidden-accessible') && window.jQuery && window.jQuery.fn.select2) {
          window.jQuery(input).select2('open');
        } else if (editor && !editor.isHidden()) {
          editor.focus();
        } else if (code && code.CodeMirror) {
          code.CodeMirror.focus();
        } else {
          const control = input.type === 'hidden' ? field.querySelector('button:not(:disabled), input:not([type="hidden"]):not(:disabled), select:not(:disabled), textarea:not(:disabled)') : input;
          if (control) control.focus({ preventScroll: true });
        }
        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    }

    showSubmitNotice(form, messages) {
      const notice = document.createElement('div');
      notice.className = 'notice notice-error wcf-validation-notice';
      notice.setAttribute('role', 'alert');
      notice.tabIndex = -1;
      const summary = document.createElement('p');
      summary.textContent = wapic_field.validation.submitFailed;
      notice.appendChild(summary);
      if (messages.length) {
        const list = document.createElement('ol');
        messages.forEach((message) => {
          const item = document.createElement('li');
          const link = document.createElement('a');
          link.href = '#' + message.input.id;
          link.textContent = message.text;
          link.addEventListener('click', (event) => {
            event.preventDefault();
            this.focusErrorField(message.input);
          });
          item.appendChild(link);
          list.appendChild(item);
        });
        notice.appendChild(list);
      }
      form.before(notice);
      this.submitNotices.set(form, notice);
      notice.focus({ preventScroll: true });
      notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    updateRepeaterWarning(row) {
      if (!row) return;
      const hasError = Array.from(row.querySelectorAll('.wcf-field.has-field-error')).some((field) => {
        return !field.closest('[hidden]') && Array.from(field.querySelectorAll('input, textarea, select')).some((input) => !input.disabled);
      });
      const warning = row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-warning');
      if (warning) warning.hidden = !hasError;
      row.classList.toggle('has-validation-warning', hasError);
    }

    showError(input, message) {
      const field = input.closest(".wcf-field");
      const error = document.getElementById(`${input.id}_error`);
      if (!error) return;

      field.classList.add("has-field-error");
      const row = input.closest('.wcf-repeater-row.is-collapsed');
      if (row) {
        const toggle = row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-collapse');
        if (toggle) toggle.click();
      }
      error.textContent = message;
      error.style.display = "block";
      this.updateRepeaterWarning(input.closest('.wcf-repeater-row'));
    }

    clearError(input) {
      const field = input.closest(".wcf-field");
      const error = document.getElementById(`${input.id}_error`);
      if (!error) return;

      field.classList.remove("has-field-error");
      error.textContent = "";
      error.style.display = "none";
      this.updateRepeaterWarning(input.closest('.wcf-repeater-row'));
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
      if (input.disabled || input.closest("[hidden]")) return true;
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

        // Only validate if sale price has a value
        if (saleInput && saleInput.value.trim() !== '') {
          this.showError(regularInput, wapic_field.validation.compareRegularPrice);
          this.showError(saleInput, wapic_field.validation.compareSalePrice);

          if (regularInput && this.isNumber(regularInput.value) && this.isNumber(saleInput.value)) {
            if (parseFloat(saleInput.value) > parseFloat(regularInput.value)) {
              this.showError(regularInput, wapic_field.validation.compareRegularPrice);
              this.showError(saleInput, wapic_field.validation.compareSalePrice);
              return false;
            } else {
              this.clearError(regularInput);
              this.clearError(saleInput);
            }
          }
        } else {
          // Clear any existing errors if sale price is empty
          if (regularInput) this.clearError(regularInput);
          if (saleInput) this.clearError(saleInput);
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
      this.clearSubmitNotice(form);
      const inputs = form.querySelectorAll(".wcf-field input, .wcf-field textarea, .wcf-field select");
      let allValid = true;
      let errorMessages = [];
      const invalidInputs = new Set();

      inputs.forEach((input) => {
        if (!this.validateField(input)) {
          allValid = false;
          invalidInputs.add(input);

          const errorElement = document.getElementById(`${input.id}_error`);
          const errorText = errorElement ? errorElement.textContent.trim() : "";

          const labelEl = input.labels && input.labels[0];
          const labelText = labelEl ? labelEl.textContent.trim() : input.name || input.id;

          // Include row headings so identical child labels remain distinguishable.
          const headings = [];
          for (let row = input.closest('.wcf-repeater-row'); row; row = row.parentElement.closest('.wcf-repeater-row')) {
            const heading = row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-title');
            if (heading) headings.unshift(heading.textContent.trim());
          }
          headings.push(labelText);

          if (errorText !== "") {
            errorMessages.push({ input: input, text: `${headings.join(' / ')}: ${errorText}` });
          }
        }
      });

      this.invalidInputs.set(form, invalidInputs);
      const firstInvalidTab = this.updateTabWarnings(form, invalidInputs);
      if (!allValid) {
        e.preventDefault();
        // Reuse the existing tab engine and follow navigation order on every submit.
        if (firstInvalidTab) firstInvalidTab.click();

        this.showSubmitNotice(form, errorMessages);
      }
    }

    /*------------------------------------------------
    | Event Handlers
    ------------------------------------------------*/
    onInputChange(e) {
      if (e.target.matches(".wcf-field input, .wcf-field textarea, .wcf-field select")) {
        const valid = this.validateField(e.target);
        const form = e.target.closest('form');
        const invalidInputs = form && this.invalidInputs.get(form);
        if (invalidInputs) {
          if (valid) invalidInputs.delete(e.target);
          else invalidInputs.add(e.target);
          this.updateTabWarnings(form, invalidInputs);
        }
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
        // Repeater rows start collapsed. Validate their children on edit or
        // submit so a required field does not open every row during page load.
        if (input.closest(".wcf-repeater-row")) return;
        this.validateField(input);
      });
    }
  }

  // Initialize on DOM ready
  document.addEventListener("DOMContentLoaded", () => {
    new WapicFieldValidation();
  });
})();
