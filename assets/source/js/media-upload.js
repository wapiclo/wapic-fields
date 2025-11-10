class wapicFieldMediaUploader {
  constructor() {
    this.init();
  }

  // ----------------------------------------------------
  // Init
  // ----------------------------------------------------
  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.initImageUploader();
      this.initRemoveImage();
      this.initGalleryUploader();
      this.initRemoveGalleryThumb();
      this.initFileUploader();
    });
  }

  // ----------------------------------------------------
  // Helper
  // ----------------------------------------------------
  getPreviewElement(button, targetId) {
    let preview = document.getElementById(targetId + "_preview");
    if (!preview) {
      let parent = button.parentElement;
      // Jika parent ditemukan .wcf-repeater-field, maka hentikan
      if (parent && parent.closest(".wcf-repeater-template")) return null;
      preview = parent.querySelector(
        ".wcf-field .wcf-field-image-preview, .wcf-field .wcf-field-gallery-preview"
      );
    }
    return preview;
  }

  // ----------------------------------------------------
  // Image Uploader
  // ----------------------------------------------------
  initImageUploader() {
    document.body.addEventListener("click", (e) => {
      const button = e.target.closest(".wcf-field .wcf-field-image-upload");

      if (!button) return;
      if (button.closest(".wcf-repeater-template")) return; // stop jika di repeater

      e.preventDefault();

      const targetId = button.getAttribute("data-target");
      const targetInput = document.getElementById(targetId);
      const preview = this.getPreviewElement(button, targetId);
      const form = button.closest("form");
      const isTermForm = form && form.id === "edittag";

      const frame = wp.media({
        title: "Select Image",
        button: { text: "Use Image" },
        multiple: false,
      });

      frame.on("select", () => {
        const attachment = frame.state().get("selection").first().toJSON();

        const thumb =
          (attachment.sizes && attachment.sizes.thumbnail?.url) ||
          attachment.icon ||
          attachment.url ||
          "";

        targetInput.value = attachment.id;
        targetInput.dispatchEvent(new Event("change", { bubbles: true }));

        preview.innerHTML = `
          <span class="wcf-field-image-thumb">
            <img src="${thumb}">
            <a href="#" class="wcf-field-remove-image">×</a>
          </span>
        `;

        console.log(preview);

        if (isTermForm) button.textContent = "Change Thumbnail";
      });

      frame.open();
    });
  }

  // ----------------------------------------------------
  // Image Removal
  // ----------------------------------------------------
  initRemoveImage() {
    document.body.addEventListener("click", (e) => {
      const removeBtn = e.target.closest(".wcf-field .wcf-field-remove-image");
      if (!removeBtn) return;
      if (removeBtn.closest(".wcf-repeater-template")) return; // stop jika di repeater

      e.preventDefault();

      const preview = removeBtn.closest(".wcf-field-image-preview");
      if (!preview) return;

      const inputId = preview.id.replace("_preview", "");
      const input = document.getElementById(inputId);
      const button = preview
        .closest(".wcf-field")
        ?.querySelector(".wcf-field-image-upload");

      if (input) {
        input.value = "";
        input.dispatchEvent(new Event("change"));
      }

      preview.innerHTML = "";
      if (button) button.textContent = "Add Image";
    });
  }

  // ----------------------------------------------------
  // Gallery Uploader
  // ----------------------------------------------------
  initGalleryUploader() {
    document.body.addEventListener("click", (e) => {
      const button = e.target.closest(".wcf-field .wcf-field-gallery-upload");
      if (!button) return;
      if (button.closest(".wcf-repeater-template")) return; // stop jika di repeater

      e.preventDefault();

      const targetId = button.getAttribute("data-target");
      const targetInput = document.getElementById(targetId);
      const preview = this.getPreviewElement(button, targetId);
      const container = button.closest(".wcf-gallery-actions");
      const clearButton = container?.querySelector(".wcf-field-gallery-clear");

      const frame = wp.media({
        title: "Select Images",
        button: { text: "Use Images" },
        multiple: true,
      });

      frame.on("select", () => {
        const selection = frame.state().get("selection");
        const existingIds = targetInput.value
          ? targetInput.value.split(",").filter(Boolean)
          : [];
        const newIds = [];

        selection.each((attachment) => {
          const id = String(attachment.id);
          if (!existingIds.includes(id)) {
            newIds.push(id);
          } else {
            wp.data
              .dispatch("core/notices")
              .createNotice("warning", "Image ID " + id + " already exists", {
                type: "snackbar",
                isDismissible: true,
              });
          }
        });

        if (!newIds.length) return;

        const ids = [...existingIds, ...newIds]
          .filter(Boolean)
          .map(Number)
          .filter((id) => !isNaN(id));

        preview.innerHTML = "";
        if (ids.length > 0) {
          button.textContent = "Edit Gallery";
          if (clearButton) clearButton.style.display = "inline-block";

          ids.forEach((id) => {
            const attachment = wp.media.attachment(id);
            attachment.fetch().then(() => {
              const data = attachment.toJSON();
              const imgUrl =
                (data.sizes && data.sizes.thumbnail?.url) ||
                data.icon ||
                data.url ||
                "";

              if (imgUrl) {
                preview.insertAdjacentHTML(
                  "beforeend",
                  `<span class="wcf-field-gallery-thumb" data-id="${id}">
                    <img src="${imgUrl}" style="max-width:80px;height:auto;" alt="">
                    <a href="#" class="wcf-field-remove-gallery-thumb" title="Remove image">×</a>
                  </span>`
                );
              }
            });
          });
        }

        targetInput.value = ids.join(",");
        targetInput.dispatchEvent(new Event("change", { bubbles: true }));
      });

      frame.open();
    });
  }

  // ----------------------------------------------------
  // Gallery Removal & Clear
  // ----------------------------------------------------
  initRemoveGalleryThumb() {
    document.body.addEventListener("click", (e) => {
      // Hapus satu gambar dari galeri
      const removeBtn = e.target.closest(".wcf-field .wcf-field-remove-gallery-thumb");
      if (removeBtn) {
        if (removeBtn.closest(".wcf-repeater-template")) return; // stop jika di repeater
        e.preventDefault();

        const thumb = removeBtn.closest(".wcf-field-gallery-thumb");
        if (!thumb) return;

        const wrapper = thumb.closest(".wcf-field-gallery-preview");
        if (!wrapper) return;

        const inputId = wrapper.id.replace("_preview", "");
        const input = document.getElementById(inputId);
        thumb.remove();

        const ids = Array.from(wrapper.querySelectorAll(".wcf-field-gallery-thumb"))
          .map((el) => el.getAttribute("data-id"))
          .filter(Boolean);

        if (input) {
          input.value = ids.join(",");
          if (!ids.length) {
            const button = wrapper.parentElement.querySelector(".wcf-field-gallery-upload");
            const clearButton = wrapper.parentElement.querySelector(".wcf-field-gallery-clear");
            if (button) button.textContent = "Add Gallery";
            if (clearButton) clearButton.style.display = "none";
          }
          input.dispatchEvent(new Event("change"));
        }
        return;
      }

      // Clear seluruh galeri
      const clearBtn = e.target.closest(".wcf-field-gallery-clear");
      if (clearBtn) {
        if (clearBtn.closest(".wcf-repeater-template")) return; // stop jika di repeater
        e.preventDefault();

        const targetId = clearBtn.getAttribute("data-target");
        const input = document.getElementById(targetId);
        const preview = document.getElementById(targetId + "_preview");

        if (input && preview) {
          preview.innerHTML = "";
          input.value = "";

          const uploadBtn = clearBtn
            .closest(".wcf-gallery-actions")
            ?.querySelector(".wcf-field-gallery-upload");

          if (uploadBtn) uploadBtn.textContent = "Add Gallery";
          clearBtn.style.display = "none";
          input.dispatchEvent(new Event("change"));
        }
      }
    });
  }

  // ----------------------------------------------------
  // File Uploader
  // ----------------------------------------------------
  initFileUploader() {
    document.body.addEventListener("click", (e) => {
      const button = e.target.closest(".wcf-field .wcf-file-upload-button");
      if (!button) return;
      if (button.closest(".wcf-repeater-template")) return; // stop jika di repeater

      e.preventDefault();

      const targetId = button.getAttribute("data-target");
      const targetInput = document.getElementById(targetId);
      if (!targetInput) return;

      const frame = wp.media({
        title: "Select File",
        button: { text: "Use Selected" },
        multiple: false,
      });

      frame.on("select", () => {
        const attachment = frame.state().get("selection").first().toJSON();
        if (attachment?.url) {
          const errorElement = document.getElementById(targetInput.id + "_error");
          if (errorElement) {
            errorElement.style.display = "none";
            errorElement.textContent = "";
          }

          const field = targetInput.closest(".wcf-field");
          if (field) field.classList.remove("has-field-error");

          targetInput.value = attachment.url;
          targetInput.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });

      frame.open();
    });
  }
}

// Inisialisasi langsung
new wapicFieldMediaUploader();
