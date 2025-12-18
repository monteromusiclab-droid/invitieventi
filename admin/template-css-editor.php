<?php
if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die(__('Non hai i permessi per accedere a questa pagina.'));
}

$template_id = isset($_GET['css_editor']) ? intval($_GET['css_editor']) : 0;
if (!$template_id) {
    wp_die(__('ID template non valido.'));
}

global $wpdb;
$table = $wpdb->prefix . 'wi_templates';
$template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id));

if (!$template) {
    wp_die(__('Template non trovato.'));
}

wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');
?>

<div class="wrap wi-css-editor">
    <div class="wi-editor-header">
        <h1>Modifica Stili CSS - <?php echo esc_html($template->name); ?></h1>
        <a href="?page=wedding-invites-templates&edit=<?php echo $template_id; ?>" class="button">← Indietro</a>
    </div>
    
    <div class="wi-css-editor-layout">
        <div class="wi-css-sidebar">
            <div class="wi-css-sections">
                <button class="wi-css-section-btn active" data-section="title" onclick="switchSection(this)">
                    <span class="dashicons dashicons-heading"></span>
                    Titolo
                </button>
                <button class="wi-css-section-btn" data-section="countdown" onclick="switchSection(this)">
                    <span class="dashicons dashicons-clock"></span>
                    Countdown
                </button>
                <button class="wi-css-section-btn" data-section="message" onclick="switchSection(this)">
                    <span class="dashicons dashicons-editor-quote"></span>
                    Messaggio
                </button>
                <button class="wi-css-section-btn" data-section="details" onclick="switchSection(this)">
                    <span class="dashicons dashicons-info"></span>
                    Dettagli Evento
                </button>
                <button class="wi-css-section-btn" data-section="buttons" onclick="switchSection(this)">
                    <span class="dashicons dashicons-button"></span>
                    Pulsanti
                </button>
                <button class="wi-css-section-btn" data-section="images" onclick="switchSection(this)">
                    <span class="dashicons dashicons-format-image"></span>
                    Immagini
                </button>
                <button class="wi-css-section-btn" data-section="background" onclick="switchSection(this)">
                    <span class="dashicons dashicons-block-default"></span>
                    Sfondo
                </button>
            </div>
        </div>
        
        <div class="wi-css-editor-main">
            <form method="post" id="css-editor-form">
                <input type="hidden" name="action" value="save_css_styles">
                <input type="hidden" name="template_id" value="<?php echo $template_id; ?>">
                <?php wp_nonce_field('wi_save_css_styles', 'nonce'); ?>
                
                <!-- SEZIONE TITOLO -->
                <div class="wi-css-section-content active" data-section="title">
                    <h3>Stile Titolo</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Font</label>
                            <select name="title_font" class="widefat">
                                <option value="Playfair Display" <?php selected($template->title_font, 'Playfair Display'); ?>>Playfair Display</option>
                                <option value="Lora" <?php selected($template->title_font, 'Lora'); ?>>Lora</option>
                                <option value="Montserrat" <?php selected($template->title_font, 'Montserrat'); ?>>Montserrat</option>
                                <option value="Dancing Script" <?php selected($template->title_font, 'Dancing Script'); ?>>Dancing Script</option>
                                <option value="Roboto" <?php selected($template->title_font, 'Roboto'); ?>>Roboto</option>
                                <option value="Open Sans" <?php selected($template->title_font, 'Open Sans'); ?>>Open Sans</option>
                            </select>
                        </div>
                        
                        <div class="wi-control-row">
                            <div class="wi-control-group">
                                <label>Grandezza Font (px)</label>
                                <input type="number" name="title_size" value="<?php echo $template->title_size; ?>" min="20" max="100" class="small-text">
                            </div>
                            <div class="wi-control-group">
                                <label>Colore</label>
                                <input type="text" name="title_color" class="wi-color-picker" value="<?php echo esc_attr($template->title_color); ?>" data-default-color="<?php echo esc_attr($template->title_color); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE COUNTDOWN -->
                <div class="wi-css-section-content" data-section="countdown">
                    <h3>Stile Countdown</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Stile Countdown</label>
                            <select name="countdown_style" class="widefat" onchange="updateCountdownPreview()">
                                <?php
                                $styles = WI_Countdown_Styles::get_countdown_styles();
                                foreach ($styles as $id => $style) {
                                    $selected = ($template->countdown_style == $id) ? 'selected' : '';
                                    echo '<option value="' . $id . '" ' . $selected . '>' . esc_html($style['name']) . ' - ' . esc_html($style['description']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="wi-control-group">
                            <label>Font Countdown</label>
                            <select name="countdown_font" class="widefat">
                                <option value="Lora" <?php selected($template->countdown_font, 'Lora'); ?>>Lora</option>
                                <option value="Montserrat" <?php selected($template->countdown_font, 'Montserrat'); ?>>Montserrat</option>
                                <option value="Playfair Display" <?php selected($template->countdown_font, 'Playfair Display'); ?>>Playfair Display</option>
                                <option value="Roboto" <?php selected($template->countdown_font, 'Roboto'); ?>>Roboto</option>
                                <option value="Open Sans" <?php selected($template->countdown_font, 'Open Sans'); ?>>Open Sans</option>
                            </select>
                        </div>

                        <div class="wi-control-row">
                            <div class="wi-control-group">
                                <label>Colore Numeri</label>
                                <input type="text" name="countdown_color" class="wi-color-picker" value="<?php echo esc_attr($template->countdown_color); ?>" data-default-color="<?php echo esc_attr($template->countdown_color); ?>">
                            </div>
                            <div class="wi-control-group">
                                <label>Colore Sfondo</label>
                                <input type="text" name="countdown_bg_color" class="wi-color-picker" value="<?php echo esc_attr($template->countdown_bg_color); ?>" data-default-color="<?php echo esc_attr($template->countdown_bg_color); ?>">
                            </div>
                        </div>

                        <div class="wi-control-group">
                            <label class="wi-checkbox">
                                <input type="checkbox" name="countdown_animated" <?php checked($template->countdown_animated, 1); ?>>
                                <span>Anima Countdown</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE MESSAGGIO -->
                <div class="wi-css-section-content" data-section="message">
                    <h3>Stile Messaggio</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Font</label>
                            <select name="message_font" class="widefat">
                                <option value="Lora" <?php selected($template->message_font, 'Lora'); ?>>Lora</option>
                                <option value="Open Sans" <?php selected($template->message_font, 'Open Sans'); ?>>Open Sans</option>
                                <option value="Roboto" <?php selected($template->message_font, 'Roboto'); ?>>Roboto</option>
                                <option value="Montserrat" <?php selected($template->message_font, 'Montserrat'); ?>>Montserrat</option>
                            </select>
                        </div>
                        
                        <div class="wi-control-row">
                            <div class="wi-control-group">
                                <label>Grandezza Font (px)</label>
                                <input type="number" name="message_size" value="<?php echo $template->message_size; ?>" min="12" max="32" class="small-text">
                            </div>
                            <div class="wi-control-group">
                                <label>Colore</label>
                                <input type="text" name="message_color" class="wi-color-picker" value="<?php echo esc_attr($template->message_color); ?>" data-default-color="<?php echo esc_attr($template->message_color); ?>">
                            </div>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Colore Sfondo</label>
                            <input type="text" name="message_bg_color" class="wi-color-picker" value="<?php echo esc_attr($template->message_bg_color); ?>" data-default-color="<?php echo esc_attr($template->message_bg_color); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE DETTAGLI -->
                <div class="wi-css-section-content" data-section="details">
                    <h3>Stile Dettagli Evento</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Font</label>
                            <select name="details_font" class="widefat">
                                <option value="Lora" <?php selected($template->details_font, 'Lora'); ?>>Lora</option>
                                <option value="Open Sans" <?php selected($template->details_font, 'Open Sans'); ?>>Open Sans</option>
                                <option value="Roboto" <?php selected($template->details_font, 'Roboto'); ?>>Roboto</option>
                            </select>
                        </div>
                        
                        <div class="wi-control-row">
                            <div class="wi-control-group">
                                <label>Colore</label>
                                <input type="text" name="details_color" class="wi-color-picker" value="<?php echo esc_attr($template->details_color); ?>" data-default-color="<?php echo esc_attr($template->details_color); ?>">
                            </div>
                            <div class="wi-control-group">
                                <label>Colore Sfondo</label>
                                <input type="text" name="details_bg_color" class="wi-color-picker" value="<?php echo esc_attr($template->details_bg_color); ?>" data-default-color="<?php echo esc_attr($template->details_bg_color); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE PULSANTI -->
                <div class="wi-css-section-content" data-section="buttons">
                    <h3>Stile Pulsanti</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-row">
                            <div class="wi-control-group">
                                <label>Colore Sfondo</label>
                                <input type="text" name="button_bg_color" class="wi-color-picker" value="<?php echo esc_attr($template->button_bg_color); ?>" data-default-color="<?php echo esc_attr($template->button_bg_color); ?>">
                            </div>
                            <div class="wi-control-group">
                                <label>Colore Testo</label>
                                <input type="text" name="button_text_color" class="wi-color-picker" value="<?php echo esc_attr($template->button_text_color); ?>" data-default-color="<?php echo esc_attr($template->button_text_color); ?>">
                            </div>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Colore Marker Mappa</label>
                            <input type="text" name="map_marker_color" class="wi-color-picker" value="<?php echo esc_attr($template->map_marker_color); ?>" data-default-color="<?php echo esc_attr($template->map_marker_color); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE IMMAGINI -->
                <div class="wi-css-section-content" data-section="images">
                    <h3>Gestione Immagini</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Header Image - Opacità: <span><?php echo ($template->header_opacity * 100); ?>%</span></label>
                            <input type="range" name="header_opacity" min="0" max="1" step="0.05" value="<?php echo $template->header_opacity; ?>" class="widefat" oninput="this.nextElementSibling.textContent = Math.round(this.value * 100) + '%'">
                            <output><?php echo ($template->header_opacity * 100); ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Header Image - Grandezza: <span><?php echo $template->header_size; ?>%</span></label>
                            <input type="range" name="header_size" min="50" max="150" value="<?php echo $template->header_size; ?>" class="widefat" oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <output><?php echo $template->header_size; ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Decorazione Top - Opacità: <span><?php echo ($template->decoration_top_opacity * 100); ?>%</span></label>
                            <input type="range" name="decoration_top_opacity" min="0" max="1" step="0.05" value="<?php echo $template->decoration_top_opacity; ?>" class="widefat" oninput="this.nextElementSibling.textContent = Math.round(this.value * 100) + '%'">
                            <output><?php echo ($template->decoration_top_opacity * 100); ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Decorazione Top - Grandezza: <span><?php echo $template->decoration_top_size; ?>%</span></label>
                            <input type="range" name="decoration_top_size" min="50" max="150" value="<?php echo $template->decoration_top_size; ?>" class="widefat" oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <output><?php echo $template->decoration_top_size; ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Decorazione Bottom - Opacità: <span><?php echo ($template->decoration_bottom_opacity * 100); ?>%</span></label>
                            <input type="range" name="decoration_bottom_opacity" min="0" max="1" step="0.05" value="<?php echo $template->decoration_bottom_opacity; ?>" class="widefat" oninput="this.nextElementSibling.textContent = Math.round(this.value * 100) + '%'">
                            <output><?php echo ($template->decoration_bottom_opacity * 100); ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Decorazione Bottom - Grandezza: <span><?php echo $template->decoration_bottom_size; ?>%</span></label>
                            <input type="range" name="decoration_bottom_size" min="50" max="150" value="<?php echo $template->decoration_bottom_size; ?>" class="widefat" oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <output><?php echo $template->decoration_bottom_size; ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Immagine Utente - Opacità: <span><?php echo ($template->user_image_opacity * 100); ?>%</span></label>
                            <input type="range" name="user_image_opacity" min="0" max="1" step="0.05" value="<?php echo $template->user_image_opacity; ?>" class="widefat" oninput="this.nextElementSibling.textContent = Math.round(this.value * 100) + '%'">
                            <output><?php echo ($template->user_image_opacity * 100); ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Immagine Utente - Grandezza: <span><?php echo $template->user_image_size; ?>%</span></label>
                            <input type="range" name="user_image_size" min="50" max="150" value="<?php echo $template->user_image_size; ?>" class="widefat" oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <output><?php echo $template->user_image_size; ?>%</output>
                        </div>
                    </div>
                </div>
                
                <!-- SEZIONE SFONDO -->
                <div class="wi-css-section-content" data-section="background">
                    <h3>Stile Sfondo</h3>
                    <div class="wi-css-controls">
                        <div class="wi-control-group">
                            <label>Colore Sfondo</label>
                            <input type="text" name="background_color" class="wi-color-picker" value="<?php echo esc_attr($template->background_color); ?>" data-default-color="<?php echo esc_attr($template->background_color); ?>">
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Grandezza Immagine Sfondo: <span><?php echo $template->background_size; ?>%</span></label>
                            <input type="range" name="background_size" min="50" max="200" value="<?php echo $template->background_size; ?>" class="widefat" oninput="this.nextElementSibling.textContent = this.value + '%'">
                            <output><?php echo $template->background_size; ?>%</output>
                        </div>
                        
                        <div class="wi-control-group">
                            <label>Opacità Sfondo: <span><?php echo ($template->background_opacity * 100); ?>%</span></label>
                            <input type="range" name="background_opacity" min="0" max="1" step="0.05" value="<?php echo $template->background_opacity; ?>" class="widefat" oninput="this.nextElementSibling.textContent = Math.round(this.value * 100) + '%'">
                            <output><?php echo ($template->background_opacity * 100); ?>%</output>
                        </div>

                        <div class="wi-control-group" style="margin-top: 30px; border-top: 2px solid #e0e0e0; padding-top: 30px;">
                            <label style="font-size: 15px; font-weight: 600; color: #667eea;">
                                <span class="dashicons dashicons-editor-code"></span>
                                CSS Personalizzato
                                <span class="description" style="display: block; margin-top: 5px; font-size: 12px; font-weight: normal; color: #666;">(Codice CSS aggiuntivo per personalizzazioni avanzate)</span>
                            </label>
                            <textarea name="custom_css" class="widefat" rows="12" style="font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 15px;" placeholder="Esempio:&#10;&#10;.wi-title {&#10;    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);&#10;    letter-spacing: 2px;&#10;}&#10;&#10;.wi-countdown-section {&#10;    border: 2px solid gold;&#10;    box-shadow: 0 4px 10px rgba(0,0,0,0.1);&#10;}"><?php echo esc_textarea($template->custom_css ?? ''); ?></textarea>
                            <p class="description" style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-left: 3px solid #2196F3; border-radius: 3px;">
                                <strong>💡 Suggerimento:</strong> Questo CSS verrà aggiunto DOPO gli stili del template, permettendoti di sovrascrivere qualsiasi regola. Usa <code>!important</code> se necessario.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="wi-editor-actions">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-yes"></span>
                        Salva Stili
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.wi-css-editor {
    margin: 20px 20px 0 0;
}

.wi-editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.wi-editor-header h1 {
    margin: 0;
    font-size: 1.5rem;
}

.wi-css-editor-layout {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
}

.wi-css-sidebar {
    background: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.wi-css-sections {
    display: flex;
    flex-direction: column;
}

.wi-css-section-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    border-left: 4px solid transparent;
    text-align: left;
    transition: all 0.3s;
    font-weight: 500;
}

.wi-css-section-btn:hover {
    background: #f1f3f5;
}

.wi-css-section-btn.active {
    background: #e7f5ff;
    border-left-color: #0084ff;
    color: #0084ff;
}

.wi-css-section-btn .dashicons {
    font-size: 1.2rem;
    width: 1.2rem;
    height: 1.2rem;
}

.wi-css-editor-main {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.wi-css-section-content {
    display: none;
}

.wi-css-section-content.active {
    display: block;
}

.wi-css-section-content h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.2rem;
    color: #1e293b;
}

.wi-css-controls {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.wi-control-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.wi-control-group label {
    font-weight: 600;
    color: #334155;
    font-size: 0.9rem;
}

.wi-control-group input[type="text"],
.wi-control-group input[type="number"],
.wi-control-group input[type="range"],
.wi-control-group select {
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.wi-control-group input:focus,
.wi-control-group select:focus {
    border-color: #0084ff;
    box-shadow: 0 0 0 3px rgba(0,132,255,0.1);
    outline: none;
}

.wi-control-group input[type="range"] {
    border: none;
    padding: 0;
}

.wi-control-group output {
    font-weight: 600;
    color: #0084ff;
}

.wi-control-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.wi-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 4px;
    transition: all 0.3s;
}

.wi-checkbox:hover {
    background: #f1f3f5;
}

.wi-checkbox input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.wp-color-picker-container {
    width: 100%;
}

.wp-picker-container {
    margin: 0 !important;
}

.wi-editor-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.wi-editor-actions .button-primary {
    background: linear-gradient(135deg, #0084ff 0%, #0066cc 100%);
    border: none;
}

@media (max-width: 768px) {
    .wi-css-editor-layout {
        grid-template-columns: 1fr;
    }
    
    .wi-css-sidebar {
        position: static;
    }
    
    .wi-css-sections {
        flex-direction: row;
        overflow-x: auto;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.wi-color-picker').wpColorPicker();
    
    $('#css-editor-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Stili salvati con successo!');
                } else {
                    alert('Errore: ' + response.data);
                }
            }
        });
    });
});

function switchSection(btn) {
    document.querySelectorAll('.wi-css-section-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.wi-css-section-content').forEach(c => c.classList.remove('active'));
    
    btn.classList.add('active');
    var section = btn.getAttribute('data-section');
    document.querySelector('[data-section="' + section + '"].wi-css-section-content').classList.add('active');
}
</script>
