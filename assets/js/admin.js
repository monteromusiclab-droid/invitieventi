/**
 * Wedding Invites Pro - Admin JavaScript
 * Script per l'area amministrativa
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Color Picker
    if ($('.wi-color-picker').length) {
        $('.wi-color-picker').wpColorPicker();
    }
    
    // Media Uploader per logo e immagini
    var mediaUploader;
    
    $('#wi-upload-logo').on('click', function(e) {
        e.preventDefault();
        
        // Se il media uploader esiste già, riaprilo
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Crea un nuovo media uploader
        mediaUploader = wp.media({
            title: 'Seleziona o Carica Logo',
            button: {
                text: 'Usa questo logo'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // Quando un'immagine viene selezionata
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Mostra loading
            showLoading();
            
            // Salva via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wi_save_logo',
                    nonce: wiAdmin.nonce,
                    logo_id: attachment.id,
                    logo_url: attachment.url
                },
                success: function(response) {
                    if (response.success) {
                        // Aggiorna l'anteprima
                        $('#wi-logo-preview').html('<img src="' + attachment.url + '" alt="Logo">');
                        
                        // Mostra il pulsante rimuovi se non esiste
                        if (!$('#wi-remove-logo').length) {
                            $('.wi-logo-actions').append(
                                '<button type="button" id="wi-remove-logo" class="button button-link-delete">' +
                                '<span class="dashicons dashicons-trash"></span> Rimuovi' +
                                '</button>'
                            );
                        }
                        
                        showNotice('Logo caricato con successo', 'success');
                    } else {
                        showNotice('Errore nel caricamento del logo', 'error');
                    }
                    hideLoading();
                },
                error: function() {
                    showNotice('Errore di connessione', 'error');
                    hideLoading();
                }
            });
        });
        
        mediaUploader.open();
    });
    
    // Rimuovi logo
    $(document).on('click', '#wi-remove-logo', function(e) {
        e.preventDefault();
        
        if (!confirm('Vuoi davvero rimuovere il logo?')) {
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wi_remove_logo',
                nonce: wiAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#wi-logo-preview').html(
                        '<div class="wi-no-logo">' +
                        '<span class="dashicons dashicons-format-image"></span>' +
                        '<p>Nessun logo caricato</p>' +
                        '</div>'
                    );
                    $('#wi-remove-logo').remove();
                    showNotice('Logo rimosso', 'success');
                } else {
                    showNotice('Errore nella rimozione', 'error');
                }
                hideLoading();
            },
            error: function() {
                showNotice('Errore di connessione', 'error');
                hideLoading();
            }
        });
    });
    
    // Eliminazione invito dalla lista
    $('.wi-btn-delete').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Sei sicuro di voler eliminare questo invito?')) {
            return;
        }
        
        var $button = $(this);
        var inviteId = $button.data('invite-id');
        var $row = $button.closest('tr');
        
        showLoading();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wi_delete_invite',
                nonce: wiAdmin.nonce,
                invite_id: inviteId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Se non ci sono più righe, ricarica la pagina
                        if ($('.wi-invites-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                    showNotice('Invito eliminato', 'success');
                } else {
                    showNotice('Errore nell\'eliminazione', 'error');
                }
                hideLoading();
            },
            error: function() {
                showNotice('Errore di connessione', 'error');
                hideLoading();
            }
        });
    });
    
    // Code editor per template HTML e CSS
    if ($('#template_html').length && typeof wp.codeEditor !== 'undefined') {
        wp.codeEditor.initialize('template_html', {
            codemirror: {
                mode: 'htmlmixed',
                lineNumbers: true,
                lineWrapping: true,
                theme: 'default'
            }
        });
    }
    
    if ($('#template_css').length && typeof wp.codeEditor !== 'undefined') {
        wp.codeEditor.initialize('template_css', {
            codemirror: {
                mode: 'css',
                lineNumbers: true,
                lineWrapping: true,
                theme: 'default'
            }
        });
    }
    
    // Conferma prima di lasciare la pagina se ci sono modifiche non salvate
    var formChanged = false;
    
    $('.wi-template-form input, .wi-template-form textarea, .wi-template-form select').on('change', function() {
        formChanged = true;
    });
    
    $('.wi-template-form').on('submit', function() {
        formChanged = false;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'Hai modifiche non salvate. Sei sicuro di voler uscire?';
        }
    });
    
    // Sortable per template (se usato)
    if ($('.wi-templates-grid').length && typeof $.fn.sortable !== 'undefined') {
        $('.wi-templates-grid').sortable({
            handle: '.wi-template-handle',
            placeholder: 'wi-template-placeholder',
            update: function(event, ui) {
                var order = $(this).sortable('toArray', { attribute: 'data-template-id' });
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wi_reorder_templates',
                        nonce: wiAdmin.nonce,
                        order: order
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotice('Ordine salvato', 'success');
                        }
                    }
                });
            }
        });
    }
    
    // Auto-save per template (draft)
    var autoSaveTimer;
    
    $('.wi-template-form input, .wi-template-form textarea').on('input', function() {
        clearTimeout(autoSaveTimer);
        
        autoSaveTimer = setTimeout(function() {
            autoSaveTemplate();
        }, 5000); // Auto-save dopo 5 secondi di inattività
    });
    
    function autoSaveTemplate() {
        if (!$('.wi-template-form').length) return;
        
        var formData = $('.wi-template-form').serialize();
        formData += '&action=wi_autosave_template&nonce=' + wiAdmin.nonce;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showNotice('Bozza salvata automaticamente', 'info', 2000);
                }
            }
        });
    }
    
    // Funzioni di utilità
    function showLoading() {
        if (!$('.wi-loading-overlay').length) {
            $('body').append(
                '<div class="wi-loading-overlay">' +
                '<div class="wi-spinner"></div>' +
                '</div>'
            );
        }
        $('.wi-loading-overlay').fadeIn(200);
    }
    
    function hideLoading() {
        $('.wi-loading-overlay').fadeOut(200);
    }
    
    function showNotice(message, type, duration) {
        type = type || 'success';
        duration = duration || 4000;
        
        var noticeClass = 'notice notice-' + type + ' is-dismissible';
        
        var $notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
        
        $('.wi-admin-wrap').prepend($notice);
        
        // Auto-dismiss
        setTimeout(function() {
            $notice.fadeOut(400, function() {
                $(this).remove();
            });
        }, duration);
        
        // Dismiss button
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(400, function() {
                $(this).remove();
            });
        });
    }
    
    // Gestione AJAX per salvare il logo
    $.ajaxSetup({
        data: {
            _ajax_nonce: wiAdmin.nonce
        }
    });
    
    // Preview template inline (se richiesto)
    $('.wi-preview-template').on('click', function(e) {
        e.preventDefault();
        
        var templateId = $(this).data('template-id');
        
        // Apri modale con preview
        var modal = $('<div class="wi-modal-overlay">' +
                        '<div class="wi-modal">' +
                        '<button class="wi-modal-close">&times;</button>' +
                        '<div class="wi-modal-content"></div>' +
                        '</div>' +
                      '</div>');
        
        $('body').append(modal);
        
        // Carica preview via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wi_preview_template',
                nonce: wiAdmin.nonce,
                template_id: templateId
            },
            success: function(response) {
                if (response.success) {
                    modal.find('.wi-modal-content').html(response.data.html);
                }
            }
        });
        
        modal.fadeIn(300);
        
        // Chiudi modale
        modal.on('click', '.wi-modal-close, .wi-modal-overlay', function(e) {
            if (e.target === this) {
                modal.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
    });
    
    // Tooltips
    if (typeof $.fn.tooltip !== 'undefined') {
        $('[data-tooltip]').tooltip({
            position: { my: 'center bottom-10', at: 'center top' }
        });
    }
    
    // Conferma eliminazioni
    $('.button-link-delete').on('click', function(e) {
        if (!$(this).hasClass('wi-confirmed')) {
            e.preventDefault();
            
            if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
                return false;
            }
        }
    });
});

/**
 * Validazione form template
 */
function validateTemplateForm() {
    var isValid = true;
    var errors = [];
    
    var name = jQuery('#template_name').val();
    var html = jQuery('#template_html').val();
    
    if (!name || name.trim() === '') {
        errors.push('Il nome del template è obbligatorio');
        isValid = false;
    }
    
    if (!html || html.trim() === '') {
        errors.push('La struttura HTML è obbligatoria');
        isValid = false;
    }
    
    if (!isValid) {
        alert('Errori di validazione:\n\n' + errors.join('\n'));
    }
    
    return isValid;
}

/**
 * Esporta funzioni globali
 */
window.wiAdmin = window.wiAdmin || {};
window.wiAdmin.validateTemplateForm = validateTemplateForm;