document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 0. GLOBAL UTILITIES & STYLES
    // ==========================================
    const Utils = {
        injectSpinnerStyles() {
            if (!document.getElementById('spinner-keyframes')) {
                const style = document.createElement('style');
                style.id = 'spinner-keyframes';
                style.innerHTML = `@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
                document.head.appendChild(style);
            }
        },

        setPreviewSource(element, file) {
            if (!element || !file) return;
            this.clearPreviewSource(element);
            element.src = URL.createObjectURL(file);
            element.style.display = 'block';
        },

        clearPreviewSource(element) {
            if (!element) return;
            if (element.src && element.src.startsWith('blob:')) {
                URL.revokeObjectURL(element.src);
            }
            element.removeAttribute('src');
            element.style.display = 'none';
        }
    };

    Utils.injectSpinnerStyles();


   // ==========================================
// 1. HERO SECTION MODULE
// ==========================================
const HeroModule = {
    // 100MB client-side threshold
    MAX_FILE_SIZE: 100 * 1024 * 1024, 

    init() {
        this.cacheDOM();
        if (!this.inputEyebrow) return; // Exit gracefully if not on Hero page
        this.bindEvents();
    },

    cacheDOM() {
        this.form = document.querySelector('form[action*="hero_controller.php"]');
        this.inputEyebrow = document.getElementById('inputEyebrow');
        this.inputTitle = document.getElementById('inputTitle');
        this.previewEyebrow = document.getElementById('previewEyebrow');
        this.previewTitle = document.getElementById('previewTitle');

        this.imageInput = document.getElementById('imageInput');
        this.videoInput = document.getElementById('videoInput');
        this.imgPreview = document.getElementById('imgPreview');
        this.vidPreview = document.getElementById('vidPreview');

        this.badgeImage = document.getElementById('badgeImage');
        this.badgeVideo = document.getElementById('badgeVideo');
        this.badgeDefault = document.getElementById('badgeDefault');
    },

    bindEvents() {
        if (this.inputEyebrow && this.previewEyebrow) {
            this.inputEyebrow.addEventListener('input', (e) => {
                this.previewEyebrow.textContent = e.target.value.trim() || 'BHUTAN BELIEVE';
            });
        }

        if (this.inputTitle && this.previewTitle) {
            this.inputTitle.addEventListener('input', (e) => {
                this.previewTitle.textContent = e.target.value.trim() || 'Experience Stillness. Make an Impact.';
            });
        }

        if (this.imageInput) {
            this.imageInput.addEventListener('change', (e) => this.handleImageChange(e));
        }

        if (this.videoInput) {
            this.videoInput.addEventListener('change', (e) => this.handleVideoChange(e));
        }

        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    },

    handleImageChange(event) {
        const file = event.target.files[0];

        // Handle File Removal / Cancellation
        if (!file) {
            this.clearMediaPreview(this.imgPreview);
            if (!this.vidPreview || this.vidPreview.style.display === 'none') {
                this.updateBadges('none');
            }
            return;
        }

        // Validate Image Size (100MB limit)
        if (file.size > this.MAX_FILE_SIZE) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(1);
            alert(`The selected image "${file.name}" is ${sizeInMB}MB, which exceeds the maximum allowed limit of 100MB.`);
            this.imageInput.value = ''; // Reset input
            return;
        }

        // Clear Video Input & Preview
        if (this.videoInput) this.videoInput.value = '';
        if (this.vidPreview) {
            this.vidPreview.pause();
            this.clearMediaPreview(this.vidPreview);
        }

        this.setMediaPreview(this.imgPreview, file);
        this.updateBadges('image');
    },

    handleVideoChange(event) {
        const file = event.target.files[0];

        // Handle File Removal / Cancellation
        if (!file) {
            if (this.vidPreview) {
                this.vidPreview.pause();
                this.clearMediaPreview(this.vidPreview);
            }
            if (!this.imgPreview || this.imgPreview.style.display === 'none') {
                this.updateBadges('none');
            }
            return;
        }

        // Validate Video Size (100MB limit)
        if (file.size > this.MAX_FILE_SIZE) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(1);
            alert(`The selected video "${file.name}" is ${sizeInMB}MB, which exceeds the maximum allowed limit of 100MB.`);
            this.videoInput.value = ''; // Reset input
            return;
        }

        // Clear Image Input & Preview
        if (this.imageInput) this.imageInput.value = '';
        if (this.imgPreview) {
            this.clearMediaPreview(this.imgPreview);
        }

        this.setMediaPreview(this.vidPreview, file);
        if (this.vidPreview) {
            this.vidPreview.play().catch(err => console.log('Autoplay prevented:', err));
        }

        this.updateBadges('video');
    },

    handleFormSubmit(event) {
        const videoFile = this.videoInput?.files[0];
        const imageFile = this.imageInput?.files[0];

        if ((videoFile && videoFile.size > this.MAX_FILE_SIZE) || (imageFile && imageFile.size > this.MAX_FILE_SIZE)) {
            event.preventDefault();
            alert(`File size exceeds the 100MB server limit. Please select a smaller file.`);
        }
    },

    // Safe fallbacks in case `Utils` helper object is missing
    setMediaPreview(element, file) {
        if (!element || !file) return;
        if (typeof Utils !== 'undefined' && Utils.setPreviewSource) {
            Utils.setPreviewSource(element, file);
        } else {
            element.src = URL.createObjectURL(file);
            element.style.display = 'block';
        }
    },

    clearMediaPreview(element) {
        if (!element) return;
        if (typeof Utils !== 'undefined' && Utils.clearPreviewSource) {
            Utils.clearPreviewSource(element);
        } else {
            element.src = '';
            element.style.display = 'none';
        }
    },

    updateBadges(type) {
        if (this.badgeImage) this.badgeImage.style.display = (type === 'image') ? 'inline-block' : 'none';
        if (this.badgeVideo) this.badgeVideo.style.display = (type === 'video') ? 'inline-block' : 'none';
        if (this.badgeDefault) this.badgeDefault.style.display = (type === 'none') ? 'inline-block' : 'none';
    }
};

HeroModule.init();

    // ==========================================
    // 2. STANDARD FORM SUBMISSION FEEDBACK
    // ==========================================
    const FormHandlerModule = {
        init() {
            const adminForms = document.querySelectorAll(`
                form[action*="hero_controller.php"], 
                form[action*="brand_controller.php"],
                form[action*="destinations_controller.php"],
                form[action*="events_controller.php"]
            `);

            adminForms.forEach(form => this.bindSubmitListener(form));
        },

        bindSubmitListener(form) {
            form.addEventListener('submit', function () {
                if (!this.checkValidity()) return;

                const submitBtn = this.querySelector('button[type="submit"]');

                if (submitBtn) {
                    setTimeout(() => {
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.7';
                        submitBtn.style.cursor = 'wait';
                        submitBtn.dataset.originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = `
                            <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 0.5rem;">⚙️</span>
                            Saving Changes...
                        `;
                    }, 0);
                }
            });
        }
    };

    FormHandlerModule.init();


    // ==========================================
    // 3. AUTO-HIDE NOTIFICATIONS / ALERTS
    // ==========================================
    window.AlertsModule = {
        init() {
            const alerts = document.querySelectorAll('.alert, .session-flash');
            alerts.forEach(alert => this.autoHide(alert));
        },

        autoHide(alertElement) {
            if (!alertElement) return;
            setTimeout(() => {
                alertElement.style.transition = 'opacity 0.5s ease';
                alertElement.style.opacity = '0';
                setTimeout(() => alertElement.style.display = 'none', 500);
            }, 4000);
        }
    };

    window.AlertsModule.init();


    // ==========================================
    // 4. DESTINATIONS SHOWCASE MODULE
    // ==========================================
    const DestinationsModule = {
        init() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM() {
            this.toggleAddBtn = document.getElementById('btn-toggle-add-dest');
            this.addCard = document.getElementById('add-destination-card');
            this.cancelAddBtns = document.querySelectorAll('.btn-cancel-add-dest');
            this.toggleEditBtns = document.querySelectorAll('.btn-toggle-edit-dest');
            this.deleteForms = document.querySelectorAll('.delete-dest-form');
        },

        bindEvents() {
            if (this.toggleAddBtn) {
                this.toggleAddBtn.addEventListener('click', this.toggleAddForm.bind(this));
            }

            this.cancelAddBtns.forEach(btn => {
                btn.addEventListener('click', this.toggleAddForm.bind(this));
            });

            this.toggleEditBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = e.currentTarget.getAttribute('data-id');
                    this.toggleEdit(id);
                });
            });

            this.deleteForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (!confirm('Delete this destination?')) {
                        e.preventDefault();
                    }
                });
            });
        },

        toggleAddForm() {
            if (!this.addCard) return;
            const isHidden = window.getComputedStyle(this.addCard).display === 'none' || this.addCard.style.display === 'none';
            this.addCard.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                this.addCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        toggleEdit(id) {
            const card = document.getElementById('dest-card-' + id);
            const edit = document.getElementById('dest-edit-' + id);

            if (card && edit) {
                const isEditHidden = window.getComputedStyle(edit).display === 'none' || edit.style.display === 'none';
                if (isEditHidden) {
                    card.style.display = 'none';
                    edit.style.display = 'block';
                } else {
                    card.style.display = 'flex';
                    edit.style.display = 'none';
                }
            }
        }
    };

    DestinationsModule.init();


    // ==========================================
    // 5. EVENTS SHOWCASE MODULE
    // ==========================================
    const EventsModule = {
        init() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM() {
            this.toggleAddBtn = document.getElementById('btn-toggle-add-event');
            this.addCard = document.getElementById('add-event-card');
            this.cancelAddBtns = document.querySelectorAll('.btn-cancel-add-event');
            this.toggleEditBtns = document.querySelectorAll('.btn-toggle-edit-event');
            this.deleteForms = document.querySelectorAll('.delete-event-form');
        },

        bindEvents() {
            if (this.toggleAddBtn) {
                this.toggleAddBtn.addEventListener('click', this.toggleAddForm.bind(this));
            }

            this.cancelAddBtns.forEach(btn => {
                btn.addEventListener('click', this.toggleAddForm.bind(this));
            });

            this.toggleEditBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = e.currentTarget.getAttribute('data-id');
                    this.toggleEdit(id);
                });
            });

            this.deleteForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    const confirmed = confirm('Are you sure you want to delete this event? This action cannot be undone.');
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });
            });
        },

        toggleAddForm() {
            if (!this.addCard) return;
            const isHidden = window.getComputedStyle(this.addCard).display === 'none' || this.addCard.style.display === 'none';
            this.addCard.style.display = isHidden ? 'block' : 'none';

            if (isHidden) {
                this.addCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        toggleEdit(id) {
            const displayCard = document.getElementById(`event-card-${id}`);
            const editFormCard = document.getElementById(`event-edit-${id}`);

            if (displayCard && editFormCard) {
                const isEditHidden = window.getComputedStyle(editFormCard).display === 'none' || editFormCard.style.display === 'none';

                if (isEditHidden) {
                    displayCard.style.display = 'none';
                    editFormCard.style.display = 'block';
                } else {
                    displayCard.style.display = 'flex';
                    editFormCard.style.display = 'none';
                }
            }
        }
    };

    EventsModule.init();


    // ==========================================
    // 6. LUXURY SECTION MODULE (AJAX HANDLED)
    // ==========================================
    const LuxuryModule = {
        init() {
            const form = document.getElementById('luxury-form');
            if (!form) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                    submitBtn.style.cursor = 'wait';
                    if (!submitBtn.dataset.originalText) {
                        submitBtn.dataset.originalText = submitBtn.innerHTML;
                    }
                    submitBtn.innerHTML = `
                        <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 0.5rem;">⚙️</span>
                        Saving Changes...
                    `;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });

                    const data = await response.json().catch(() => ({ 
                        success: false, 
                        message: 'Server returned an unreadable response.' 
                    }));

                    if (response.status === 401) {
                        alert('Your session has expired. Please log in again.');
                        window.location.reload();
                        return;
                    }

                    if (!response.ok) {
                        throw new Error(data.message || `HTTP Error ${response.status}`);
                    }

                    if (data.success) {
                        this.showAlert('✅ ' + data.message, '#dcfce7', '#15803d');
                    } else {
                        this.showAlert('❌ ' + (data.message || 'Failed to update section.'), '#fee2e2', '#dc2626');
                    }
                } catch (err) {
                    this.showAlert('❌ ' + (err.message || 'A network error occurred while saving.'), '#fee2e2', '#dc2626');
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '1';
                        submitBtn.style.cursor = 'pointer';
                        if (submitBtn.dataset.originalText) {
                            submitBtn.innerHTML = submitBtn.dataset.originalText;
                        }
                    }
                }
            });
        },

        showAlert(message, bg, color) {
            const alertBox = document.getElementById('alert-box');
            if (!alertBox) return;

            alertBox.textContent = message;
            alertBox.style.background = bg;
            alertBox.style.color = color;
            alertBox.style.display = 'block';
            alertBox.style.opacity = '1';

            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (window.AlertsModule && typeof window.AlertsModule.autoHide === 'function') {
                window.AlertsModule.autoHide(alertBox);
            }
        }
    };

    LuxuryModule.init();


    // ==========================================
    // 7. INQUIRIES & LEADS MANAGEMENT MODULE
    // ==========================================
   const InquiryModule = {
    endpoint: '../controllers/inquiry_controller.php',
    styles: {
        new: { bg: '#fef3c7', text: '#b45309' },
        contacted: { bg: '#dbeafe', text: '#1d4ed8' },
        confirmed: { bg: '#dcfce7', text: '#15803d' },
        archived: { bg: '#f1f5f9', text: '#64748b' }
    },

    async updateStatus(id, selectElement) {
        const selectedStatus = selectElement.value;
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', selectedStatus);

        selectElement.disabled = true;
        selectElement.style.opacity = '0.6';

        try {
            const response = await fetch(`${this.endpoint}?action=update_status`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const rawText = await response.text();
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (e) {
                console.error('Non-JSON Server Output:', rawText);
                throw new Error('Invalid response format');
            }

            if (response.status === 401) {
                alert('Your session has expired. Please log in again.');
                window.location.reload();
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error updating status');
            }

            // Style dropdown element dynamically
            const style = this.styles[selectedStatus] || this.styles.archived;
            selectElement.style.background = style.bg;
            selectElement.style.color = style.text;

            // Sync with DataTables if active
            if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#inquiriesTable')) {
                const table = $('#inquiriesTable').DataTable();
                const tdCell = $(selectElement).closest('td');
                tdCell.attr('data-search', selectedStatus);
                table.cell(tdCell).invalidate().draw(false);
            }

            if (data.metrics) {
                this.updateMetricsUI(data.metrics);
            }
        } catch (err) {
            alert(err.message || 'Network error while updating status');
        } finally {
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
        }
    },

    async deleteInquiry(id) {
        if (!confirm('Are you sure you want to delete this inquiry?')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);

        try {
            const response = await fetch(`${this.endpoint}?action=delete_inquiry`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const rawText = await response.text();
            let data;
            try {
                data = JSON.parse(rawText);
            } catch (e) {
                console.error('Non-JSON Server Output:', rawText);
                throw new Error('Invalid response format');
            }

            if (response.status === 401) {
                alert('Your session has expired. Please log in again.');
                window.location.reload();
                return;
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Error deleting inquiry');
            }

            const row = document.getElementById(`inquiry-row-${id}`);
            if (row) {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                
                setTimeout(() => {
                    if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#inquiriesTable')) {
                        const table = $('#inquiriesTable').DataTable();
                        table.row($(row)).remove().draw(false);
                    } else if (row.parentNode) {
                        row.remove();
                    }
                }, 300);
            }

            if (data.metrics) {
                this.updateMetricsUI(data.metrics);
            }
        } catch (err) {
            alert(err.message || 'Network error while deleting inquiry');
        }
    },

    updateMetricsUI(metrics) {
        if (!metrics) return;

        ['total', 'new', 'confirmed'].forEach(key => {
            const el = document.getElementById(`metric-${key}`);
            if (el && metrics[key] !== undefined) {
                el.textContent = metrics[key];
            }
        });
    }
};

window.InquiryModule = InquiryModule;
window.updateInquiryStatus = (id, selectElement) => InquiryModule.updateStatus(id, selectElement);
window.deleteInquiry = (id) => InquiryModule.deleteInquiry(id);


    // ==========================================
    // 8. TRIP PLANNER & BASE RATES MODULE
    // ==========================================
    const PlanModule = {
        init() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM() {
            this.stepsTableBody = document.getElementById('steps-table-body');
            this.addStepBtn = document.getElementById('btn-add-step');
            this.stepsForm = document.getElementById('plan-steps-form');
            this.ratesForm = document.getElementById('plan-rates-form');
            this.alertBox = document.getElementById('plan-alert-box');
        },

        bindEvents() {
            if (this.addStepBtn) {
                this.addStepBtn.addEventListener('click', () => this.addStepRow());
            }

            if (this.stepsTableBody) {
                this.stepsTableBody.addEventListener('click', (e) => {
                    const removeBtn = e.target.closest('.btn-remove-step');
                    if (removeBtn) {
                        this.removeStepRow(removeBtn);
                    }
                });
            }

            if (this.stepsForm) {
                this.stepsForm.addEventListener('submit', (e) => this.handleFormSubmit(e, this.stepsForm));
            }

            if (this.ratesForm) {
                this.ratesForm.addEventListener('submit', (e) => this.handleFormSubmit(e, this.ratesForm));
            }
        },

        addStepRow() {
            if (!this.stepsTableBody) return;

            const tr = document.createElement('tr');
            tr.className = 'step-row';
            tr.style.borderBottom = '1px solid #f1f5f9';
            
            tr.innerHTML = `
                <td class="step-label" style="padding: 0.6rem 0.5rem; font-weight: 600; color: #64748b; vertical-align: top; text-align: center; font-size: 0.85rem;"></td>
                <td style="padding: 0.5rem;">
                    <textarea name="steps[]" rows="2" style="width: 100%; box-sizing: border-box; padding: 0.5rem; border: 1px solid var(--border-color, #cbd5e1); border-radius: 4px; font-size: 0.85rem;" placeholder="Enter step description..." required></textarea>
                </td>
                <td style="padding: 0.5rem; text-align: center; vertical-align: top;">
                    <button type="button" class="btn-remove-step" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600; padding: 0.25rem;">Remove</button>
                </td>
            `;

            this.stepsTableBody.appendChild(tr);
            this.reindexSteps();
        },

        removeStepRow(button) {
            if (!this.stepsTableBody) return;
            
            const rows = this.stepsTableBody.querySelectorAll('.step-row');
            if (rows.length <= 1) {
                alert('At least one travel step is required.');
                return;
            }

            const tr = button.closest('.step-row');
            if (tr) {
                tr.remove();
                this.reindexSteps();
            }
        },

        reindexSteps() {
            if (!this.stepsTableBody) return;
            const labels = this.stepsTableBody.querySelectorAll('.step-label');
            labels.forEach((label, idx) => {
                label.textContent = idx + 1;
            });
        },

        async handleFormSubmit(e, form) {
            e.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';
                if (!submitBtn.dataset.originalText) {
                    submitBtn.dataset.originalText = submitBtn.innerHTML;
                }
                submitBtn.innerHTML = `
                    <span style="display: inline-block; animation: spin 1s linear infinite; margin-right: 0.5rem;">⚙️</span>
                    Saving...
                `;
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json().catch(() => ({ 
                    success: false, 
                    message: 'Server returned an unreadable response.' 
                }));

                if (response.status === 401) {
                    alert('Your session has expired. Please log in again.');
                    window.location.reload();
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || `HTTP Error ${response.status}`);
                }

                this.showAlert('✅ ' + data.message, '#dcfce7', '#15803d');
            } catch (err) {
                this.showAlert('❌ ' + (err.message || 'A network error occurred while saving.'), '#fee2e2', '#dc2626');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    if (submitBtn.dataset.originalText) {
                        submitBtn.innerHTML = submitBtn.dataset.originalText;
                    }
                }
            }
        },

        showAlert(message, bg, color) {
            if (!this.alertBox) return;

            this.alertBox.textContent = message;
            this.alertBox.style.background = bg;
            this.alertBox.style.color = color;
            this.alertBox.style.display = 'block';
            this.alertBox.style.opacity = '1';

            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (window.AlertsModule && typeof window.AlertsModule.autoHide === 'function') {
                window.AlertsModule.autoHide(this.alertBox);
            }
        }
    };

    PlanModule.init();


    // ==========================================
    // 9. PROMO BANNER MODULE (AJAX HANDLED)
    // ==========================================
    const PromoModule = {
        init() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM() {
            this.form = document.getElementById('promo-banner-form');
            this.alertBox = document.getElementById('promo-alert-box');
        },

        bindEvents() {
            if (this.form) {
                this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            }
        },

        async handleSubmit(e) {
            e.preventDefault();

            if (!this.form.checkValidity()) {
                this.form.reportValidity();
                return;
            }

            const submitBtn = this.form.querySelector('button[type="submit"]');
            const formData = new FormData(this.form);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';
                if (!submitBtn.dataset.originalText) {
                    submitBtn.dataset.originalText = submitBtn.innerHTML;
                }
                submitBtn.innerHTML = 'Saving...';
            }

            try {
                const response = await fetch(this.form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json().catch(() => ({
                    success: false,
                    message: 'Server returned an unreadable response.'
                }));

                if (response.status === 401) {
                    alert('Your session has expired. Please log in again.');
                    window.location.reload();
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || `HTTP Error ${response.status}`);
                }

                this.showAlert('✅ ' + data.message, '#dcfce7', '#15803d');
            } catch (err) {
                this.showAlert('❌ ' + (err.message || 'A network error occurred while saving.'), '#fee2e2', '#dc2626');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.cursor = 'pointer';
                    if (submitBtn.dataset.originalText) {
                        submitBtn.innerHTML = submitBtn.dataset.originalText;
                    }
                }
            }
        },

        showAlert(message, bg, color) {
            if (!this.alertBox) return;

            this.alertBox.textContent = message;
            this.alertBox.style.background = bg;
            this.alertBox.style.color = color;
            this.alertBox.style.display = 'block';

            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (window.AlertsModule && typeof window.AlertsModule.autoHide === 'function') {
                window.AlertsModule.autoHide(this.alertBox);
            }
        }
    };

    PromoModule.init();


    // ==========================================
    // 10. SDF IMPACT MODULE (AJAX & CARD MANAGEMENT)
    // ==========================================
    const SdfModule = {
        init() {
            this.cacheDOM();
            this.bindEvents();
        },

        cacheDOM() {
            this.headingsForm = document.getElementById('sdf-headings-form');
            this.cardForm     = document.getElementById('sdf-card-form');
            this.alertBox     = document.getElementById('sdf-alert-box');
            
            this.cardIdInput   = document.getElementById('card_id');
            this.cardTitleInput= document.getElementById('card_title');
            this.cardDescInput = document.getElementById('card_desc');
            this.cardImgInput  = document.getElementById('card_image');
            this.cardFormTitle = document.getElementById('card-form-title');
            this.cardSubmitBtn = document.getElementById('card-submit-btn');
            this.cardCancelBtn = document.getElementById('card-cancel-btn');
            this.cardImageNote = document.getElementById('card-image-note');
        },

        bindEvents() {
            if (this.headingsForm) {
                this.headingsForm.addEventListener('submit', (e) => this.handleHeadingsSubmit(e));
            }

            if (this.cardForm) {
                this.cardForm.addEventListener('submit', (e) => this.handleCardSubmit(e));
            }

            if (this.cardCancelBtn) {
                this.cardCancelBtn.addEventListener('click', () => this.resetCardForm());
            }

            document.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.btn-edit-card');
                if (editBtn) {
                    this.populateCardForm(editBtn.dataset);
                }

                const deleteBtn = e.target.closest('.btn-delete-card');
                if (deleteBtn) {
                    this.handleCardDelete(deleteBtn.dataset.id);
                }
            });
        },

        async handleHeadingsSubmit(e) {
            e.preventDefault();
            const formData = new FormData(this.headingsForm);
            await this.sendRequest(this.headingsForm.action, formData);
        },

        async handleCardSubmit(e) {
            e.preventDefault();
            const formData = new FormData(this.cardForm);
            const isSuccess = await this.sendRequest(this.cardForm.action, formData);
            
            if (isSuccess) {
                this.resetCardForm();
                setTimeout(() => window.location.reload(), 800);
            }
        },

        async handleCardDelete(id) {
            if (!confirm('Are you sure you want to delete this feature card?')) return;

            const formData = new FormData();
            formData.append('card_id', id);

            const isSuccess = await this.sendRequest('/admin/controllers/sdf_controller.php?action=delete_card', formData);
            if (isSuccess) {
                setTimeout(() => window.location.reload(), 600);
            }
        },

        populateCardForm(data) {
            if (this.cardIdInput) this.cardIdInput.value = data.id || '';
            if (this.cardTitleInput) this.cardTitleInput.value = data.title || '';
            if (this.cardDescInput) this.cardDescInput.value = data.desc || '';

            if (this.cardFormTitle) this.cardFormTitle.textContent = '2. Edit Feature Card';
            if (this.cardSubmitBtn) this.cardSubmitBtn.textContent = 'Update Card';
            if (this.cardCancelBtn) this.cardCancelBtn.style.display = 'inline-block';
            if (this.cardImgInput) this.cardImgInput.required = false;
            if (this.cardImageNote) this.cardImageNote.textContent = 'Leave empty to keep current image.';

            if (this.cardForm) {
                requestAnimationFrame(() => {
                    this.cardForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            }
        },

        resetCardForm() {
            if (this.cardForm) this.cardForm.reset();
            if (this.cardIdInput) this.cardIdInput.value = '';
            if (this.cardFormTitle) this.cardFormTitle.textContent = '2. Add Feature Card';
            if (this.cardSubmitBtn) this.cardSubmitBtn.textContent = 'Add Card to Grid';
            if (this.cardCancelBtn) this.cardCancelBtn.style.display = 'none';
            if (this.cardImgInput) this.cardImgInput.required = true;
            if (this.cardImageNote) this.cardImageNote.textContent = 'Accepted formats: JPG, PNG, WEBP';
        },

        async sendRequest(url, formData) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                const data = await response.json().catch(() => ({
                    success: false,
                    message: 'Invalid server response.'
                }));

                if (response.status === 401) {
                    alert('Your session has expired. Please log in again.');
                    window.location.reload();
                    return false;
                }

                if (!response.ok) throw new Error(data.message || `HTTP ${response.status}`);

                if (data.success) {
                    this.showAlert('✅ ' + data.message, '#dcfce7', '#15803d');
                    return true;
                } else {
                    this.showAlert('❌ ' + (data.message || 'Operation failed.'), '#fee2e2', '#dc2626');
                    return false;
                }
            } catch (err) {
                this.showAlert('❌ ' + (err.message || 'Network error occurred.'), '#fee2e2', '#dc2626');
                return false;
            }
        },

        showAlert(message, bg, color) {
            if (!this.alertBox) return;
            this.alertBox.textContent = message;
            this.alertBox.style.background = bg;
            this.alertBox.style.color = color;
            this.alertBox.style.display = 'block';

            window.scrollTo({ top: 0, behavior: 'smooth' });

            if (window.AlertsModule && typeof window.AlertsModule.autoHide === 'function') {
                window.AlertsModule.autoHide(this.alertBox);
            }
        }
    };

    SdfModule.init();

// ==========================================================================
// 11. Admin Series Departures Management Module
// ==========================================================================
window.AdminSeriesDepartures = {
    modal: null,
    form: null,
    titleEl: null,
    alertEl: null,
    btnSave: null,

    init: function () {
        this.modal = document.getElementById('seriesModal');
        this.form = document.getElementById('seriesForm');
        this.titleEl = document.getElementById('modalTitle');
        this.alertEl = document.getElementById('modalAlert');
        this.btnSave = document.getElementById('btnSave');

        // Stop execution if elements don't exist on current page
        if (!this.modal && !this.form) return;

        this.bindEvents();
    },

    openModal: function (depData = null) {
        if (!this.form || !this.modal) return;

        this.form.reset();
        if (this.alertEl) this.alertEl.style.display = 'none';

        if (depData) {
            // Edit Mode
            if (this.titleEl) this.titleEl.textContent = 'Edit Series Departure';
            
            document.getElementById('dep_id').value = depData.id || '';
            document.getElementById('start_date').value = depData.start_date || '';
            document.getElementById('end_date').value = depData.end_date || '';
            document.getElementById('total_capacity').value = depData.total_capacity || 12;
            document.getElementById('min_passengers').value = depData.min_passengers || 4;
            document.getElementById('base_price').value = depData.base_price || '';
            document.getElementById('booked_seats').value = depData.booked_seats || 0;
        } else {
            // Create Mode
            if (this.titleEl) this.titleEl.textContent = 'Create Series Departure';
            const depIdInput = document.getElementById('dep_id');
            if (depIdInput) depIdInput.value = '';
        }

        this.modal.style.display = 'flex';
    },

    closeModal: function () {
        if (this.modal) this.modal.style.display = 'none';
        if (this.form) this.form.reset();
        if (this.btnSave) {
            this.btnSave.disabled = false;
            this.btnSave.textContent = 'Save Departure';
        }
    },

    saveDeparture: async function (e) {
        if (e && e.preventDefault) e.preventDefault();
        const self = this;

        if (!self.form) return;
        if (self.btnSave) self.btnSave.disabled = true;

        const formData = new FormData(self.form);

        try {
            const response = await fetch('/admin/api/series-departures.php?action=save', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (self.alertEl) {
                self.alertEl.style.display = 'block';
                if (result.success) {
                    self.alertEl.style.background = '#dcfce7';
                    self.alertEl.style.color = '#166534';
                    self.alertEl.textContent = result.message;
                    setTimeout(() => location.reload(), 1000);
                } else {
                    throw new Error(result.message || 'Operation failed.');
                }
            }
        } catch (err) {
            if (self.alertEl) {
                self.alertEl.style.display = 'block';
                self.alertEl.style.background = '#fee2e2';
                self.alertEl.style.color = '#991b1b';
                self.alertEl.textContent = err.message || 'Network error occurred.';
            }
            if (self.btnSave) self.btnSave.disabled = false;
        }
    },

    deleteDeparture: async function (id) {
        if (!confirm('Are you sure you want to delete this departure schedule?')) return;

        const formData = new FormData();
        formData.append('id', id);

        try {
            const response = await fetch('/admin/api/series-departures.php?action=delete', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Failed to delete departure.');
            }
        } catch (err) {
            alert('Network error while deleting departure.');
        }
    },

    /**
     * Renders customer bookings into the admin details modal, including passport BLOB links
     */
    renderBookingsModal: function (title, dates, capacity, price, desc, bookings) {
        const modalTitle = document.getElementById('adminModalTitle');
        const modalDates = document.getElementById('adminModalDates');
        const modalCapacity = document.getElementById('adminModalCapacity');
        const modalPrice = document.getElementById('adminModalPrice');
        const modalDesc = document.getElementById('adminModalDesc');
        const tbody = document.getElementById('adminModalBookingsBody');

        if (modalTitle) modalTitle.textContent = title || 'Departure Details';
        if (modalDates) modalDates.textContent = 'Scheduled: ' + dates;
        if (modalCapacity) modalCapacity.textContent = capacity;
        if (modalPrice) modalPrice.textContent = price;
        if (modalDesc) modalDesc.textContent = desc || 'No description available.';

        if (!tbody) return;
        tbody.innerHTML = '';

        if (bookings && bookings.length > 0) {
            bookings.forEach(b => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f1f5f9';

                const currentStatus = (b.payment_status || 'pending').toLowerCase();
                const email = b.customer_email || '';
                
                // Check if passenger passport BLOB exists
                const hasPassport = b.passenger_passport_id !== null && b.passenger_passport_id !== undefined;

                const passportHtml = hasPassport
                    ? `<a href="DepartureController.php?action=download_passport&booking_id=${b.id}" 
                          style="color: #0284c7; font-weight: 600; text-decoration: underline;" 
                          target="_blank">
                          📄 View Scan
                       </a>`
                    : `<span style="color: #94a3b8; font-style: italic;">None</span>`;

                tr.innerHTML = `
                    <td style="padding: 0.6rem 0.85rem; font-weight: 600; color: #0284c7;">${b.booking_reference || 'N/A'}</td>
                    <td style="padding: 0.6rem 0.85rem;">
                        <div style="font-weight: 600; color: #0f172a;">${b.customer_name || 'N/A'}</div>
                        ${email ? `<a href="mailto:${email}" style="font-size: 0.75rem; color: #0284c7; text-decoration: underline;">${email}</a>` : ''}
                    </td>
                    <td style="padding: 0.6rem 0.85rem;">${passportHtml}</td>
                    <td style="padding: 0.6rem 0.85rem;">${b.seats_booked || 1}</td>
                    <td style="padding: 0.6rem 0.85rem; font-weight: 600;">$${parseFloat(b.total_amount || 0).toFixed(2)}</td>
                    <td style="padding: 0.6rem 0.85rem;">
                        <select onchange="updatePaymentStatus(this, ${b.id})" 
                                data-current="${currentStatus}"
                                style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; cursor: pointer; background: #ffffff;">
                            <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="paid" ${currentStatus === 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="cancelled" ${currentStatus === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 1rem; text-align: center; color: #64748b;">No customer bookings recorded for this departure.</td>
                </tr>
            `;
        }

        const viewModal = document.getElementById('adminViewModal');
        if (viewModal) viewModal.style.display = 'flex';
    },

    /**
     * Handles payment status change from the customer booking modal
     */
    updatePaymentStatus: async function (selectElement, bookingId) {
        if (!selectElement || !bookingId) return;

        const newStatus = selectElement.value;
        const oldStatus = selectElement.getAttribute('data-current') || 'pending';

        selectElement.disabled = true;

        try {
            const response = await fetch('DepartureController.php?action=update_payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId, status: newStatus })
            });

            const result = await response.json();

            if (result.success) {
                selectElement.setAttribute('data-current', newStatus);
                // Reload page to reflect updated seat counts across tables instantly
                location.reload();
            } else {
                alert(result.message || 'Failed to update payment status.');
                selectElement.value = oldStatus;
            }
        } catch (err) {
            console.error('Network Error updating payment status:', err);
            alert('Server error occurred while updating status.');
            selectElement.value = oldStatus;
        } finally {
            selectElement.disabled = false;
        }
    },

    bindEvents: function () {
        const self = this;

        // Form Submit
        if (this.form) {
            this.form.addEventListener('submit', function (e) {
                self.saveDeparture(e);
            });
        }

        // Global Event Delegation for buttons
        document.addEventListener('click', function (e) {
            // Trigger Create Modal
            if (e.target.closest('[data-action="create-departure"]')) {
                self.openModal();
            }

            // Trigger Edit Modal
            const editBtn = e.target.closest('[data-action="edit-departure"]');
            if (editBtn) {
                const rawData = editBtn.getAttribute('data-departure');
                if (rawData) {
                    try {
                        const depData = JSON.parse(rawData);
                        self.openModal(depData);
                    } catch (err) {
                        console.error('Invalid departure JSON data', err);
                    }
                }
            }

            // Trigger Delete
            const deleteBtn = e.target.closest('[data-action="delete-departure"]');
            if (deleteBtn) {
                const id = deleteBtn.getAttribute('data-id');
                if (id) self.deleteDeparture(id);
            }

            // Trigger Modal Close
            if (e.target.closest('[data-action="close-modal"]') || e.target === self.modal) {
                self.closeModal();
            }
        });

        // Close on Escape key press
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (self.modal && self.modal.style.display === 'flex') {
                    self.closeModal();
                }
                const adminViewModal = document.getElementById('adminViewModal');
                if (adminViewModal && adminViewModal.style.display === 'flex') {
                    adminViewModal.style.display = 'none';
                }
            }
        });
    }
};

// Global backward-compatibility helpers for inline HTML events
window.openSeriesModal = function () {
    window.AdminSeriesDepartures.openModal();
};

window.closeSeriesModal = function () {
    window.AdminSeriesDepartures.closeModal();
};

window.openAdminViewModal = function (title, dates, capacity, price, desc, bookings) {
    window.AdminSeriesDepartures.renderBookingsModal(title, dates, capacity, price, desc, bookings);
};

window.closeAdminViewModal = function () {
    const adminViewModal = document.getElementById('adminViewModal');
    if (adminViewModal) adminViewModal.style.display = 'none';
};

window.editDeparture = function (dep) {
    window.AdminSeriesDepartures.openModal(dep);
};

window.saveDeparture = function (e) {
    window.AdminSeriesDepartures.saveDeparture(e);
};

window.deleteDeparture = function (id) {
    window.AdminSeriesDepartures.deleteDeparture(id);
};

window.updatePaymentStatus = function (selectElement, bookingId) {
    window.AdminSeriesDepartures.updatePaymentStatus(selectElement, bookingId);
};

// Auto-initialize module on DOM ready
document.addEventListener('DOMContentLoaded', function () {
    window.AdminSeriesDepartures.init();
});

});