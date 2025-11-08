(function(window, document, wp) {
    'use strict';

    class MediaUploader {
        constructor() {
            this.init();
        }

        /*====================================================*/
        /* Init All */
        /*====================================================*/
        init() {
            document.addEventListener('DOMContentLoaded', () => {
                this.initImageUploader();
                this.initRemoveImage();
                this.initGalleryUploader();
                this.initRemoveGalleryThumb();
                this.initFileUploader();
            });
        }

        /*====================================================*/
        /* Helper */
        /*====================================================*/
        getPreviewElement(button, targetId) {
            let preview = document.getElementById(targetId + '_preview');
            if (!preview) {
                let parent = button.parentElement;
                preview = parent.querySelector('.wcf-field .wcf-field-image-preview, .wcf-field .wcf-field-gallery-preview');
            }
            return preview;
        }

        /*====================================================*/
        /* Image Uploader */
        /*====================================================*/
        initImageUploader() {
            document.body.addEventListener('click', (e) => {
                if (!e.target.matches('.wcf-field .wcf-field-image-upload')) return;
                e.preventDefault();

                let button = e.target;
                let targetId = button.getAttribute('data-target');
                let targetInput = document.getElementById(targetId);
                let preview = this.getPreviewElement(button, targetId);
                let form = button.closest('form');
                let isTermForm = form && form.id === 'edittag';

                let frame = wp.media({
                    title: 'Select Image',
                    button: { text: 'Use Image' },
                    multiple: false
                });

                frame.on('select', () => {
                    let attachment = frame.state().get('selection').first().toJSON();
                    let thumb = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url :
                        (attachment.icon ? attachment.icon : (attachment.url ? attachment.url : ''));

                    targetInput.value = attachment.id;
                    targetInput.dispatchEvent(new Event('change', { bubbles: true }));

                    preview.innerHTML =
                        `<span class="wcf-field-image-thumb">
                            <img src="${thumb}">
                            <a href="#" class="wcf-field-remove-image">×</a>
                        </span>`;

                    if (isTermForm) {
                        button.textContent = 'Change Thumbnail';
                    }
                });

                frame.open();
            });
        }

        /*====================================================*/
        /* Image Removal */
        /*====================================================*/
        initRemoveImage() {
            document.body.addEventListener('click', (e) => {
                if (!e.target.matches('.wcf-field .wcf-field-remove-image')) return;
                e.preventDefault();

                let preview = e.target.closest('.wcf-field-image-preview');
                if (!preview) return;

                let inputId = preview.id.replace('_preview', '');
                let input = document.getElementById(inputId);
                let button = preview.closest('.wcf-field').querySelector('.wcf-field-image-upload');

                if (!input) {
                    input = preview.parentElement.querySelector('input[type=hidden]');
                }

                if (input) {
                    input.value = '';
                    input.dispatchEvent(new Event('change'));
                }
                preview.innerHTML = '';

                if (button) {
                    button.textContent = 'Add Image';
                }
            });
        }

        /*====================================================*/
        /* Gallery Uploader */
        /*====================================================*/
        initGalleryUploader() {
            document.body.addEventListener('click', (e) => {
                if (!e.target.matches('.wcf-field .wcf-field-gallery-upload')) return;
                e.preventDefault();

                let button = e.target;
                let targetId = button.getAttribute('data-target');
                let targetInput = document.getElementById(targetId);
                let preview = this.getPreviewElement(button, targetId);
                let container = button.closest('.wcf-gallery-actions');
                let clearButton = container ? container.querySelector('.wcf-field-gallery-clear') : null;

                let frame = wp.media({
                    title: 'Select Images',
                    button: { text: 'Use Images' },
                    multiple: true
                });

                frame.on('select', () => {
                    let selection = frame.state().get('selection');
                    let existingIds = targetInput.value ? targetInput.value.split(',').filter(Boolean) : [];
                    let newIds = [];

                    selection.each((attachment) => {
                        let attachmentId = String(attachment.id);
                        if (!existingIds.includes(attachmentId)) {
                            newIds.push(attachmentId);
                        } else {
                            wp.data.dispatch('core/notices').createNotice(
                                'warning',
                                'Gambar dengan ID ' + attachmentId + ' sudah ada di galeri',
                                { type: 'snackbar', isDismissible: true }
                            );
                        }
                    });

                    if (newIds.length === 0) return;

                    let allIds = [...existingIds, ...newIds];
                    let ids = allIds.filter(Boolean).map(Number).filter(id => !isNaN(id));

                    preview.innerHTML = '';

                    if (ids.length > 0) {
                        if (button) button.textContent = 'Edit Gallery';
                        if (clearButton) clearButton.style.display = 'inline-block';

                        ids.forEach((id) => {
                            let attachment = wp.media.attachment(id);
                            attachment.fetch().then(() => {
                                let data = attachment.toJSON();
                                let imgUrl = (data.sizes && data.sizes.thumbnail) ?
                                    data.sizes.thumbnail.url : (data.icon || data.url || '');

                                if (imgUrl) {
                                    preview.insertAdjacentHTML('beforeend',
                                        `<span class="wcf-field-gallery-thumb" data-id="${id}">
                                            <img src="${imgUrl}" style="max-width:80px;height:auto;" alt="">
                                            <a href="#" class="wcf-field-remove-gallery-thumb" title="Remove image">×</a>
                                        </span>`
                                    );
                                }
                            });
                        });
                    }

                    targetInput.value = ids.join(',');
                    targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                });

                frame.open();
            });
        }

        /*====================================================*/
        /* Gallery Removal */
        /*====================================================*/
        initRemoveGalleryThumb() {
            document.body.addEventListener('click', (e) => {
                if (e.target.matches('.wcf-field .wcf-field-remove-gallery-thumb')) {
                    e.preventDefault();

                    let thumb = e.target.closest('.wcf-field-gallery-thumb');
                    if (!thumb) return;

                    let wrapper = thumb.closest('.wcf-field-gallery-preview');
                    if (!wrapper) return;

                    let inputId = wrapper.id.replace('_preview', '');
                    let input = document.getElementById(inputId);
                    if (!input) {
                        input = wrapper.parentElement.querySelector('input[type=hidden]');
                    }

                    thumb.remove();

                    let ids = [];
                    wrapper.querySelectorAll('.wcf-field-gallery-thumb').forEach((el) => {
                        let id = el.getAttribute('data-id');
                        if (id) ids.push(id);
                    });

                    if (input) {
                        input.value = ids.join(',');

                        if (ids.length === 0) {
                            let button = wrapper.parentElement.querySelector('.wcf-field-gallery-upload');
                            let clearButton = wrapper.parentElement.querySelector('.wcf-field-gallery-clear');
                            if (button) button.textContent = 'Add Gallery';
                            if (clearButton) clearButton.style.display = 'none';
                        }

                        input.dispatchEvent(new Event('change'));
                    }
                } else if (e.target.matches('.wcf-field-gallery-clear')) {
                    e.preventDefault();

                    let button = e.target;
                    let targetId = button.getAttribute('data-target');
                    let input = document.getElementById(targetId);
                    let preview = document.getElementById(targetId + '_preview');

                    if (input && preview) {
                        preview.innerHTML = '';
                        input.value = '';

                        let uploadButton = button.closest('.wcf-gallery-actions').querySelector('.wcf-field-gallery-upload');
                        if (uploadButton) uploadButton.textContent = 'Add Gallery';

                        button.style.display = 'none';
                        input.dispatchEvent(new Event('change'));
                    }
                }
            });
        }

        /*====================================================*/
        /* File Uploader */
        /*====================================================*/
        initFileUploader() {
            document.body.addEventListener('click', (e) => {
                if (!e.target.closest('.wcf-field .wcf-file-upload-button')) return;
                e.preventDefault();

                let button = e.target.closest('.wcf-file-upload-button');
                let targetId = button.getAttribute('data-target');
                let targetInput = document.getElementById(targetId);
                if (!targetInput) return;

                let frame = wp.media({
                    title: 'Select File',
                    button: { text: 'Use Selected' },
                    multiple: false,
                    library: { type: '' }
                });

                frame.on('select', () => {
                    let attachment = frame.state().get('selection').first().toJSON();
                    if (attachment && attachment.url) {
                        let errorElement = document.getElementById(targetInput.id + '_error');
                        if (errorElement) {
                            errorElement.style.display = 'none';
                            errorElement.textContent = '';
                        }

                        let field = targetInput.closest('.wcf-field');
                        if (field) field.classList.remove('has-field-error');

                        targetInput.value = attachment.url;
                        targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });

                frame.open();
            });
        }
    }

    // Inisialisasi langsung
    new MediaUploader();

})(window, document, wp);
