/**
 * Wedding Invites Pro - QR Code Generator
 * Gestione generazione e personalizzazione QR Code
 */

(function($) {
    'use strict';

    const QRGenerator = {
        currentQR: null,
        inviteId: null,

        init: function() {
            this.inviteId = $('#wi-generate-qr').data('invite-id');
            this.bindEvents();
            this.loadExistingQR();
        },

        bindEvents: function() {
            $('#wi-generate-qr').on('click', (e) => {
                e.preventDefault();
                this.generateQR();
            });

            $('#wi-download-qr').on('click', (e) => {
                e.preventDefault();
                this.downloadQR();
            });

            $('#wi-customize-qr').on('click', (e) => {
                e.preventDefault();
                this.showCustomizeModal();
            });

            // Gestione modal personalizzazione
            $(document).on('click', '#wi-apply-qr-customization', () => {
                this.applyCustomization();
            });

            $(document).on('click', '.wi-qr-modal-close', () => {
                this.closeCustomizeModal();
            });
        },

        loadExistingQR: function() {
            // Controlla se esiste già un QR salvato
            $.ajax({
                url: wiAdmin.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_qr_code',
                    nonce: wiAdmin.nonce,
                    invite_id: this.inviteId
                },
                success: (response) => {
                    if (response.success && response.data.qr_url) {
                        this.displayQR(response.data.qr_url);
                    }
                }
            });
        },

        generateQR: function(options = {}) {
            const $button = $('#wi-generate-qr');
            $button.prop('disabled', true).text('Generazione...');

            $('#wi-qr-preview').html('<div class="spinner is-active" style="float: none; margin: 40px auto;"></div>');

            $.ajax({
                url: wiAdmin.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_generate_qr_code',
                    nonce: wiAdmin.nonce,
                    invite_id: this.inviteId,
                    options: JSON.stringify(options)
                },
                success: (response) => {
                    if (response.success) {
                        this.displayQR(response.data.qr_url);
                        this.showSuccessMessage('QR Code generato con successo!');
                    } else {
                        this.showErrorMessage(response.data.message || 'Errore nella generazione');
                        this.resetPreview();
                    }
                },
                error: () => {
                    this.showErrorMessage('Errore di connessione');
                    this.resetPreview();
                },
                complete: () => {
                    $button.prop('disabled', false);
                    $button.html('<span class="dashicons dashicons-update"></span> Rigenera QR Code');
                }
            });
        },

        displayQR: function(qrUrl) {
            this.currentQR = qrUrl;

            const $preview = $('#wi-qr-preview');
            $preview.html(`
                <img src="${qrUrl}?t=${Date.now()}" alt="QR Code" class="wi-qr-image">
            `);

            // Mostra pulsanti download e personalizza
            $('#wi-download-qr, #wi-customize-qr').show();
            $('#wi-generate-qr').html('<span class="dashicons dashicons-update"></span> Rigenera QR Code');
        },

        resetPreview: function() {
            $('#wi-qr-preview').html(`
                <div class="wi-qr-placeholder">
                    <span class="dashicons dashicons-smartphone" style="font-size: 48px; color: #dcdcde;"></span>
                    <p>Genera QR Code</p>
                </div>
            `);
            $('#wi-download-qr, #wi-customize-qr').hide();
        },

        downloadQR: function() {
            if (!this.currentQR) {
                alert('Nessun QR Code da scaricare');
                return;
            }

            // Crea link temporaneo per download
            const a = document.createElement('a');
            a.href = this.currentQR;
            a.download = `qr-invite-${this.inviteId}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            this.showSuccessMessage('Download avviato!');
        },

        showCustomizeModal: function() {
            const modalHTML = `
                <div class="wi-qr-modal">
                    <div class="wi-qr-modal-overlay"></div>
                    <div class="wi-qr-modal-content">
                        <div class="wi-qr-modal-header">
                            <h2>Personalizza QR Code</h2>
                            <button type="button" class="wi-qr-modal-close">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </div>
                        <div class="wi-qr-modal-body">
                            <div class="wi-qr-customize-section">
                                <h3>Dimensioni</h3>
                                <label>
                                    Larghezza (px):
                                    <input type="number" id="qr-size" value="300" min="100" max="1000" step="50">
                                </label>
                            </div>

                            <div class="wi-qr-customize-section">
                                <h3>Colori</h3>
                                <label>
                                    Colore Primo Piano:
                                    <input type="color" id="qr-fg-color" value="#000000">
                                </label>
                                <label>
                                    Colore Sfondo:
                                    <input type="color" id="qr-bg-color" value="#FFFFFF">
                                </label>
                            </div>

                            <div class="wi-qr-customize-section">
                                <h3>Opzioni Avanzate</h3>
                                <label>
                                    Margine:
                                    <input type="number" id="qr-margin" value="10" min="0" max="50">
                                </label>
                                <label>
                                    Correzione Errori:
                                    <select id="qr-error-correction">
                                        <option value="L">Bassa (7%)</option>
                                        <option value="M" selected>Media (15%)</option>
                                        <option value="Q">Alta (25%)</option>
                                        <option value="H">Massima (30%)</option>
                                    </select>
                                </label>
                            </div>

                            <div class="wi-qr-customize-section">
                                <h3>Anteprima</h3>
                                <div class="wi-qr-preview-mini" id="qr-preview-mini">
                                    <img src="${this.currentQR}" alt="Preview">
                                </div>
                            </div>
                        </div>
                        <div class="wi-qr-modal-footer">
                            <button type="button" class="button wi-qr-modal-close">Annulla</button>
                            <button type="button" class="button button-primary" id="wi-apply-qr-customization">
                                Applica Modifiche
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(modalHTML);

            // Live preview changes
            $('#qr-size, #qr-margin, #qr-error-correction, #qr-fg-color, #qr-bg-color').on('change input', () => {
                this.updateModalPreview();
            });
        },

        updateModalPreview: function() {
            // Mostra indicatore loading
            $('#qr-preview-mini').html('<div class="spinner is-active"></div>');

            clearTimeout(this.previewTimeout);
            this.previewTimeout = setTimeout(() => {
                const options = this.getCustomizationOptions();

                // Genera preview temporaneo
                $.ajax({
                    url: wiAdmin.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'wi_preview_qr_code',
                        nonce: wiAdmin.nonce,
                        invite_id: this.inviteId,
                        options: JSON.stringify(options)
                    },
                    success: (response) => {
                        if (response.success) {
                            $('#qr-preview-mini').html(`<img src="${response.data.qr_url}?t=${Date.now()}" alt="Preview">`);
                        }
                    }
                });
            }, 500);
        },

        getCustomizationOptions: function() {
            return {
                size: parseInt($('#qr-size').val()),
                margin: parseInt($('#qr-margin').val()),
                error_correction: $('#qr-error-correction').val(),
                foreground_color: $('#qr-fg-color').val().replace('#', ''),
                background_color: $('#qr-bg-color').val().replace('#', '')
            };
        },

        applyCustomization: function() {
            const options = this.getCustomizationOptions();

            this.closeCustomizeModal();
            this.generateQR(options);
        },

        closeCustomizeModal: function() {
            $('.wi-qr-modal').remove();
        },

        showSuccessMessage: function(message) {
            this.showMessage(message, 'success');
        },

        showErrorMessage: function(message) {
            this.showMessage(message, 'error');
        },

        showMessage: function(message, type) {
            const className = type === 'success' ? 'notice-success' : 'notice-error';

            const $notice = $(`
                <div class="notice ${className} is-dismissible" style="margin: 15px 0;">
                    <p>${message}</p>
                </div>
            `);

            $('.wi-qr-box').prepend($notice);

            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 3000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('#wi-generate-qr').length) {
            QRGenerator.init();
        }
    });

})(jQuery);
