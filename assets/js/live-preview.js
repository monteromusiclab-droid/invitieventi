/**
 * Live Preview - Anteprima in tempo reale mentre si modifica l'invito
 * Aggiorna l'anteprima senza salvare, in tempo reale
 */

(function($) {
    'use strict';

    const LivePreview = {
        previewContainer: null,
        previewFrame: null,
        isPreviewVisible: false,
        debounceTimer: null,
        currentData: {},

        init: function() {
            this.previewContainer = $('#wi-live-preview-container');
            this.previewFrame = $('#wi-live-preview-frame');

            if (!this.previewContainer.length) {
                this.createPreviewContainer();
            }

            this.bindEvents();
            this.setupResponsiveToggles();

            console.log('🎨 Live Preview initialized');
        },

        createPreviewContainer: function() {
            const html = `
                <div id="wi-live-preview-container" class="wi-live-preview-sidebar">
                    <div class="wi-preview-header">
                        <div class="wi-preview-title">
                            <span class="dashicons dashicons-visibility"></span>
                            <span>Anteprima Live</span>
                        </div>
                        <div class="wi-preview-controls">
                            <div class="wi-device-toggles">
                                <button type="button" class="wi-device-btn active" data-device="desktop" title="Desktop">
                                    <span class="dashicons dashicons-desktop"></span>
                                </button>
                                <button type="button" class="wi-device-btn" data-device="tablet" title="Tablet">
                                    <span class="dashicons dashicons-tablet"></span>
                                </button>
                                <button type="button" class="wi-device-btn" data-device="mobile" title="Mobile">
                                    <span class="dashicons dashicons-smartphone"></span>
                                </button>
                            </div>
                            <button type="button" class="wi-preview-refresh" title="Aggiorna anteprima">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="wi-preview-close" title="Chiudi anteprima">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        </div>
                    </div>
                    <div class="wi-preview-body">
                        <div class="wi-preview-frame-wrapper desktop">
                            <iframe id="wi-live-preview-frame" frameborder="0"></iframe>
                            <div class="wi-preview-loading">
                                <span class="spinner is-active"></span>
                                <p>Caricamento anteprima...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(html);
            this.previewContainer = $('#wi-live-preview-container');
            this.previewFrame = $('#wi-live-preview-frame');
        },

        bindEvents: function() {
            const self = this;

            // Toggle anteprima live
            $(document).on('click', '#wi-toggle-live-preview', function(e) {
                e.preventDefault();
                self.togglePreview();
            });

            // Chiudi anteprima
            $(document).on('click', '.wi-preview-close', function() {
                self.hidePreview();
            });

            // Refresh manuale
            $(document).on('click', '.wi-preview-refresh', function() {
                self.updatePreview(true);
            });

            // Device toggles
            $(document).on('click', '.wi-device-btn', function() {
                const device = $(this).data('device');
                self.switchDevice(device);
            });

            // Watch form changes
            this.watchFormChanges();
        },

        watchFormChanges: function() {
            const self = this;
            const $form = $('#wi-edit-form, #wi-invite-form');

            if (!$form.length) return;

            // Input text, textarea, date, time
            $form.on('input change', 'input[type="text"], input[type="date"], input[type="time"], textarea, select', function() {
                self.scheduleUpdate();
            });

            // Template change
            $form.on('change', 'input[name="template_id"]', function() {
                self.scheduleUpdate();
            });

            // Image upload
            $(document).on('wi_image_uploaded', function() {
                self.scheduleUpdate();
            });
        },

        scheduleUpdate: function() {
            const self = this;

            // Debounce per evitare troppe chiamate
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(function() {
                self.updatePreview();
            }, 800); // Attendi 800ms dopo l'ultima modifica
        },

        updatePreview: function(force = false) {
            if (!this.isPreviewVisible && !force) return;

            const self = this;
            const formData = this.collectFormData();

            // Se i dati non sono cambiati, non aggiornare
            if (!force && JSON.stringify(formData) === JSON.stringify(this.currentData)) {
                return;
            }

            this.currentData = formData;
            this.showLoading();

            $.ajax({
                url: wiAdmin.ajax_url || ajaxurl,
                type: 'POST',
                data: {
                    action: 'wi_live_preview',
                    nonce: wiAdmin.nonce || wiPublic.nonce,
                    invite_data: formData,
                    template_id: formData.template_id
                },
                success: function(response) {
                    if (response.success) {
                        self.renderPreview(response.data.html);
                    } else {
                        self.showError(response.data || 'Errore nel caricamento');
                    }
                },
                error: function() {
                    self.showError('Errore di connessione');
                },
                complete: function() {
                    self.hideLoading();
                }
            });
        },

        collectFormData: function() {
            const $form = $('#wi-edit-form, #wi-invite-form');

            return {
                title: $form.find('[name="invite_title"]').val() || 'Titolo Invito',
                message: $form.find('[name="invite_message"]').val() || 'Messaggio invito...',
                final_message: $form.find('[name="final_message"]').val() || '',
                final_message_button_text: $form.find('[name="final_message_button_text"]').val() || '',
                event_date: $form.find('[name="event_date"]').val() || new Date().toISOString().split('T')[0],
                event_time: $form.find('[name="event_time"]').val() || '18:00',
                event_location: $form.find('[name="event_location"]').val() || 'Nome Luogo',
                event_address: $form.find('[name="event_address"]').val() || 'Indirizzo completo',
                template_id: $form.find('[name="template_id"]:checked').val() || 1,
                user_image_id: $form.find('[name="user_image_id"]').val() || 0,
                user_image_url: this.getCurrentImageUrl()
            };
        },

        getCurrentImageUrl: function() {
            const $preview = $('#current-image-preview');
            if ($preview.length && $preview.attr('src')) {
                return $preview.attr('src');
            }
            return '';
        },

        renderPreview: function(html) {
            const doc = this.previewFrame[0].contentDocument || this.previewFrame[0].contentWindow.document;

            doc.open();
            doc.write(html);
            doc.close();

            // Trigger evento per eventuali script esterni
            $(document).trigger('wi_preview_rendered');
        },

        togglePreview: function() {
            if (this.isPreviewVisible) {
                this.hidePreview();
            } else {
                this.showPreview();
            }
        },

        showPreview: function() {
            this.previewContainer.addClass('visible');
            $('body').addClass('wi-preview-active');
            this.isPreviewVisible = true;

            // Update subito quando si apre
            this.updatePreview(true);

            // Trigger evento
            $(document).trigger('wi_preview_shown');
        },

        hidePreview: function() {
            this.previewContainer.removeClass('visible');
            $('body').removeClass('wi-preview-active');
            this.isPreviewVisible = false;

            $(document).trigger('wi_preview_hidden');
        },

        switchDevice: function(device) {
            $('.wi-device-btn').removeClass('active');
            $(`.wi-device-btn[data-device="${device}"]`).addClass('active');

            $('.wi-preview-frame-wrapper')
                .removeClass('desktop tablet mobile')
                .addClass(device);
        },

        showLoading: function() {
            $('.wi-preview-loading').addClass('active');
        },

        hideLoading: function() {
            $('.wi-preview-loading').removeClass('active');
        },

        showError: function(message) {
            const errorHtml = `
                <div class="wi-preview-error">
                    <span class="dashicons dashicons-warning"></span>
                    <p>${message}</p>
                </div>
            `;

            const doc = this.previewFrame[0].contentDocument || this.previewFrame[0].contentWindow.document;
            doc.open();
            doc.write(errorHtml);
            doc.close();
        },

        setupResponsiveToggles: function() {
            // Già implementato nei device toggles
        }
    };

    // Init on document ready
    $(document).ready(function() {
        LivePreview.init();
    });

    // Esponi globalmente per estensioni
    window.WI_LivePreview = LivePreview;

})(jQuery);
