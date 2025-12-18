<?php
/**
 * Pagina amministrazione - Modifica Invito
 */

if (!current_user_can('manage_options')) {
    wp_die('Non hai i permessi per accedere a questa pagina');
}

// Ottieni ID invito (0 = nuovo invito)
$invite_id = isset($_GET['invite_id']) ? intval($_GET['invite_id']) : 0;
$is_new = ($invite_id === 0);

// Carica dati invito esistente o prepara array vuoto per nuovo invito
if ($is_new) {
    $invite_data = array(
        'ID' => 0,
        'title' => '',
        'message' => '',
        'final_message' => '',
        'final_message_button_text' => '',
        'event_date' => '',
        'event_time' => '',
        'event_location' => '',
        'event_address' => '',
        'template_id' => 1,
        'user_image_url' => '',
        'unique_code' => '',
        'status' => 'draft'
    );
} else {
    $invite_data = WI_Invites::get_invite_data($invite_id);

    if (!$invite_data) {
        wp_die('Invito non trovato');
    }
}

// Carica categoria evento scelta dall'utente (se presente)
$event_category_name = '';
$event_category_icon = '';
if (!$is_new) {
    $event_category_slug = get_post_meta($invite_id, '_wi_event_category', true);

    if ($event_category_slug) {
        global $wpdb;
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT name, icon FROM $categories_table WHERE slug = %s",
            $event_category_slug
        ));

        if ($category) {
            $event_category_name = $category->name;
            $event_category_icon = $category->icon;
        }
    }
}

// Salvataggio modifiche (creazione nuovo o aggiornamento esistente)
if (isset($_POST['update_invite']) && check_admin_referer('wi_update_invite_' . $invite_id)) {
    // Validazione e sanitizzazione input
    $errors = array();

    // Valida titolo (richiesto, max 255 caratteri)
    $title = sanitize_text_field($_POST['invite_title']);
    if (empty($title)) {
        $errors[] = 'Il titolo è obbligatorio';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Il titolo non può superare 255 caratteri';
    }

    // Valida messaggio (richiesto)
    $message = sanitize_textarea_field($_POST['invite_message']);
    if (empty($message)) {
        $errors[] = 'Il messaggio è obbligatorio';
    }

    // Valida data evento (formato YYYY-MM-DD, futura)
    $event_date = sanitize_text_field($_POST['event_date']);
    if (empty($event_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $errors[] = 'Data evento non valida (formato richiesto: YYYY-MM-DD)';
    }

    // Valida ora evento (formato HH:MM)
    $event_time = sanitize_text_field($_POST['event_time']);
    if (empty($event_time) || !preg_match('/^\d{2}:\d{2}$/', $event_time)) {
        $errors[] = 'Ora evento non valida (formato richiesto: HH:MM)';
    }

    // Valida location (richiesta)
    $event_location = sanitize_text_field($_POST['event_location']);
    if (empty($event_location)) {
        $errors[] = 'Il nome del luogo è obbligatorio';
    }

    // Valida indirizzo (richiesto)
    $event_address = sanitize_text_field($_POST['event_address']);
    if (empty($event_address)) {
        $errors[] = 'L\'indirizzo completo è obbligatorio';
    }

    // Valida template ID
    $template_id = intval($_POST['template_id']);
    if ($template_id <= 0) {
        $errors[] = 'Seleziona un template valido';
    }

    // Se ci sono errori, mostra e ferma il salvataggio
    if (!empty($errors)) {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Errori di validazione:</strong></p><ul>';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';
    } else {
        // Dati validati
        $updated_data = array(
            'title' => $title,
            'message' => $message,
            'final_message' => sanitize_textarea_field($_POST['final_message']),
            'final_message_button_text' => sanitize_text_field($_POST['final_message_button_text'] ?? ''),
            'event_date' => $event_date,
            'event_time' => $event_time,
            'event_location' => $event_location,
            'event_address' => $event_address,
            'user_image_id' => intval($_POST['user_image_id'])
        );

    if ($is_new) {
        // CREA NUOVO INVITO
        $new_post_id = wp_insert_post(array(
            'post_type' => 'wi_invite',
            'post_title' => $updated_data['title'],
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ));

        if ($new_post_id && !is_wp_error($new_post_id)) {
            $invite_id = $new_post_id;

            // Genera codice univoco
            $unique_code = WI_Invites::generate_unique_code();
            update_post_meta($invite_id, '_wi_unique_code', $unique_code);

            // Salva meta
            update_post_meta($invite_id, '_wi_template_id', $template_id);
            update_post_meta($invite_id, '_wi_message', $updated_data['message']);
            update_post_meta($invite_id, '_wi_final_message', $updated_data['final_message']);
            update_post_meta($invite_id, '_wi_final_message_button_text', $updated_data['final_message_button_text']);
            update_post_meta($invite_id, '_wi_event_date', $updated_data['event_date']);
            update_post_meta($invite_id, '_wi_event_time', $updated_data['event_time']);
            update_post_meta($invite_id, '_wi_event_location', $updated_data['event_location']);
            update_post_meta($invite_id, '_wi_event_address', $updated_data['event_address']);

            // Salva immagine
            if ($updated_data['user_image_id']) {
                set_post_thumbnail($invite_id, $updated_data['user_image_id']);
            }

            // Salva impostazioni RSVP (con valori predefiniti per nuovi inviti)
            WI_RSVP_Database::save_settings($invite_id, array(
                'rsvp_enabled' => isset($_POST['rsvp_enabled']) ? 1 : 0,
                'rsvp_deadline' => !empty($_POST['rsvp_deadline']) ? sanitize_text_field($_POST['rsvp_deadline']) : null,
                'max_guests_per_response' => intval($_POST['max_guests_per_response'] ?? 4),
                'menu_choices' => !empty($_POST['menu_choices']) ? json_encode(array_map('sanitize_text_field', explode(',', $_POST['menu_choices']))) : json_encode(['Carne', 'Pesce', 'Vegetariano']),
                'notify_admin' => isset($_POST['notify_admin']) ? 1 : 1,
                'admin_email' => !empty($_POST['admin_email']) ? sanitize_email($_POST['admin_email']) : get_option('admin_email')
            ));

            echo '<div class="notice notice-success is-dismissible"><p><strong>Invito creato con successo!</strong> <a href="?page=wedding-invites-edit&invite_id=' . $invite_id . '">Continua a modificarlo</a> o <a href="' . get_permalink($invite_id) . '" target="_blank">visualizzalo</a>.</p></div>';

            // Aggiorna variabili per mostrare il form compilato
            $is_new = false;
            $invite_data = WI_Invites::get_invite_data($invite_id);
        } else {
            echo '<div class="notice notice-error is-dismissible"><p><strong>Errore nella creazione dell\'invito.</strong></p></div>';
        }

    } else {
        // AGGIORNA INVITO ESISTENTE
        wp_update_post(array(
            'ID' => $invite_id,
            'post_title' => $updated_data['title']
        ));

        // Aggiorna meta
        update_post_meta($invite_id, '_wi_template_id', $template_id);
        update_post_meta($invite_id, '_wi_message', $updated_data['message']);
        update_post_meta($invite_id, '_wi_final_message', $updated_data['final_message']);
        update_post_meta($invite_id, '_wi_final_message_button_text', $updated_data['final_message_button_text']);
        update_post_meta($invite_id, '_wi_event_date', $updated_data['event_date']);
        update_post_meta($invite_id, '_wi_event_time', $updated_data['event_time']);
        update_post_meta($invite_id, '_wi_event_location', $updated_data['event_location']);
        update_post_meta($invite_id, '_wi_event_address', $updated_data['event_address']);

        // Aggiorna immagine
        if ($updated_data['user_image_id']) {
            set_post_thumbnail($invite_id, $updated_data['user_image_id']);
        }

        // Salva impostazioni RSVP
        if (isset($_POST['rsvp_enabled'])) {
            WI_RSVP_Database::save_settings($invite_id, array(
                'rsvp_enabled' => isset($_POST['rsvp_enabled']) ? 1 : 0,
                'rsvp_deadline' => !empty($_POST['rsvp_deadline']) ? sanitize_text_field($_POST['rsvp_deadline']) : null,
                'max_guests_per_response' => intval($_POST['max_guests_per_response'] ?? 4),
                'menu_choices' => !empty($_POST['menu_choices']) ? json_encode(array_map('sanitize_text_field', explode(',', $_POST['menu_choices']))) : json_encode(['Carne', 'Pesce', 'Vegetariano']),
                'notify_admin' => isset($_POST['notify_admin']) ? 1 : 0,
                'admin_email' => !empty($_POST['admin_email']) ? sanitize_email($_POST['admin_email']) : get_option('admin_email')
            ));
        }

        // Invalida cache post per forzare rigenerazione contenuto
        clean_post_cache($invite_id);
        wp_cache_delete($invite_id, 'posts');
        wp_cache_delete($invite_id, 'post_meta');

        echo '<div class="notice notice-success is-dismissible"><p><strong>Invito aggiornato con successo!</strong></p></div>';

        // Ricarica dati aggiornati
        $invite_data = WI_Invites::get_invite_data($invite_id);
    }
    } // Fine validazione
}

$templates = WI_Templates::get_all_templates();
$invite_url = $is_new ? '#' : get_permalink($invite_id);
$page_title = $is_new ? 'Nuovo Invito' : 'Modifica Invito';
$page_icon = $is_new ? 'dashicons-plus-alt' : 'dashicons-edit';

// Carica impostazioni RSVP per questo invito
$rsvp_settings = $is_new ? null : WI_RSVP_Database::get_settings($invite_id);
if ($is_new) {
    // Valori predefiniti per nuovo invito
    $rsvp_settings = (object) array(
        'rsvp_enabled' => 0,
        'rsvp_deadline' => '',
        'max_guests_per_response' => 4,
        'menu_choices' => json_encode(['Carne', 'Pesce', 'Vegetariano']),
        'notify_admin' => 1,
        'admin_email' => get_option('admin_email')
    );
}
$menu_choices_array = json_decode($rsvp_settings->menu_choices ?? '[]', true);
if (!is_array($menu_choices_array)) {
    $menu_choices_array = ['Carne', 'Pesce', 'Vegetariano'];
}
?>

<div class="wrap wi-admin-wrap">
    <h1 class="wi-admin-title">
        <span class="dashicons <?php echo $page_icon; ?>"></span>
        <?php echo $page_title; ?>
    </h1>

    <div class="wi-edit-container">
        <?php if (!$is_new): ?>
        <div class="wi-edit-header">
            <div class="wi-edit-info">
                <h2><?php echo esc_html($invite_data['title']); ?></h2>
                <div class="wi-edit-meta">
                    <span class="wi-meta-item">
                        <span class="dashicons dashicons-admin-users"></span>
                        Autore: <?php echo get_the_author_meta('display_name', get_post_field('post_author', $invite_id)); ?>
                    </span>
                    <span class="wi-meta-item">
                        <span class="dashicons dashicons-calendar"></span>
                        Creato: <?php echo get_the_date('d/m/Y H:i', $invite_id); ?>
                    </span>
                    <?php if ($event_category_name) : ?>
                    <span class="wi-meta-item wi-category-badge">
                        <span style="font-size: 16px;"><?php echo esc_html($event_category_icon); ?></span>
                        Tipo Evento: <strong><?php echo esc_html($event_category_name); ?></strong>
                    </span>
                    <?php endif; ?>
                    <span class="wi-meta-item">
                        <span class="dashicons dashicons-visibility"></span>
                        <a href="<?php echo $invite_url; ?>" target="_blank">Visualizza Invito</a>
                    </span>
                </div>
            </div>
            <div class="wi-edit-actions">
                <a href="?page=wedding-invites" class="button">
                    <span class="dashicons dashicons-arrow-left-alt"></span> Torna alla Lista
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="wi-edit-header">
            <div class="wi-edit-info">
                <h2>Crea un nuovo invito compilando il form sottostante</h2>
                <p style="color: #666; margin-top: 10px;">Dopo aver salvato, potrai visualizzare e condividere il tuo invito.</p>
            </div>
            <div class="wi-edit-actions">
                <a href="?page=wedding-invites" class="button">
                    <span class="dashicons dashicons-arrow-left-alt"></span> Torna alla Lista
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="post" id="wi-edit-form" class="wi-admin-form">
            <?php wp_nonce_field('wi_update_invite_' . $invite_id); ?>
            
            <div class="wi-form-columns">
                <!-- Colonna Sinistra - Form -->
                <div class="wi-form-column wi-form-main">
                    
                    <div class="wi-form-section">
                        <h3>Informazioni Principali</h3>
                        
                        <div class="wi-form-group">
                            <label for="invite_title">Titolo Invito</label>
                            <input type="text"
                                   id="invite_title"
                                   name="invite_title"
                                   value="<?php echo esc_attr($invite_data['title']); ?>"
                                   class="widefat">
                        </div>

                        <div class="wi-form-group">
                            <label for="invite_message">Messaggio Personalizzato</label>
                            <textarea id="invite_message"
                                      name="invite_message"
                                      rows="6"
                                      class="widefat"><?php echo esc_textarea($invite_data['message']); ?></textarea>
                        </div>

                        <div class="wi-form-group">
                            <label for="final_message">Messaggio Finale <span class="description">(Opzionale - apribile con pulsante nell'invito)</span></label>
                            <textarea id="final_message"
                                      name="final_message"
                                      rows="4"
                                      class="widefat"
                                      placeholder="Es. Non vediamo l'ora di festeggiare insieme a voi questo giorno speciale!"><?php echo esc_textarea($invite_data['final_message'] ?? ''); ?></textarea>
                            <p class="description">Questo messaggio apparirà in fondo all'invito e sarà espandibile con un click</p>
                        </div>

                        <div class="wi-form-group">
                            <label for="final_message_button_text">Testo Pulsante Messaggio Finale <span class="description">(Opzionale)</span></label>
                            <input type="text"
                                   id="final_message_button_text"
                                   name="final_message_button_text"
                                   class="widefat"
                                   value="<?php echo esc_attr($invite_data['final_message_button_text'] ?? ''); ?>"
                                   placeholder="Es: Leggi il messaggio, Scopri di più, Clicca qui...">
                            <p class="description">Lascia vuoto per usare un testo predefinito</p>
                        </div>
                    </div>
                    
                    <div class="wi-form-section">
                        <h3>Dettagli Evento</h3>
                        
                        <div class="wi-form-row">
                            <div class="wi-form-group">
                                <label for="event_date">Data Evento</label>
                                <input type="date"
                                       id="event_date"
                                       name="event_date"
                                       value="<?php echo esc_attr($invite_data['event_date']); ?>"
                                       class="widefat">
                            </div>

                            <div class="wi-form-group">
                                <label for="event_time">Ora Evento</label>
                                <input type="time"
                                       id="event_time"
                                       name="event_time"
                                       value="<?php echo esc_attr($invite_data['event_time']); ?>"
                                       class="widefat">
                            </div>
                        </div>
                        
                        <div class="wi-form-group">
                            <label for="event_location">Nome Luogo</label>
                            <input type="text"
                                   id="event_location"
                                   name="event_location"
                                   value="<?php echo esc_attr($invite_data['event_location']); ?>"
                                   class="widefat">
                        </div>

                        <div class="wi-form-group">
                            <label for="event_address">Indirizzo Completo</label>
                            <input type="text"
                                   id="event_address"
                                   name="event_address"
                                   value="<?php echo esc_attr($invite_data['event_address']); ?>"
                                   class="widefat">
                            <p class="description">Usato per la mappa Google Maps</p>
                        </div>
                    </div>
                    
                    <div class="wi-form-section">
                        <h3>Immagine Evento</h3>
                        
                        <div class="wi-form-group">
                            <label>Immagine Corrente</label>
                            <div class="wi-current-image">
                                <?php if ($invite_data['user_image_url']) : ?>
                                    <img src="<?php echo esc_url($invite_data['user_image_url']); ?>" 
                                         alt="Immagine evento" 
                                         id="current-image-preview">
                                <?php else : ?>
                                    <div class="wi-no-image">Nessuna immagine</div>
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" 
                                   id="user_image_id" 
                                   name="user_image_id" 
                                   value="<?php echo esc_attr($invite_data['user_image_id']); ?>">
                            
                            <button type="button" id="wi-change-image" class="button">
                                <span class="dashicons dashicons-format-image"></span>
                                Cambia Immagine
                            </button>
                            
                            <?php if ($invite_data['user_image_url']) : ?>
                            <button type="button" id="wi-remove-image" class="button button-link-delete">
                                <span class="dashicons dashicons-trash"></span>
                                Rimuovi Immagine
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sezione RSVP Settings -->
                    <div class="wi-form-section">
                        <h3>
                            <span class="dashicons dashicons-email" style="color: #7c3aed;"></span>
                            Impostazioni RSVP
                        </h3>

                        <div class="wi-form-group">
                            <label>
                                <input type="checkbox"
                                       name="rsvp_enabled"
                                       id="rsvp_enabled"
                                       value="1"
                                       <?php checked($rsvp_settings->rsvp_enabled, 1); ?>>
                                <strong>Abilita RSVP per questo invito</strong>
                            </label>
                            <p class="description">Consenti agli ospiti di confermare la partecipazione direttamente dall'invito</p>
                        </div>

                        <div id="rsvp-options" style="<?php echo $rsvp_settings->rsvp_enabled ? '' : 'display:none;'; ?>">

                            <div class="wi-form-group">
                                <label for="rsvp_deadline">Scadenza RSVP</label>
                                <input type="date"
                                       id="rsvp_deadline"
                                       name="rsvp_deadline"
                                       class="widefat"
                                       value="<?php echo esc_attr($rsvp_settings->rsvp_deadline ?? ''); ?>">
                                <p class="description">Data limite per confermare (lascia vuoto per nessuna scadenza)</p>
                            </div>

                            <div class="wi-form-group">
                                <label for="max_guests_per_response">Numero massimo ospiti per risposta</label>
                                <input type="number"
                                       id="max_guests_per_response"
                                       name="max_guests_per_response"
                                       class="widefat"
                                       min="1"
                                       max="20"
                                       value="<?php echo esc_attr($rsvp_settings->max_guests_per_response ?? 4); ?>">
                                <p class="description">Quanti ospiti può portare ogni invitato (es: 4 = famiglia)</p>
                            </div>

                            <div class="wi-form-group">
                                <label for="menu_choices">Opzioni Menu (separate da virgola)</label>
                                <input type="text"
                                       id="menu_choices"
                                       name="menu_choices"
                                       class="widefat"
                                       value="<?php echo esc_attr(implode(', ', $menu_choices_array)); ?>"
                                       placeholder="Es: Carne, Pesce, Vegetariano, Vegano">
                                <p class="description">Gli ospiti potranno scegliere una di queste opzioni</p>
                            </div>

                            <div class="wi-form-group">
                                <label>
                                    <input type="checkbox"
                                           name="notify_admin"
                                           value="1"
                                           <?php checked($rsvp_settings->notify_admin, 1); ?>>
                                    <strong>Ricevi notifica email per ogni nuova risposta</strong>
                                </label>
                            </div>

                            <div class="wi-form-group">
                                <label for="admin_email">Email per notifiche</label>
                                <input type="email"
                                       id="admin_email"
                                       name="admin_email"
                                       class="widefat"
                                       value="<?php echo esc_attr($rsvp_settings->admin_email ?? get_option('admin_email')); ?>">
                                <p class="description">Email dove ricevere le conferme RSVP</p>
                            </div>

                            <?php if (!$is_new): ?>
                            <div class="wi-form-group" style="background: #f0f9ff; padding: 15px; border-left: 4px solid #0284c7; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0;">
                                    <strong>📊 Visualizza risposte RSVP ricevute</strong>
                                </p>
                                <a href="<?php echo admin_url('admin.php?page=wi-rsvp&invite_id=' . $invite_id); ?>"
                                   class="button button-secondary">
                                    <span class="dashicons dashicons-chart-bar"></span>
                                    Vai al Dashboard RSVP
                                </a>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>

                <!-- Colonna Destra - Sidebar -->
                <div class="wi-form-column wi-form-sidebar">
                    
                    <div class="wi-sidebar-box">
                        <h3>Azioni</h3>
                        <div class="wi-sidebar-actions">
                            <button type="submit" name="update_invite" class="button button-primary button-large">
                                <span class="dashicons dashicons-<?php echo $is_new ? 'plus-alt' : 'yes'; ?>"></span>
                                <?php echo $is_new ? 'Crea Invito' : 'Salva Modifiche'; ?>
                            </button>

                            <button type="button" id="wi-preview-changes" class="button button-large">
                                <span class="dashicons dashicons-visibility"></span>
                                Anteprima <?php echo $is_new ? 'Invito' : 'Modifiche'; ?>
                            </button>

                            <button type="button" id="wi-toggle-live-preview" class="button button-large">
                                <span class="dashicons dashicons-laptop"></span>
                                Anteprima Live
                                <span class="wi-live-badge">LIVE</span>
                            </button>

                            <?php if (!$is_new): ?>
                            <a href="<?php echo $invite_url; ?>"
                               target="_blank"
                               class="button button-large">
                                <span class="dashicons dashicons-external"></span>
                                Visualizza Pubblicato
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="wi-sidebar-box">
                        <h3>Template</h3>
                        <div class="wi-template-selector">
                            <?php foreach ($templates as $template) : ?>
                            <label class="wi-template-option <?php echo ($template->id == $invite_data['template_id']) ? 'selected' : ''; ?>">
                                <input type="radio"
                                       name="template_id"
                                       value="<?php echo $template->id; ?>"
                                       <?php checked($template->id, $invite_data['template_id']); ?>>
                                <span class="wi-template-name"><?php echo esc_html($template->name); ?></span>
                                <span class="wi-template-check">✓</span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="wi-sidebar-box">
                        <h3>Informazioni</h3>
                        <div class="wi-info-list">
                            <div class="wi-info-item">
                                <strong>ID Invito:</strong>
                                <span>#<?php echo $invite_id; ?></span>
                            </div>
                            <div class="wi-info-item">
                                <strong>Stato:</strong>
                                <span class="wi-status-badge">
                                    <?php echo get_post_status($invite_id) === 'publish' ? 'Pubblicato' : 'Bozza'; ?>
                                </span>
                            </div>
                            <div class="wi-info-item">
                                <strong>Ultimo aggiornamento:</strong>
                                <span><?php echo get_the_modified_date('d/m/Y H:i', $invite_id); ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if (!$is_new): ?>
                    <div class="wi-sidebar-box wi-qr-box">
                        <h3>QR Code</h3>
                        <div class="wi-qr-section">
                            <div id="wi-qr-preview" class="wi-qr-preview">
                                <div class="wi-qr-placeholder">
                                    <span class="dashicons dashicons-smartphone" style="font-size: 48px; color: #dcdcde;"></span>
                                    <p>Genera QR Code</p>
                                </div>
                            </div>
                            <div class="wi-qr-actions">
                                <button type="button" id="wi-generate-qr" class="button button-secondary button-large" data-invite-id="<?php echo $invite_id; ?>">
                                    <span class="dashicons dashicons-update"></span>
                                    Genera QR Code
                                </button>
                                <button type="button" id="wi-download-qr" class="button button-large" style="display: none;">
                                    <span class="dashicons dashicons-download"></span>
                                    Scarica PNG
                                </button>
                                <button type="button" id="wi-customize-qr" class="button button-large" style="display: none;">
                                    <span class="dashicons dashicons-admin-customizer"></span>
                                    Personalizza
                                </button>
                            </div>
                            <p class="wi-qr-info" style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                                Il QR Code permette di accedere all'invito tramite smartphone
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="wi-sidebar-box wi-danger-zone">
                        <h3>Zona Pericolosa</h3>
                        <button type="button" 
                                id="wi-delete-invite" 
                                class="button button-link-delete"
                                data-invite-id="<?php echo $invite_id; ?>">
                            <span class="dashicons dashicons-trash"></span>
                            Elimina Invito
                        </button>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sidebar Anteprima LIVE -->
<div class="wi-live-preview-sidebar">
    <div class="wi-preview-header">
        <div class="wi-preview-title">
            <span class="dashicons dashicons-laptop"></span>
            Anteprima Live
            <span class="wi-live-badge">LIVE</span>
        </div>
        <div class="wi-preview-controls">
            <div class="wi-device-toggles">
                <button type="button" class="wi-device-btn active" data-device="desktop" title="Desktop">
                    <span class="dashicons dashicons-desktop"></span>
                </button>
                <button type="button" class="wi-device-btn" data-device="tablet" title="Tablet">
                    <span class="dashicons dashicons-tablet"></span>
                </button>
                <button type="button" class="wi-device-btn" data-device="mobile" title="Mobile">
                    <span class="dashicons dashicons-smartphone"></span>
                </button>
            </div>
            <button type="button" class="wi-preview-refresh" title="Ricarica">
                <span class="dashicons dashicons-update"></span>
            </button>
            <button type="button" class="wi-preview-close" title="Chiudi">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
    </div>
    <div class="wi-preview-body">
        <div class="wi-preview-loading">
            <span class="spinner is-active"></span>
            <p>Caricamento anteprima...</p>
        </div>
        <div class="wi-preview-frame-wrapper desktop">
            <iframe id="wi-live-preview-frame" title="Anteprima Live"></iframe>
        </div>
    </div>
</div>

<!-- Modal Anteprima -->
<div id="wi-preview-modal" class="wi-modal" style="display: none;">
    <div class="wi-modal-overlay"></div>
    <div class="wi-modal-content">
        <div class="wi-modal-header">
            <h2>Anteprima Modifiche</h2>
            <button type="button" class="wi-modal-close">
                <span class="dashicons dashicons-no"></span>
            </button>
        </div>
        <div class="wi-modal-body">
            <div id="wi-preview-container"></div>
        </div>
        <div class="wi-modal-footer">
            <button type="button" class="button wi-modal-close">Chiudi</button>
        </div>
    </div>
</div>

<style>
.wi-edit-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-top: 20px;
}

.wi-edit-header {
    padding: 30px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wi-edit-info h2 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.wi-edit-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.wi-meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.wi-meta-item .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}

.wi-form-columns {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 0;
}

.wi-form-main {
    padding: 30px;
    border-right: 1px solid #e0e0e0;
}

.wi-form-sidebar {
    padding: 30px 20px;
    background: #f8f9fa;
}

.wi-form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.wi-form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.wi-form-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 1.3rem;
}

.wi-form-group {
    margin-bottom: 20px;
}

.wi-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #34495e;
}

.wi-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.wi-current-image {
    margin-bottom: 15px;
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.wi-current-image img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.wi-no-image {
    padding: 40px;
    color: #999;
    font-size: 1.1rem;
}

.wi-sidebar-box {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.wi-sidebar-box h3 {
    margin: 0 0 15px 0;
    font-size: 1.1rem;
    color: #2c3e50;
}

.wi-sidebar-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wi-sidebar-actions .button {
    justify-content: center;
}

.wi-template-selector {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wi-template-option {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.wi-template-option:hover {
    border-color: #3498db;
}

.wi-template-option.selected {
    border-color: #2ecc71;
    background: #f0fdf4;
}

.wi-template-option input {
    margin-right: 10px;
}

.wi-template-name {
    flex: 1;
    font-weight: 500;
}

.wi-template-check {
    display: none;
    color: #2ecc71;
    font-size: 1.2rem;
}

.wi-template-option.selected .wi-template-check {
    display: block;
}

.wi-info-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.wi-info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.wi-info-item strong {
    color: #555;
}

.wi-status-badge {
    padding: 4px 12px;
    background: #d4edda;
    color: #155724;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.wi-danger-zone {
    border-color: #e74c3c;
}

.wi-danger-zone h3 {
    color: #e74c3c;
}

.wi-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999999;
}

.wi-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
}

.wi-modal-content {
    position: relative;
    background: white;
    max-width: 1200px;
    max-height: 90vh;
    margin: 30px auto;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.wi-modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wi-modal-header h2 {
    margin: 0;
}

.wi-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
    padding: 0;
}

.wi-modal-close:hover {
    color: #333;
}

.wi-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.wi-modal-footer {
    padding: 15px 30px;
    border-top: 1px solid #e0e0e0;
    text-align: right;
}

@media (max-width: 1024px) {
    .wi-form-columns {
        grid-template-columns: 1fr;
    }
    
    .wi-form-main {
        border-right: none;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .wi-form-sidebar {
        background: white;
    }
}

@media (max-width: 768px) {
    .wi-edit-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .wi-form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Media uploader per cambiare immagine
    var imageFrame;
    
    $('#wi-change-image').on('click', function(e) {
        e.preventDefault();
        
        if (imageFrame) {
            imageFrame.open();
            return;
        }
        
        imageFrame = wp.media({
            title: 'Seleziona Immagine Evento',
            button: {
                text: 'Usa questa immagine'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        imageFrame.on('select', function() {
            var attachment = imageFrame.state().get('selection').first().toJSON();
            
            $('#user_image_id').val(attachment.id);
            $('#current-image-preview').attr('src', attachment.url);
            $('.wi-no-image').remove();
            
            if (!$('#wi-remove-image').length) {
                $('#wi-change-image').after(
                    '<button type="button" id="wi-remove-image" class="button button-link-delete">' +
                    '<span class="dashicons dashicons-trash"></span> Rimuovi Immagine' +
                    '</button>'
                );
            }
        });
        
        imageFrame.open();
    });
    
    // Rimuovi immagine
    $(document).on('click', '#wi-remove-image', function() {
        if (confirm('Vuoi rimuovere l\'immagine?')) {
            $('#user_image_id').val('');
            $('.wi-current-image').html('<div class="wi-no-image">Nessuna immagine</div>');
            $(this).remove();
        }
    });
    
    // Template selector
    $('input[name="template_id"]').on('change', function() {
        $('.wi-template-option').removeClass('selected');
        $(this).closest('.wi-template-option').addClass('selected');
    });
    
    // Anteprima modifiche
    $('#wi-preview-changes').on('click', function() {
        var data = {
            action: 'wi_preview_edited_invite',
            nonce: wiAdmin.nonce,
            invite_data: {
                title: $('#invite_title').val(),
                message: $('#invite_message').val(),
                event_date: $('#event_date').val(),
                event_time: $('#event_time').val(),
                event_location: $('#event_location').val(),
                event_address: $('#event_address').val(),
                user_image_id: $('#user_image_id').val()
            },
            template_id: $('input[name="template_id"]:checked').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function() {
                $('#wi-preview-changes').prop('disabled', true).text('Generazione...');
            },
            success: function(response) {
                if (response.success) {
                    $('#wi-preview-container').html(response.data.html);
                    $('#wi-preview-modal').fadeIn(300);
                } else {
                    alert('Errore nella generazione dell\'anteprima');
                }
            },
            error: function() {
                alert('Errore di connessione');
            },
            complete: function() {
                $('#wi-preview-changes').prop('disabled', false).html('<span class="dashicons dashicons-visibility"></span> Anteprima Modifiche');
            }
        });
    });
    
    // Chiudi modal
    $('.wi-modal-close, .wi-modal-overlay').on('click', function() {
        $('#wi-preview-modal').fadeOut(300);
    });
    
    // Elimina invito
    $('#wi-delete-invite').on('click', function() {
        if (!confirm('Sei SICURO di voler eliminare questo invito? Questa azione è irreversibile!')) {
            return;
        }
        
        var inviteId = $(this).data('invite-id');
        
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
                    alert('Invito eliminato con successo');
                    window.location.href = '?page=wedding-invites';
                } else {
                    alert('Errore nell\'eliminazione');
                }
            }
        });
    });
    
    // Validazione form
    $('#wi-edit-form').on('submit', function(e) {
        var title = $('#invite_title').val().trim();
        var message = $('#invite_message').val().trim();
        var date = $('#event_date').val();
        var time = $('#event_time').val();
        var location = $('#event_location').val().trim();
        var address = $('#event_address').val().trim();
        var template = $('input[name="template_id"]:checked').val();

        if (!title || !message || !date || !time || !location || !address || !template) {
            e.preventDefault();
            alert('Compila tutti i campi obbligatori');
            return false;
        }
    });

    // Toggle RSVP options visibility
    $('#rsvp_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#rsvp-options').slideDown(300);
        } else {
            $('#rsvp-options').slideUp(300);
        }
    });
});
</script>