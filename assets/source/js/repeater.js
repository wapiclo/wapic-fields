(function($){
  'use strict';

  function getRowCount($wrap){
    return $wrap.find('.wcf-repeater-row:not(.wcf-repeater-template)').length;
  }

  function replaceIndexAttrs($el, idx){
    $el.find('[name]').each(function(){
      var name = $(this).attr('name');
      if (!name) return;
      $(this).attr('name', name.replace('[__INDEX__]', '[' + idx + ']'));
    });
    $el.find('[id]').each(function(){
      var id = $(this).attr('id');
      if (id) $(this).attr('id', id.replace('__INDEX__', String(idx)));
    });
    $el.find('[aria-controls]').each(function(){
      var ac = $(this).attr('aria-controls');
      if (ac) $(this).attr('aria-controls', ac.replace('__INDEX__', String(idx)));
    });
    $el.find('[for]').each(function(){
      var fr = $(this).attr('for');
      if (fr) $(this).attr('for', fr.replace('__INDEX__', String(idx)));
    });
  }

  function updateRowTitle($row, titleField){
    if (!titleField) return;
    var $input = $row.find('.wcf-repeater-field[data-subfield-id="'+titleField+'"]').find('input, select, textarea').first();
    var val = ($input.val() || '').toString().trim();
    $row.find('.wcf-repeater-row-title').text(val !== '' ? val : 'Item');
  }

  function setOpen($row, open){
    var $btn = $row.find('> .wcf-repeater-row-header .wcf-repeater-accordion-toggle');
    var $body = $row.find('> .wcf-repeater-row-body');
    if(open){
      $btn.attr('aria-expanded','true');
      $body.stop(true, true).slideDown(150);
      $btn.text('▲');
      $row.addClass('is-active');
    } else {
      $btn.attr('aria-expanded','false');
      $body.stop(true, true).slideUp(150);
      $btn.text('▼');
      $row.removeClass('is-active');
    }
  }

  function bindRowEvents($wrap, $row, titleField){
    if (titleField) {
      $row.on('input change', '.wcf-repeater-field[data-subfield-id="'+titleField+'"] input, .wcf-repeater-field[data-subfield-id="'+titleField+'"] select, .wcf-repeater-field[data-subfield-id="'+titleField+'"] textarea', function(){
        updateRowTitle($row, titleField);
      });
      updateRowTitle($row, titleField);
    }
    $row.on('click', '.wcf-repeater-accordion-toggle', function(e){
      e.preventDefault();
      var $btn = $(this);
      var expanded = $btn.attr('aria-expanded') === 'true';
      setOpen($row, !expanded);
    });
    // Header click no longer toggles; arrow button only
    $row.on('click', '.wcf-repeater-remove', function(e){
      e.preventDefault();
      $row.remove();
    });

    // Initialize field plugins for this row
    initRowPlugins($row);

    // Apply required markers from wrapper to inputs (for new rows cloned from template)
    $row.find('.wcf-repeater-field[data-wcf-required="1"]').each(function(){
      $(this).find('input, select, textarea').each(function(){
        $(this).attr('data-required','true');
        if (!$(this).hasClass('wcf-required')) $(this).addClass('wcf-required');
      });
    });

    // Keep row error class in sync
    function updateRowError(){
      if ($row.find('.wcf-field.has-field-error, .wcf-field-error:visible').length){
        $row.addClass('has-error');
      } else {
        $row.removeClass('has-error');
      }
    }
    // Initial check and on changes
    updateRowError();
    $row.on('input change blur', 'input, select, textarea', function(){
      // small delay to let validation handlers run
      setTimeout(updateRowError, 0);
    });

    // Media select/clear handlers
    $row.off('click.wcfMedia').on('click.wcfMedia', '.wcf-media-select', function(e){
      e.preventDefault();
      if (!(window.wp && wp.media)) return;
      var type = $(this).data('media-type') || 'image';
      var targetSel = $(this).data('target');
      var $target = $row.find(targetSel);
      if (!$target.length) return;

      var frame = wp.media({
        title: type === 'gallery' ? 'Select Images' : 'Select Image',
        library: { type: 'image' },
        multiple: type === 'gallery'
      });

      frame.on('select', function(){
        if (type === 'gallery') {
          var ids = frame.state().get('selection').map(function(att){ return att.id; });
          $target.val(ids.join(',')).trigger('change');
        } else {
          var att = frame.state().get('selection').first();
          if (att) {
            var data = att.toJSON();
            $target.val(data.url || '').trigger('change');
          }
        }
      });

      frame.open();
    }).on('click.wcfMedia', '.wcf-media-clear', function(e){
      e.preventDefault();
      var targetSel = $(this).data('target');
      var $target = $row.find(targetSel);
      if ($target.length) {
        $target.val('').trigger('change');
      }
    })
    // Image (ID) uploader similar to core Field::control_image
    .on('click.wcfMedia', '.wcf-field-image-upload', function(e){
      e.preventDefault();
      if (!(window.wp && wp.media)) return;
      var targetId = $(this).data('target');
      var $hidden = $row.find('#' + targetId);
      var $preview = $row.find('#' + targetId + '_preview');
      var frame = wp.media({ title: 'Select Image', library: { type: 'image' }, multiple: false });
      frame.on('select', function(){
        var att = frame.state().get('selection').first();
        if (!att) return;
        var data = att.toJSON();
        $hidden.val(data.id || '').trigger('change');
        if ($preview.length) {
          var html = '';
          if (data.url) {
            html += '<span class="wcf-field-image-thumb">';
            html += '<img src="' + data.url + '">';
            html += '<a href="#" class="wcf-field-remove-image" title="Remove image">×</a>';
            html += '</span>';
          }
          $preview.html(html);
        }
      });
      frame.open();
    })
    .on('click.wcfMedia', '.wcf-field-remove-image', function(e){
      e.preventDefault();
      var $thumb = $(this).closest('.wcf-field-image-thumb');
      var $hidden = $thumb.closest('.wcf-repeater-row-body').find('input.wcf-media-id').first();
      $thumb.remove();
      if ($hidden.length) $hidden.val('').trigger('change');
    })
    // Gallery (IDs) uploader similar to core Field::control_gallery
    .on('click.wcfMedia', '.wcf-field-gallery-upload', function(e){
      e.preventDefault();
      if (!(window.wp && wp.media)) return;
      var targetId = $(this).data('target');
      var $hidden = $row.find('#' + targetId + '.wcf-gallery-ids');
      var $preview = $row.find('#' + targetId + '_preview');
      var frame = wp.media({ title: 'Select Images', library: { type: 'image' }, multiple: true });
      frame.on('select', function(){
        var selection = frame.state().get('selection');
        var ids = selection.map(function(att){ return att.id; });
        $hidden.val(ids.join(',')).trigger('change');
        if ($preview.length) {
          var html = '';
          selection.each(function(att){
            var d = att.toJSON();
            var url = (d.sizes && d.sizes.thumbnail && d.sizes.thumbnail.url) ? d.sizes.thumbnail.url : d.url;
            html += '<span class="wcf-field-gallery-thumb" data-id="' + d.id + '">';
            html += '<img src="' + url + '" alt="">';
            html += '<a href="#" class="wcf-field-remove-gallery-thumb" title="Remove image">×</a>';
            html += '</span>';
          });
          $preview.html(html);
        }
      });
      frame.open();
    })
    .on('click.wcfMedia', '.wcf-field-remove-gallery-thumb', function(e){
      e.preventDefault();
      var $thumb = $(this).closest('.wcf-field-gallery-thumb');
      var id = String($thumb.data('id'));
      var $wrapBody = $thumb.closest('.wcf-repeater-row-body');
      var $hidden = $wrapBody.find('input.wcf-gallery-ids');
      $thumb.remove();
      if ($hidden.length) {
        var current = ($hidden.val() || '').split(',').filter(Boolean);
        var next = current.filter(function(v){ return String(v) !== id; });
        $hidden.val(next.join(',')).trigger('change');
      }
    });
  }

  $(document).ready(function(){
    $('.wcf-repeater').each(function(){
      var $wrap = $(this);
      var $form = $wrap.closest('form');
      if ($form.length && !$form.data('wcfPopupBound')){
        $form.on('submit.wcfPopup', function(){
          // If any field has error, show a generic popup as well
          if ($(this).find('.has-field-error, .wcf-field-error:visible').length){
            try { alert('Please fix the highlighted fields.'); } catch(e){}
          }
          // Sync row error classes after validation marks fields
          var $f = $(this);
          setTimeout(function(){
            $f.find('.wcf-repeater-row').each(function(){
              var $r = $(this);
              if ($r.find('.wcf-field.has-field-error, .wcf-field-error:visible').length){
                $r.addClass('has-error');
              } else {
                $r.removeClass('has-error');
              }
            });
          }, 0);
        });
        $form.data('wcfPopupBound', true);
      }
      var max   = parseInt($wrap.data('max'), 10) || 0;
      var titleField = ($wrap.data('title-field') || '').toString();

      // Make rows sortable
      if ($.fn.sortable) {
        $wrap.sortable({
          items: '> .wcf-repeater-row:not(.wcf-repeater-template)',
          handle: '.wcf-repeater-drag-handle',
          axis: 'y',
          placeholder: 'wcf-repeater-placeholder',
          update: function(){ renumberRows($wrap); }
        });
      }

      $wrap.find('.wcf-repeater-row:not(.wcf-repeater-template)').each(function(){
        var $row = $(this);
        bindRowEvents($wrap, $row, titleField);
        // ensure collapsed initially via helper (sets icon too)
        setOpen($row, false);
      });

      $wrap.on('click', '.wcf-repeater-add', function(e){
        e.preventDefault();
        var count = getRowCount($wrap);
        if (max > 0 && count >= max) return;

        var $tpl = $wrap.find('.wcf-repeater-template').first().clone(true, true);
        $tpl.removeClass('wcf-repeater-template').show();

        var idx = count;
        replaceIndexAttrs($tpl, idx);

        // reset values
        $tpl.find('input[type="text"], input[type="email"], input[type="url"], input[type="number"], input[type="hidden"], input[type="date"]').val('');
        $tpl.find('textarea').val('');
        $tpl.find('select').each(function(){
          var $s = $(this);
          if ($s.prop('multiple')) $s.val([]);
          else $s.val($s.find('option').first().val() || '');
        });

        // enable inputs on the cloned row (template had disabled)
        $tpl.find('input, select, textarea').prop('disabled', false).removeAttr('disabled');

        $wrap.find('.wcf-repeater-actions').before($tpl);

        bindRowEvents($wrap, $tpl, titleField);
        // ensure expanded
        setOpen($tpl, true);

        // Initialize WP editor for editor textareas if available
        if (window.wp && wp.editor && typeof wp.editor.initialize === 'function') {
          $tpl.find('textarea.wcf-field-editor').each(function(){
            var id = $(this).attr('id');
            if (id) {
              try { wp.editor.initialize(id, { tinymce: true, quicktags: true }); } catch(e) {}
            }
          });
        }
      });
    });
  });

  function initRowPlugins($row){
    // Select2
    if ($.fn.select2) {
      $row.find('select.wcf-field-select2').each(function(){
        var $s = $(this);
        var width = $s.data('width') || '100%';
        var placeholder = $s.data('placeholder') || '';
        var allowClear = ($s.data('allow-clear') || '').toString() === 'true';
        if ($s.data('select2')) { $s.select2('destroy'); }
        $s.select2({ width: width, placeholder: placeholder, allowClear: allowClear });
        $s.trigger('change');
      });
    }
    // Color Picker
    if ($.fn.wpColorPicker) {
      $row.find('input.wcf-field-color').each(function(){
        var $c = $(this);
        if (!$c.hasClass('wp-color-picker')) {
          try { $c.wpColorPicker(); } catch(e){}
        }
      });
    }
    // Datepicker
    if ($.fn.datepicker) {
      $row.find('input.wcf-field-date').each(function(){
        var $d = $(this);
        try { $d.datepicker(); } catch(e){}
      });
    }
  }

  function renumberRows($wrap){
    $wrap.find('> .wcf-repeater-row:not(.wcf-repeater-template)').each(function(i){
      var $row = $(this);
      $row.attr('data-index', i);
      // ids and controls
      $row.find('[id]').each(function(){
        var id = $(this).attr('id');
        if (!id) return;
        id = id.replace(/_(?:__INDEX__|\d+)_/, '_' + i + '_');
        $(this).attr('id', id);
      });
      $row.find('[aria-controls]').each(function(){
        var ac = $(this).attr('aria-controls');
        if (ac) $(this).attr('aria-controls', ac.replace(/_(?:__INDEX__|\d+)_/, '_' + i + '_'));
      });
      $row.find('[for]').each(function(){
        var fr = $(this).attr('for');
        if (fr) $(this).attr('for', fr.replace(/_(?:__INDEX__|\d+)_/, '_' + i + '_'));
      });
      // names
      $row.find('[name]').each(function(){
        var name = $(this).attr('name');
        if (!name) return;
        name = name.replace(/\[[0-9]+\]/, '[' + i + ']');
        $(this).attr('name', name);
      });
    });
  }
})(jQuery);
