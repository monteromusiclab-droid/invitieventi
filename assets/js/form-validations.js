jQuery(document).ready(function($) {
    console.log('📋 Form Validations v2.0.8: Inizializzazione...');

    const form = $('#wi-invite-form');
    const previewContainer = $('#wi-preview-container');

    if (!form.length) {
        console.warn('⚠️ Form non trovato sul DOM');
        return;
    }

    console.log('✅ Form trovato, setup listeners...');
    setupEventListeners();

    function setupEventListeners() {
        // RIMOSSO: form.on('change') che causava validazione continua
        // La validazione ora avviene SOLO quando si clicca Preview o Pubblica

        $('#wi-preview-btn').on('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Click su Anteprima');
            if (validateForm()) {
                generatePreview();
            }
        });

        $('#wi-publish-btn').on('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Click su Pubblica');
            if (validateForm()) {
                publishInvite();
            }
        });

        $('#wi-edit-btn').on('click', function(e) {
            e.preventDefault();
            closePreview();
        });

        $('#wi-close-preview').on('click', function(e) {
            e.preventDefault();
            closePreview();
        });

        $('#wi-reset-btn').on('click', function(e) {
            e.preventDefault();
            if (confirm('Sei sicuro di voler azzerare il modulo?')) {
                form[0].reset();
                $('#user_image_preview').html('');
                removeErrors();
                console.log('🔄 Form azzerato');
            }
        });

        $('#user_image').on('change', function() {
            handleImageUpload(this);
        });

        console.log('✅ Event listeners configurati');
    }

    function validateForm() {
        console.log('🔍 Validazione modulo...');
        let isValid = true;
        const errors = [];

        // Validazione: almeno un template deve essere selezionato
        const selectedTemplate = $('input[name="selected_template"]:checked');
        console.log('📌 Template selezionati:', selectedTemplate.length);

        if (selectedTemplate.length === 0) {
            isValid = false;
            errors.push('Devi selezionare un template per l\'invito');
            console.log('❌ Nessun template selezionato');
        } else {
            console.log('✅ Template selezionato: ID', selectedTemplate.val());
        }

        // Validazione: se c'è data evento, deve essere nel futuro
        const eventDate = form.find('#event_date').val();
        const eventTime = form.find('#event_time').val();

        if (eventDate) {
            console.log('📅 Data evento:', eventDate, '⏰ Ora:', eventTime);

            let dateTimeString = eventDate;
            if (eventTime) {
                dateTimeString += ' ' + eventTime;
            } else {
                dateTimeString += ' 23:59'; // Default alla fine del giorno se non specificato
            }

            const selectedDateTime = new Date(dateTimeString);
            const now = new Date();

            console.log('📊 Confronto date - Selezionata:', selectedDateTime.toLocaleString(), 'Ora:', now.toLocaleString());

            // Tolleranza di 1 ora per evitare problemi con fusi orari
            const oneHourAgo = new Date(now.getTime() - (60 * 60 * 1000));

            if (selectedDateTime < oneHourAgo) {
                isValid = false;
                errors.push('La data e ora dell\'evento deve essere nel futuro o almeno entro l\'ultima ora');
                console.log('❌ Data nel passato');
            } else {
                console.log('✅ Data valida (nel futuro)');
            }
        } else {
            console.log('ℹ️ Data evento non inserita (campo opzionale)');
        }

        // Mostra risultato validazione
        if (errors.length > 0) {
            console.log('❌ Validazione FALLITA. Errori trovati:', errors);
            showErrors(errors);
        } else {
            console.log('✅ Validazione SUPERATA');
            removeErrors();
        }

        return isValid;
    }

    function showErrors(errors) {
        removeErrors();
        const errorContainer = $('<div class="wi-form-errors"></div>');
        errorContainer.append('<div class="wi-error-header">⚠️ Per favore correggi gli errori:</div>');

        errors.forEach(function(error) {
            errorContainer.append('<div class="wi-error-item">• ' + error + '</div>');
        });

        form.prepend(errorContainer);

        // Scroll verso il container errori
        if (errorContainer[0]) {
            errorContainer[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        console.log('📢 Errori mostrati all\'utente');
    }

    function removeErrors() {
        form.find('.wi-form-errors').remove();
        form.find('.wi-field-error').removeClass('wi-field-error');
        form.find('.wi-error-message').remove();
    }

    // Variabile globale per memorizzare l'ID immagine caricata
    let uploadedImageId = 0;

    function handleImageUpload(input) {
        console.log('🖼️ Upload immagine...');

        if (!input.files || !input.files[0]) {
            console.log('⚠️ Nessun file selezionato');
            return;
        }

        const file = input.files[0];
        const maxSize = 7 * 1024 * 1024; // 7MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        console.log('📂 File:', file.name, 'Tipo:', file.type, 'Dimensione:', (file.size / 1024 / 1024).toFixed(2) + 'MB');

        if (!allowedTypes.includes(file.type)) {
            alert('❌ Formato non supportato. Usa: JPG, PNG, GIF, WebP');
            input.value = '';
            console.log('❌ Formato file non valido');
            return;
        }

        if (file.size > maxSize) {
            alert('❌ File troppo grande. Massimo 7MB');
            input.value = '';
            console.log('❌ File supera dimensione massima');
            return;
        }

        // Preview locale
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = $('#user_image_preview');
            preview.html('<img src="' + e.target.result + '" alt="Preview">');
            console.log('✅ Preview immagine caricata');
        };
        reader.readAsDataURL(file);

        // Upload immediato sul server per ottenere l'ID WordPress
        const formData = new FormData();
        formData.append('action', 'wi_upload_user_image');
        formData.append('nonce', wiPublic.nonce);
        formData.append('user_image', file);

        $('#wi-loading').show();

        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#wi-loading').hide();
                if (response.success) {
                    uploadedImageId = response.data.id;
                    console.log('✅ Immagine caricata sul server. ID:', uploadedImageId);
                } else {
                    alert('❌ Errore nel caricamento dell\'immagine: ' + (response.data || 'Errore sconosciuto'));
                    $(input).val('');
                    $('#user_image_preview').empty();
                    uploadedImageId = 0;
                }
            },
            error: function() {
                $('#wi-loading').hide();
                alert('❌ Errore di connessione durante il caricamento dell\'immagine');
                $(input).val('');
                $('#user_image_preview').empty();
                uploadedImageId = 0;
            }
        });
    }

    function getFormData() {
        console.log('📊 Raccolta dati dal form...');

        const data = {
            invite_title: form.find('#invite_title').val() || '',
            invite_message: form.find('#invite_message').val() || '',
            final_message: form.find('#final_message').val() || '',
            final_message_button_text: form.find('#final_message_button_text').val() || 'Leggi il messaggio',
            event_date: form.find('#event_date').val() || '',
            event_time: form.find('#event_time').val() || '',
            event_location: form.find('#event_location').val() || '',
            event_address: form.find('#event_address').val() || '',
            template_id: form.find('input[name="selected_template"]:checked').val() || ''
        };

        // DEBUG: Log dettagliato dei dati raccolti
        console.log('📋 Dati raccolti dal form:');
        console.log('  ├─ invite_title:', data.invite_title ? '✅ "' + data.invite_title + '"' : '⚠️ vuoto');
        console.log('  ├─ invite_message:', data.invite_message ? '✅ (lunghezza: ' + data.invite_message.length + ')' : '⚠️ vuoto');
        console.log('  ├─ final_message:', data.final_message ? '✅ "' + data.final_message + '"' : 'ℹ️ vuoto (opzionale)');
        console.log('  ├─ final_message_button_text:', data.final_message_button_text);
        console.log('  ├─ event_date:', data.event_date || '⚠️ vuoto');
        console.log('  ├─ event_time:', data.event_time || '⚠️ vuoto');
        console.log('  ├─ event_location:', data.event_location || '⚠️ vuoto');
        console.log('  ├─ event_address:', data.event_address || '⚠️ vuoto');
        console.log('  └─ template_id:', data.template_id || '❌ NESSUNO');

        return data;
    }

    function generatePreview() {
        console.log('👁️ Generazione anteprima...');

        const data = getFormData();
        const loading = $('#wi-loading');

        if (!data.template_id) {
            alert('❌ Seleziona un template');
            console.log('❌ Template ID mancante');
            return;
        }

        loading.show();
        previewContainer.hide();

        const formData = new FormData();
        formData.append('action', 'wi_preview_invite');
        formData.append('nonce', wiPublic.nonce);
        formData.append('invite_data[title]', data.invite_title);
        formData.append('invite_data[message]', data.invite_message);
        formData.append('invite_data[final_message]', data.final_message);
        formData.append('invite_data[final_message_button_text]', data.final_message_button_text);
        formData.append('invite_data[event_date]', data.event_date);
        formData.append('invite_data[event_time]', data.event_time);
        formData.append('invite_data[event_location]', data.event_location);
        formData.append('invite_data[event_address]', data.event_address);
        formData.append('template_id', data.template_id);

        console.log('📤 Dati AJAX per anteprima preparati (template_id:', data.template_id + ')');

        const userImage = document.getElementById('user_image');
        if (userImage && userImage.files && userImage.files[0]) {
            formData.append('user_image', userImage.files[0]);
            console.log('🖼️ Immagine utente inclusa');
        }

        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                loading.hide();
                console.log('📥 Risposta AJAX ricevuta:', response);

                if (response.success) {
                    $('#wi-preview-content').html(response.data.html);
                    previewContainer.show();

                    if (previewContainer[0]) {
                        previewContainer[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }

                    console.log('✅ Anteprima caricata e visualizzata');
                } else {
                    const errorMsg = response.data || 'Errore sconosciuto';
                    alert('❌ Errore: ' + errorMsg);
                    console.error('❌ Errore backend:', errorMsg);
                }
            },
            error: function(xhr, _status, error) {
                loading.hide();
                console.error('❌ Errore AJAX:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    response: xhr.responseText
                });
                alert('❌ Errore nel caricamento dell\'anteprima. Controlla la console per dettagli.');
            }
        });
    }

    function publishInvite() {
        console.log('📤 Pubblicazione invito...');

        if (!confirm('Sei sicuro di voler pubblicare questo invito?')) {
            console.log('ℹ️ Pubblicazione annullata dall\'utente');
            return;
        }

        const data = getFormData();
        const loading = $('#wi-loading');
        loading.show();

        const formData = new FormData();
        formData.append('action', 'wi_publish_invite');
        formData.append('nonce', wiPublic.nonce);
        formData.append('invite_data[title]', data.invite_title);
        formData.append('invite_data[message]', data.invite_message);
        formData.append('invite_data[final_message]', data.final_message);
        formData.append('invite_data[final_message_button_text]', data.final_message_button_text);
        formData.append('invite_data[event_date]', data.event_date);
        formData.append('invite_data[event_time]', data.event_time);
        formData.append('invite_data[event_location]', data.event_location);
        formData.append('invite_data[event_address]', data.event_address);
        formData.append('template_id', data.template_id);

        // Passa l'ID immagine caricata (se presente)
        if (uploadedImageId > 0) {
            formData.append('invite_data[user_image_id]', uploadedImageId);
            console.log('🖼️ ID immagine incluso:', uploadedImageId);
        }

        // Passa l'ID invito se esiste (per evitare duplicati)
        const inviteIdField = document.getElementById('invite_id');
        if (inviteIdField && inviteIdField.value) {
            formData.append('invite_id', inviteIdField.value);
        }

        console.log('📤 Dati AJAX per pubblicazione preparati');
        console.log('  ├─ final_message:', data.final_message ? '✅ presente' : 'ℹ️ vuoto');
        console.log('  └─ final_message_button_text:', data.final_message_button_text);

        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                loading.hide();
                console.log('📥 Risposta AJAX ricevuta:', response);

                if (response.success) {
                    console.log('✅ Invito pubblicato con successo!');
                    console.log('🔗 Reindirizzamento a:', response.data.url);
                    window.location.href = response.data.url;
                } else {
                    const errorMsg = response.data || 'Errore sconosciuto';
                    alert('❌ Errore: ' + errorMsg);
                    console.error('❌ Errore backend:', errorMsg);
                }
            },
            error: function(xhr, _status, error) {
                loading.hide();
                console.error('❌ Errore AJAX:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    response: xhr.responseText
                });
                alert('❌ Errore nella pubblicazione. Controlla la console per dettagli.');
            }
        });
    }

    function closePreview() {
        previewContainer.hide();
        form.show();
        console.log('✅ Anteprima chiusa');
    }

    console.log('✅ Form Validations v2.0.8 caricato completamente');
});
