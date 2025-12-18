<?php
/**
 * Pagina amministrazione - Impostazioni
 */

if (!current_user_can('manage_options')) {
    wp_die('Non hai i permessi per accedere a questa pagina');
}

// Salvataggio impostazioni
if (isset($_POST['save_settings']) && check_admin_referer('wi_save_settings')) {
    update_option('wi_google_maps_key', sanitize_text_field($_POST['google_maps_key']));
    update_option('wi_primary_color', sanitize_hex_color($_POST['primary_color']));
    update_option('wi_secondary_color', sanitize_hex_color($_POST['secondary_color']));
    update_option('wi_font_family', sanitize_text_field($_POST['font_family']));
    update_option('wi_enable_social_share', isset($_POST['enable_social_share']) ? '1' : '0');
    update_option('wi_enable_calendar', isset($_POST['enable_calendar']) ? '1' : '0');
    
    echo '<div class="notice notice-success is-dismissible"><p>Impostazioni salvate con successo!</p></div>';
}

$google_maps_key = get_option('wi_google_maps_key', '');
$primary_color = get_option('wi_primary_color', '#3498db');
$secondary_color = get_option('wi_secondary_color', '#2ecc71');
$font_family = get_option('wi_font_family', 'Montserrat');
$enable_social_share = get_option('wi_enable_social_share', '1');
$enable_calendar = get_option('wi_enable_calendar', '1');
$site_logo_id = get_option('wi_site_logo', 0);
?>

<div class="wrap wi-admin-wrap">
    <h1 class="wi-admin-title">
        <span class="dashicons dashicons-admin-settings"></span>
        Impostazioni Plugin
    </h1>
    
    <div class="wi-settings-container">
        <form method="post" id="wi-settings-form">
            <?php wp_nonce_field('wi_save_settings'); ?>
            
            <div class="wi-settings-grid">
                <!-- Sezione API e Integrazioni -->
                <div class="wi-settings-section">
                    <h2><span class="dashicons dashicons-admin-plugins"></span> API e Integrazioni</h2>
                    
                    <div class="wi-form-group">
                        <label for="google_maps_key">Google Maps API Key</label>
                        <input type="text" id="google_maps_key" name="google_maps_key" 
                               value="<?php echo esc_attr($google_maps_key); ?>" 
                               class="regular-text" placeholder="AIzaSy...">
                        <p class="description">
                            Necessaria per visualizzare le mappe negli inviti. 
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                                Ottieni una chiave API
                            </a>
                        </p>
                    </div>
                </div>
                
                <!-- Sezione Personalizzazione -->
                <div class="wi-settings-section">
                    <h2><span class="dashicons dashicons-admin-appearance"></span> Personalizzazione</h2>
                    
                    <div class="wi-form-group">
                        <label for="primary_color">Colore Primario</label>
                        <input type="text" id="primary_color" name="primary_color" 
                               value="<?php echo esc_attr($primary_color); ?>" 
                               class="wi-color-picker">
                    </div>
                    
                    <div class="wi-form-group">
                        <label for="secondary_color">Colore Secondario</label>
                        <input type="text" id="secondary_color" name="secondary_color" 
                               value="<?php echo esc_attr($secondary_color); ?>" 
                               class="wi-color-picker">
                    </div>
                    
                    <div class="wi-form-group">
                        <label for="font_family">Font Principale</label>
                        <select id="font_family" name="font_family" class="regular-text">
                            <option value="Montserrat" <?php selected($font_family, 'Montserrat'); ?>>Montserrat</option>
                            <option value="Playfair Display" <?php selected($font_family, 'Playfair Display'); ?>>Playfair Display</option>
                            <option value="Roboto" <?php selected($font_family, 'Roboto'); ?>>Roboto</option>
                            <option value="Open Sans" <?php selected($font_family, 'Open Sans'); ?>>Open Sans</option>
                            <option value="Lato" <?php selected($font_family, 'Lato'); ?>>Lato</option>
                            <option value="Great Vibes" <?php selected($font_family, 'Great Vibes'); ?>>Great Vibes (Corsivo)</option>
                            <option value="Dancing Script" <?php selected($font_family, 'Dancing Script'); ?>>Dancing Script (Corsivo)</option>
                        </select>
                        <p class="description">Font utilizzato di default nei template</p>
                    </div>
                </div>
                
                <!-- Sezione Logo -->
                <div class="wi-settings-section">
                    <h2><span class="dashicons dashicons-format-image"></span> Logo del Sito</h2>
                    
                    <div class="wi-form-group">
                        <label>Logo Footer Inviti</label>
                        <div class="wi-logo-upload">
                            <div id="wi-logo-preview" class="wi-logo-preview">
                                <?php if ($site_logo_id) : 
                                    $logo_url = wp_get_attachment_url($site_logo_id);
                                ?>
                                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo">
                                <?php else : ?>
                                    <div class="wi-no-logo">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <p>Nessun logo caricato</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="wi-logo-actions">
                                <button type="button" id="wi-upload-logo" class="button">
                                    <span class="dashicons dashicons-upload"></span> Carica Logo
                                </button>
                                <?php if ($site_logo_id) : ?>
                                    <button type="button" id="wi-remove-logo" class="button button-link-delete">
                                        <span class="dashicons dashicons-trash"></span> Rimuovi
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="description">Logo che apparirà nel footer di tutti gli inviti (dimensione consigliata: 200x60px)</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sezione Funzionalità -->
                <div class="wi-settings-section">
                    <h2><span class="dashicons dashicons-admin-tools"></span> Funzionalità</h2>
                    
                    <div class="wi-form-group">
                        <label class="wi-checkbox-label">
                            <input type="checkbox" name="enable_social_share" 
                                   <?php checked($enable_social_share, '1'); ?>>
                            <span>Abilita condivisione social</span>
                        </label>
                        <p class="description">Mostra i pulsanti di condivisione negli inviti</p>
                    </div>
                    
                    <div class="wi-form-group">
                        <label class="wi-checkbox-label">
                            <input type="checkbox" name="enable_calendar" 
                                   <?php checked($enable_calendar, '1'); ?>>
                            <span>Abilita "Aggiungi al calendario"</span>
                        </label>
                        <p class="description">Permette agli utenti di aggiungere l'evento al calendario Google</p>
                    </div>
                </div>
                
                <!-- Sezione Informazioni -->
                <div class="wi-settings-section wi-info-section">
                    <h2><span class="dashicons dashicons-info"></span> Informazioni Plugin</h2>
                    
                    <div class="wi-info-grid">
                        <div class="wi-info-item">
                            <strong>Versione:</strong>
                            <span><?php echo WI_VERSION; ?></span>
                        </div>
                        <div class="wi-info-item">
                            <strong>Shortcode Form:</strong>
                            <code>[wedding_invites_form]</code>
                        </div>
                        <div class="wi-info-item">
                            <strong>Pagina Inviti:</strong>
                            <a href="<?php echo get_permalink(get_page_by_path('crea-invito')); ?>" target="_blank">
                                Visualizza
                            </a>
                        </div>
                    </div>
                    
                    <div class="wi-info-box">
                        <h4>Come utilizzare il plugin:</h4>
                        <ol>
                            <li>Configura la chiave API di Google Maps per le mappe</li>
                            <li>Personalizza i colori e il font secondo il tuo brand</li>
                            <li>Carica il logo del tuo sito</li>
                            <li>Gli utenti registrati possono creare inviti dalla pagina "Crea Invito"</li>
                            <li>Gestisci template e inviti da questo pannello admin</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="wi-settings-footer">
                <button type="submit" name="save_settings" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes"></span> Salva Impostazioni
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.wi-settings-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.wi-settings-grid {
    display: grid;
    gap: 30px;
    max-width: 1200px;
}

.wi-settings-section {
    background: #f9f9f9;
    padding: 25px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.wi-settings-section h2 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.3rem;
}

.wi-settings-section h2 .dashicons {
    color: #3498db;
}

.wi-form-group {
    margin-bottom: 25px;
}

.wi-form-group:last-child {
    margin-bottom: 0;
}

.wi-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.wi-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.wi-checkbox-label input[type="checkbox"] {
    margin: 0;
}

.wi-logo-upload {
    margin-top: 10px;
}

.wi-logo-preview {
    width: 300px;
    height: 150px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    background: white;
}

.wi-logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.wi-no-logo {
    text-align: center;
    color: #bdc3c7;
}

.wi-no-logo .dashicons {
    font-size: 3rem;
    width: 3rem;
    height: 3rem;
}

.wi-no-logo p {
    margin: 10px 0 0 0;
}

.wi-logo-actions {
    display: flex;
    gap: 10px;
}

.wi-info-section {
    border-left-color: #9b59b6;
}

.wi-info-section h2 .dashicons {
    color: #9b59b6;
}

.wi-info-grid {
    display: grid;
    gap: 15px;
    margin-bottom: 20px;
}

.wi-info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: white;
    border-radius: 4px;
}

.wi-info-box {
    background: white;
    padding: 20px;
    border-radius: 4px;
    border-left: 3px solid #9b59b6;
}

.wi-info-box h4 {
    margin-top: 0;
    color: #2c3e50;
}

.wi-info-box ol {
    margin: 10px 0 0 20px;
    color: #555;
}

.wi-info-box ol li {
    margin-bottom: 8px;
}

.wi-settings-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e0e0e0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Color picker
    $('.wi-color-picker').wpColorPicker();
    
    // Upload logo
    var logoFrame;
    
    $('#wi-upload-logo').on('click', function(e) {
        e.preventDefault();
        
        if (logoFrame) {
            logoFrame.open();
            return;
        }
        
        logoFrame = wp.media({
            title: 'Seleziona Logo',
            button: {
                text: 'Usa questo logo'
            },
            multiple: false
        });
        
        logoFrame.on('select', function() {
            var attachment = logoFrame.state().get('selection').first().toJSON();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wi_upload_logo',
                    nonce: wiAdmin.nonce,
                    logo_id: attachment.id
                },
                success: function(response) {
                    if (response.success) {
                        $('#wi-logo-preview').html('<img src="' + attachment.url + '" alt="Logo">');
                        location.reload();
                    }
                }
            });
        });
        
        logoFrame.open();
    });
    
    // Rimuovi logo
    $('#wi-remove-logo').on('click', function() {
        if (!confirm('Vuoi rimuovere il logo?')) {
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wi_remove_logo',
                nonce: wiAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
