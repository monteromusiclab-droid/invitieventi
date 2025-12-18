<?php
/**
 * Template Manager Visual PRO
 * Interfaccia professionale con editor visuale completo
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('edit_posts') && !current_user_can('manage_options')) {
    wp_die(__('Non hai i permessi per accedere a questa pagina.'));
}

// Carica Google Fonts (inclusi nuovi font: Poppins, Raleway, Merriweather, Crimson Text, Bebas Neue)
wp_enqueue_style('google-fonts-editor', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Lora:wght@400;500;600&family=Montserrat:wght@400;500;600;700&family=Dancing+Script:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&family=Merriweather:wght@300;400;700&family=Crimson+Text:wght@400;600;700&family=Bebas+Neue&display=swap');

// Carica WordPress Media Uploader
wp_enqueue_media();

// Carica Color Picker
wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');

// Gestione salvataggio
$message = '';
$message_type = '';

if (isset($_POST['save_template']) && check_admin_referer('wi_save_visual_template')) {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_templates';

    $template_id = intval($_POST['template_id']);
    $template_data = array(
        'name' => sanitize_text_field($_POST['template_name']),
        'description' => sanitize_textarea_field($_POST['template_description']),
        'category' => sanitize_text_field($_POST['template_category']),
        'html_structure' => wp_unslash($_POST['template_html']),
        'css_styles' => wp_unslash($_POST['template_css']),
        'is_active' => isset($_POST['template_active']) ? 1 : 0,
        'sort_order' => intval($_POST['template_order']),

        // Immagini
        'header_image' => esc_url($_POST['header_image']),
        'decoration_top' => esc_url($_POST['decoration_top']),
        'decoration_bottom' => esc_url($_POST['decoration_bottom']),
        'background_image' => esc_url($_POST['background_image']),
        'footer_logo' => esc_url($_POST['footer_logo']),

        // Font e colori
        'title_font' => sanitize_text_field($_POST['font_title']),
        'message_font' => sanitize_text_field($_POST['font_body']),
        'background_color' => sanitize_text_field($_POST['color_background']),
        'title_color' => sanitize_text_field($_POST['color_primary']),
        'message_color' => sanitize_text_field($_POST['color_text']),

        // CSS Titoli Sezioni
        'section_title_font' => sanitize_text_field($_POST['section_title_font'] ?? 'inherit'),
        'section_title_size' => intval($_POST['section_title_size'] ?? 28),
        'section_title_color' => sanitize_text_field($_POST['section_title_color'] ?? '#2c3e50'),
        'section_title_weight' => sanitize_text_field($_POST['section_title_weight'] ?? '600'),

        // Countdown
        'countdown_style' => sanitize_text_field($_POST['countdown_style'] ?? 'style1'),
        'countdown_font' => sanitize_text_field($_POST['countdown_font'] ?? 'Lora'),
        'countdown_color' => sanitize_text_field($_POST['countdown_color'] ?? '#2c3e50'),
        'countdown_bg_color' => sanitize_text_field($_POST['countdown_bg_color'] ?? '#f8f9fa'),

        // Opacità e animazioni
        'background_opacity' => floatval($_POST['background_opacity']),
        'countdown_animated' => isset($_POST['countdown_animated']) ? 1 : 0
    );
    
    if ($template_id > 0) {
        $result = $wpdb->update($table, $template_data, array('id' => $template_id));
    } else {
        $result = $wpdb->insert($table, $template_data);
        $template_id = $wpdb->insert_id;
    }

    // Salva relazioni categorie in tabella relazionale
    if ($result !== false && $template_id > 0) {
        $relations_table = $wpdb->prefix . 'wi_template_categories';

        // Elimina vecchie relazioni
        $wpdb->delete($relations_table, array('template_id' => $template_id));

        // Inserisci nuove relazioni da checkbox
        if (!empty($_POST['template_categories'])) {
            foreach ($_POST['template_categories'] as $category_id) {
                $wpdb->insert($relations_table, array(
                    'template_id' => $template_id,
                    'category_id' => intval($category_id)
                ));
            }
        }

        $message = 'Template salvato con successo!';
        $message_type = 'success';
    } else {
        $message = 'Errore nel salvataggio del template.';
        $message_type = 'error';
    }
}

// CSS Editor
if (isset($_GET['css_editor']) && intval($_GET['css_editor']) > 0) {
    require_once WI_PLUGIN_DIR . 'admin/template-css-editor.php';
    return;
}

// REDIRECT al nuovo editor unificato se richiesta modifica
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $template_id = intval($_GET['edit']);
    wp_redirect(admin_url('admin.php?page=wedding-invites-template-edit&id=' . $template_id));
    exit;
}

// REDIRECT al nuovo editor unificato se richiesta creazione
if (isset($_GET['new'])) {
    wp_redirect(admin_url('admin.php?page=wedding-invites-template-edit'));
    exit;
}

// Lista template
if (!isset($_GET['edit']) && !isset($_GET['new'])) {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_templates';
    $templates = $wpdb->get_results("SELECT * FROM $table ORDER BY sort_order ASC, id ASC");
    ?>
    
    <div class="wrap wi-visual-manager">
        <h1 class="wi-page-title">
            <span class="dashicons dashicons-admin-customizer"></span>
            Template Manager Visual PRO
        </h1>
        
        <?php if ($message) : ?>
        <div class="notice notice-<?php echo $message_type; ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="wi-templates-grid">
            <?php if (empty($templates)) : ?>
            <div class="wi-empty-state">
                <div class="wi-empty-icon">🎨</div>
                <h2>Nessun Template Disponibile</h2>
                <p>Crea il tuo primo template visuale per iniziare</p>
                <a href="?page=wedding-invites-templates&new=1" class="button button-primary button-hero">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Crea Primo Template
                </a>
            </div>
            <?php else : ?>
            
            <div class="wi-create-card">
                <a href="?page=wedding-invites-templates&new=1" class="wi-create-link">
                    <div class="wi-create-icon">
                        <span class="dashicons dashicons-plus-alt"></span>
                    </div>
                    <h3>Crea Nuovo Template</h3>
                    <p>Inizia da zero con l'editor visuale</p>
                </a>
            </div>
            
            <?php foreach ($templates as $template) : ?>
            <div class="wi-template-card <?php echo !$template->is_active ? 'inactive' : ''; ?>">
                <div class="wi-card-preview">
                    <?php if ($template->header_image) : ?>
                        <img src="<?php echo esc_url($template->header_image); ?>" alt="Preview">
                    <?php else : ?>
                        <div class="wi-card-placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($template->countdown_animated) : ?>
                    <div class="wi-card-badge">✨ Animato</div>
                    <?php endif; ?>
                </div>
                
                <div class="wi-card-body">
                    <h3 class="wi-card-title"><?php echo esc_html($template->name); ?></h3>
                    <p class="wi-card-description"><?php echo esc_html($template->description); ?></p>
                    
                    <div class="wi-card-meta">
                        <?php if ($template->is_active) : ?>
                        <span class="wi-status active">
                            <span class="dashicons dashicons-yes-alt"></span> Attivo
                        </span>
                        <?php else : ?>
                        <span class="wi-status inactive">
                            <span class="dashicons dashicons-dismiss"></span> Inattivo
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="wi-card-actions">
                        <a href="?page=wedding-invites-templates&edit=<?php echo $template->id; ?>" class="button button-primary">
                            <span class="dashicons dashicons-edit"></span> Modifica
                        </a>
                        <button class="button button-secondary wi-delete-template" data-template-id="<?php echo $template->id; ?>" data-template-name="<?php echo esc_attr($template->name); ?>">
                            <span class="dashicons dashicons-trash"></span> Elimina
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .wi-visual-manager {
        margin: 20px 20px 0 0;
    }
    
    .wi-page-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 2rem;
        margin-bottom: 30px;
        color: #1e293b;
    }
    
    .wi-page-title .dashicons {
        font-size: 2.5rem;
        width: 2.5rem;
        height: 2.5rem;
        color: #6366f1;
    }
    
    .wi-templates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }
    
    .wi-empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .wi-empty-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    
    .wi-empty-state h2 {
        font-size: 1.8rem;
        margin-bottom: 10px;
    }
    
    .wi-empty-state p {
        font-size: 1.1rem;
        color: #64748b;
        margin-bottom: 30px;
    }
    
    .wi-create-card {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(99,102,241,0.25);
        transition: all 0.3s;
    }
    
    .wi-create-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(99,102,241,0.35);
    }
    
    .wi-create-link {
        display: block;
        padding: 40px 30px;
        text-align: center;
        color: white;
        text-decoration: none;
    }
    
    .wi-create-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .wi-create-icon .dashicons {
        font-size: 3rem;
        width: 3rem;
        height: 3rem;
    }
    
    .wi-create-link h3 {
        font-size: 1.4rem;
        margin: 0 0 10px 0;
        color: white;
    }
    
    .wi-create-link p {
        margin: 0;
        opacity: 0.9;
        font-size: 1rem;
    }
    
    .wi-template-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .wi-template-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        border-color: #6366f1;
    }
    
    .wi-template-card.inactive {
        opacity: 0.6;
    }
    
    .wi-card-preview {
        position: relative;
        height: 200px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        overflow: hidden;
    }
    
    .wi-card-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .wi-card-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .wi-card-placeholder .dashicons {
        font-size: 4rem;
        width: 4rem;
        height: 4rem;
        color: #cbd5e1;
    }
    
    .wi-card-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(99,102,241,0.95);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .wi-card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    
    .wi-card-title {
        font-size: 1.3rem;
        margin: 0 0 10px 0;
        color: #1e293b;
    }
    
    .wi-card-description {
        color: #64748b;
        margin-bottom: 15px;
        min-height: 40px;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .wi-card-meta {
        margin-bottom: 15px;
        padding-top: 15px;
        border-top: 1px solid #e2e8f0;
    }
    
    .wi-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .wi-status.active {
        background: #d1fae5;
        color: #065f46;
    }
    
    .wi-status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .wi-status .dashicons {
        font-size: 1rem;
        width: 1rem;
        height: 1rem;
    }
    
    .wi-card-actions {
        display: flex;
        gap: 10px;
        margin-top: auto;
    }
    
    .wi-card-actions .button {
        flex: 1;
        justify-content: center;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .wi-card-actions .wi-delete-template {
        background: #dc2626;
        color: white;
        border-color: #b91c1c;
    }

    .wi-card-actions .wi-delete-template:hover {
        background: #b91c1c;
        border-color: #991b1b;
        color: white;
    }
    
    @media (max-width: 768px) {
        .wi-templates-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Gestione eliminazione template
        $('.wi-delete-template').on('click', function(e) {
            e.preventDefault();

            var templateId = $(this).data('template-id');
            var templateName = $(this).data('template-name');
            var $button = $(this);
            var $card = $button.closest('.wi-template-card');

            if (!confirm('Sei sicuro di voler eliminare il template "' + templateName + '"?\n\nATTENZIONE: Questa azione non può essere annullata.\n\nNota: Se il template è utilizzato da inviti esistenti, non potrà essere eliminato.')) {
                return false;
            }

            // Disabilita il pulsante durante la richiesta
            $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Eliminazione...');

            // Richiesta AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wi_delete_template',
                    template_id: templateId,
                    nonce: '<?php echo wp_create_nonce("wi_delete_template"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        // Animazione di rimozione
                        $card.fadeOut(400, function() {
                            $(this).remove();

                            // Mostra messaggio di successo
                            $('.wrap.wi-visual-manager h1').after(
                                '<div class="notice notice-success is-dismissible"><p><strong>Template eliminato con successo!</strong></p></div>'
                            );

                            // Se non ci sono più template, ricarica la pagina per mostrare empty state
                            if ($('.wi-template-card').length === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        // Mostra errore
                        alert('ERRORE: ' + response.data.message);
                        $button.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Elimina');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Errore durante l\'eliminazione del template. Riprova.');
                    console.error('Errore AJAX:', error);
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> Elimina');
                }
            });
        });
    });
    </script>

    <?php
    return;
}

// EDITOR VISUALE
?>

<div class="wrap wi-visual-editor">
    <div class="wi-editor-header">
        <div class="wi-header-left">
            <a href="?page=wedding-invites-templates" class="wi-back-btn">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                Torna ai Template
            </a>
            <h1><?php echo $edit_template ? 'Modifica: ' . esc_html($edit_template->name) : 'Nuovo Template'; ?></h1>
        </div>
        <div class="wi-header-right">
            <button type="button" class="button wi-preview-btn" onclick="openPreview()">
                <span class="dashicons dashicons-visibility"></span>
                Anteprima Live
            </button>
        </div>
    </div>
    
    <?php if ($message) : ?>
    <div class="notice notice-<?php echo $message_type; ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <form method="post" id="visual-template-form">
        <?php wp_nonce_field('wi_save_visual_template'); ?>
        <input type="hidden" name="save_template" value="1">
        <input type="hidden" name="template_id" value="<?php echo $edit_template ? $edit_template->id : 0; ?>">
        <input type="hidden" name="template_html" id="template_html" value="">
        <input type="hidden" name="template_css" id="template_css" value="">
        <input type="hidden" name="header_image" id="header_image_value" value="<?php echo $edit_template ? esc_url($edit_template->header_image) : ''; ?>">
        <input type="hidden" name="decoration_top" id="decoration_top_value" value="<?php echo $edit_template ? esc_url($edit_template->decoration_top) : ''; ?>">
        <input type="hidden" name="decoration_bottom" id="decoration_bottom_value" value="<?php echo $edit_template ? esc_url($edit_template->decoration_bottom) : ''; ?>">
        <input type="hidden" name="background_image" id="background_image_value" value="<?php echo $edit_template ? esc_url($edit_template->background_image) : ''; ?>">
        <input type="hidden" name="footer_logo" id="footer_logo_value" value="<?php echo $edit_template ? esc_url($edit_template->footer_logo) : ''; ?>">

        <div class="wi-editor-layout">
            <!-- Sidebar Controlli -->
            <div class="wi-editor-sidebar">
                
                <!-- Informazioni Base -->
                <div class="wi-panel">
                    <div class="wi-panel-header">
                        <h3><span class="dashicons dashicons-info"></span> Informazioni</h3>
                    </div>
                    <div class="wi-panel-body">
                        <div class="wi-form-group">
                            <label>Nome Template</label>
                            <input type="text" name="template_name" id="template_name" class="widefat" 
                                   value="<?php echo $edit_template ? esc_attr($edit_template->name) : ''; ?>" required>
                        </div>
                        
                        <div class="wi-form-group">
                            <label>Descrizione</label>
                            <textarea name="template_description" class="widefat" rows="3"><?php echo $edit_template ? esc_textarea($edit_template->description) : ''; ?></textarea>
                        </div>

                        <div class="wi-form-group">
                            <label>Categorie Template</label>
                            <div class="wi-categories-checkboxes" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px;">
                                <?php
                                global $wpdb;

                                // Verifica se tabella categorie esiste
                                $categories_table = $wpdb->prefix . 'wi_event_categories';
                                $table_exists = ($wpdb->get_var("SHOW TABLES LIKE '$categories_table'") !== null);

                                if (!$table_exists) {
                                    echo '<div style="grid-column: 1 / -1; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">';
                                    echo '<p style="margin: 0;"><strong>⚠️ Sistema categorie non attivato</strong></p>';
                                    echo '<p style="margin: 10px 0 0 0;">Per utilizzare le categorie, esegui prima la migrazione database v2.2.0.</p>';
                                    echo '<a href="' . admin_url('admin.php?page=wedding-invites-categories') . '" class="button button-small" style="margin-top: 10px;">Vai alla pagina Categorie</a>';
                                    echo '</div>';
                                } else {
                                    // Carica categorie dal database
                                    $categories = $wpdb->get_results("SELECT * FROM $categories_table WHERE is_active = 1 ORDER BY sort_order");

                                // Ottieni categorie già assegnate
                                $assigned_categories = array();
                                if ($edit_template) {
                                    $relations_table = $wpdb->prefix . 'wi_template_categories';
                                    $assigned = $wpdb->get_results($wpdb->prepare(
                                        "SELECT category_id FROM $relations_table WHERE template_id = %d",
                                        $edit_template->id
                                    ));
                                    foreach ($assigned as $rel) {
                                        $assigned_categories[] = $rel->category_id;
                                    }
                                }

                                    foreach ($categories as $cat) {
                                        $checked = in_array($cat->id, $assigned_categories) ? 'checked' : '';
                                        echo '<label style="display: flex; align-items: center; gap: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px; cursor: pointer;">';
                                        echo '<input type="checkbox" name="template_categories[]" value="' . $cat->id . '" ' . $checked . '>';
                                        echo '<span style="font-size: 18px;">' . esc_html($cat->icon) . '</span>';
                                        echo '<span>' . esc_html($cat->name) . '</span>';
                                        echo '</label>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="description" style="margin-top: 10px;">
                                Seleziona i tipi di evento per cui questo template è adatto.
                                <a href="<?php echo admin_url('admin.php?page=wedding-invites-categories'); ?>" target="_blank">Gestisci categorie</a>
                            </p>
                        </div>

                        <div class="wi-form-group">
                            <label>Ordine</label>
                            <input type="number" name="template_order" class="small-text"
                                   value="<?php echo $edit_template ? $edit_template->sort_order : 0; ?>">
                        </div>
                        
                        <div class="wi-form-group">
                            <label class="wi-checkbox">
                                <input type="checkbox" name="template_active" 
                                       <?php echo (!$edit_template || $edit_template->is_active) ? 'checked' : ''; ?>>
                                <span>Template Attivo</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Font e Colori -->
                <div class="wi-panel">
                    <div class="wi-panel-header">
                        <h3><span class="dashicons dashicons-art"></span> Stile e Font</h3>
                    </div>
                    <div class="wi-panel-body">
                        <div class="wi-form-group">
                            <label>Font Titolo</label>
                            <select id="font_title" name="font_title" class="widefat" onchange="updatePreview()">
                                <option value="Playfair Display" <?php selected($edit_template ? $edit_template->title_font : '', 'Playfair Display'); ?>>Playfair Display (Elegante)</option>
                                <option value="Lora" <?php selected($edit_template ? $edit_template->title_font : '', 'Lora'); ?>>Lora (Serif Moderno)</option>
                                <option value="Montserrat" <?php selected($edit_template ? $edit_template->title_font : '', 'Montserrat'); ?>>Montserrat (Sans-serif)</option>
                                <option value="Dancing Script" <?php selected($edit_template ? $edit_template->title_font : '', 'Dancing Script'); ?>>Dancing Script (Calligrafico)</option>
                                <option value="Roboto" <?php selected($edit_template ? $edit_template->title_font : '', 'Roboto'); ?>>Roboto (Moderno)</option>
                                <option value="Open Sans" <?php selected($edit_template ? $edit_template->title_font : '', 'Open Sans'); ?>>Open Sans (Pulito)</option>
                                <option value="Poppins" <?php selected($edit_template ? $edit_template->title_font : '', 'Poppins'); ?>>Poppins (Geometrico)</option>
                                <option value="Raleway" <?php selected($edit_template ? $edit_template->title_font : '', 'Raleway'); ?>>Raleway (Elegante Sans)</option>
                                <option value="Merriweather" <?php selected($edit_template ? $edit_template->title_font : '', 'Merriweather'); ?>>Merriweather (Serif Classico)</option>
                                <option value="Crimson Text" <?php selected($edit_template ? $edit_template->title_font : '', 'Crimson Text'); ?>>Crimson Text (Editoriale)</option>
                                <option value="Bebas Neue" <?php selected($edit_template ? $edit_template->title_font : '', 'Bebas Neue'); ?>>Bebas Neue (Bold Display)</option>
                            </select>
                        </div>

                        <div class="wi-form-group">
                            <label>Font Testo</label>
                            <select id="font_body" name="font_body" class="widefat" onchange="updatePreview()">
                                <option value="Lora" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Lora'); ?>>Lora (Serif Moderno)</option>
                                <option value="Open Sans" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Open Sans'); ?>>Open Sans (Leggibile)</option>
                                <option value="Roboto" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Roboto'); ?>>Roboto (Neutro)</option>
                                <option value="Montserrat" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Montserrat'); ?>>Montserrat (Geometrico)</option>
                                <option value="Poppins" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Poppins'); ?>>Poppins (Friendly)</option>
                                <option value="Raleway" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Raleway'); ?>>Raleway (Leggero)</option>
                                <option value="Merriweather" <?php selected($edit_template ? $edit_template->message_font : 'Open Sans', 'Merriweather'); ?>>Merriweather (Lettura)</option>
                            </select>
                        </div>

                        <div class="wi-form-group">
                            <label>Colore Primario</label>
                            <input type="text" id="color_primary" name="color_primary" class="wi-color-picker"
                                   value="<?php echo $edit_template ? esc_attr($edit_template->title_color) : '#6366f1'; ?>"
                                   data-default-color="<?php echo $edit_template ? esc_attr($edit_template->title_color) : '#6366f1'; ?>">
                        </div>

                        <div class="wi-form-group">
                            <label>Colore Testo</label>
                            <input type="text" id="color_text" name="color_text" class="wi-color-picker"
                                   value="<?php echo $edit_template ? esc_attr($edit_template->message_color) : '#1e293b'; ?>"
                                   data-default-color="<?php echo $edit_template ? esc_attr($edit_template->message_color) : '#1e293b'; ?>">
                        </div>

                        <div class="wi-form-group">
                            <label>Colore Sfondo</label>
                            <input type="text" id="color_background" name="color_background" class="wi-color-picker"
                                   value="<?php echo $edit_template ? esc_attr($edit_template->background_color) : '#ffffff'; ?>"
                                   data-default-color="<?php echo $edit_template ? esc_attr($edit_template->background_color) : '#ffffff'; ?>">
                        </div>

                        <hr style="margin: 30px 0; border: none; border-top: 2px solid #e0e0e0;">
                        <h4 style="margin-bottom: 20px; color: #667eea;">Personalizzazione Titoli Sezioni (.wi-section-title)</h4>

                        <div class="wi-form-group">
                            <label>Font Titoli Sezioni</label>
                            <select name="section_title_font" class="widefat" onchange="updatePreview()">
                                <option value="inherit" <?php selected($edit_template->section_title_font ?? 'inherit', 'inherit'); ?>>Eredita da Titolo Principale</option>
                                <option value="Playfair Display" <?php selected($edit_template->section_title_font ?? '', 'Playfair Display'); ?>>Playfair Display (Elegante)</option>
                                <option value="Lora" <?php selected($edit_template->section_title_font ?? '', 'Lora'); ?>>Lora (Serif Moderno)</option>
                                <option value="Montserrat" <?php selected($edit_template->section_title_font ?? '', 'Montserrat'); ?>>Montserrat (Sans-serif)</option>
                                <option value="Roboto" <?php selected($edit_template->section_title_font ?? '', 'Roboto'); ?>>Roboto (Moderno)</option>
                                <option value="Open Sans" <?php selected($edit_template->section_title_font ?? '', 'Open Sans'); ?>>Open Sans (Pulito)</option>
                                <option value="Poppins" <?php selected($edit_template->section_title_font ?? '', 'Poppins'); ?>>Poppins (Geometrico)</option>
                                <option value="Raleway" <?php selected($edit_template->section_title_font ?? '', 'Raleway'); ?>>Raleway (Elegante Sans)</option>
                                <option value="Merriweather" <?php selected($edit_template->section_title_font ?? '', 'Merriweather'); ?>>Merriweather (Serif Classico)</option>
                                <option value="Crimson Text" <?php selected($edit_template->section_title_font ?? '', 'Crimson Text'); ?>>Crimson Text (Editoriale)</option>
                                <option value="Bebas Neue" <?php selected($edit_template->section_title_font ?? '', 'Bebas Neue'); ?>>Bebas Neue (Bold Display)</option>
                            </select>
                        </div>

                        <div class="wi-form-group">
                            <label>Dimensione Font Sezioni</label>
                            <input type="number" name="section_title_size" class="small-text"
                                   value="<?php echo $edit_template->section_title_size ?? 28; ?>"
                                   min="16" max="48" step="1" onchange="updatePreview()"> px
                        </div>

                        <div class="wi-form-group">
                            <label>Colore Titoli Sezioni</label>
                            <input type="text" name="section_title_color" class="wi-color-picker"
                                   value="<?php echo $edit_template->section_title_color ?? '#2c3e50'; ?>"
                                   data-default-color="<?php echo $edit_template->section_title_color ?? '#2c3e50'; ?>"
                                   onchange="updatePreview()">
                        </div>

                        <div class="wi-form-group">
                            <label>Peso Font (font-weight)</label>
                            <select name="section_title_weight" onchange="updatePreview()">
                                <option value="400" <?php selected($edit_template->section_title_weight ?? '', '400'); ?>>Normale (400)</option>
                                <option value="500" <?php selected($edit_template->section_title_weight ?? '', '500'); ?>>Medio (500)</option>
                                <option value="600" <?php selected($edit_template->section_title_weight ?? '600', '600'); ?>>Semi-Bold (600)</option>
                                <option value="700" <?php selected($edit_template->section_title_weight ?? '', '700'); ?>>Bold (700)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Immagini -->
                <div class="wi-panel">
                    <div class="wi-panel-header">
                        <h3><span class="dashicons dashicons-format-image"></span> Immagini</h3>
                    </div>
                    <div class="wi-panel-body">
                        
                        <!-- Header Image -->
                        <div class="wi-form-group">
                            <label>Immagine Header</label>
                            <div class="wi-image-upload" id="header_image_container">
                                <?php if ($edit_template && $edit_template->header_image) : ?>
                                <img src="<?php echo esc_url($edit_template->header_image); ?>" class="wi-preview-img">
                                <button type="button" class="wi-remove-img" onclick="removeImage('header_image')">×</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-small" onclick="uploadImage('header_image')">
                                <span class="dashicons dashicons-upload"></span> Carica Immagine
                            </button>
                        </div>
                        
                        <!-- Decoration Top -->
                        <div class="wi-form-group">
                            <label>Decorazione Sopra</label>
                            <div class="wi-image-upload small" id="decoration_top_container">
                                <?php if ($edit_template && $edit_template->decoration_top) : ?>
                                <img src="<?php echo esc_url($edit_template->decoration_top); ?>" class="wi-preview-img">
                                <button type="button" class="wi-remove-img" onclick="removeImage('decoration_top')">×</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-small" onclick="uploadImage('decoration_top')">
                                <span class="dashicons dashicons-upload"></span> Carica
                            </button>
                        </div>
                        
                        <!-- Decoration Bottom -->
                        <div class="wi-form-group">
                            <label>Decorazione Sotto</label>
                            <div class="wi-image-upload small" id="decoration_bottom_container">
                                <?php if ($edit_template && $edit_template->decoration_bottom) : ?>
                                <img src="<?php echo esc_url($edit_template->decoration_bottom); ?>" class="wi-preview-img">
                                <button type="button" class="wi-remove-img" onclick="removeImage('decoration_bottom')">×</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-small" onclick="uploadImage('decoration_bottom')">
                                <span class="dashicons dashicons-upload"></span> Carica
                            </button>
                        </div>
                        
                        <!-- Background -->
                        <div class="wi-form-group">
                            <label>Immagine Sfondo</label>
                            <div class="wi-image-upload small" id="background_image_container">
                                <?php if ($edit_template && $edit_template->background_image) : ?>
                                <img src="<?php echo esc_url($edit_template->background_image); ?>" class="wi-preview-img">
                                <button type="button" class="wi-remove-img" onclick="removeImage('background_image')">×</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-small" onclick="uploadImage('background_image')">
                                <span class="dashicons dashicons-upload"></span> Carica
                            </button>
                        </div>
                        
                        <div class="wi-form-group">
                            <label>Trasparenza Sfondo: <span id="opacity_value"><?php echo $edit_template ? ($edit_template->background_opacity * 100) : 90; ?>%</span></label>
                            <input type="range" name="background_opacity" id="background_opacity"
                                   min="0" max="1" step="0.01"
                                   value="<?php echo $edit_template ? $edit_template->background_opacity : 0.9; ?>"
                                   oninput="document.getElementById('opacity_value').textContent = Math.round(this.value * 100) + '%'; updatePreview();">
                        </div>

                        <!-- Footer Logo -->
                        <div class="wi-form-group">
                            <label>Logo Finale (Footer)</label>
                            <div class="wi-image-upload small" id="footer_logo_container">
                                <?php if ($edit_template && $edit_template->footer_logo) : ?>
                                <img src="<?php echo esc_url($edit_template->footer_logo); ?>" class="wi-preview-img">
                                <button type="button" class="wi-remove-img" onclick="removeImage('footer_logo')">×</button>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-small" onclick="uploadImage('footer_logo')">
                                <span class="dashicons dashicons-upload"></span> Carica Logo
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Animazioni -->
                <div class="wi-panel">
                    <div class="wi-panel-header">
                        <h3><span class="dashicons dashicons-controls-play"></span> Effetti</h3>
                    </div>
                    <div class="wi-panel-body">
                        <div class="wi-form-group">
                            <label class="wi-checkbox">
                                <input type="checkbox" name="countdown_animated" id="countdown_animated" 
                                       <?php echo ($edit_template && $edit_template->countdown_animated) ? 'checked' : ''; ?>
                                       onchange="updatePreview()">
                                <span>Anima Countdown</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Salva -->
                <div class="wi-save-panel">
                    <button type="submit" class="button button-primary button-large widefat">
                        <span class="dashicons dashicons-yes-alt"></span>
                        Salva Template
                    </button>
                </div>
                
            </div>
            
            <!-- Anteprima -->
            <div class="wi-editor-preview">
                <div class="wi-preview-header">
                    <h3>Anteprima Live</h3>
                    <div class="wi-preview-devices">
                        <button type="button" class="wi-device-btn active" data-device="desktop" onclick="changeDevice('desktop')">
                            <span class="dashicons dashicons-desktop"></span>
                        </button>
                        <button type="button" class="wi-device-btn" data-device="tablet" onclick="changeDevice('tablet')">
                            <span class="dashicons dashicons-tablet"></span>
                        </button>
                        <button type="button" class="wi-device-btn" data-device="mobile" onclick="changeDevice('mobile')">
                            <span class="dashicons dashicons-smartphone"></span>
                        </button>
                    </div>
                </div>
                
                <div class="wi-preview-container" id="preview_container">
                    <iframe id="preview_iframe" class="wi-preview-iframe"></iframe>
                </div>
            </div>
            
        </div>
    </form>
</div>

<!-- Continua nel prossimo file per CSS e JavaScript... -->
<style>
.wi-visual-editor {
    margin: 20px 20px 0 0;
}

.wi-editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.wi-header-left h1 {
    margin: 10px 0 0 0;
    font-size: 1.8rem;
    color: #1e293b;
}

.wi-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.wi-back-btn:hover {
    color: #4f46e5;
    gap: 12px;
}

.wi-preview-btn {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.wi-preview-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99,102,241,0.4);
}

.wi-editor-layout {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 20px;
}

.wi-editor-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.wi-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

.wi-panel-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 15px 20px;
    border-bottom: 2px solid #e2e8f0;
}

.wi-panel-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.wi-panel-header .dashicons {
    color: #6366f1;
    font-size: 1.2rem;
    width: 1.2rem;
    height: 1.2rem;
}

.wi-panel-body {
    padding: 20px;
}

.wi-form-group {
    margin-bottom: 20px;
}

.wi-form-group:last-child {
    margin-bottom: 0;
}

.wi-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #334155;
    font-size: 0.9rem;
}

.wi-form-group input[type="text"],
.wi-form-group input[type="number"],
.wi-form-group select,
.wi-form-group textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.3s;
}

.wi-form-group input:focus,
.wi-form-group select:focus,
.wi-form-group textarea:focus {
    border-color: #6366f1;
    outline: none;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.wi-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 12px;
    background: #f8fafc;
    border-radius: 6px;
    transition: all 0.3s;
}

.wi-checkbox:hover {
    background: #f1f5f9;
}

.wi-checkbox input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.wi-checkbox span {
    font-weight: 500;
}

/* Image Upload */
.wi-image-upload {
    position: relative;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f8fafc;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.wi-image-upload.small {
    min-height: 100px;
}

.wi-image-upload:hover {
    border-color: #6366f1;
    background: #f1f5f9;
}

.wi-preview-img {
    max-width: 100%;
    max-height: 140px;
    border-radius: 4px;
}

.wi-remove-img {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef4444;
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    line-height: 1;
    transition: all 0.3s;
}

.wi-remove-img:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Color Picker */
.wi-color-picker {
    width: 100% !important;
}

.wp-picker-container {
    width: 100%;
}

/* Save Panel */
.wi-save-panel {
    position: sticky;
    bottom: 20px;
}

.wi-save-panel .button-primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(16,185,129,0.3);
    transition: all 0.3s;
}

.wi-save-panel .button-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16,185,129,0.4);
}

/* Preview */
.wi-editor-preview {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
    position: sticky;
    top: 32px;
    height: calc(100vh - 130px);
    display: flex;
    flex-direction: column;
}

.wi-preview-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wi-preview-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.wi-preview-devices {
    display: flex;
    gap: 8px;
}

.wi-device-btn {
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.2);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}

.wi-device-btn:hover,
.wi-device-btn.active {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.4);
}

.wi-device-btn .dashicons {
    font-size: 1.2rem;
    width: 1.2rem;
    height: 1.2rem;
}

.wi-preview-container {
    flex: 1;
    padding: 20px;
    background: #f1f5f9;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wi-preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.wi-preview-container.tablet .wi-preview-iframe {
    max-width: 768px;
}

.wi-preview-container.mobile .wi-preview-iframe {
    max-width: 375px;
}

@media (max-width: 1400px) {
    .wi-editor-layout {
        grid-template-columns: 320px 1fr;
    }
}

@media (max-width: 1200px) {
    .wi-editor-layout {
        grid-template-columns: 1fr;
    }
    
    .wi-editor-preview {
        position: static;
        height: 600px;
    }
}
</style>

<!-- Modal Anteprima Preview -->
<div id="preview-modal" class="wi-preview-modal" style="display: none;">
    <div class="wi-modal-overlay" onclick="closePreviewModal()"></div>
    <div class="wi-modal-content">
        <div class="wi-modal-header">
            <h2>Anteprima Template</h2>
            <div class="wi-modal-devices">
                <button type="button" class="wi-device-btn active" onclick="changePreviewDevice('desktop')">
                    <span class="dashicons dashicons-desktop"></span>
                </button>
                <button type="button" class="wi-device-btn" onclick="changePreviewDevice('tablet')">
                    <span class="dashicons dashicons-tablet"></span>
                </button>
                <button type="button" class="wi-device-btn" onclick="changePreviewDevice('mobile')">
                    <span class="dashicons dashicons-smartphone"></span>
                </button>
            </div>
            <button type="button" class="wi-close-modal" onclick="closePreviewModal()">×</button>
        </div>
        <div class="wi-modal-body">
            <div id="modal-preview-container" class="wi-modal-preview-container desktop">
                <!-- Contenuto anteprima -->
            </div>
        </div>
    </div>
</div>


<script>
// ========================================
// WEDDING INVITES - TEMPLATE MANAGER JS
// ========================================

// Carica template esistente (se in modalità edit)
<?php if ($edit_template && $edit_template->html_structure && $edit_template->css_styles) : ?>
var existingTemplateHtml = <?php echo json_encode($edit_template->html_structure); ?>;
var existingTemplateCss = <?php echo json_encode($edit_template->css_styles); ?>;
console.log('📦 Template esistente caricato - HTML:', existingTemplateHtml.length, 'chars, CSS:', existingTemplateCss.length, 'chars');
<?php else : ?>
var existingTemplateHtml = '';
var existingTemplateCss = '';
<?php endif; ?>

// Variabili globali
var currentImageField = '';

// ===== FUNZIONI UPLOAD IMMAGINI =====

function uploadImage(field) {
    console.log('📤 Upload image per campo:', field);
    
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        alert('ERRORE: Media Library non disponibile. Ricarica la pagina.');
        return;
    }
    
    currentImageField = field;
    
    var mediaUploader = wp.media({
        title: 'Seleziona Immagine',
        button: { text: 'Usa questa immagine' },
        multiple: false,
        library: { type: 'image' }
    });
    
    mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        console.log('✅ Immagine selezionata:', attachment.url);

        // Aggiorna hidden field
        document.getElementById(field + '_value').value = attachment.url;

        // Aggiorna preview
        var container = document.getElementById(field + '_container');
        if (container) {
            container.innerHTML = '<img src="' + attachment.url + '" class="wi-preview-img">' +
                                '<button type="button" class="wi-remove-img" onclick="removeImage(\'' + field + '\')">×</button>';
        }

        // Segna modifiche visuali
        visualSettingsChanged = true;

        // Aggiorna anteprima live
        updatePreview();
    });
    
    mediaUploader.open();
}

function removeImage(field) {
    console.log('🗑️ Rimuovi immagine:', field);

    // Pulisci hidden field
    document.getElementById(field + '_value').value = '';

    // Pulisci preview
    var container = document.getElementById(field + '_container');
    if (container) {
        container.innerHTML = '';
    }

    // Segna modifiche visuali
    visualSettingsChanged = true;

    // Aggiorna anteprima
    updatePreview();
}

// ===== FUNZIONI DEVICE SWITCHER =====

function changeDevice(device) {
    console.log('📱 Cambio device:', device);
    
    var container = document.getElementById('preview_container');
    if (container) {
        container.className = 'wi-preview-container ' + device;
    }
    
    // Aggiorna pulsanti
    var buttons = document.querySelectorAll('.wi-device-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    var activeBtn = document.querySelector('.wi-device-btn[data-device="' + device + '"]');
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

// ===== FUNZIONE GENERA CODICE TEMPLATE =====

function generateTemplateCode() {
    console.log('📝 ========== GENERAZIONE CODICE TEMPLATE ==========');
    
    try {
        // Ottieni valori form
        var fontTitle = document.getElementById('font_title').value;
        var fontBody = document.getElementById('font_body').value;
        var colorPrimary = document.getElementById('color_primary').value;
        var colorText = document.getElementById('color_text').value;
        var colorBg = document.getElementById('color_background').value;
        var headerImage = document.getElementById('header_image_value').value;
        var decorTop = document.getElementById('decoration_top_value').value;
        var decorBottom = document.getElementById('decoration_bottom_value').value;
        var bgImage = document.getElementById('background_image_value').value;
        var bgOpacity = document.getElementById('background_opacity').value;
        var animated = document.getElementById('countdown_animated').checked;
        
        console.log('📋 Valori letti:', {
            fontTitle: fontTitle,
            fontBody: fontBody,
            colorPrimary: colorPrimary,
            headerImage: headerImage ? 'SI' : 'NO',
            decorTop: decorTop ? 'SI' : 'NO',
            decorBottom: decorBottom ? 'SI' : 'NO',
            bgImage: bgImage ? 'SI' : 'NO',
            animated: animated
        });
        
        // ===== GENERA HTML COMPLETO - SCHEMA DEFINITIVO =====
        var html = '<div class="wi-invite wi-template-custom" style="background-color: {{background_color}};">';

        // 1. Immagine di sfondo: gestita via CSS (background-image)

        // 2. Immagine in cima (Header Image - se caricata)
        html += '{{#if header_image}}';
        html += '<div class="wi-header-image">';
        html += '<img src="{{header_image}}" alt="Header">';
        html += '</div>';
        html += '{{/if}}';

        // 3. Titolo
        html += '<header class="wi-header">';
        html += '<h1 class="wi-title">{{title}}</h1>';
        html += '<div class="wi-divider"></div>';
        html += '</header>';

        // 4. Decorazione sopra countdown (se caricata)
        html += '{{#if decoration_top}}';
        html += '<div class="wi-decoration-top">';
        html += '<img src="{{decoration_top}}" alt="Decorazione">';
        html += '</div>';
        html += '{{/if}}';

        // 5. Countdown dinamico
        html += '<section class="wi-countdown-section">';
        html += '<h2 class="wi-countdown-label">Mancano ancora...</h2>';
        html += '<div id="countdown" class="wi-countdown"></div>';
        html += '</section>';

        // 6. Decorazione sotto countdown (se caricata) - poco spazio
        html += '{{#if decoration_bottom}}';
        html += '<div class="wi-decoration-bottom">';
        html += '<img src="{{decoration_bottom}}" alt="Decorazione">';
        html += '</div>';
        html += '{{/if}}';

        // 7. Riquadro con messaggio
        html += '<section class="wi-message-section">';
        html += '<div class="wi-message-box">';
        html += '<div class="wi-message-content">';
        html += '{{message}}';
        html += '</div>';
        html += '</div>';
        html += '</section>';

        // 8. Immagine caricata dall'utente (se inserita nel form)
        html += '{{#if user_image}}';
        html += '<section class="wi-user-image-section">';
        html += '<div class="wi-image-frame">';
        html += '<img src="{{user_image}}" alt="Foto evento" class="wi-user-image">';
        html += '</div>';
        html += '</section>';
        html += '{{/if}}';

        // 9. Informazioni evento (luogo, data, ora) - INSIEME
        html += '<section class="wi-event-details-section">';
        html += '<div class="wi-event-details-box">';
        html += '<div class="wi-detail-row">';
        html += '<div class="wi-detail-icon">📍</div>';
        html += '<div class="wi-detail-text">';
        html += '<strong>Luogo</strong>';
        html += '<p>{{event_location}}</p>';
        html += '</div>';
        html += '</div>';
        html += '<div class="wi-detail-row">';
        html += '<div class="wi-detail-icon">📅</div>';
        html += '<div class="wi-detail-text">';
        html += '<strong>Data</strong>';
        html += '<p>{{event_date}}</p>';
        html += '</div>';
        html += '</div>';
        html += '<div class="wi-detail-row">';
        html += '<div class="wi-detail-icon">🕐</div>';
        html += '<div class="wi-detail-text">';
        html += '<strong>Orario</strong>';
        html += '<p>{{event_time}}</p>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</section>';

        // 10. Pulsante "Aggiungi al Calendario"
        html += '<section class="wi-calendar-section">';
        html += '<button class="wi-btn wi-btn-primary" onclick="addToCalendar()">';
        html += '📅 Aggiungi al Calendario';
        html += '</button>';
        html += '</section>';

        // 11. Mappa OpenStreetMap visibile (senza API)
        html += '<section class="wi-map-section">';
        html += '<div id="invite-map-{{event_date}}" class="wi-map" data-address="{{event_address}}"></div>';
        html += '</section>';

        // 12. Pulsante Google Maps indicazioni
        html += '<section class="wi-google-maps-section">';
        html += '<a href="https://www.google.com/maps/search/?api=1&query={{event_address}}" target="_blank" class="wi-btn wi-btn-secondary">';
        html += '🗺️ Apri in Google Maps';
        html += '</a>';
        html += '</section>';

        // 13. Messaggio finale (apribile con un tap)
        html += '{{#if final_message}}';
        html += '<section class="wi-final-message-section">';
        html += '<button class="wi-final-message-toggle" onclick="toggleFinalMessage()">';
        html += '<span class="wi-toggle-icon">▼</span> {{final_message_button_text}}';
        html += '</button>';
        html += '<div class="wi-final-message-content" style="display:none;">';
        html += '<div class="wi-final-message">';
        html += '{{final_message}}';
        html += '</div>';
        html += '</div>';
        html += '</section>';
        html += '{{/if}}';

        // 14. Logo sito in basso (caricato dal backend)
        html += '{{#if footer_logo}}';
        html += '<footer class="wi-footer">';
        html += '<img src="{{footer_logo}}" alt="Logo" class="wi-footer-logo">';
        html += '</footer>';
        html += '{{/if}}';

        html += '</div>';
        
        // ===== GENERA CSS COMPLETO =====
        var css = '';

        // Import Google Fonts
        css += '@import url("https://fonts.googleapis.com/css2?family=' + fontTitle.replace(/ /g, '+') + ':wght@400;600;700&family=' + fontBody.replace(/ /g, '+') + ':wght@400;500;600&display=swap");';

        // Template Container - CON WRAPPER .wi-invite-content
        css += '.wi-invite-content .wi-invite.wi-template-custom { ';
        css += 'max-width: 800px; ';
        css += 'margin: 0 auto; ';
        css += 'padding: 40px 20px; ';
        css += 'font-family: "' + fontBody + '", sans-serif; ';
        css += 'color: ' + colorText + '; ';
        css += 'position: relative; ';

        // Aggiungi background image se presente
        if (bgImage) {
            css += 'background-image: url(' + bgImage + '); ';
            css += 'background-size: cover; ';
            css += 'background-position: center; ';
            css += 'background-attachment: fixed; ';
            css += 'border-radius: 12px; ';
        }

        css += '}';

        // Background overlay (per opacità sfondo)
        if (bgImage) {
            css += '.wi-invite-content .wi-invite.wi-template-custom::before { ';
            css += 'content: ""; ';
            css += 'position: absolute; ';
            css += 'top: 0; ';
            css += 'left: 0; ';
            css += 'right: 0; ';
            css += 'bottom: 0; ';
            css += 'background-color: ' + colorBg + '; ';
            css += 'opacity: ' + bgOpacity + '; ';
            css += 'z-index: 0; ';
            css += 'border-radius: 12px; ';
            css += 'pointer-events: none; ';
            css += '}';

            // Assicura che il contenuto sia sopra l'overlay
            css += '.wi-invite-content .wi-invite.wi-template-custom > * { ';
            css += 'position: relative; ';
            css += 'z-index: 1; ';
            css += '}';
        }

        // Header Image
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-header-image img { ';
        css += 'width: 100%; ';
        css += 'height: 400px; ';
        css += 'object-fit: cover; ';
        css += 'border-radius: 10px; ';
        css += 'margin-bottom: 40px; ';
        css += '}';

        // Decorazione Top
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-top { ';
        css += 'text-align: center; ';
        css += 'margin: 30px 0 20px; ';
        css += '}';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-top img { ';
        css += 'max-width: 300px; ';
        css += 'height: auto; ';
        css += '}';

        // Decorazione Bottom - POCO SPAZIO dal countdown
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-bottom { ';
        css += 'text-align: center; ';
        css += 'margin: 10px 0 30px; ';
        css += '}';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-bottom img { ';
        css += 'max-width: 300px; ';
        css += 'height: auto; ';
        css += '}';

        // Header
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-header { ';
        css += 'text-align: center; ';
        css += 'padding: 40px 0; ';
        css += '}';

        // Titolo
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-title { ';
        css += 'font-family: "' + fontTitle + '", serif; ';
        css += 'font-size: 48px; ';
        css += 'font-weight: 700; ';
        css += 'color: ' + colorPrimary + '; ';
        css += 'margin: 0 0 20px; ';
        css += 'line-height: 1.2; ';
        css += '}';

        // Divider
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-divider { ';
        css += 'width: 100px; ';
        css += 'height: 2px; ';
        css += 'background: linear-gradient(to right, transparent, ' + colorPrimary + ', transparent); ';
        css += 'margin: 20px auto; ';
        css += '}';

        // Countdown Section
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-countdown-section { ';
        css += 'background: #fff; ';
        css += 'padding: 40px 20px; ';
        css += 'border-radius: 15px; ';
        css += 'text-align: center; ';
        css += 'margin: 40px 0; ';
        css += 'box-shadow: 0 5px 20px rgba(0,0,0,0.1); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-countdown-label { ';
        css += 'font-size: 24px; ';
        css += 'color: ' + colorPrimary + '; ';
        css += 'margin-bottom: 30px; ';
        css += 'font-weight: 400; ';
        css += '}';

        // Countdown
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-countdown { ';
        css += 'display: flex; ';
        css += 'justify-content: center; ';
        css += 'gap: 20px; ';
        css += 'flex-wrap: wrap; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-item { ';
        css += 'background: #fdfbfb; ';
        css += 'padding: 20px; ';
        css += 'border-radius: 10px; ';
        css += 'width: 120px; ';
        css += 'min-width: 120px; ';
        css += 'border: 2px solid ' + colorPrimary + '; ';
        css += 'text-align: center; ';
        css += 'flex-shrink: 0; ';
        css += '}';

        // ANIMAZIONE COUNTDOWN RIMOSSA

        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-value { ';
        css += 'font-size: 48px; ';
        css += 'font-weight: 700; ';
        css += 'color: ' + colorPrimary + '; ';
        css += 'line-height: 1; ';
        css += 'margin-bottom: 10px; ';
        css += 'width: 100%; ';
        css += 'display: block; ';
        css += 'font-variant-numeric: tabular-nums; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-label { ';
        css += 'font-size: 16px; ';
        css += 'color: ' + colorText + '; ';
        css += 'text-transform: uppercase; ';
        css += 'letter-spacing: 1px; ';
        css += 'display: block; ';
        css += 'width: 100%; ';
        css += '}';

        // Message Section - Riquadro con messaggio
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-section { ';
        css += 'margin: 40px 0; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-box { ';
        css += 'background: #f9f9f9; ';
        css += 'padding: 40px; ';
        css += 'border-radius: 15px; ';
        css += 'border-left: 5px solid ' + colorPrimary + '; ';
        css += 'box-shadow: 0 4px 15px rgba(0,0,0,0.08); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-content { ';
        css += 'font-size: 18px; ';
        css += 'line-height: 1.8; ';
        css += 'color: ' + colorText + '; ';
        css += 'text-align: center; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-content p { ';
        css += 'margin: 15px 0; ';
        css += '}';

        // User Image Section - Immagine caricata dall'utente
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-user-image-section { ';
        css += 'margin: 50px 0; ';
        css += 'text-align: center; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-image-frame { ';
        css += 'display: inline-block; ';
        css += 'padding: 15px; ';
        css += 'background: #fff; ';
        css += 'border-radius: 15px; ';
        css += 'box-shadow: 0 10px 40px rgba(0,0,0,0.1); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-user-image { ';
        css += 'width: 100%; ';
        css += 'max-width: 600px; ';
        css += 'height: auto; ';
        css += 'border-radius: 8px; ';
        css += 'display: block; ';
        css += '}';

        // Event Details Section - Informazioni evento INSIEME
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-event-details-section { ';
        css += 'margin: 40px 0; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-event-details-box { ';
        css += 'background: #fff; ';
        css += 'padding: 40px; ';
        css += 'border-radius: 15px; ';
        css += 'box-shadow: 0 5px 20px rgba(0,0,0,0.08); ';
        css += 'border-top: 4px solid ' + colorPrimary + '; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-row { ';
        css += 'display: flex; ';
        css += 'align-items: center; ';
        css += 'gap: 20px; ';
        css += 'padding: 20px 0; ';
        css += 'border-bottom: 1px solid #f0f0f0; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-row:last-child { ';
        css += 'border-bottom: none; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-icon { ';
        css += 'font-size: 32px; ';
        css += 'width: 50px; ';
        css += 'text-align: center; ';
        css += 'flex-shrink: 0; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-text { ';
        css += 'flex: 1; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-text strong { ';
        css += 'display: block; ';
        css += 'font-size: 13px; ';
        css += 'text-transform: uppercase; ';
        css += 'letter-spacing: 1.5px; ';
        css += 'color: ' + colorPrimary + '; ';
        css += 'margin-bottom: 6px; ';
        css += 'font-weight: 700; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-text p { ';
        css += 'font-size: 22px; ';
        css += 'color: ' + colorText + '; ';
        css += 'margin: 0; ';
        css += 'font-weight: 500; ';
        css += 'line-height: 1.4; ';
        css += '}';

        // Map Section
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-map-section { ';
        css += 'margin: 40px 0 20px; ';
        css += 'text-align: center; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-map { ';
        css += 'width: 100%; ';
        css += 'height: 400px; ';
        css += 'border-radius: 15px; ';
        css += 'border: none; ';
        css += 'box-shadow: 0 5px 20px rgba(0,0,0,0.1); ';
        css += '}';

        // Google Maps Button Section
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-google-maps-section { ';
        css += 'margin: 20px 0; ';
        css += 'text-align: center; ';
        css += '}';

        // Calendar Button Section
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-calendar-section { ';
        css += 'margin: 20px 0 40px; ';
        css += 'text-align: center; ';
        css += '}';

        // Button Styles
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn { ';
        css += 'display: inline-block; ';
        css += 'padding: 15px 40px; ';
        css += 'border: none; ';
        css += 'border-radius: 50px; ';
        css += 'font-size: 16px; ';
        css += 'font-weight: 600; ';
        css += 'cursor: pointer; ';
        css += 'transition: all 0.3s ease; ';
        css += 'text-decoration: none; ';
        css += 'box-shadow: 0 4px 12px rgba(0,0,0,0.1); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn:hover { ';
        css += 'transform: translateY(-2px); ';
        css += 'box-shadow: 0 6px 20px rgba(0,0,0,0.15); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn-primary { ';
        css += 'background: ' + colorPrimary + '; ';
        css += 'color: #fff; ';
        css += 'font-size: 18px; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn-secondary { ';
        css += 'background: #64748b; ';
        css += 'color: #fff; ';
        css += 'font-size: 16px; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn-secondary:hover { ';
        css += 'background: #475569; ';
        css += '}';

        // Final Message Section - Collapsabile (apribile con tap)
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-section { ';
        css += 'margin: 40px 0; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-toggle { ';
        css += 'width: 100%; ';
        css += 'background: linear-gradient(135deg, ' + colorPrimary + ' 0%, ' + colorPrimary + 'dd 100%); ';
        css += 'color: #fff; ';
        css += 'border: none; ';
        css += 'padding: 18px 30px; ';
        css += 'border-radius: 12px; ';
        css += 'font-size: 18px; ';
        css += 'font-weight: 600; ';
        css += 'cursor: pointer; ';
        css += 'transition: all 0.3s ease; ';
        css += 'display: flex; ';
        css += 'align-items: center; ';
        css += 'justify-content: center; ';
        css += 'gap: 10px; ';
        css += 'box-shadow: 0 4px 15px rgba(0,0,0,0.1); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-toggle:hover { ';
        css += 'transform: translateY(-2px); ';
        css += 'box-shadow: 0 6px 20px rgba(0,0,0,0.15); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-toggle-icon { ';
        css += 'transition: transform 0.3s ease; ';
        css += 'display: inline-block; ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-toggle.active .wi-toggle-icon { ';
        css += 'transform: rotate(180deg); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-content { ';
        css += 'margin-top: 20px; ';
        css += 'background: linear-gradient(to right, #fdfbfb, #f9f9f9, #fdfbfb); ';
        css += 'padding: 30px; ';
        css += 'border-radius: 12px; ';
        css += 'border: 2px dashed ' + colorPrimary + '; ';
        css += 'animation: slideDown 0.3s ease-out; ';
        css += '}';

        css += '@keyframes slideDown { ';
        css += 'from { opacity: 0; transform: translateY(-10px); } ';
        css += 'to { opacity: 1; transform: translateY(0); } ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message { ';
        css += 'font-size: 18px; ';
        css += 'color: ' + colorText + '; ';
        css += 'line-height: 1.8; ';
        css += 'font-weight: 500; ';
        css += 'text-align: center; ';
        css += '}';

        // Footer
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-footer { ';
        css += 'text-align: center; ';
        css += 'padding: 40px 0 20px; ';
        css += 'margin-top: 60px; ';
        css += 'border-top: 1px solid rgba(0,0,0,0.1); ';
        css += '}';

        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-footer-logo { ';
        css += 'max-width: 150px; ';
        css += 'height: auto; ';
        css += 'opacity: 0.7; ';
        css += '}';

        // Responsive
        css += '@media (max-width: 768px) { ';
        css += '.wi-invite-content .wi-invite.wi-template-custom { padding: 20px 15px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-title { font-size: 32px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-header-image img { height: 250px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-top img, ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-decoration-bottom img { max-width: 200px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-countdown { ';
        css += 'display: grid; ';
        css += 'grid-template-columns: 1fr 1fr; ';
        css += 'gap: 12px; ';
        css += 'max-width: 300px; ';
        css += 'margin: 0 auto; ';
        css += '} ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-item { ';
        css += 'width: 100%; ';
        css += 'min-width: auto; ';
        css += 'padding: 15px 10px; ';
        css += '} ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-value { font-size: 28px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .countdown-label { font-size: 11px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-box { padding: 25px 20px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-message-content { font-size: 16px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-event-details-box { padding: 25px 20px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-row { ';
        css += 'flex-direction: column; ';
        css += 'text-align: center; ';
        css += 'gap: 10px; ';
        css += 'padding: 15px 0; ';
        css += '} ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-detail-text p { font-size: 18px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-map { height: 300px; } ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-btn { ';
        css += 'padding: 12px 24px; ';
        css += 'font-size: 14px; ';
        css += 'width: 100%; ';
        css += 'max-width: 300px; ';
        css += '} ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-toggle { ';
        css += 'font-size: 16px; ';
        css += 'padding: 15px 20px; ';
        css += '} ';
        css += '.wi-invite-content .wi-invite.wi-template-custom .wi-final-message-content { padding: 20px; } ';
        css += '}';
        
        // Salva nei campi hidden
        document.getElementById('template_html').value = html;
        document.getElementById('template_css').value = css;
        
        console.log('✅ ========== CODICE GENERATO ==========');
        console.log('📝 HTML length:', html.length);
        console.log('🎨 CSS length:', css.length);
        console.log('📊 Placeholder trovati:', (html.match(/\{\{/g) || []).length);
        console.log('🔍 CSS contiene .wi-invite-content?', css.includes('.wi-invite-content') ? 'SI' : 'NO');
        console.log('🔍 CSS contiene !important?', css.includes('!important') ? 'SI' : 'NO');
        console.log('📦 Campo #template_html value length:', document.getElementById('template_html').value.length);
        console.log('📦 Campo #template_css value length:', document.getElementById('template_css').value.length);
        console.log('========================================');
        
    } catch(error) {
        console.error('❌ Errore generazione codice:', error);
    }
}

// ===== FUNZIONE UPDATE PREVIEW =====

function updatePreview() {
    console.log('🔄 Aggiornamento anteprima...');

    try {
        var iframe = document.getElementById('preview_iframe');
        if (!iframe) {
            console.error('❌ Iframe non trovato');
            return;
        }

        // Ottieni valori
        var fontTitle = document.getElementById('font_title').value;
        var fontBody = document.getElementById('font_body').value;
        var colorPrimary = document.getElementById('color_primary').value;
        var colorText = document.getElementById('color_text').value;
        var colorBg = document.getElementById('color_background').value;
        var headerImage = document.getElementById('header_image_value').value;
        var decorTop = document.getElementById('decoration_top_value').value;
        var decorBottom = document.getElementById('decoration_bottom_value').value;
        var bgImage = document.getElementById('background_image_value').value;
        var bgOpacity = document.getElementById('background_opacity').value;
        var footerLogo = document.getElementById('footer_logo_value').value;
        var animated = document.getElementById('countdown_animated').checked;

        // Controlla se esiste HTML dal database
        var existingHtml = $('#template_html').val();
        var existingCss = $('#template_css').val();

        if (existingHtml && existingCss) {
            // USA IL TEMPLATE REALE DAL DATABASE
            console.log('📦 Uso template reale dal database');

            // Sostituisci placeholder con dati di esempio
            var previewHtml = existingHtml;
            previewHtml = previewHtml.replace(/\{\{title\}\}/g, 'Il Nostro Matrimonio');
            previewHtml = previewHtml.replace(/\{\{message\}\}/g, 'Con grande gioia vi invitiamo a condividere con noi questo momento speciale.');
            previewHtml = previewHtml.replace(/\{\{final_message\}\}/g, 'Non vediamo l\'ora di festeggiare insieme a voi!');
            previewHtml = previewHtml.replace(/\{\{event_date\}\}/g, '25 Dicembre 2025');
            previewHtml = previewHtml.replace(/\{\{event_time\}\}/g, '15:30');
            previewHtml = previewHtml.replace(/\{\{event_location\}\}/g, 'Villa Bellini, Milano');
            previewHtml = previewHtml.replace(/\{\{event_address\}\}/g, 'Via Roma 123, Milano');
            previewHtml = previewHtml.replace(/\{\{background_color\}\}/g, colorBg);
            previewHtml = previewHtml.replace(/\{\{header_image\}\}/g, headerImage || '');
            previewHtml = previewHtml.replace(/\{\{decoration_top\}\}/g, decorTop || '');
            previewHtml = previewHtml.replace(/\{\{decoration_bottom\}\}/g, decorBottom || '');
            previewHtml = previewHtml.replace(/\{\{user_image\}\}/g, 'https://via.placeholder.com/600x400/667eea/ffffff?text=Immagine+Utente');
            previewHtml = previewHtml.replace(/\{\{footer_logo\}\}/g, footerLogo || 'https://via.placeholder.com/150x50/94a3b8/ffffff?text=Logo');

            // Gestisci condizionali Handlebars
            previewHtml = previewHtml.replace(/\{\{#if header_image\}\}/g, headerImage ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if decoration_top\}\}/g, decorTop ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if decoration_bottom\}\}/g, decorBottom ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if user_image\}\}/g, '');
            previewHtml = previewHtml.replace(/\{\{#if final_message\}\}/g, '');
            previewHtml = previewHtml.replace(/\{\{#if footer_logo\}\}/g, footerLogo ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{\/if\}\}/g, function(match, offset, string) {
                // Trova se il blocco precedente inizia con <!--
                var lastIfPos = string.lastIndexOf('{{#if', offset);
                var lastCommentPos = string.lastIndexOf('<!--', offset);
                return (lastCommentPos > lastIfPos && lastCommentPos < offset) ? '-->' : '';
            });

            // Genera HTML completo con CSS dal database
            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            html += '<style>' + existingCss + '</style>';
            html += '</head><body>';
            html += '<div class="wi-invite-content">';
            html += previewHtml;
            html += '</div>';

            // Aggiungi JavaScript per countdown
            html += '<script>';
            html += 'function initCountdown() {';
            html += '  var countdown = document.getElementById("countdown");';
            html += '  if (countdown) {';
            html += '    countdown.innerHTML = \'<div class="countdown-container">\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">30</div><div class="countdown-label">Giorni</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">12</div><div class="countdown-label">Ore</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">45</div><div class="countdown-label">Minuti</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">20</div><div class="countdown-label">Secondi</div></div>\' +';
            html += '      \'</div>\';';
            html += '  }';
            html += '}';
            html += 'window.addEventListener("DOMContentLoaded", initCountdown);';
            html += '<\/script>';
            html += '</body></html>';

            // Scrivi nell'iframe
            var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(html);
            iframeDoc.close();

            console.log('✅ Anteprima dal database caricata');
        } else {
            // GENERA ANTEPRIMA CON HTML/CSS REALE (identico all'invito pubblicato)
            console.log('🆕 Genero anteprima con HTML/CSS reale');

            // Prima genera il codice HTML/CSS reale
            generateTemplateCode();

            // Prendi HTML e CSS generati
            var realHtml = document.getElementById('template_html').value;
            var realCss = document.getElementById('template_css').value;

            // Sostituisci placeholder con dati di esempio
            var previewHtml = realHtml;
            previewHtml = previewHtml.replace(/\{\{title\}\}/g, 'Il Nostro Matrimonio');
            previewHtml = previewHtml.replace(/\{\{message\}\}/g, 'Con grande gioia vi invitiamo a condividere con noi questo momento speciale. Sarà un giorno indimenticabile!');
            previewHtml = previewHtml.replace(/\{\{final_message\}\}/g, 'Vi aspettiamo per festeggiare insieme questo giorno speciale. Confermate la vostra presenza!');
            previewHtml = previewHtml.replace(/\{\{event_date\}\}/g, '25/12/2025');
            previewHtml = previewHtml.replace(/\{\{event_time\}\}/g, '15:30');
            previewHtml = previewHtml.replace(/\{\{event_location\}\}/g, 'Villa Bellini, Milano');
            previewHtml = previewHtml.replace(/\{\{event_address\}\}/g, 'Via Roma 123, Milano, Italia');
            previewHtml = previewHtml.replace(/\{\{background_color\}\}/g, colorBg);
            previewHtml = previewHtml.replace(/\{\{header_image\}\}/g, headerImage || '');
            previewHtml = previewHtml.replace(/\{\{decoration_top\}\}/g, decorTop || '');
            previewHtml = previewHtml.replace(/\{\{decoration_bottom\}\}/g, decorBottom || '');
            previewHtml = previewHtml.replace(/\{\{user_image\}\}/g, 'https://via.placeholder.com/600x400/f0f0f0/666?text=Foto+Evento');
            previewHtml = previewHtml.replace(/\{\{footer_logo\}\}/g, 'https://via.placeholder.com/150x50/ddd/666?text=Logo');

            // Gestisci condizionali Handlebars
            previewHtml = previewHtml.replace(/\{\{#if header_image\}\}/g, headerImage ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if decoration_top\}\}/g, decorTop ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if decoration_bottom\}\}/g, decorBottom ? '' : '<!--');
            previewHtml = previewHtml.replace(/\{\{#if user_image\}\}/g, ''); // Mostra sempre nell'anteprima
            previewHtml = previewHtml.replace(/\{\{#if final_message\}\}/g, ''); // Mostra sempre
            previewHtml = previewHtml.replace(/\{\{#if footer_logo\}\}/g, ''); // Mostra sempre
            previewHtml = previewHtml.replace(/\{\{\/if\}\}/g, function(match, offset, string) {
                var lastIfPos = string.lastIndexOf('{{#if', offset);
                var lastCommentPos = string.lastIndexOf('<!--', offset);
                return (lastCommentPos > lastIfPos && lastCommentPos < offset) ? '-->' : '';
            });

            // Genera HTML completo identico all'invito pubblicato
            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            html += '<style>' + realCss + '</style>';
            html += '</head><body>';
            html += '<div class="wi-invite-content">';
            html += previewHtml;
            html += '</div>';

            // Aggiungi JavaScript per countdown (statico per anteprima)
            html += '<script>';
            html += 'function initCountdown() {';
            html += '  var countdown = document.getElementById("countdown");';
            html += '  if (countdown) {';
            html += '    countdown.innerHTML = \'<div class="countdown-container">\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">30</div><div class="countdown-label">Giorni</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">12</div><div class="countdown-label">Ore</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">45</div><div class="countdown-label">Minuti</div></div>\' +';
            html += '      \'<div class="countdown-item"><div class="countdown-value">20</div><div class="countdown-label">Secondi</div></div>\' +';
            html += '      \'</div>\';';
            html += '  }';
            html += '}';
            html += 'window.addEventListener("DOMContentLoaded", initCountdown);';
            html += '<\/script>';
            html += '</body></html>';

            // Scrivi nell'iframe
            var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(html);
            iframeDoc.close();

            console.log('✅ Anteprima REALE caricata (identica all\'invito pubblicato)');
        }

    } catch(error) {
        console.error('❌ Errore updatePreview:', error);
    }
}

// ===== FUNZIONI MODAL PREVIEW =====

function openPreview() {
    console.log('🔍 Apertura modal anteprima...');
    
    updatePreview();
    
    var iframe = document.getElementById('preview_iframe');
    if (!iframe) {
        alert('Errore: impossibile caricare anteprima.');
        return;
    }
    
    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    var html = iframeDoc.documentElement.outerHTML;
    
    var modal = document.getElementById('preview-modal');
    if (!modal) {
        alert('Errore: modal non trovato.');
        return;
    }
    
    modal.style.display = 'flex';
    
    var container = document.getElementById('modal-preview-container');
    if (container) {
        container.innerHTML = '<iframe style="width: 100%; height: 100%; min-height: 600px; border: none;"></iframe>';
        var modalIframe = container.querySelector('iframe');
        if (modalIframe) {
            var modalIframeDoc = modalIframe.contentDocument || modalIframe.contentWindow.document;
            modalIframeDoc.open();
            modalIframeDoc.write(html);
            modalIframeDoc.close();
        }
    }
}

function closePreviewModal() {
    var modal = document.getElementById('preview-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function changePreviewDevice(device) {
    var container = document.getElementById('modal-preview-container');
    if (container) {
        container.className = 'wi-modal-preview-container ' + device;
    }
    
    var buttons = document.querySelectorAll('.wi-modal-devices .wi-device-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    var activeBtn = document.querySelector('.wi-modal-devices .wi-device-btn[onclick*="' + device + '"]');
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

// ===== DOCUMENT READY =====

// Flag per tracciare modifiche visuali
var visualSettingsChanged = false;

jQuery(document).ready(function($) {
    console.log('✅ Template Manager caricato');

    // CRITICO: Carica HTML/CSS esistente nei campi hidden PRIMA di tutto
    if (existingTemplateHtml && existingTemplateCss) {
        $('#template_html').val(existingTemplateHtml);
        $('#template_css').val(existingTemplateCss);
        console.log('✅ HTML/CSS esistente caricato nei campi hidden');
        console.log('📏 HTML:', $('#template_html').val().length, 'chars');
        console.log('📏 CSS:', $('#template_css').val().length, 'chars');
    }

    // Verifica wp.media
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.warn('⚠️ wp.media non disponibile immediatamente');
        setTimeout(function() {
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                console.error('❌ wp.media ancora non disponibile');
                alert('ATTENZIONE: Media Library non disponibile.\n\nRicarica la pagina.');
            } else {
                console.log('✅ wp.media caricato con ritardo');
            }
        }, 2000);
    } else {
        console.log('✅ wp.media disponibile');
    }

    // Traccia modifiche ai controlli visuali (DOPO il caricamento iniziale)
    $('#font_title, #font_body, #background_opacity, #countdown_animated').on('change', function() {
        console.log('🎨 Impostazione visuale modificata');
        visualSettingsChanged = true;
    });
    
    // Inizializza Color Picker (con delay per evitare trigger durante init)
    var colorPickerInitialized = false;
    $('.wi-color-picker').wpColorPicker({
        change: function() {
            // Ignora eventi durante inizializzazione
            if (!colorPickerInitialized) {
                console.log('⏸️ Colore cambiato durante init - ignoro');
                return;
            }
            console.log('🎨 Colore modificato dall\'utente');
            visualSettingsChanged = true;
            setTimeout(function() {
                updatePreview();
            }, 100);
        }
    });

    // Dopo 1 secondo, considera i color picker inizializzati
    setTimeout(function() {
        colorPickerInitialized = true;
        console.log('✅ Color picker pronti per tracciare modifiche');
    }, 1000);
    
    // Prima anteprima (solo per nuovi template)
    var isEditMode = $('#template_html').val().length > 0;

    if (isEditMode) {
        console.log('📝 Modalità edit - preservo HTML/CSS esistente');
        console.log('📏 HTML length:', $('#template_html').val().length);
        console.log('📏 CSS length:', $('#template_css').val().length);
    } else {
        console.log('🆕 Nuovo template - genero anteprima iniziale');
        setTimeout(function() {
            console.log('🎨 Inizializzazione prima anteprima...');
            updatePreview();
        }, 500);
    }
    
    // ⚠️ CRITICO: Genera codice prima del submit
    $('#visual-template-form').on('submit', function(e) {
        console.log('📤 Form submit...');

        try {
            var isEditMode = $('#template_html').val().length > 0 && !visualSettingsChanged;

            if (isEditMode) {
                console.log('📝 Modalità edit SENZA modifiche visuali - preservo HTML/CSS esistente');
            } else {
                console.log('🎨 Generazione nuovo codice template...');
                generateTemplateCode();
            }

            var htmlVal = $('#template_html').val();
            var cssVal = $('#template_css').val();

            console.log('📝 HTML length:', htmlVal.length);
            console.log('🎨 CSS length:', cssVal.length);
            console.log('🔧 Modifiche visuali:', visualSettingsChanged);

            if (htmlVal.length === 0) {
                alert('ERRORE: HTML template vuoto! Non posso salvare.');
                e.preventDefault();
                return false;
            }

            if (cssVal.length === 0) {
                alert('ERRORE: CSS template vuoto! Non posso salvare.');
                e.preventDefault();
                return false;
            }

            console.log('✅ Procedo con submit');
            return true;

        } catch(error) {
            console.error('❌ Errore salvataggio:', error);
            alert('ERRORE: Impossibile salvare template. Controlla console.');
            e.preventDefault();
            return false;
        }
    });
    
    // Chiudi modal con ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewModal();
        }
    });
});

// ===== FUNZIONE TOGGLE FINAL MESSAGE =====

// Toggle Final Message (collapsabile)
function toggleFinalMessage() {
    var toggle = document.querySelector('.wi-final-message-toggle');
    var content = document.querySelector('.wi-final-message-content');

    if (content) {
        if (content.style.display === 'none' || !content.style.display) {
            content.style.display = 'block';
            if (toggle) toggle.classList.add('active');
        } else {
            content.style.display = 'none';
            if (toggle) toggle.classList.remove('active');
        }
    }
}

// ===== FUNZIONE OPEN PREVIEW =====

// Apri anteprima (genera il template e aggiorna l'iframe)
function openPreview() {
    console.log('👁️ Apertura anteprima...');

    // Prima genera il codice HTML/CSS
    generateTemplateCode();

    // Poi aggiorna l'anteprima
    setTimeout(function() {
        updatePreview();
    }, 100);
}

</script>

<?php