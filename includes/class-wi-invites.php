<?php
/**
 * Classe per la gestione degli inviti
 */
class WI_Invites {
    
    public static function save_invite($data, $template_id, $invite_id = 0) {
        $is_update = ($invite_id > 0);

        // Filtro: modifica dati prima del salvataggio
        $data = apply_filters('wi_before_save_invite_data', $data, $template_id, $invite_id);

        wi_log(
            $is_update ? 'Updating invite' : 'Creating new invite',
            'info',
            array('invite_id' => $invite_id, 'template_id' => $template_id)
        );

        $post_data = array(
            'post_title'    => sanitize_text_field($data['title']),
            'post_status'   => 'publish',
            'post_type'     => 'wi_invite',
            'post_author'   => get_current_user_id()
        );

        // Filtro: modifica dati post prima del salvataggio
        $post_data = apply_filters('wi_invite_post_data', $post_data, $data, $template_id, $invite_id);

        if ($is_update) {
            $post_data['ID'] = $invite_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }

        if (is_wp_error($post_id)) {
            wi_log('Error saving invite', 'error', array('error' => $post_id->get_error_message()));
            return false;
        }

        if ($post_id) {
            // Salva i metadati
            update_post_meta($post_id, '_wi_template_id', intval($template_id));
            update_post_meta($post_id, '_wi_message', sanitize_textarea_field($data['message']));

            $final_msg = sanitize_textarea_field($data['final_message'] ?? '');
            $final_btn = sanitize_text_field($data['final_message_button_text'] ?? '');

            update_post_meta($post_id, '_wi_final_message', $final_msg);
            update_post_meta($post_id, '_wi_final_message_button_text', $final_btn);

            update_post_meta($post_id, '_wi_event_date', sanitize_text_field($data['event_date']));
            update_post_meta($post_id, '_wi_event_time', sanitize_text_field($data['event_time']));
            update_post_meta($post_id, '_wi_event_location', sanitize_text_field($data['event_location']));
            update_post_meta($post_id, '_wi_event_address', sanitize_text_field($data['event_address']));

            // Salva l'immagine utente
            if (!empty($data['user_image_id'])) {
                set_post_thumbnail($post_id, intval($data['user_image_id']));
            }

            wi_log('Invite saved successfully', 'info', array('post_id' => $post_id));

            // Action: dopo il salvataggio dell'invito
            do_action('wi_after_save_invite', $post_id, $data, $template_id, $is_update);
        }

        return $post_id;
    }
    
    public static function get_invite_data($invite_id) {
        $post = get_post($invite_id);
        
        if (!$post || $post->post_type !== 'wi_invite') {
            return false;
        }
        
        $thumbnail_id = get_post_thumbnail_id($invite_id);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';

        // Batch query per tutti i meta in una volta (ottimizzazione)
        $meta = get_post_meta($invite_id);

        return array(
            'id' => $invite_id,
            'title' => $post->post_title,
            'template_id' => isset($meta['_wi_template_id'][0]) ? $meta['_wi_template_id'][0] : '',
            'message' => isset($meta['_wi_message'][0]) ? $meta['_wi_message'][0] : '',
            'final_message' => isset($meta['_wi_final_message'][0]) ? $meta['_wi_final_message'][0] : '',
            'final_message_button_text' => isset($meta['_wi_final_message_button_text'][0]) ? $meta['_wi_final_message_button_text'][0] : '',
            'event_date' => isset($meta['_wi_event_date'][0]) ? $meta['_wi_event_date'][0] : '',
            'event_time' => isset($meta['_wi_event_time'][0]) ? $meta['_wi_event_time'][0] : '',
            'event_location' => isset($meta['_wi_event_location'][0]) ? $meta['_wi_event_location'][0] : '',
            'event_address' => isset($meta['_wi_event_address'][0]) ? $meta['_wi_event_address'][0] : '',
            'user_image_id' => $thumbnail_id,
            'user_image_url' => $thumbnail_url
        );
    }
    
    public static function generate_invite_html($data, $template_id, $is_preview = false) {
        // Prepara i dati con event_address
        $invite_data = array(
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'final_message' => $data['final_message'] ?? '',
            'final_message_button_text' => !empty($data['final_message_button_text']) ? $data['final_message_button_text'] : 'Leggi il messaggio',
            'event_date' => $data['event_date'] ?? '',
            'event_time' => $data['event_time'] ?? '',
            'event_location' => $data['event_location'] ?? '',
            'event_address' => $data['event_address'] ?? $data['event_location'] ?? '',
            'user_image' => $data['user_image_url'] ?? WI_PLUGIN_URL . 'assets/images/placeholder.svg'
        );

        // Filtro: modifica dati prima del rendering
        $invite_data = apply_filters('wi_before_render_invite', $invite_data, $template_id, $is_preview);

        // render_template già include CSS, non duplicare
        $html = WI_Templates::render_template($template_id, $invite_data);

        // Aggiungi script per countdown (anche in preview per visualizzazione completa)
        $event_datetime = $data['event_date'] . ' ' . $data['event_time'];
        $event_address = !empty($data['event_address']) ? $data['event_address'] : $data['event_location'];

        $html .= '<script>
            var eventDateTime = "' . esc_js($event_datetime) . '";
            var eventAddress = "' . esc_js($event_address) . '";
        </script>';

        // Filtro: modifica HTML finale prima del return
        $html = apply_filters('wi_after_render_invite', $html, $invite_data, $template_id, $is_preview);

        return $html;
    }
    
    public static function get_all_invites($user_id = 0) {
        $args = array(
            'post_type' => 'wi_invite',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        if ($user_id > 0) {
            $args['author'] = $user_id;
        }
        
        return get_posts($args);
    }
    
    public static function delete_invite($invite_id) {
        return wp_delete_post($invite_id, true);
    }

    /**
     * Genera un codice univoco per l'invito (crittograficamente sicuro)
     *
     * @return string Codice univoco (8 caratteri alfanumerici maiuscoli)
     */
    public static function generate_unique_code() {
        global $wpdb;

        do {
            // Usa wp_generate_password per generazione sicura
            // Genera 10 caratteri e prende i primi 8 alfanumerici
            $raw_code = wp_generate_password(12, false, false); // no special chars
            $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $raw_code), 0, 8));

            // Fallback se la generazione produce meno di 8 caratteri
            if (strlen($code) < 8) {
                // Usa random_bytes per sicurezza crittografica
                $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            }

            // Verifica che non esista già nel database
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta}
                WHERE meta_key = '_wi_unique_code'
                AND meta_value = %s",
                $code
            ));
        } while ($exists > 0);

        return $code;
    }
}