/**
 * Wedding Invites Pro - Creation Wizard
 * Wizard guidato per creazione inviti step-by-step
 */

(function($) {
    'use strict';

    const WizardSteps = {
        currentStep: 0, // Parte da 0 se c'è lo step selezione modalità
        totalSteps: 6, // Aumentato da 5 a 6 per includere RSVP
        formData: {},
        editMode: false,
        inviteId: null,

        init: function() {
            // Controlla modalità edit
            if (wiWizard.edit_mode && wiWizard.invite_data) {
                this.editMode = true;
                this.inviteId = wiWizard.invite_data.id;
                this.currentStep = 1; // Salta lo step 0 se già in edit mode
                console.log('Edit mode enabled for invite ID:', this.inviteId);
            } else {
                // Se non in edit mode, controlla se esiste lo step 0
                if ($('#wi-mode-selection-step').length > 0) {
                    this.currentStep = 0;
                    this.loadUserInvitesCount(); // Carica conteggio inviti utente
                } else {
                    this.currentStep = 1;
                }
            }

            this.bindEvents();
            this.updateProgress();
            this.loadEventCategories();

            // Se edit mode, carica dati dopo un breve delay per permettere il caricamento DOM
            if (this.editMode) {
                setTimeout(() => {
                    this.loadInviteData();
                }, 500);
            }
        },

        bindEvents: function() {
            const self = this;

            // Step 0: Selezione modalità
            $(document).on('click', '.wi-mode-card[data-mode="create"]', function(e) {
                e.preventDefault();
                console.log('Mode selected: Create new');
                self.goToStep(1); // Va allo step 1 (categoria)
            });

            $(document).on('click', '.wi-mode-card[data-mode="edit"]', function(e) {
                e.preventDefault();
                console.log('Mode selected: Edit existing');
                self.loadUserInvites(); // Carica lista inviti
            });

            $(document).on('click', '.wi-btn-back-to-mode', function(e) {
                e.preventDefault();
                $('#wi-existing-invites-list').fadeOut(300);
                setTimeout(() => {
                    $('.wi-mode-selection-grid').fadeIn(300);
                }, 300);
            });

            $(document).on('click', '.wi-invite-selection-card', function(e) {
                e.preventDefault();
                const inviteId = $(this).data('invite-id');
                console.log('Selected invite for editing:', inviteId);
                window.location.href = window.location.pathname + '?edit=' + inviteId;
            });

            // Pulsanti navigazione
            $('.wi-wizard-next').on('click', (e) => {
                e.preventDefault();
                this.nextStep();
            });

            $('.wi-wizard-prev').on('click', (e) => {
                e.preventDefault();
                this.prevStep();
            });

            // Step indicator click
            $('.wi-step-indicator').on('click', (e) => {
                const step = $(e.currentTarget).data('step');
                if (step < this.currentStep) {
                    this.goToStep(step);
                }
            });

            // Categoria evento selection
            $(document).on('click', '.wi-category-card-modern', (e) => {
                $('.wi-category-card-modern').removeClass('selected');
                $(e.currentTarget).addClass('selected');
                this.formData.event_category = $(e.currentTarget).data('category-slug');
                this.validateStep(1);

                // Carica template filtrati per questa categoria
                this.loadTemplatesByCategory(this.formData.event_category);

                // FIX v2.5.2: Auto-avanza allo step successivo dopo breve delay
                setTimeout(() => {
                    if (this.currentStep === 1 && this.formData.event_category) {
                        this.nextStep();
                    }
                }, 300); // Delay di 300ms per feedback visivo
            });

            // Template selection
            $(document).on('click', '.wi-template-card-modern', (e) => {
                $('.wi-template-card-modern').removeClass('selected');
                $(e.currentTarget).addClass('selected');
                this.formData.template_id = $(e.currentTarget).data('template-id');
                this.validateStep(2);

                // FIX v2.5.2: Auto-avanza allo step successivo dopo breve delay
                setTimeout(() => {
                    if (this.currentStep === 2 && this.formData.template_id) {
                        this.nextStep();
                    }
                }, 300); // Delay di 300ms per feedback visivo
            });

            // Event info inputs
            $('#wizard_event_date, #wizard_event_time, #wizard_event_location, #wizard_event_address').on('change input', () => {
                this.validateStep(3);
            });

            // Content inputs
            $('#wizard_invite_title, #wizard_invite_message').on('change input', () => {
                this.validateStep(4);
            });

            // RSVP toggle
            $('#wizard_rsvp_enabled').on('change', (e) => {
                if ($(e.target).is(':checked')) {
                    $('#rsvp_options_wrapper').slideDown(300);
                } else {
                    $('#rsvp_options_wrapper').slideUp(300);
                }
            });

            // RSVP inputs validation
            $('#wizard_rsvp_deadline, #wizard_max_guests').on('change', () => {
                this.validateStep(5);
            });

            // Image upload
            $('#wizard_upload_image').on('click', (e) => {
                e.preventDefault();
                this.openMediaUploader();
            });

            // Final submit
            $('#wizard_create_invite').on('click', (e) => {
                e.preventDefault();
                this.createInvite();
            });
        },

        nextStep: function() {
            if (this.currentStep < this.totalSteps) {
                if (this.validateStep(this.currentStep)) {
                    this.currentStep++;
                    this.showStep(this.currentStep);
                    this.updateProgress();
                }
            }
        },

        prevStep: function() {
            if (this.currentStep > 1) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },

        goToStep: function(step) {
            // Permetti di andare avanti solo se step valido
            const minStep = $('#wi-mode-selection-step').length > 0 ? 0 : 1;
            if (step >= minStep && step <= this.totalSteps) {
                this.currentStep = step;
                this.showStep(step);
                this.updateProgress();
            }
        },

        showStep: function(step) {
            $('.wi-wizard-step-modern').removeClass('active');
            $(`.wi-wizard-step-modern[data-step="${step}"]`).addClass('active');

            // Update navigation buttons
            const minStep = $('#wi-mode-selection-step').length > 0 ? 0 : 1;
            $('.wi-wizard-prev').prop('disabled', step === minStep);
            $('.wi-btn-secondary').prop('disabled', step === minStep);

            // Update step counter
            $('.wi-current-step').text(step);

            if (step === this.totalSteps) {
                $('.wi-wizard-next').hide();
                $('#wizard_create_invite').show();
                this.updateFinalPreview();
            } else {
                $('.wi-wizard-next').show();
                $('#wizard_create_invite').hide();
            }

            // Se arriviamo allo step 2 e abbiamo già una categoria selezionata, ricarica i template
            if (step === 2 && this.formData.event_category && $('.wi-template-card-modern').length === 0) {
                this.loadTemplatesByCategory(this.formData.event_category);
            }

            // Scroll to top
            $('.wi-wizard-container-modern').scrollTop(0);
        },

        updateProgress: function() {
            // Nascondi progress bar se siamo allo Step 0
            if (this.currentStep === 0) {
                $('.wi-wizard-progress-modern').hide();
                return;
            } else {
                $('.wi-wizard-progress-modern').show();
            }

            const progress = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            $('.wi-progress-fill').css('width', progress + '%');

            $('.wi-step-item').each((index, el) => {
                const step = $(el).data('step');
                $(el).removeClass('active completed');

                if (step === this.currentStep) {
                    $(el).addClass('active');
                } else if (step < this.currentStep) {
                    $(el).addClass('completed');
                }
            });
        },

        validateStep: function(step) {
            let isValid = false;

            switch(step) {
                case 1: // Categoria evento
                    isValid = !!this.formData.event_category;
                    break;

                case 2: // Template
                    isValid = !!this.formData.template_id;
                    break;

                case 3: // Event info
                    const date = $('#wizard_event_date').val();
                    const time = $('#wizard_event_time').val();
                    const location = $('#wizard_event_location').val();
                    const address = $('#wizard_event_address').val();

                    isValid = date && time && location && address;

                    if (isValid) {
                        this.formData.event_date = date;
                        this.formData.event_time = time;
                        this.formData.event_location = location;
                        this.formData.event_address = address;
                    }
                    break;

                case 4: // Contenuto
                    const title = $('#wizard_invite_title').val().trim();
                    const message = $('#wizard_invite_message').val().trim();

                    isValid = title && message;

                    if (isValid) {
                        this.formData.invite_title = title;
                        this.formData.invite_message = message;
                        this.formData.final_message = $('#wizard_final_message').val().trim();
                        this.formData.final_message_button_text = $('#wizard_final_message_button').val().trim();
                    }
                    break;

                case 5: // RSVP Settings
                    const rsvpEnabled = $('#wizard_rsvp_enabled').is(':checked');
                    this.formData.rsvp_enabled = rsvpEnabled;

                    if (rsvpEnabled) {
                        // Se RSVP è abilitato, raccoglie tutte le impostazioni
                        this.formData.rsvp_deadline = $('#wizard_rsvp_deadline').val();
                        this.formData.rsvp_max_guests = $('#wizard_max_guests').val();
                        this.formData.rsvp_menu_choices = $('#wizard_menu_choices').val().trim();
                        this.formData.rsvp_notify_admin = $('#wizard_notify_admin').is(':checked');
                        this.formData.rsvp_admin_email = $('#wizard_admin_email').val().trim();

                        // Validazione: se abilitato, almeno la deadline è obbligatoria
                        isValid = !!this.formData.rsvp_deadline;
                    } else {
                        // Se RSVP è disabilitato, lo step è sempre valido
                        isValid = true;
                    }
                    break;

                case 6: // Final preview
                    isValid = true;
                    break;
            }

            $('.wi-wizard-next').prop('disabled', !isValid);
            return isValid;
        },

        loadEventCategories: function() {
            console.log('Loading event categories...', wiWizard);

            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_event_categories',
                    nonce: wiWizard.nonce
                },
                success: (response) => {
                    console.log('Categories response:', response);
                    if (response.success) {
                        this.renderEventCategories(response.data);
                    } else {
                        console.error('Categories error:', response);
                        this.showCategoriesError();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', status, error);
                    this.showCategoriesError();
                }
            });
        },

        showCategoriesError: function() {
            const container = $('#wi-categories-grid');
            container.html('<div class="wi-category-card-modern" style="grid-column: 1/-1; text-align: center; padding: 40px;"><p style="color: #ef4444;">Errore nel caricamento delle categorie. Ricarica la pagina.</p></div>');
        },

        renderEventCategories: function(categories) {
            const container = $('#wi-categories-grid');
            container.empty();

            categories.forEach(category => {
                const card = $(`
                    <div class="wi-category-card-modern" data-category-slug="${category.slug}">
                        <div class="wi-category-icon-modern">${category.icon}</div>
                        <div class="wi-category-name-modern">${category.name}</div>
                        <div class="wi-category-check">✓</div>
                    </div>
                `);
                container.append(card);
            });
        },

        loadTemplatesByCategory: function(categorySlug) {
            console.log('Loading templates for category:', categorySlug);

            const container = $('.wi-templates-grid-modern');

            // Mostra loader
            container.html('<div class="wi-templates-loading" style="grid-column: 1/-1; text-align: center; padding: 60px 20px;"><div class="spinner" style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div><p style="color: #64748b; font-size: 16px;">Caricamento template...</p></div>');

            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_templates_by_category',
                    nonce: wiWizard.nonce,
                    category_slug: categorySlug
                },
                success: (response) => {
                    console.log('Templates response:', response);
                    if (response.success && response.data.length > 0) {
                        this.renderTemplates(response.data);
                    } else {
                        this.showTemplatesError('Nessun template disponibile per questa categoria.');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error loading templates:', status, error);
                    this.showTemplatesError('Errore nel caricamento dei template. Ricarica la pagina.');
                }
            });
        },

        renderTemplates: function(templates) {
            const container = $('.wi-templates-grid-modern');
            container.empty();

            templates.forEach(template => {
                const card = $(`
                    <div class="wi-template-card-modern" data-template-id="${template.id}">
                        <div class="wi-template-preview-modern">
                            ${template.preview_url ?
                                `<img src="${template.preview_url}" alt="${template.name}" class="wi-template-image">` :
                                `<div class="wi-template-placeholder"><span class="wi-placeholder-icon">🎨</span></div>`
                            }
                            <div class="wi-template-overlay">
                                <div class="wi-template-badge">Premium</div>
                            </div>
                        </div>
                        <div class="wi-template-info-modern">
                            <h3 class="wi-template-name-modern">${template.name}</h3>
                            <p class="wi-template-description-modern">${template.description}</p>
                        </div>
                        <div class="wi-template-checkmark">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </div>
                `);
                container.append(card);
            });

            console.log(`Rendered ${templates.length} templates`);
        },

        showTemplatesError: function(message) {
            const container = $('.wi-templates-grid-modern');
            container.html(`<div class="wi-templates-error" style="grid-column: 1/-1; text-align: center; padding: 60px 20px;"><p style="color: #ef4444; font-size: 16px;">${message}</p></div>`);
        },

        openMediaUploader: function() {
            if (this.mediaUploader) {
                this.mediaUploader.open();
                return;
            }

            this.mediaUploader = wp.media({
                title: 'Scegli Immagine Invito',
                button: { text: 'Usa questa immagine' },
                multiple: false,
                library: { type: 'image' }
            });

            this.mediaUploader.on('select', () => {
                const attachment = this.mediaUploader.state().get('selection').first().toJSON();
                this.formData.user_image_id = attachment.id;
                this.formData.user_image_url = attachment.url;

                $('#wizard_image_preview').html(`<img src="${attachment.url}" alt="Preview">`);
                $('#wizard_remove_image').show();
            });

            this.mediaUploader.open();
        },

        updateFinalPreview: function() {
            // Mostra riepilogo
            $('#preview_event_category').text(this.getCategoryName(this.formData.event_category));
            $('#preview_template').text(this.getTemplateName(this.formData.template_id));
            $('#preview_title').text(this.formData.invite_title);
            $('#preview_message').text(this.formData.invite_message);
            $('#preview_date').text(this.formatDate(this.formData.event_date));
            $('#preview_time').text(this.formData.event_time);
            $('#preview_location').text(this.formData.event_location);
            $('#preview_address').text(this.formData.event_address);

            // RSVP info nel riepilogo
            if (this.formData.rsvp_enabled) {
                $('#preview_rsvp_status').html('<span style="color: #10b981; font-weight: 600;">✓ Abilitato</span>');
                if (this.formData.rsvp_deadline) {
                    $('#preview_rsvp_deadline').text(this.formatDate(this.formData.rsvp_deadline));
                    $('#preview_rsvp_deadline_wrapper').show();
                } else {
                    $('#preview_rsvp_deadline_wrapper').hide();
                }
            } else {
                $('#preview_rsvp_status').html('<span style="color: #94a3b8;">Disabilitato</span>');
                $('#preview_rsvp_deadline_wrapper').hide();
            }

            if (this.formData.user_image_url) {
                $('#preview_image').html(`<img src="${this.formData.user_image_url}" alt="Immagine">`);
            }

            // Genera anteprima HTML reale
            this.loadLivePreview();
        },

        loadLivePreview: function() {
            $('#wizard_preview_loading').show();

            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_live_preview',
                    nonce: wiWizard.nonce,
                    ...this.formData
                },
                success: (response) => {
                    if (response.success) {
                        const iframe = $('#wizard_final_preview_frame')[0];
                        iframe.srcdoc = response.data.html;
                    }
                    $('#wizard_preview_loading').hide();
                },
                error: () => {
                    $('#wizard_preview_loading').hide();
                }
            });
        },

        createInvite: function() {
            const isEdit = this.editMode && this.inviteId;
            const buttonText = isEdit ? '💾 Aggiorna Invito' : '🎉 Crea Invito';
            const processingText = isEdit ? 'Aggiornamento in corso...' : 'Creazione in corso...';

            $('#wizard_create_invite').prop('disabled', true).text(processingText);

            // Se in edit mode, aggiungi l'ID all'AJAX data
            const ajaxData = {
                action: 'wi_wizard_create_invite',
                nonce: wiWizard.nonce,
                ...this.formData
            };

            if (isEdit) {
                ajaxData.invite_id = this.inviteId;
            }

            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: ajaxData,
                success: (response) => {
                    if (response.success) {
                        if (isEdit) {
                            // In edit mode, mostra messaggio e poi ricarica
                            alert('✅ Invito aggiornato con successo!');
                            window.location.href = response.data.view_url || response.data.edit_url;
                        } else {
                            // Nuovo invito, redirect alla pagina di visualizzazione
                            window.location.href = response.data.view_url || response.data.edit_url;
                        }
                    } else {
                        alert('Errore: ' + response.data.message);
                        $('#wizard_create_invite').prop('disabled', false).text(buttonText);
                    }
                },
                error: () => {
                    alert('Errore di connessione. Riprova.');
                    $('#wizard_create_invite').prop('disabled', false).text(buttonText);
                }
            });
        },

        getCategoryName: function(slug) {
            const card = $(`.wi-category-card-modern[data-category-slug="${slug}"]`);
            return card.find('.wi-category-name-modern').text() || slug;
        },

        getTemplateName: function(id) {
            const card = $(`.wi-template-card-modern[data-template-id="${id}"]`);
            return card.find('.wi-template-name-modern').text() || 'Template #' + id;
        },

        formatDate: function(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('it-IT', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        /**
         * Carica conteggio inviti utente per badge Step 0
         */
        loadUserInvitesCount: function() {
            console.log('Loading user invites count...');
            console.log('AJAX URL:', wiWizard.ajax_url);
            console.log('Nonce:', wiWizard.nonce);

            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_user_invites_count',
                    nonce: wiWizard.nonce
                },
                success: (response) => {
                    console.log('Count response:', response);
                    if (response.success) {
                        const count = response.data.count;
                        const text = count === 0 ? 'Nessun invito' :
                                    count === 1 ? '1 invito' :
                                    `${count} inviti`;
                        $('#wi-user-invites-count').text(text);
                        console.log('User has', count, 'invites');

                        // Disabilita la card se non ci sono inviti
                        if (count === 0) {
                            $('.wi-mode-card[data-mode="edit"]')
                                .css('opacity', '0.6')
                                .css('cursor', 'not-allowed')
                                .off('click')
                                .on('click', (e) => {
                                    e.stopPropagation();
                                    alert('Non hai ancora creato nessun invito. Crea il tuo primo invito!');
                                });
                        }
                    } else {
                        console.error('Count request failed:', response);
                        $('#wi-user-invites-count').text('Errore');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error loading count:', status, error);
                    console.error('Response:', xhr.responseText);
                    $('#wi-user-invites-count').text('Errore');
                }
            });
        },

        /**
         * Carica lista inviti utente per selezione
         */
        loadUserInvites: function() {
            console.log('Loading user invites...');
            console.log('AJAX URL:', wiWizard.ajax_url);
            console.log('Nonce:', wiWizard.nonce);

            // Nascondi selezione modalità
            $('.wi-mode-selection-grid').fadeOut(300);

            setTimeout(() => {
                $('#wi-existing-invites-list').fadeIn(300);
            }, 300);

            // Carica inviti via AJAX
            $.ajax({
                url: wiWizard.ajax_url,
                method: 'POST',
                data: {
                    action: 'wi_get_user_invites',
                    nonce: wiWizard.nonce
                },
                success: (response) => {
                    console.log('Invites response:', response);
                    if (response.success) {
                        console.log('Invites loaded:', response.data.invites.length);
                        this.renderInvitesList(response.data.invites);
                    } else {
                        console.error('Invites request failed:', response);
                        $('#wi-invites-grid').html(`
                            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                                <div style="font-size: 48px; margin-bottom: 15px;">😔</div>
                                <p style="color: #64748b; font-size: 18px;">${response.data.message || 'Errore nel caricamento'}</p>
                            </div>
                        `);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error loading invites:', status, error);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Status Code:', xhr.status);

                    let errorMessage = 'Errore di connessione. Riprova.';
                    let debugInfo = '';

                    if (xhr.responseText) {
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            console.error('Parsed error data:', errorData);

                            if (errorData.data) {
                                errorMessage = errorData.data.message || errorMessage;

                                // Costruisci info debug dettagliate
                                if (errorData.data.error) {
                                    debugInfo += '<div style="text-align: left; background: #fee2e2; padding: 20px; border-radius: 8px; margin-top: 20px; font-family: monospace; font-size: 12px; max-width: 600px; margin-left: auto; margin-right: auto;">';
                                    debugInfo += '<strong style="color: #dc2626; display: block; margin-bottom: 10px;">DEBUG INFO:</strong>';
                                    debugInfo += '<div style="color: #991b1b; margin-bottom: 8px;"><strong>Error:</strong> ' + this.escapeHtml(errorData.data.error) + '</div>';

                                    if (errorData.data.file) {
                                        debugInfo += '<div style="color: #991b1b; margin-bottom: 8px;"><strong>File:</strong> ' + this.escapeHtml(errorData.data.file) + '</div>';
                                    }
                                    if (errorData.data.line) {
                                        debugInfo += '<div style="color: #991b1b; margin-bottom: 8px;"><strong>Line:</strong> ' + errorData.data.line + '</div>';
                                    }
                                    if (errorData.data.trace) {
                                        debugInfo += '<div style="color: #991b1b; margin-top: 10px;"><strong>Trace:</strong><pre style="white-space: pre-wrap; word-wrap: break-word; font-size: 10px; max-height: 200px; overflow-y: auto;">' + this.escapeHtml(errorData.data.trace) + '</pre></div>';
                                    }
                                    debugInfo += '</div>';

                                    // Alert per visibilità immediata
                                    alert('DEBUG ERROR:\n\n' +
                                          'Error: ' + errorData.data.error + '\n' +
                                          'File: ' + (errorData.data.file || 'Unknown') + '\n' +
                                          'Line: ' + (errorData.data.line || 'Unknown'));
                                }
                            }
                        } catch(e) {
                            console.error('Failed to parse error response:', e);
                            errorMessage += ' (Controlla la console per dettagli)';
                            debugInfo = '<div style="background: #fee2e2; padding: 15px; margin-top: 20px; border-radius: 8px; font-size: 11px; color: #991b1b; max-width: 600px; margin-left: auto; margin-right: auto; font-family: monospace; white-space: pre-wrap; word-wrap: break-word; max-height: 200px; overflow-y: auto;">' + this.escapeHtml(xhr.responseText.substring(0, 500)) + '</div>';
                        }
                    }

                    $('#wi-invites-grid').html(`
                        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                            <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                            <p style="color: #64748b; font-size: 18px;">${errorMessage}</p>
                            ${debugInfo}
                            <button class="wi-btn-back-to-mode" style="margin-top: 20px; background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                                ← Torna Indietro
                            </button>
                        </div>
                    `);
                }
            });
        },

        /**
         * Renderizza lista inviti selezionabili
         */
        renderInvitesList: function(invites) {
            if (invites.length === 0) {
                $('#wi-invites-grid').html(`
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                        <div style="font-size: 64px; margin-bottom: 20px; opacity: 0.6;">📭</div>
                        <h3 style="color: #475569; font-size: 22px; margin-bottom: 10px;">Nessun Invito Trovato</h3>
                        <p style="color: #94a3b8; font-size: 16px; margin-bottom: 25px;">Non hai ancora creato nessun invito.</p>
                        <button class="wi-btn-back-to-mode" style="background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 15px;">
                            ✨ Crea il Tuo Primo Invito
                        </button>
                    </div>
                `);
                return;
            }

            let html = '';
            invites.forEach((invite) => {
                const rsvpBadge = invite.rsvp_enabled ?
                    `<span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">RSVP ✓</span>` :
                    '';

                html += `
                    <div class="wi-invite-selection-card" data-invite-id="${invite.id}">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <h4 style="color: #1e293b; font-size: 20px; font-weight: 700; margin: 0; flex: 1;">${this.escapeHtml(invite.title)}</h4>
                            ${rsvpBadge}
                        </div>
                        <div style="color: #64748b; font-size: 14px; line-height: 1.8; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span>📅</span>
                                <span><strong>Data:</strong> ${this.formatDate(invite.event_date)}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span>⏰</span>
                                <span><strong>Ora:</strong> ${invite.event_time}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span>📍</span>
                                <span><strong>Luogo:</strong> ${this.escapeHtml(invite.event_location)}</span>
                            </div>
                        </div>
                        ${invite.rsvp_stats ? `
                            <div style="display: flex; gap: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 13px;">
                                <div><span style="color: #10b981; font-weight: 700;">${invite.rsvp_stats.confirmed}</span> Confermati</div>
                                <div><span style="color: #ef4444; font-weight: 700;">${invite.rsvp_stats.declined}</span> Rifiutati</div>
                                <div><span style="color: #f59e0b; font-weight: 700;">${invite.rsvp_stats.maybe}</span> Forse</div>
                            </div>
                        ` : ''}
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px;">
                            Ultima modifica: ${this.formatDateTime(invite.modified_date)}
                        </div>
                    </div>
                `;
            });

            $('#wi-invites-grid').html(html);
        },

        /**
         * Formatta data e ora
         */
        formatDateTime: function(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('it-IT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        /**
         * Escape HTML per prevenire XSS
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, (m) => map[m]);
        },

        /**
         * Carica dati invito esistente in modalità edit
         */
        loadInviteData: function() {
            const data = wiWizard.invite_data;
            console.log('Loading invite data:', data);

            // Popola formData
            this.formData = {
                id: data.id,
                event_category: null, // Verrà caricato quando le categorie sono pronte
                category_id: data.category_id,
                template_id: data.template_id,
                event_date: data.event_date,
                event_time: data.event_time,
                event_location: data.event_location,
                event_address: data.event_address,
                invite_title: data.invite_title,
                invite_message: data.invite_message,
                final_message: data.final_message || '',
                final_message_button_text: data.final_message_button_text || 'Chiudi',
                user_image_url: data.user_image_url || '',
                rsvp_enabled: data.rsvp_enabled || false,
                rsvp_deadline: data.rsvp_deadline || '',
                rsvp_max_guests: data.rsvp_max_guests || 1,
                rsvp_menu_choices: data.rsvp_menu_choices || '',
                rsvp_notify_admin: data.rsvp_notify_admin || false,
                rsvp_admin_email: data.rsvp_admin_email || ''
            };

            // Popola campi Step 3 (Event Details)
            $('#wizard_event_date').val(data.event_date);
            $('#wizard_event_time').val(data.event_time);
            $('#wizard_event_location').val(data.event_location);
            $('#wizard_event_address').val(data.event_address);

            // Popola campi Step 4 (Content)
            $('#wizard_invite_title').val(data.invite_title);
            $('#wizard_invite_message').val(data.invite_message);
            $('#wizard_final_message').val(data.final_message || '');
            $('#wizard_final_message_button').val(data.final_message_button_text || 'Chiudi');

            // Popola campi Step 5 (RSVP)
            if (data.rsvp_enabled) {
                $('#wizard_rsvp_enabled').prop('checked', true);
                $('#rsvp_options_wrapper').show();
                $('#wizard_rsvp_deadline').val(data.rsvp_deadline);
                $('#wizard_max_guests').val(data.rsvp_max_guests);
                $('#wizard_menu_choices').val(data.rsvp_menu_choices);
                $('#wizard_notify_admin').prop('checked', data.rsvp_notify_admin);
                $('#wizard_admin_email').val(data.rsvp_admin_email);
            }

            // Immagine utente
            if (data.user_image_url) {
                $('#wizard_image_preview').html(`<img src="${data.user_image_url}" style="max-width: 200px; border-radius: 8px;">`).show();
            }

            // Cambia testo bottone finale
            $('#wizard_create_invite').text('💾 Aggiorna Invito');

            console.log('Invite data loaded successfully');
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.wi-wizard-container-modern').length || $('.wi-wizard-container').length) {
            console.log('Wizard container found, initializing...');
            WizardSteps.init();
        } else {
            console.log('Wizard container not found');
        }
    });

})(jQuery);
