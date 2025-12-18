jQuery(document).ready(function($) {
    'use strict';
    
    var currentInviteId = 0;
    var uploadedImageId = 0;
    var uploadedImageUrl = '';
    
    // Caricamento invito esistente
    $('#load_invite').on('change', function() {
        var inviteId = $(this).val();
        
        if (!inviteId) {
            resetForm();
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: {
                action: 'wi_get_invite',
                nonce: wiPublic.nonce,
                invite_id: inviteId
            },
            success: function(response) {
                if (response.success) {
                    loadInviteData(response.data);
                } else {
                    alert('Errore nel caricamento dell\'invito');
                }
                hideLoading();
            },
            error: function() {
                alert('Errore di connessione');
                hideLoading();
            }
        });
    });
    
    // Preview immagine utente
    $('#user_image').on('change', function(e) {
        var file = e.target.files[0];
        
        if (!file) return;
        
        // Validazione tipo
        if (!validateFileType(file)) {
            $(this).val('');
            return;
        }
        
        // Validazione dimensione (max 2MB)
        if (!validateFileSize(file)) {
            $(this).val('');
            return;
        }
        
        // Preview locale
        var reader = new FileReader();
        reader.onload = function(e) {
            uploadedImageUrl = e.target.result;
            $('#user_image_preview').html(
                '<div class="wi-image-preview-container">' +
                '<img src="' + e.target.result + '" alt="Preview">' +
                '<button type="button" class="wi-remove-image-btn" onclick="removeUserImage()">✕ Rimuovi</button>' +
                '</div>'
            );
        };
        reader.readAsDataURL(file);
        
        // Upload immediato via AJAX per ottenere l'ID WordPress
        var formData = new FormData();
        formData.append('action', 'wi_upload_user_image');
        formData.append('nonce', wiPublic.nonce);
        formData.append('user_image', file);
        
        showLoading();
        
        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    uploadedImageId = response.data.id;
                    uploadedImageUrl = response.data.url;
                } else {
                    alert('Errore nel caricamento dell\'immagine: ' + response.data);
                    $('#user_image').val('');
                    $('#user_image_preview').empty();
                }
                hideLoading();
            },
            error: function(xhr, status, error) {
                console.error('Errore AJAX:', error);
                alert('Errore di connessione durante il caricamento dell\'immagine');
                $('#user_image').val('');
                $('#user_image_preview').empty();
                hideLoading();
            }
        });
    });
    
    // Anteprima invito
    $('#wi-preview-btn').on('click', function(e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        var inviteData = getFormData();
        var templateId = $('input[name="selected_template"]:checked').val();
        
        if (!templateId) {
            alert('Seleziona un template');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: wiPublic.ajax_url,
            type: 'POST',
            data: {
                action: 'wi_preview_invite',
                nonce: wiPublic.nonce,
                invite_data: inviteData,
                template_id: templateId
            },
            success: function(response) {
                if (response.success) {
                    showPreview(response.data.html);
                } else {
                    alert('Errore nella generazione dell\'anteprima');
                }
                hideLoading();
            },
            error: function() {
                alert('Errore di connessione');
                hideLoading();
            }
        });
    });
    
    // Chiudi anteprima
    $('#wi-close-preview, #wi-edit-btn').on('click', function() {
        hidePreview();
    });

    // NOTA: Il listener per #wi-publish-btn è gestito in form-validations.js
    // per evitare duplicazioni e garantire la validazione corretta
    
    // Reset form
    $('#wi-reset-btn').on('click', function() {
        if (confirm('Vuoi cancellare tutti i dati inseriti?')) {
            resetForm();
        }
    });
    
    // Funzioni di utilità
    function getFormData() {
        return {
            title: $('#invite_title').val(),
            message: $('#invite_message').val(),
            final_message: $('#final_message').val(),
            final_message_button_text: $('#final_message_button_text').val() || 'Leggi il messaggio',
            event_date: $('#event_date').val(),
            event_time: $('#event_time').val(),
            event_location: $('#event_location').val(),
            event_address: $('#event_address').val(),
            user_image_id: uploadedImageId,
            user_image_url: uploadedImageUrl
        };
    }
    
    function validateForm() {
        var title = $('#invite_title').val().trim();
        var message = $('#invite_message').val().trim();
        var eventDate = $('#event_date').val();
        var eventTime = $('#event_time').val();
        var template = $('input[name="selected_template"]:checked').val();
        
        if (!title) {
            alert('Inserisci il titolo dell\'invito');
            $('#invite_title').focus();
            return false;
        }
        
        if (!message) {
            alert('Inserisci un messaggio');
            $('#invite_message').focus();
            return false;
        }
        
        if (!eventDate) {
            alert('Seleziona la data dell\'evento');
            $('#event_date').focus();
            return false;
        }
        
        if (!eventTime) {
            alert('Seleziona l\'ora dell\'evento');
            $('#event_time').focus();
            return false;
        }
        
        // Validazione rimossa - tutti i campi sono opzionali
        // Richiesto solo il template

        if (!template) {
            alert('Seleziona un template');
            return false;
        }

        return true;
    }
    
    function loadInviteData(data) {
        currentInviteId = data.id;
        $('#invite_id').val(data.id); // Popola campo hidden per update

        $('#invite_title').val(data.title);
        $('#invite_message').val(data.message);
        $('#final_message').val(data.final_message || '');
        $('#final_message_button_text').val(data.final_message_button_text || 'Leggi il messaggio');
        $('#event_date').val(data.event_date);
        $('#event_time').val(data.event_time);
        $('#event_location').val(data.event_location);
        $('#event_address').val(data.event_address);

        if (data.user_image_url) {
            uploadedImageId = data.user_image_id;
            uploadedImageUrl = data.user_image_url;
            $('#user_image_preview').html('<img src="' + data.user_image_url + '" alt="Preview">');
        }

        if (data.template_id) {
            $('#template_' + data.template_id).prop('checked', true);
        }
    }
    
    function resetForm() {
        currentInviteId = 0;
        uploadedImageId = 0;
        uploadedImageUrl = '';

        $('#wi-invite-form')[0].reset();
        $('#invite_id').val(''); // Reset campo hidden
        $('#load_invite').val('');
        $('#user_image_preview').empty();
    }
    
    function showPreview(html) {
        $('#wi-preview-content').html(html);
        $('#wi-preview-container').fadeIn(300);
        $('body').css('overflow', 'hidden');
    }
    
    function hidePreview() {
        $('#wi-preview-container').fadeOut(300);
        $('body').css('overflow', 'auto');
    }
    
    function showLoading() {
        $('#wi-loading').fadeIn(200);
    }
    
    function hideLoading() {
        $('#wi-loading').fadeOut(200);
    }
    
    // Validazione dimensione file prima dell'upload
    function validateFileSize(file) {
        var maxSize = 7 * 1024 * 1024; // 7MB
        if (file.size > maxSize) {
            alert('Il file è troppo grande. Dimensione massima: 7MB');
            return false;
        }
        return true;
    }
    
    // Validazione tipo file
    function validateFileType(file) {
        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('Tipo di file non valido. Usa: JPG, PNG, GIF o WebP');
            return false;
        }
        return true;
    }
});

// Funzione globale per rimuovere immagine utente
function removeUserImage() {
    if (confirm('Vuoi rimuovere l\'immagine caricata?')) {
        jQuery('#user_image').val('');
        jQuery('#user_image_preview').empty();
        uploadedImageId = null;
        uploadedImageUrl = null;
    }
}

// Funzione globale per toggle messaggio finale
function toggleFinalMessage() {
    var messageContent = document.getElementById('finalMessageContent');
    if (messageContent) {
        if (messageContent.style.display === 'none' || messageContent.style.display === '') {
            messageContent.style.display = 'block';
            // Animazione fade in
            messageContent.style.opacity = '0';
            setTimeout(function() {
                messageContent.style.transition = 'opacity 0.5s ease-in-out';
                messageContent.style.opacity = '1';
            }, 10);
        } else {
            // Animazione fade out
            messageContent.style.opacity = '0';
            setTimeout(function() {
                messageContent.style.display = 'none';
            }, 500);
        }
    }
}
