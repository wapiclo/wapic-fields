/** Repeater rows use stable control IDs and sequential submitted array indexes. */
(function ($) {
  'use strict';

  let confirmation = null;
  let confirmationId = 0;

  function closeConfirmation(restoreFocus) {
    if (!confirmation) return;
    const trigger = confirmation.trigger;
    confirmation.popover.remove();
    trigger.setAttribute('aria-expanded', 'false');
    trigger.removeAttribute('aria-controls');
    confirmation = null;
    if (restoreFocus && trigger.isConnected) trigger.focus({ preventScroll: true });
  }

  function removeRow(trigger) {
    const repeater = trigger.closest('.wcf-repeater');
    if (!repeater || trigger.disabled) return;
    const list = rows(repeater);
    if (list.children.length <= Number(repeater.getAttribute('data-min-rows'))) return;
    const row = trigger.closest('.wcf-repeater-row');
    closeConfirmation(false);
    removeEditors(row);
    if ($.fn.select2) $(row).find('.wcf-field-select2.select2-hidden-accessible').select2('destroy');
    row.remove();
    refresh(repeater);
    repeater.querySelector(':scope > .wcf-repeater-add').focus();
  }

  function showConfirmation(trigger) {
    if (confirmation && confirmation.trigger === trigger) {
      closeConfirmation(true);
      return;
    }
    closeConfirmation(false);
    const repeater = trigger.closest('.wcf-repeater');
    const popover = document.createElement('div');
    const message = document.createElement('span');
    const remove = document.createElement('button');
    const cancel = document.createElement('button');
    popover.id = 'wcf-repeater-confirm-' + (++confirmationId);
    popover.className = 'wcf-repeater-confirm';
    popover.setAttribute('role', 'dialog');
    message.id = popover.id + '-message';
    message.className = 'wcf-repeater-confirm-message';
    message.textContent = repeater.getAttribute('data-confirm-message');
    popover.setAttribute('aria-labelledby', message.id);
    remove.type = cancel.type = 'button';
    remove.className = 'wcf-repeater-confirm-remove';
    cancel.className = 'wcf-repeater-confirm-cancel';
    remove.textContent = repeater.getAttribute('data-remove-label');
    cancel.textContent = repeater.getAttribute('data-cancel-label');
    remove.addEventListener('click', function () { removeRow(trigger); });
    cancel.addEventListener('click', function () { closeConfirmation(true); });
    popover.append(message, remove, cancel);
    document.body.appendChild(popover);
    const anchor = trigger.getBoundingClientRect();
    const bounds = popover.getBoundingClientRect();
    const center = anchor.left + anchor.width / 2;
    const left = Math.max(8, Math.min(center - bounds.width / 2, window.innerWidth - bounds.width - 8));
    const below = anchor.top - bounds.height - 8 < 8;
    popover.classList.toggle('is-below', below);
    popover.style.left = left + 'px';
    popover.style.top = (below ? anchor.bottom + 8 : anchor.top - bounds.height - 8) + 'px';
    popover.style.setProperty('--wcf-confirm-arrow-left', Math.max(10, Math.min(center - left, bounds.width - 10)) + 'px');
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'true');
    trigger.setAttribute('aria-controls', popover.id);
    confirmation = { popover: popover, trigger: trigger };
    cancel.focus({ preventScroll: true });
  }

  function rows(repeater) {
    return repeater.querySelector(':scope > .wcf-repeater-rows');
  }

  function notify(root) {
    document.dispatchEvent(new CustomEvent('wcf_fields_initialized', { detail: { root: root } }));
  }

  function label(repeater, row) {
    const template = repeater.getAttribute('data-row-label');
    const text = template.replace(/\{\{\s*([\w-]+)\s*\}\}/g, function (match, key) {
      const field = Array.from(row.querySelectorAll('[data-wcf-field-key]')).find(function (element) {
        return element.getAttribute('data-wcf-field-key') === key && element.closest('.wcf-repeater-row') === row;
      });
      if (!field) return '';
      const inputs = Array.from(field.querySelectorAll('input, select, textarea')).filter(input => input.closest('.wcf-field') === field);
      const input = inputs.find(control => control.type !== 'hidden') || inputs[0];
      if (!input) return '';
      if (input.type === 'checkbox' || input.type === 'radio') {
        return inputs.filter(control => control.checked).map(control => control.value).join(', ');
      }
      if (input.multiple) return Array.from(input.selectedOptions).map(option => option.textContent).join(', ');
      return input.value;
    }).trim();
    return text;
  }

  function refresh(repeater) {
    const list = rows(repeater);
    const prefix = repeater.getAttribute('data-name-prefix');
    const min = Number(repeater.getAttribute('data-min-rows'));
    const max = Number(repeater.getAttribute('data-max-rows'));
    Array.from(list.children).forEach(function (row, index) {
      const previous = prefix + '[' + row.getAttribute('data-row-index') + ']';
      const next = prefix + '[' + index + ']';
      row.querySelectorAll('[name], [data-name-prefix]').forEach(function (control) {
        ['name', 'data-name-prefix'].forEach(function (attribute) {
          const value = control.getAttribute(attribute);
          if (value && value.indexOf(previous + '[') === 0) {
            control.setAttribute(attribute, next + value.slice(previous.length));
          }
        });
      });
      // Keep nested repeater templates in sync with the new outer array index.
      row.querySelectorAll('template.wcf-repeater-template').forEach(function (template) {
        template.innerHTML = template.innerHTML.split(previous + '[').join(next + '[');
      });
      row.setAttribute('data-row-index', index);
      const rowLabel = label(repeater, row);
      const heading = rowLabel || (repeater.getAttribute('data-fallback-label') + ' - ' + (index + 1));
      row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-title').textContent = heading;
      const button = row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-delete');
      button.disabled = list.children.length <= min;
    });
    repeater.querySelector(':scope > .wcf-repeater-add').disabled = max > 0 && list.children.length >= max;
    document.dispatchEvent(new CustomEvent('wcf_repeater_updated'));
  }

  function initEditors(root) {
    if (!window.wp || !wp.editor || !wp.editor.initialize) return;
    root.querySelectorAll('.wcf-repeater textarea.wcf-field-editor').forEach(function (input) {
      if (input.closest('.wcf-repeater-row.is-collapsed')) return;
      if (input.closest('.wp-editor-wrap')) return;
      wp.editor.initialize(input.id, { tinymce: true, quicktags: true, mediaButtons: true });
    });
  }

  function setCollapsed(row, collapsed) {
    const toggle = row.querySelector(':scope > .wcf-repeater-header > .wcf-repeater-collapse');
    row.classList.toggle('is-collapsed', collapsed);
    toggle.setAttribute('aria-expanded', String(!collapsed));
    toggle.setAttribute('aria-label', toggle.getAttribute(collapsed ? 'data-expand-label' : 'data-collapse-label'));
    toggle.querySelector('.dashicons').className = 'dashicons ' + (collapsed ? 'dashicons-arrow-down-alt2' : 'dashicons-arrow-up-alt2');
  }

  function removeEditors(root) {
    root.querySelectorAll('textarea.wcf-field-editor').forEach(function (input) {
      if (window.tinymce) {
        const editor = tinymce.get(input.id);
        if (editor) editor.save();
      }
      if (window.wp && wp.editor && wp.editor.remove) wp.editor.remove(input.id);
    });
  }

  function initialize(repeater) {
    if (repeater.dataset.initialized) return;
    repeater.dataset.initialized = 'true';
    const list = rows(repeater);
    if (repeater.getAttribute('data-sortable') === 'true' && $.fn.sortable) {
      $(list).sortable({
        items: '> .wcf-repeater-row',
        handle: '> .wcf-repeater-header .wcf-repeater-handle',
        cancel: 'input, textarea, select, option, button:not(.wcf-repeater-handle)',
        placeholder: 'wcf-repeater-placeholder',
        forcePlaceholderSize: true,
        start: function (event, ui) {
          closeConfirmation(false);
          removeEditors(ui.item[0]);
        },
        stop: function (event, ui) {
          initEditors(ui.item[0]);
          refresh(repeater);
        }
      });
    }
    refresh(repeater);
  }

  document.addEventListener('wcf_fields_initialized', function (event) {
    const root = event.detail && event.detail.root || document;
    root.querySelectorAll('.wcf-repeater').forEach(initialize);
    initEditors(root);
  });

  $(function () {
    document.querySelectorAll('.wcf-repeater').forEach(initialize);
    initEditors(document);

    $(document).on('click', '.wcf-repeater-collapse', function () {
      const row = this.closest('.wcf-repeater-row');
      const collapsed = !row.classList.contains('is-collapsed');
      setCollapsed(row, collapsed);
      if (!collapsed) {
        notify(row);
        row.querySelectorAll('.CodeMirror').forEach(function (element) {
          if (element.CodeMirror) element.CodeMirror.refresh();
        });
      }
    });

    $(document).on('click', '.wcf-repeater-add', function () {
      const repeater = this.closest('.wcf-repeater');
      const list = rows(repeater);
      const max = Number(repeater.getAttribute('data-max-rows'));
      if (max > 0 && list.children.length >= max) return;
      const template = repeater.querySelector(':scope > .wcf-repeater-template');
      const key = Number(repeater.getAttribute('data-next-key'));
      repeater.setAttribute('data-next-key', key + 1);
      const container = document.createElement('div');
      container.innerHTML = template.innerHTML.split(repeater.getAttribute('data-row-token')).join(String(key));
      const row = container.firstElementChild;
      row.setAttribute('data-row-index', key);
      // Existing items load closed; newly added items open for immediate editing.
      setCollapsed(row, false);
      list.appendChild(row);
      refresh(repeater);
      notify(row);
      const first = row.querySelector('input:not([type="hidden"]), select, textarea');
      if (first && !first.disabled) first.focus();
    });

    $(document).on('click', '.wcf-repeater-delete', function () {
      const repeater = this.closest('.wcf-repeater');
      const list = rows(repeater);
      if (list.children.length <= Number(repeater.getAttribute('data-min-rows'))) return;
      if (repeater.getAttribute('data-confirm-delete') === 'true') {
        showConfirmation(this);
      } else {
        removeRow(this);
      }
    });

    document.addEventListener('click', function (event) {
      if (confirmation && !confirmation.popover.contains(event.target) && !confirmation.trigger.contains(event.target)) {
        closeConfirmation(false);
      }
    });
    document.addEventListener('keydown', function (event) {
      if (confirmation && event.key === 'Escape') {
        event.preventDefault();
        closeConfirmation(true);
      }
    });
    document.addEventListener('focusin', function (event) {
      if (confirmation && !confirmation.popover.contains(event.target) && !confirmation.trigger.contains(event.target)) {
        closeConfirmation(false);
      }
    });
    window.addEventListener('resize', function () { closeConfirmation(false); });
    window.addEventListener('scroll', function () { closeConfirmation(false); }, true);

    $(document).on('input change', '.wcf-repeater input, .wcf-repeater select, .wcf-repeater textarea', function () {
      const repeater = this.closest('.wcf-repeater');
      if (repeater) refresh(repeater);
    });
  });
})(jQuery);
