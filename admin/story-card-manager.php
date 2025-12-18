<?php
/**
 * Story Card Templates Manager
 * Pannello admin per gestione Story Card 9:16
 *
 * @package Wedding_Invites_Pro
 */

if (!defined('ABSPATH')) exit;

// Gestione azioni
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

// Salvataggio template
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wi_save_story_template'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'wi_story_template_save')) {
        wp_die(__('Verifica di sicurezza fallita', 'wedding-invites'));
    }

    $template_data = array(
        'name' => sanitize_text_field($_POST['template_name']),
        'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
        'invite_template_id' => !empty($_POST['invite_template_id']) ? intval($_POST['invite_template_id']) : null,
        'background_image_url' => esc_url_raw($_POST['background_image_url']),
        'is_default' => isset($_POST['is_default']) ? 1 : 0,
        'layout_config' => array(
            'title' => array(
                'top' => floatval($_POST['title_top']),
                'left' => floatval($_POST['title_left']),
                'width' => floatval($_POST['title_width']),
                'fontSize' => intval($_POST['title_fontSize']),
                'fontWeight' => intval($_POST['title_fontWeight']),
                'color' => sanitize_hex_color($_POST['title_color']),
                'textAlign' => sanitize_text_field($_POST['title_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['title_fontFamily']),
                'textShadow' => isset($_POST['title_textShadow'])
            ),
            'date' => array(
                'top' => floatval($_POST['date_top']),
                'left' => floatval($_POST['date_left']),
                'width' => floatval($_POST['date_width']),
                'fontSize' => intval($_POST['date_fontSize']),
                'fontWeight' => intval($_POST['date_fontWeight']),
                'color' => sanitize_hex_color($_POST['date_color']),
                'textAlign' => sanitize_text_field($_POST['date_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['date_fontFamily']),
                'textShadow' => isset($_POST['date_textShadow'])
            ),
            'time' => array(
                'top' => floatval($_POST['time_top']),
                'left' => floatval($_POST['time_left']),
                'width' => floatval($_POST['time_width']),
                'fontSize' => intval($_POST['time_fontSize']),
                'fontWeight' => intval($_POST['time_fontWeight']),
                'color' => sanitize_hex_color($_POST['time_color']),
                'textAlign' => sanitize_text_field($_POST['time_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['time_fontFamily']),
                'textShadow' => isset($_POST['time_textShadow'])
            ),
            'location' => array(
                'top' => floatval($_POST['location_top']),
                'left' => floatval($_POST['location_left']),
                'width' => floatval($_POST['location_width']),
                'fontSize' => intval($_POST['location_fontSize']),
                'fontWeight' => intval($_POST['location_fontWeight']),
                'color' => sanitize_hex_color($_POST['location_color']),
                'textAlign' => sanitize_text_field($_POST['location_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['location_fontFamily']),
                'textShadow' => isset($_POST['location_textShadow'])
            ),
            'address' => array(
                'top' => floatval($_POST['address_top']),
                'left' => floatval($_POST['address_left']),
                'width' => floatval($_POST['address_width']),
                'fontSize' => intval($_POST['address_fontSize']),
                'fontWeight' => intval($_POST['address_fontWeight']),
                'color' => sanitize_hex_color($_POST['address_color']),
                'textAlign' => sanitize_text_field($_POST['address_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['address_fontFamily']),
                'textShadow' => isset($_POST['address_textShadow'])
            ),
            'rsvp_deadline' => array(
                'top' => floatval($_POST['rsvp_deadline_top']),
                'left' => floatval($_POST['rsvp_deadline_left']),
                'width' => floatval($_POST['rsvp_deadline_width']),
                'fontSize' => intval($_POST['rsvp_deadline_fontSize']),
                'fontWeight' => intval($_POST['rsvp_deadline_fontWeight']),
                'color' => sanitize_hex_color($_POST['rsvp_deadline_color']),
                'textAlign' => sanitize_text_field($_POST['rsvp_deadline_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['rsvp_deadline_fontFamily']),
                'textShadow' => isset($_POST['rsvp_deadline_textShadow'])
            ),
            'message' => array(
                'top' => floatval($_POST['message_top']),
                'left' => floatval($_POST['message_left']),
                'width' => floatval($_POST['message_width']),
                'fontSize' => intval($_POST['message_fontSize']),
                'fontWeight' => intval($_POST['message_fontWeight']),
                'color' => sanitize_hex_color($_POST['message_color']),
                'textAlign' => sanitize_text_field($_POST['message_textAlign']),
                'fontFamily' => sanitize_text_field($_POST['message_fontFamily']),
                'textShadow' => isset($_POST['message_textShadow'])
            )
        )
    );

    if ($template_id > 0) {
        $template_data['id'] = $template_id;
    }

    $saved_id = WI_Story_Cards::save_template($template_data);

    if ($saved_id) {
        $redirect_url = add_query_arg(array(
            'page' => 'wedding-invites-story-cards',
            'message' => 'saved'
        ), admin_url('admin.php'));
        wp_redirect($redirect_url);
        exit;
    }
}

// Eliminazione template
if ($action === 'delete' && $template_id > 0) {
    if (!wp_verify_nonce($_GET['_wpnonce'], 'wi_delete_story_template_' . $template_id)) {
        wp_die(__('Verifica di sicurezza fallita', 'wedding-invites'));
    }

    WI_Story_Cards::delete_template($template_id);

    $redirect_url = add_query_arg(array(
        'page' => 'wedding-invites-story-cards',
        'message' => 'deleted'
    ), admin_url('admin.php'));
    wp_redirect($redirect_url);
    exit;
}

// Installazione template predefiniti
if ($action === 'install_predefined') {
    if (!wp_verify_nonce($_GET['_wpnonce'], 'wi_install_predefined_templates')) {
        wp_die(__('Verifica di sicurezza fallita', 'wedding-invites'));
    }

    $result = WI_Story_Cards::install_predefined_templates();

    $redirect_url = add_query_arg(array(
        'page' => 'wedding-invites-story-cards',
        'message' => 'predefined_installed'
    ), admin_url('admin.php'));
    wp_redirect($redirect_url);
    exit;
}

?>

<div class="wrap wi-story-cards-manager">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-format-image" style="font-size: 28px; margin-right: 8px;"></span>
        Story Card Templates (9:16)
    </h1>

    <?php if ($action === 'list') : ?>
        <a href="<?php echo admin_url('admin.php?page=wedding-invites-story-cards&action=new'); ?>" class="page-title-action">
            Aggiungi Nuovo Template
        </a>

        <?php
        $install_url = wp_nonce_url(
            admin_url('admin.php?page=wedding-invites-story-cards&action=install_predefined'),
            'wi_install_predefined_templates'
        );
        ?>
        <a href="<?php echo esc_url($install_url); ?>" class="page-title-action" style="background: #10b981; border-color: #10b981;">
            📦 Installa Template Predefiniti
        </a>

        <hr class="wp-header-end">

        <?php if (isset($_GET['message'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    if ($_GET['message'] === 'saved') {
                        echo 'Template salvato con successo!';
                    } elseif ($_GET['message'] === 'deleted') {
                        echo 'Template eliminato con successo!';
                    } elseif ($_GET['message'] === 'predefined_installed') {
                        echo '📦 Template predefiniti installati con successo! (Matrimonio Classico, Compleanno Divertente)';
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="wi-intro-box" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #667eea; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin-top: 0;">📱 Cosa sono le Story Card?</h2>
            <p>Le <strong>Story Card</strong> sono immagini formato <strong>9:16</strong> (formato Instagram Stories) che vengono generate automaticamente per ogni invito.</p>
            <p>Gli utenti possono <strong>scaricarle come PNG</strong> e condividerle facilmente sui social media (Instagram, Facebook, WhatsApp).</p>
            <p><strong>Caratteristiche:</strong></p>
            <ul>
                <li>✅ Formato ottimizzato per Instagram Stories (1080x1920px)</li>
                <li>✅ Download diretto come PNG ad alta risoluzione</li>
                <li>✅ Posizionamento personalizzabile di tutti i campi testo</li>
                <li>✅ Template diversi per ogni categoria di evento</li>
                <li>✅ Supporto Google Fonts e text shadow per leggibilità</li>
            </ul>
        </div>

        <?php
        $templates = WI_Story_Cards::get_all_templates();

        // Ottieni categorie dal database
        global $wpdb;
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $categories = $wpdb->get_results("SELECT * FROM $categories_table WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 150px;">Anteprima</th>
                    <th>Nome Template</th>
                    <th>Categoria</th>
                    <th>Template Invito</th>
                    <th>Default</th>
                    <th style="width: 200px;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($templates)) : ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            <p style="font-size: 16px; color: #666;">
                                <span class="dashicons dashicons-format-image" style="font-size: 48px; opacity: 0.3;"></span><br>
                                Nessun template Story Card creato.<br>
                                <a href="<?php echo admin_url('admin.php?page=wedding-invites-story-cards&action=new'); ?>" class="button button-primary" style="margin-top: 15px;">
                                    Crea il Primo Template
                                </a>
                            </p>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($templates as $template) : ?>
                        <tr>
                            <td>
                                <?php if (!empty($template->background_image_url)) : ?>
                                    <img src="<?php echo esc_url($template->background_image_url); ?>"
                                         alt="<?php echo esc_attr($template->name); ?>"
                                         style="max-width: 100%; height: auto; aspect-ratio: 9/16; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                <?php else : ?>
                                    <div style="width: 100%; aspect-ratio: 9/16; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                        <span class="dashicons dashicons-format-image" style="font-size: 32px;"></span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html($template->name); ?></strong>
                            </td>
                            <td>
                                <?php
                                if ($template->category_id) {
                                    $category = array_filter($categories, function($cat) use ($template) {
                                        return $cat->id == $template->category_id;
                                    });
                                    $category = reset($category);
                                    echo $category ? esc_html($category->name) : '—';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($template->invite_template_id) {
                                    $invite_template = $wpdb->get_row($wpdb->prepare(
                                        "SELECT name FROM {$wpdb->prefix}wi_templates WHERE id = %d",
                                        $template->invite_template_id
                                    ));
                                    echo $invite_template ? '<strong>' . esc_html($invite_template->name) . '</strong> <span style="color: #d63638;">★</span>' : '—';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($template->is_default) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 20px;"></span>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wedding-invites-story-cards&action=edit&template_id=' . $template->id); ?>"
                                   class="button button-small">
                                    Modifica
                                </a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wedding-invites-story-cards&action=delete&template_id=' . $template->id), 'wi_delete_story_template_' . $template->id); ?>"
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('Sei sicuro di voler eliminare questo template?');">
                                    Elimina
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($action === 'new' || $action === 'edit') : ?>

        <?php
        $template = null;
        if ($action === 'edit' && $template_id > 0) {
            $template = WI_Story_Cards::get_template($template_id);
        }

        // Ottieni categorie e template inviti dal database
        global $wpdb;
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $categories = $wpdb->get_results("SELECT * FROM $categories_table WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");

        $invite_templates_table = $wpdb->prefix . 'wi_templates';
        $invite_templates = $wpdb->get_results("SELECT id, name FROM $invite_templates_table WHERE is_active = 1 ORDER BY name ASC");

        // Valori default per nuovo template
        $default_layout = array(
            'title' => array(
                'top' => 30, 'left' => 10, 'width' => 80,
                'fontSize' => 42, 'fontWeight' => 700,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Playfair Display', serif",
                'textShadow' => true
            ),
            'date' => array(
                'top' => 43, 'left' => 10, 'width' => 80,
                'fontSize' => 24, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'time' => array(
                'top' => 49, 'left' => 10, 'width' => 80,
                'fontSize' => 20, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'location' => array(
                'top' => 55, 'left' => 10, 'width' => 80,
                'fontSize' => 22, 'fontWeight' => 500,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'address' => array(
                'top' => 61, 'left' => 10, 'width' => 80,
                'fontSize' => 16, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'rsvp_deadline' => array(
                'top' => 70, 'left' => 10, 'width' => 80,
                'fontSize' => 18, 'fontWeight' => 500,
                'color' => '#ffeb3b', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'message' => array(
                'top' => 78, 'left' => 10, 'width' => 80,
                'fontSize' => 16, 'fontWeight' => 300,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            )
        );

        $layout = $template ? $template->layout_config : $default_layout;
        ?>

        <a href="<?php echo admin_url('admin.php?page=wedding-invites-story-cards'); ?>" class="page-title-action">
            ← Torna alla Lista
        </a>

        <hr class="wp-header-end">

        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 20px; margin-top: 20px;">

            <!-- Form Configurazione -->
            <div style="background: white; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>⚙️ Configurazione Template</h2>

                <form method="post" id="wi-story-template-form">
                    <?php wp_nonce_field('wi_story_template_save'); ?>
                    <input type="hidden" name="wi_save_story_template" value="1">

                    <table class="form-table">
                        <tr>
                            <th><label for="template_name">Nome Template *</label></th>
                            <td>
                                <input type="text"
                                       id="template_name"
                                       name="template_name"
                                       class="regular-text"
                                       value="<?php echo $template ? esc_attr($template->name) : ''; ?>"
                                       required>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="category_id">Categoria Associata</label></th>
                            <td>
                                <select name="category_id" id="category_id" class="regular-text">
                                    <option value="">— Nessuna (per tutte) —</option>
                                    <?php foreach ($categories as $category) : ?>
                                        <option value="<?php echo $category->id; ?>"
                                                <?php echo ($template && $template->category_id == $category->id) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Lascia vuoto per usare come template default per tutte le categorie.</p>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="invite_template_id">Template Invito Associato</label></th>
                            <td>
                                <select name="invite_template_id" id="invite_template_id" class="regular-text">
                                    <option value="">— Nessuno (usa categoria o default) —</option>
                                    <?php foreach ($invite_templates as $inv_template) : ?>
                                        <option value="<?php echo $inv_template->id; ?>"
                                                <?php echo ($template && $template->invite_template_id == $inv_template->id) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($inv_template->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <strong>Sistema a cascata (priorità):</strong><br>
                                    1️⃣ <strong>Template Invito</strong> (priorità massima) - usa questo Story Card per inviti con questo template specifico<br>
                                    2️⃣ <strong>Categoria</strong> - fallback se nessun template invito è associato<br>
                                    3️⃣ <strong>Default</strong> - fallback finale se non trovato nulla
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="background_image_url">Immagine Background 9:16 *</label></th>
                            <td>
                                <input type="text"
                                       id="background_image_url"
                                       name="background_image_url"
                                       class="regular-text"
                                       value="<?php echo $template ? esc_url($template->background_image_url) : ''; ?>"
                                       required>
                                <button type="button" class="button" id="wi-upload-background">Carica Immagine</button>
                                <p class="description">
                                    <strong>Dimensioni consigliate:</strong> 1080x1920px (formato 9:16)<br>
                                    Questa immagine sarà lo sfondo della Story Card. Assicurati di lasciare spazi vuoti per i testi.
                                </p>
                                <div id="wi-background-preview" style="margin-top: 10px;">
                                    <?php if ($template && !empty($template->background_image_url)) : ?>
                                        <img src="<?php echo esc_url($template->background_image_url); ?>"
                                             style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th><label for="is_default">Template Default</label></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="is_default"
                                           id="is_default"
                                           value="1"
                                           <?php echo ($template && $template->is_default) ? 'checked' : ''; ?>>
                                    Usa questo template come default quando non c'è template specifico per categoria
                                </label>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <h2>📐 Posizionamento Testi</h2>
                    <p><strong>Nota:</strong> Le posizioni sono in percentuale rispetto alla dimensione della Story Card.</p>

                    <?php
                    $fields = array(
                        'title' => 'Titolo',
                        'date' => 'Data',
                        'time' => 'Ora',
                        'location' => 'Località',
                        'address' => 'Indirizzo',
                        'rsvp_deadline' => 'Data Termine Conferma',
                        'message' => 'Messaggio'
                    );

                    foreach ($fields as $field_key => $field_label) :
                        $config = isset($layout[$field_key]) ? $layout[$field_key] : array(
                            'top' => 50, 'left' => 10, 'width' => 80,
                            'fontSize' => 18, 'fontWeight' => 400,
                            'color' => '#ffffff', 'textAlign' => 'center',
                            'fontFamily' => "'Lato', sans-serif",
                            'textShadow' => true
                        );
                    ?>

                    <div class="wi-field-config" style="background: #f5f5f5; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                        <h3><?php echo $field_label; ?></h3>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                            <div>
                                <label><strong>Top (%)</strong></label>
                                <input type="number"
                                       name="<?php echo $field_key; ?>_top"
                                       value="<?php echo $config['top']; ?>"
                                       min="0" max="100" step="0.1"
                                       class="small-text"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="top">
                            </div>

                            <div>
                                <label><strong>Left (%)</strong></label>
                                <input type="number"
                                       name="<?php echo $field_key; ?>_left"
                                       value="<?php echo $config['left']; ?>"
                                       min="0" max="100" step="0.1"
                                       class="small-text"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="left">
                            </div>

                            <div>
                                <label><strong>Width (%)</strong></label>
                                <input type="number"
                                       name="<?php echo $field_key; ?>_width"
                                       value="<?php echo $config['width']; ?>"
                                       min="0" max="100" step="0.1"
                                       class="small-text"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="width">
                            </div>

                            <div>
                                <label><strong>Font Size (px)</strong></label>
                                <input type="number"
                                       name="<?php echo $field_key; ?>_fontSize"
                                       value="<?php echo $config['fontSize']; ?>"
                                       min="8" max="100"
                                       class="small-text"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="fontSize">
                            </div>

                            <div>
                                <label><strong>Font Weight</strong></label>
                                <select name="<?php echo $field_key; ?>_fontWeight"
                                        data-field="<?php echo $field_key; ?>"
                                        data-prop="fontWeight">
                                    <?php
                                    $weights = array(300 => 'Light', 400 => 'Normal', 500 => 'Medium', 600 => 'Semi-Bold', 700 => 'Bold');
                                    foreach ($weights as $weight => $label) :
                                    ?>
                                        <option value="<?php echo $weight; ?>"
                                                <?php selected($config['fontWeight'], $weight); ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label><strong>Colore Testo</strong></label>
                                <input type="text"
                                       name="<?php echo $field_key; ?>_color"
                                       value="<?php echo $config['color']; ?>"
                                       class="wi-color-picker"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="color">
                            </div>

                            <div>
                                <label><strong>Text Align</strong></label>
                                <select name="<?php echo $field_key; ?>_textAlign"
                                        data-field="<?php echo $field_key; ?>"
                                        data-prop="textAlign">
                                    <option value="left" <?php selected($config['textAlign'], 'left'); ?>>Left</option>
                                    <option value="center" <?php selected($config['textAlign'], 'center'); ?>>Center</option>
                                    <option value="right" <?php selected($config['textAlign'], 'right'); ?>>Right</option>
                                </select>
                            </div>

                            <div>
                                <label><strong>Font Family</strong></label>
                                <input type="text"
                                       name="<?php echo $field_key; ?>_fontFamily"
                                       value="<?php echo esc_attr($config['fontFamily']); ?>"
                                       class="regular-text"
                                       data-field="<?php echo $field_key; ?>"
                                       data-prop="fontFamily">
                            </div>

                            <div>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo $field_key; ?>_textShadow"
                                           value="1"
                                           <?php checked($config['textShadow']); ?>
                                           data-field="<?php echo $field_key; ?>"
                                           data-prop="textShadow">
                                    <strong>Text Shadow</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php endforeach; ?>

                    <p class="submit">
                        <button type="submit" class="button button-primary button-large">
                            Salva Template
                        </button>
                    </p>
                </form>
            </div>

            <!-- Preview in Tempo Reale -->
            <div style="position: sticky; top: 32px;">
                <div style="background: white; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;">👁️ Anteprima Live</h3>

                    <div id="wi-story-preview"
                         style="position: relative; width: 100%; aspect-ratio: 9/16; background: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">

                        <!-- Background -->
                        <div id="preview-background"
                             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; <?php echo $template && $template->background_image_url ? 'background-image: url(' . esc_url($template->background_image_url) . ');' : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);'; ?>">
                        </div>

                        <!-- Overlay Texts -->
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2;">
                            <?php foreach ($fields as $field_key => $field_label) :
                                $config = isset($layout[$field_key]) ? $layout[$field_key] : array(
                                    'top' => 50, 'left' => 10, 'width' => 80,
                                    'fontSize' => 18, 'fontWeight' => 400,
                                    'color' => '#ffffff', 'textAlign' => 'center',
                                    'fontFamily' => "'Lato', sans-serif",
                                    'textShadow' => true
                                );
                                $sample_text = array(
                                    'title' => 'Matrimonio di Laura & Marco',
                                    'date' => '15 Giugno 2024',
                                    'time' => 'Ore 16:00',
                                    'location' => 'Villa Reale, Como',
                                    'address' => 'Via delle Ville 123, 22100 Como CO',
                                    'rsvp_deadline' => 'Conferma entro: 1 Giugno 2024',
                                    'message' => 'Saremmo felici di condividere con voi questo giorno speciale'
                                );
                            ?>
                                <div class="preview-text-field"
                                     id="preview-<?php echo $field_key; ?>"
                                     style="position: absolute;
                                            top: <?php echo $config['top']; ?>%;
                                            left: <?php echo $config['left']; ?>%;
                                            width: <?php echo $config['width']; ?>%;
                                            font-size: <?php echo $config['fontSize']; ?>px;
                                            font-weight: <?php echo $config['fontWeight']; ?>;
                                            color: <?php echo $config['color']; ?>;
                                            text-align: <?php echo $config['textAlign']; ?>;
                                            font-family: <?php echo $config['fontFamily']; ?>;
                                            <?php echo $config['textShadow'] ? 'text-shadow: 0 2px 4px rgba(0,0,0,0.3);' : ''; ?>
                                            padding: 5px;
                                            line-height: 1.3;">
                                    <?php echo esc_html($sample_text[$field_key]); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <p style="font-size: 12px; color: #666; margin-top: 15px; text-align: center;">
                        L'anteprima si aggiorna automaticamente mentre modifichi i valori
                    </p>
                </div>
            </div>

        </div>

        <style>
            .wi-field-config label {
                display: block;
                margin-bottom: 5px;
                font-size: 13px;
            }

            .wi-field-config input,
            .wi-field-config select {
                width: 100%;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Color picker
            $('.wi-color-picker').wpColorPicker({
                change: updatePreview
            });

            // Media uploader
            $('#wi-upload-background').on('click', function(e) {
                e.preventDefault();

                var mediaUploader = wp.media({
                    title: 'Seleziona Immagine Background 9:16',
                    button: { text: 'Usa questa immagine' },
                    multiple: false,
                    library: { type: 'image' }
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#background_image_url').val(attachment.url);
                    $('#wi-background-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">');
                    $('#preview-background').css('background-image', 'url(' + attachment.url + ')');
                });

                mediaUploader.open();
            });

            // Live preview update
            function updatePreview() {
                $('input[data-field], select[data-field], input[type="checkbox"][data-field]').each(function() {
                    var $input = $(this);
                    var field = $input.data('field');
                    var prop = $input.data('prop');
                    var value = $input.is(':checkbox') ? $input.is(':checked') : $input.val();

                    var $previewField = $('#preview-' + field);

                    if (prop === 'top' || prop === 'left' || prop === 'width') {
                        $previewField.css(prop, value + '%');
                    } else if (prop === 'fontSize') {
                        $previewField.css('font-size', value + 'px');
                    } else if (prop === 'fontWeight') {
                        $previewField.css('font-weight', value);
                    } else if (prop === 'color') {
                        $previewField.css('color', value);
                    } else if (prop === 'textAlign') {
                        $previewField.css('text-align', value);
                    } else if (prop === 'fontFamily') {
                        $previewField.css('font-family', value);
                    } else if (prop === 'textShadow') {
                        $previewField.css('text-shadow', value ? '0 2px 4px rgba(0,0,0,0.3)' : 'none');
                    }
                });
            }

            // Attach live preview to all inputs
            $('input[data-field], select[data-field], input[type="checkbox"][data-field]').on('input change', updatePreview);
        });
        </script>

    <?php endif; ?>

</div>
