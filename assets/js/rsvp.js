/**
 * Wedding Invites Pro - RSVP System Frontend
 * Gestisce il form wizard RSVP multi-step
 */

(function($) {
    'use strict';

    const RSVPForm = {
        currentStep: 1,
        totalSteps: 3,
        selectedStatus: null,
        inviteId: null,

        init: function() {
            const form = $('#wi-rsvp-form');

            if (!form.length) {
                return; // RSVP non presente in questa pagina
            }

            this.inviteId = form.data('invite-id');
            this.bindEvents();

            console.log('RSVP Form initialized', {inviteId: this.inviteId});
        },

        bindEvents: function() {
            // Selezione status (attending, not_attending, maybe)
            $('input[name="status"]').on('change', (e) => {
                this.selectedStatus = $(e.currentTarget).val();
                this.handleStatusChange();
            });

            // Pulsante "Continua"
            $('.wi-btn-next').on('click', (e) => {
                e.preventDefault();
                this.nextStep();
            });

            // Pulsante "Indietro"
            $('.wi-btn-back').on('click', (e) => {
                e.preventDefault();
                this.prevStep();
            });

            // Submit finale
            $('#wi-rsvp-form').on('submit', (e) => {
                e.preventDefault();
                this.submitRSVP();
            });

            // Validazione real-time email
            $('input[type="email"]').on('blur', function() {
                const email = $(this).val();
                if (email && !RSVPForm.isValidEmail(email)) {
                    $(this).addClass('wi-input-error');
                    $(this).siblings('.wi-error-message').remove();
                    $(this).after('<span class="wi-error-message">Email non valida</span>');
                } else {
                    $(this).removeClass('wi-input-error');
                    $(this).siblings('.wi-error-message').remove();
                }
            });
        },

        handleStatusChange: function() {
            console.log('Status changed:', this.selectedStatus);

            // Abilita pulsante "Continua"
            $('.wi-btn-next').prop('disabled', false);

            // Rimuovi selezione visiva precedente
            $('.wi-option-card').removeClass('selected');

            // Aggiungi selezione visiva
            $(`input[value="${this.selectedStatus}"]`).closest('.wi-option-card').addClass('selected');
        },

        nextStep: function() {
            if (!this.validateCurrentStep()) {
                return;
            }

            // Nascondi step corrente
            $(`.wi-rsvp-step[data-step="${this.currentStep}"]`).hide();

            // Determina prossimo step in base allo status
            if (this.currentStep === 1) {
                if (this.selectedStatus === 'attending' || this.selectedStatus === 'maybe') {
                    this.currentStep = 2; // Vai ai dettagli
                } else if (this.selectedStatus === 'not_attending') {
                    this.currentStep = 3; // Vai al messaggio declino
                }
            } else {
                this.currentStep++;
            }

            // Mostra nuovo step
            $(`.wi-rsvp-step[data-step="${this.currentStep}"]`).fadeIn(300);

            // Aggiorna pulsanti
            this.updateButtons();

            // Scroll verso il form
            this.scrollToForm();
        },

        prevStep: function() {
            // Nascondi step corrente
            $(`.wi-rsvp-step[data-step="${this.currentStep}"]`).hide();

            // Torna sempre allo step 1 (selezione status)
            this.currentStep = 1;

            // Mostra step 1
            $(`.wi-rsvp-step[data-step="1"]`).fadeIn(300);

            // Aggiorna pulsanti
            this.updateButtons();

            // Scroll verso il form
            this.scrollToForm();
        },

        updateButtons: function() {
            const $btnBack = $('.wi-btn-back');
            const $btnNext = $('.wi-btn-next');
            const $btnSubmit = $('.wi-btn-submit');

            if (this.currentStep === 1) {
                // Step 1: solo "Continua" (disabilitato fino a selezione)
                $btnBack.hide();
                $btnNext.show().prop('disabled', !this.selectedStatus);
                $btnSubmit.hide();
            } else if (this.currentStep === 2 || this.currentStep === 3) {
                // Step 2/3: "Indietro" + "Conferma"
                $btnBack.show();
                $btnNext.hide();
                $btnSubmit.show();
            }
        },

        validateCurrentStep: function() {
            if (this.currentStep === 1) {
                return !!this.selectedStatus;
            }

            if (this.currentStep === 2) {
                // Valida dettagli ospite (attending)
                const name = $('input[name="guest_name"]').val().trim();
                const email = $('input[name="guest_email"]').val().trim();

                if (!name || !email) {
                    this.showError('Nome ed email sono obbligatori');
                    return false;
                }

                if (!this.isValidEmail(email)) {
                    this.showError('Email non valida');
                    return false;
                }

                return true;
            }

            if (this.currentStep === 3) {
                // Valida declino
                const name = $('input[name="guest_name_decline"]').val().trim();
                const email = $('input[name="guest_email_decline"]').val().trim();

                if (!name || !email) {
                    this.showError('Nome ed email sono obbligatori');
                    return false;
                }

                if (!this.isValidEmail(email)) {
                    this.showError('Email non valida');
                    return false;
                }

                return true;
            }

            return true;
        },

        submitRSVP: function() {
            if (!this.validateCurrentStep()) {
                return;
            }

            // Raccogli dati form
            const formData = this.collectFormData();

            console.log('Submitting RSVP:', formData);

            // Mostra loader
            this.showLoader();

            // AJAX submit
            $.ajax({
                url: wiRSVP.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_submit_rsvp',
                    nonce: wiRSVP.nonce,
                    ...formData
                },
                success: (response) => {
                    console.log('RSVP response:', response);

                    this.hideLoader();

                    if (response.success) {
                        this.showSuccess(formData.guest_email || formData.guest_email_decline);
                    } else {
                        this.showError(response.data.message || 'Errore durante l\'invio. Riprova.');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('RSVP AJAX error:', {xhr, status, error});
                    this.hideLoader();
                    this.showError('Errore di connessione. Verifica la tua connessione e riprova.');
                }
            });
        },

        collectFormData: function() {
            const data = {
                invite_id: this.inviteId,
                status: this.selectedStatus
            };

            if (this.selectedStatus === 'attending' || this.selectedStatus === 'maybe') {
                // Dettagli completi
                data.guest_name = $('input[name="guest_name"]').val().trim();
                data.guest_email = $('input[name="guest_email"]').val().trim();
                data.guest_phone = $('input[name="guest_phone"]').val().trim();
                data.num_guests = $('select[name="num_guests"]').val();
                data.menu_choice = $('input[name="menu_choice"]:checked').val() || '';
                data.notes = $('textarea[name="notes"]').val().trim();

                // Raccogli allergie (checkbox multipli)
                data.dietary = [];
                $('input[name="dietary[]"]:checked').each(function() {
                    data.dietary.push($(this).val());
                });

            } else if (this.selectedStatus === 'not_attending') {
                // Solo nome ed email per declino
                data.guest_name = $('input[name="guest_name_decline"]').val().trim();
                data.guest_email = $('input[name="guest_email_decline"]').val().trim();
                data.num_guests = 0;
            }

            return data;
        },

        showLoader: function() {
            $('#wi-rsvp-form').hide();
            $('.wi-rsvp-loading').fadeIn(300);
        },

        hideLoader: function() {
            $('.wi-rsvp-loading').hide();
        },

        showSuccess: function(email) {
            $('.wi-rsvp-loading').hide();
            $('#confirmed-email').text(email);
            $('.wi-rsvp-success').fadeIn(400);

            // Scroll verso il messaggio di successo
            $('html, body').animate({
                scrollTop: $('.wi-rsvp-success').offset().top - 100
            }, 500);
        },

        showError: function(message) {
            // Rimuovi errori precedenti
            $('.wi-error-banner').remove();

            // Aggiungi nuovo errore
            const $error = $(`
                <div class="wi-error-banner">
                    <span class="wi-error-icon">⚠️</span>
                    <span class="wi-error-text">${message}</span>
                    <button type="button" class="wi-error-close">×</button>
                </div>
            `);

            $('.wi-rsvp-form').prepend($error);

            // Auto-dismiss dopo 5 secondi
            setTimeout(() => {
                $error.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Close button
            $error.find('.wi-error-close').on('click', function() {
                $error.fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Scroll verso l'errore
            $('html, body').animate({
                scrollTop: $('.wi-rsvp-form').offset().top - 100
            }, 300);
        },

        scrollToForm: function() {
            $('html, body').animate({
                scrollTop: $('#rsvp-section').offset().top - 100
            }, 400);
        },

        isValidEmail: function(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('#wi-rsvp-form').length) {
            console.log('RSVP section found, initializing...');
            RSVPForm.init();
        }
    });

})(jQuery);
