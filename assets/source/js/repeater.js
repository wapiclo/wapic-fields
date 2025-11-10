(function ($) {
  "use strict";

  function getRowCount($wrap) {
    return $wrap.find(".wcf-repeater-row:not(.wcf-repeater-template)").length;
  }

  function replaceIndexAttrs($el, idx) {
    $el.find("[name]").each(function () {
      var name = $(this).attr("name");
      if (!name) return;
      $(this).attr("name", name.replace("[__INDEX__]", "[" + idx + "]"));
    });
    $el.find("[id]").each(function () {
      var id = $(this).attr("id");
      if (id) $(this).attr("id", id.replace("__INDEX__", String(idx)));
    });
    $el.find("[aria-controls]").each(function () {
      var ac = $(this).attr("aria-controls");
      if (ac)
        $(this).attr("aria-controls", ac.replace("__INDEX__", String(idx)));
    });
    $el.find("[for]").each(function () {
      var fr = $(this).attr("for");
      if (fr) $(this).attr("for", fr.replace("__INDEX__", String(idx)));
    });
    $el.find("[data-target]").each(function () {
      var target = $(this).attr("data-target");
      if (target) $(this).attr("data-target", target.replace("__INDEX__", String(idx)));
    });
  }

  function updateRowTitle($row, titleField) {
    if (!titleField) return;
    var $input = $row
      .find('.wcf-repeater-field[data-subfield-id="' + titleField + '"]')
      .find("input, select, textarea")
      .first();
    var val = ($input.val() || "").toString().trim();
    $row.find(".wcf-repeater-row-title").text(val !== "" ? val : "Item");
  }

  function setOpen($row, open) {
    var $btn = $row.find(
      "> .wcf-repeater-row-header .wcf-repeater-accordion-toggle"
    );
    var $body = $row.find("> .wcf-repeater-row-body");
    if (open) {
      $btn.attr("aria-expanded", "true");
      $body.stop(true, true).slideDown(150);
      $btn.text("▲");
      $row.addClass("is-active");
    } else {
      $btn.attr("aria-expanded", "false");
      $body.stop(true, true).slideUp(150);
      $btn.text("▼");
      $row.removeClass("is-active");
    }
  }

  function bindRowEvents($wrap, $row, titleField) {
    if (titleField) {
      $row.on(
        "input change",
        '.wcf-repeater-field[data-subfield-id="' +
          titleField +
          '"] input, .wcf-repeater-field[data-subfield-id="' +
          titleField +
          '"] select, .wcf-repeater-field[data-subfield-id="' +
          titleField +
          '"] textarea',
        function () {
          updateRowTitle($row, titleField);
        }
      );
      updateRowTitle($row, titleField);
    }
    $row.on("click", ".wcf-repeater-accordion-toggle", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var expanded = $btn.attr("aria-expanded") === "true";
      setOpen($row, !expanded);
    });
    // Header click no longer toggles; arrow button only
    $row.on("click", ".wcf-repeater-remove", function (e) {
      e.preventDefault();
      $row.remove();
    });

    // Initialize field plugins for this row
    initRowPlugins($row);

    // Apply required markers from wrapper to inputs (for new rows cloned from template)
    $row.find('.wcf-repeater-field[data-wcf-required="1"]').each(function () {
      $(this)
        .find("input, select, textarea")
        .each(function () {
          $(this).attr("data-required", "true");
          if (!$(this).hasClass("wcf-required"))
            $(this).addClass("wcf-required");
        });
    });

    // Keep row error class in sync
    function updateRowError() {
      if (
        $row.find(".wcf-field.has-field-error, .wcf-field-error:visible").length
      ) {
        $row.addClass("has-error");
      } else {
        $row.removeClass("has-error");
      }
    }
    // Initial check and on changes
    updateRowError();
    $row.on("input change blur", "input, select, textarea", function () {
      // small delay to let validation handlers run
      setTimeout(updateRowError, 0);
    });
  }

  function renumberRows($wrap) {
    $wrap
      .find("> .wcf-repeater-row:not(.wcf-repeater-template)")
      .each(function (i) {
        var $row = $(this);
        $row.attr("data-index", i);
        // ids and controls
        $row.find("[id]").each(function () {
          var id = $(this).attr("id");
          if (!id) return;
          id = id.replace(/_(?:__INDEX__|\d+)_/, "_" + i + "_");
          $(this).attr("id", id);
        });
        $row.find("[aria-controls]").each(function () {
          var ac = $(this).attr("aria-controls");
          if (ac)
            $(this).attr(
              "aria-controls",
              ac.replace(/_(?:__INDEX__|\d+)_/, "_" + i + "_")
            );
        });
        $row.find("[for]").each(function () {
          var fr = $(this).attr("for");
          if (fr)
            $(this).attr(
              "for",
              fr.replace(/_(?:__INDEX__|\d+)_/, "_" + i + "_")
            );
        });
        // names
        $row.find("[name]").each(function () {
          var name = $(this).attr("name");
          if (!name) return;
          name = name.replace(/\[[0-9]+\]/, "[" + i + "]");
          $(this).attr("name", name);
        });
      });
  }

  // Initialize plugins for each row
  function initRowPlugins($row) {
    // Initialize media uploader
    if (typeof wapicFieldMediaUploader === "function") {
      new wapicFieldMediaUploader();
    }

    // Initialize other plugins if they exist
    if (typeof wapiFieldColorPickerInit === "function") {
      wapiFieldColorPickerInit();
    }

    if (typeof wapicFieldSelect2Init === "function") {
      wapicFieldSelect2Init();
    }

    if (typeof wapicFieldDatePickerInit === "function") {
      wapicFieldDatePickerInit();
    }

    if (typeof wp !== "undefined" && wp.editor) {
      $row.find(".wcf-field-editor").each(function () {
        var editorId = $(this).attr("id");
        // Clear existing editor
        try { wp.editor.remove(editorId); } catch(e) {}
        // Initialize new editor
        wp.editor.initialize(editorId, {
          tinymce: {
            wpautop: true,
            toolbar1:
              "bold italic underline | alignleft aligncenter alignright",
          },
          quicktags: true,
        });
      });
    }

  }

  $(document).ready(function () {
    $(".wcf-repeater").each(function () {
      var $wrap = $(this);
      var $form = $wrap.closest("form");
      if ($form.length && !$form.data("wcfPopupBound")) {
        $form.on("submit.wcfPopup", function () {
          // If any field has error, show a generic popup as well
          if (
            $(this).find(".has-field-error, .wcf-field-error:visible").length
          ) {
            try {
              alert("Please fix the highlighted fields.");
            } catch (e) {}
          }
          // Sync row error classes after validation marks fields
          var $f = $(this);
          setTimeout(function () {
            $f.find(".wcf-repeater-row").each(function () {
              var $r = $(this);
              if (
                $r.find(".wcf-field.has-field-error, .wcf-field-error:visible")
                  .length
              ) {
                $r.addClass("has-error");
              } else {
                $r.removeClass("has-error");
              }
            });
          }, 0);
        });
        $form.data("wcfPopupBound", true);
      }
      var max = parseInt($wrap.data("max"), 10) || 0;
      var titleField = ($wrap.data("title-field") || "").toString();

      // Make rows sortable
      if ($.fn.sortable) {
        $wrap.sortable({
          items: "> .wcf-repeater-row:not(.wcf-repeater-template)",
          handle: ".wcf-repeater-drag-handle",
          axis: "y",
          placeholder: "wcf-repeater-placeholder",
          update: function () {
            renumberRows($wrap);
          },
        });
      }

      $wrap
        .find(".wcf-repeater-row:not(.wcf-repeater-template)")
        .each(function () {
          var $row = $(this);
          bindRowEvents($wrap, $row, titleField);
          // ensure collapsed initially via helper (sets icon too)
          setOpen($row, false);
        });

      $wrap.on("click", ".wcf-repeater-add", function (e) {
        e.preventDefault();
        var count = getRowCount($wrap);
        if (max > 0 && count >= max) return;

        // Clone the template row
        var $tpl = $wrap
          .find(".wcf-repeater-template")
          .first()
          .clone(true, true);

        // Remove template class and show
        $tpl.removeClass("wcf-repeater-template").show();

        // Hapus semua class wcf-repeater-field
        $tpl.find('[class*="wcf-repeater-field"]').each(function () {
          const $el = $(this);
          const classes = $el
            .attr("class")
            .split(" ")
            .filter((cls) => !cls.includes("wcf-repeater-field"));
          $el.attr("class", classes.join(" ").trim() || "");
        });

        var idx = count;
        replaceIndexAttrs($tpl, idx);

        // Reset values
        $tpl
          .find(
            'input[type="text"], input[type="email"], input[type="url"], input[type="number"], input[type="hidden"], input[type="date"]'
          )
          .val("");
        $tpl.find("textarea").val("");
        $tpl.find("select").each(function () {
          var $s = $(this);
          if ($s.prop("multiple")) $s.val([]);
          else $s.val($s.find("option").first().val() || "");
        });

        // Enable inputs on the cloned row (template had disabled)
        $tpl
          .find("input, select, textarea")
          .prop("disabled", false)
          .removeAttr("disabled");

        // Add the new row to the DOM
        $wrap.find(".wcf-repeater-actions").before($tpl);

        // Initialize the row
        bindRowEvents($wrap, $tpl, titleField);

        // Ensure the row is expanded
        setOpen($tpl, true);

        // Initialize all plugins for this row
        initRowPlugins($tpl);
      });
    });
  });
})(jQuery);
