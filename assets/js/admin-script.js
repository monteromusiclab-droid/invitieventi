/**
 * Wedding Invites - Admin Script
 * Gestione Template Manager con Upload Immagini
 */

jQuery(document).ready(function($) {
    console.log('🚀 Wedding Invites Admin Script caricato');
    
    // Verifica che wp.media sia disponibile
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('❌ WordPress Media Library non disponibile');
        return;
    }
    
    console.log('✅ WordPress Media Library disponibile');
    
    /**
     * UPLOAD IMMAGINE - Media Library WordPress
     */
    window.uploadImage = function(fieldName) {
        console.log('📸 Upload immagine per campo:', fieldName);
        
        // Previeni comportamento default
        event.preventDefault();
        
        // Crea media frame se non esiste
        var mediaFrame = wp.media({
            title: 'Seleziona o Carica Immagine',
            button: {
                text: 'Usa questa immagine'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // Quando un'immagine viene selezionata
        mediaFrame.on('select', function() {
            var attachment = mediaFrame.state().get('selection').first().toJSON();
            console.log('✅ Immagine selezionata:', attachment.url);
            
            // Aggiorna campo hidden con URL
            $('#' + fieldName).val(attachment.url);
            
            // Aggiorna preview
            $('#preview-' + fieldName).html(
                '<img src="' + attachment.url + '" style="max-width: 200px; max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">'
            );
            
            // Mostra pulsante rimuovi
            $('#remove-' + fieldName).show();
            
            console.log('✅ Preview aggiornata per:', fieldName);
            
            // Aggiorna preview live se presente
            updateLivePreview();
        });
        
        // Apri media frame
        mediaFrame.open();
    };
    
    /**
     * RIMUOVI IMMAGINE
     */
    window.removeImage = function(fieldName) {
        console.log('🗑️ Rimozione immagine:', fieldName);
        
        // Previeni comportamento default
        event.preventDefault();
        
        // Conferma rimozione
        if (!confirm('Vuoi rimuovere questa immagine?')) {
            return;
        }
        
        // Svuota campo
        $('#' + fieldName).val('');
        
        // Rimuovi preview
        $('#preview-' + fieldName).html('');
        
        // Nascondi pulsante rimuovi
        $('#remove-' + fieldName).hide();
        
        console.log('✅ Immagine rimossa:', fieldName);
        
        // Aggiorna preview live
        updateLivePreview();
    };
    
    /**
     * COLOR PICKER
     */
    if ($.fn.wpColorPicker) {
        $('.wi-color-picker').wpColorPicker({
            change: function(event, ui) {
                console.log('🎨 Colore cambiato:', ui.color.toString());
                updateLivePreview();
            },
            clear: function() {
                console.log('🎨 Colore rimosso');
                updateLivePreview();
            }
        });
    }
    
    /**
     * AGGIORNA PREVIEW LIVE
     */
    function updateLivePreview() {
        console.log('🔄 Aggiornamento preview live...');
        
        var previewFrame = $('#template-preview-frame');
        if (!previewFrame.length) {
            console.log('⚠️ Preview frame non trovato');
            return;
        }
        
        // Raccogli dati template
        var templateData = {
            // Immagini
            header_image: $('#header_image').val(),
            decoration_top: $('#decoration_top').val(),
            decoration_bottom: $('#decoration_bottom').val(),
            footer_logo: $('#footer_logo').val(),
            background_image: $('#background_image').val(),
            
            // Titolo
            title_font: $('#title_font').val(),
            title_size: $('#title_size').val(),
            title_color: $('#title_color').val(),
            
            // Countdown
            countdown_font: $('#countdown_font').val(),
            countdown_color: $('#countdown_color').val(),
            countdown_bg_color: $('#countdown_bg_color').val(),
            
            // Messaggio
            message_font: $('#message_font').val(),
            message_size: $('#message_size').val(),
            message_color: $('#message_color').val(),
            message_bg_color: $('#message_bg_color').val(),
            
            // Dettagli
            details_font: $('#details_font').val(),
            details_color: $('#details_color').val(),
            details_bg_color: $('#details_bg_color').val(),
            
            // Pulsanti
            button_bg_color: $('#button_bg_color').val(),
            button_text_color: $('#button_text_color').val(),
            
            // Sfondo
            background_color: $('#background_color').val()
        };
        
        console.log('📊 Dati template:', templateData);
        
        // Aggiorna iframe (se implementato)
        // Per ora log dei dati
        console.log('✅ Preview aggiornata');
    }
    
    /**
     * FONT SELECTOR CHANGE
     */
    $('.wi-font-select').on('change', function() {
        console.log('🔤 Font cambiato:', $(this).val());
        updateLivePreview();
    });
    
    /**
     * SIZE INPUT CHANGE
     */
    $('.wi-size-input').on('input', function() {
        console.log('📏 Dimensione cambiata:', $(this).val());
        updateLivePreview();
    });
    
    /**
     * FORM SUBMIT
     */
    $('#template-form').on('submit', function(e) {
        console.log('💾 Salvataggio template...');
        
        // Validazione base
        var templateName = $('#template_name').val();
        if (!templateName || templateName.trim() === '') {
            e.preventDefault();
            alert('Inserisci un nome per il template');
            $('#template_name').focus();
            return false;
        }
        
        console.log('✅ Validazione OK, invio form...');
        return true;
    });
    
    /**
     * TABS NAVIGATION
     */
    $('.wi-tab-button').on('click', function() {
        var tabId = $(this).data('tab');
        console.log('📑 Switch tab:', tabId);
        
        // Rimuovi classe active da tutti
        $('.wi-tab-button').removeClass('active');
        $('.wi-tab-content').removeClass('active');
        
        // Aggiungi classe active
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });
    
    /**
     * TOGGLE SECTIONS
     */
    $('.wi-section-toggle').on('click', function() {
        var section = $(this).closest('.wi-section');
        section.toggleClass('collapsed');
        console.log('🔽 Toggle sezione:', section.find('h3').text());
    });
    
    /**
     * DELETE TEMPLATE
     */
    $('.wi-delete-template').on('click', function(e) {
        if (!confirm('Sei sicuro di voler eliminare questo template? Questa azione non può essere annullata.')) {
            e.preventDefault();
            return false;
        }
        console.log('🗑️ Eliminazione template confermata');
        return true;
    });
    
    /**
     * DUPLICATE TEMPLATE
     */
    $('.wi-duplicate-template').on('click', function(e) {
        e.preventDefault();
        var templateId = $(this).data('template-id');
        
        if (!confirm('Vuoi duplicare questo template?')) {
            return;
        }
        
        console.log('📋 Duplicazione template:', templateId);
        
        // Redirect a creazione con parametro duplicate
        window.location.href = 'admin.php?page=wedding-invites-templates&new=1&duplicate=' + templateId;
    });
    
    /**
     * PREVIEW RESPONSIVE
     */
    $('.wi-preview-device').on('click', function() {
        var device = $(this).data('device');
        console.log('📱 Preview device:', device);
        
        // Rimuovi active da tutti
        $('.wi-preview-device').removeClass('active');
        $(this).addClass('active');
        
        // Cambia dimensione preview
        var previewFrame = $('#template-preview-frame');
        previewFrame.removeClass('desktop tablet mobile').addClass(device);
    });
    
    /**
     * HELP TOOLTIPS
     */
    $('.wi-help-icon').on('mouseenter', function() {
        var tooltip = $(this).find('.wi-tooltip');
        tooltip.fadeIn(200);
    }).on('mouseleave', function() {
        var tooltip = $(this).find('.wi-tooltip');
        tooltip.fadeOut(200);
    });
    
    /**
     * AUTO-SAVE (opzionale)
     */
    var autoSaveTimeout;
    $('.wi-input-field').on('input change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            console.log('💾 Auto-save...');
            // Implementa auto-save se necessario
        }, 3000);
    });
    
    /**
     * KEYBOARD SHORTCUTS
     */
    $(document).on('keydown', function(e) {
        // CTRL/CMD + S = Salva
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            console.log('⌨️ Salvataggio rapido...');
            $('#template-form').submit();
            return false;
        }
    });
    
    console.log('✅ Wedding Invites Admin Script inizializzato completamente');
});

/**
 * FUNZIONI GLOBALI (accessibili anche da inline onclick)
 */

// Upload immagine (già definita sopra come window.uploadImage)

// Rimuovi immagine (già definita sopra come window.removeImage)

/**
 * PREVIEW TEMPLATE (da chiamare quando necessario)
 */
function previewTemplate(templateId) {
    console.log('👁️ Preview template:', templateId);
    
    var previewUrl = ajaxurl + '?action=wi_preview_template&template_id=' + templateId;
    window.open(previewUrl, '_blank', 'width=1200,height=800');
}

/**
 * EXPORT TEMPLATE (futuro sviluppo)
 */
function exportTemplate(templateId) {
    console.log('📤 Export template:', templateId);
    alert('Funzionalità in sviluppo');
}

/**
 * IMPORT TEMPLATE (futuro sviluppo)
 */
function importTemplate() {
    console.log('📥 Import template');
    alert('Funzionalità in sviluppo');
}

console.log('✅ Script admin-script.js caricato completamente');
