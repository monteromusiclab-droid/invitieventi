<?php
/**
 * Classe principale per gestione RSVP
 * Gestisce risposte, email, statistiche
 */

if (!defined('ABSPATH')) exit;

class WI_RSVP {

    /**
     * Inizializza hooks e actions
     */
    public static function init() {
        // AJAX handlers
        add_action('wp_ajax_wi_submit_rsvp', array(__CLASS__, 'ajax_submit_rsvp'));
        add_action('wp_ajax_nopriv_wi_submit_rsvp', array(__CLASS__, 'ajax_submit_rsvp'));

        add_action('wp_ajax_wi_get_rsvp_stats', array(__CLASS__, 'ajax_get_rsvp_stats'));
        add_action('wp_ajax_wi_update_rsvp_settings', array(__CLASS__, 'ajax_update_rsvp_settings'));
        add_action('wp_ajax_wi_delete_rsvp_response', array(__CLASS__, 'ajax_delete_rsvp_response'));
        add_action('wp_ajax_wi_export_rsvp_csv', array(__CLASS__, 'ajax_export_rsvp_csv'));

        // Shortcode per modifica RSVP con token
        add_shortcode('wedding_invites_rsvp_edit', array(__CLASS__, 'render_edit_form'));
    }

    /**
     * AJAX: Salva risposta RSVP
     */
    public static function ajax_submit_rsvp() {
        // Dual nonce verification
        $nonce_valid = check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false) ||
                       check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false);

        if (!$nonce_valid) {
            wp_send_json_error(array('message' => 'Verifica sicurezza fallita'));
        }

        // Validazione dati
        $invite_id = intval($_POST['invite_id']);
        $status = sanitize_text_field($_POST['status']);

        if (!in_array($status, ['attending', 'not_attending', 'maybe'])) {
            wp_send_json_error(array('message' => 'Status non valido'));
        }

        // Verifica che l'invito esista
        $invite = get_post($invite_id);
        if (!$invite || $invite->post_type !== 'wi_invite') {
            wp_send_json_error(array('message' => 'Invito non trovato'));
        }

        // Verifica deadline RSVP
        $settings = WI_RSVP_Database::get_settings($invite_id);
        if ($settings->rsvp_deadline && strtotime($settings->rsvp_deadline) < time()) {
            wp_send_json_error(array('message' => 'Il termine per confermare è scaduto'));
        }

        // Raccogli dati
        $guest_name = sanitize_text_field($_POST['guest_name'] ?? '');
        $guest_email = sanitize_email($_POST['guest_email'] ?? '');

        if (empty($guest_name) || empty($guest_email)) {
            wp_send_json_error(array('message' => 'Nome ed email sono obbligatori'));
        }

        if (!is_email($guest_email)) {
            wp_send_json_error(array('message' => 'Email non valida'));
        }

        $num_guests = intval($_POST['num_guests'] ?? 1);

        // Limita numero ospiti
        if ($num_guests > $settings->max_guests_per_response) {
            wp_send_json_error(array('message' => "Massimo {$settings->max_guests_per_response} ospiti per risposta"));
        }

        // Genera token unico per modifiche future
        $token = bin2hex(random_bytes(32));

        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';

        // Verifica se email già presente per questo invito
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, token FROM $table WHERE invite_id = %d AND guest_email = %s",
            $invite_id, $guest_email
        ));

        $data = array(
            'invite_id' => $invite_id,
            'guest_name' => $guest_name,
            'guest_email' => $guest_email,
            'guest_phone' => sanitize_text_field($_POST['guest_phone'] ?? ''),
            'status' => $status,
            'num_guests' => $num_guests,
            'dietary_preferences' => !empty($_POST['dietary']) ? json_encode(array_map('sanitize_text_field', $_POST['dietary'])) : null,
            'menu_choice' => sanitize_text_field($_POST['menu_choice'] ?? ''),
            'notes' => sanitize_textarea_field($_POST['notes'] ?? ''),
            'responded_at' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        );

        if ($existing) {
            // Aggiorna esistente
            $result = $wpdb->update($table, $data, array('id' => $existing->id));
            $token = $existing->token; // Mantieni token originale
            $is_update = true;
        } else {
            // Inserisci nuovo
            $data['token'] = $token;
            $result = $wpdb->insert($table, $data);
            $is_update = false;
        }

        if ($result === false) {
            wi_log('RSVP save failed', 'error', array(
                'invite_id' => $invite_id,
                'email' => $guest_email,
                'error' => $wpdb->last_error
            ));
            wp_send_json_error(array('message' => 'Errore nel salvataggio. Riprova.'));
        }

        wi_log('RSVP saved successfully', 'info', array(
            'invite_id' => $invite_id,
            'guest' => $guest_name,
            'status' => $status,
            'is_update' => $is_update
        ));

        // Invalida cache statistiche
        wp_cache_delete('wi_rsvp_stats_' . $invite_id, 'wedding_invites');

        // Invia email conferma ospite
        self::send_confirmation_email($guest_email, $guest_name, $status, $token, $invite_id);

        // Notifica admin solo per nuove conferme
        if (!$is_update && $settings->notify_admin) {
            self::notify_admin($invite_id, $guest_name, $status, $settings->admin_email);
        }

        wp_send_json_success(array(
            'message' => $is_update ? 'RSVP aggiornato con successo!' : 'Grazie per la conferma!',
            'token' => $token,
            'is_update' => $is_update
        ));
    }

    /**
     * Invia email conferma ospite
     */
    private static function send_confirmation_email($email, $name, $status, $token, $invite_id) {
        $status_labels = array(
            'attending' => '✅ Confermata',
            'not_attending' => '❌ Declinata',
            'maybe' => '❓ In Sospeso'
        );

        $invite = get_post($invite_id);
        $invite_title = get_the_title($invite_id);
        $invite_url = get_permalink($invite_id);
        $edit_url = home_url("/rsvp-edit/?token=$token");

        $subject = "Conferma RSVP - $invite_title";

        $message = "
Ciao $name,

Grazie per aver risposto all'invito!

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 Riepilogo Risposta
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Evento: $invite_title
Stato: {$status_labels[$status]}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Puoi modificare la tua risposta in qualsiasi momento:
👉 $edit_url

Visualizza l'invito completo:
👉 $invite_url

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

A presto!
";

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        $sent = wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            wi_log('RSVP confirmation email sent', 'info', array('to' => $email));
        } else {
            wi_log('RSVP confirmation email failed', 'error', array('to' => $email));
        }

        return $sent;
    }

    /**
     * Notifica admin nuova risposta
     */
    private static function notify_admin($invite_id, $guest_name, $status, $admin_email = null) {
        if (!$admin_email) {
            $admin_email = get_option('admin_email');
        }

        $invite_title = get_the_title($invite_id);
        $admin_url = admin_url("admin.php?page=wi-rsvp&invite_id=$invite_id");

        $status_emoji = array(
            'attending' => '✅',
            'not_attending' => '❌',
            'maybe' => '❓'
        );

        $subject = "{$status_emoji[$status]} Nuova risposta RSVP da $guest_name";

        $message = "
Nuova risposta RSVP ricevuta per: $invite_title

Ospite: $guest_name
Stato: {$status_emoji[$status]} $status

Vedi tutte le risposte nel pannello admin:
$admin_url
";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * AJAX: Ottieni statistiche RSVP
     */
    public static function ajax_get_rsvp_stats() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }

        $invite_id = intval($_POST['invite_id']);
        $stats = self::get_stats($invite_id);

        wp_send_json_success($stats);
    }

    /**
     * Ottieni statistiche per multipli inviti in una query (ottimizzazione N+1)
     */
    public static function get_bulk_stats($invite_ids) {
        if (empty($invite_ids)) {
            return array();
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';
        $placeholders = implode(',', array_fill(0, count($invite_ids), '%d'));

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT
                invite_id,
                COUNT(*) as total_responses,
                SUM(CASE WHEN status = 'attending' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'not_attending' THEN 1 ELSE 0 END) as declined,
                SUM(CASE WHEN status = 'maybe' THEN 1 ELSE 0 END) as maybe,
                SUM(CASE WHEN status = 'attending' THEN num_guests ELSE 0 END) as total_guests
            FROM $table
            WHERE invite_id IN ($placeholders)
            GROUP BY invite_id
        ", ...$invite_ids), ARRAY_A);

        // Organizza risultati per invite_id
        $stats_by_invite = array();
        foreach ($results as $row) {
            $stats_by_invite[$row['invite_id']] = array(
                'summary' => $row,
                'menu' => array(),
                'dietary' => array()
            );
        }

        // Aggiungi entry vuote per inviti senza risposte
        foreach ($invite_ids as $invite_id) {
            if (!isset($stats_by_invite[$invite_id])) {
                $stats_by_invite[$invite_id] = array(
                    'summary' => array(
                        'invite_id' => $invite_id,
                        'total_responses' => 0,
                        'confirmed' => 0,
                        'declined' => 0,
                        'maybe' => 0,
                        'total_guests' => 0
                    ),
                    'menu' => array(),
                    'dietary' => array()
                );
            }
        }

        return $stats_by_invite;
    }

    /**
     * Ottieni statistiche RSVP per un invito (con caching)
     */
    public static function get_stats($invite_id) {
        // Prova cache prima (5 minuti)
        $cache_key = 'wi_rsvp_stats_' . $invite_id;
        $cached = wp_cache_get($cache_key, 'wedding_invites');

        if (false !== $cached) {
            return $cached;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';

        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total_responses,
                SUM(CASE WHEN status = 'attending' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'not_attending' THEN 1 ELSE 0 END) as declined,
                SUM(CASE WHEN status = 'maybe' THEN 1 ELSE 0 END) as maybe,
                SUM(CASE WHEN status = 'attending' THEN num_guests ELSE 0 END) as total_guests
            FROM $table
            WHERE invite_id = %d
        ", $invite_id), ARRAY_A);

        // Statistiche menu
        $menu_stats = $wpdb->get_results($wpdb->prepare("
            SELECT menu_choice, COUNT(*) as count
            FROM $table
            WHERE invite_id = %d AND status = 'attending' AND menu_choice IS NOT NULL AND menu_choice != ''
            GROUP BY menu_choice
        ", $invite_id), ARRAY_A);

        // Statistiche allergie
        $dietary_stats = array();
        $responses_with_dietary = $wpdb->get_results($wpdb->prepare("
            SELECT dietary_preferences
            FROM $table
            WHERE invite_id = %d AND status = 'attending' AND dietary_preferences IS NOT NULL
        ", $invite_id));

        foreach ($responses_with_dietary as $response) {
            $prefs = json_decode($response->dietary_preferences, true);
            if (is_array($prefs)) {
                foreach ($prefs as $pref) {
                    $dietary_stats[$pref] = ($dietary_stats[$pref] ?? 0) + 1;
                }
            }
        }

        $result = array(
            'summary' => $stats,
            'menu' => $menu_stats,
            'dietary' => $dietary_stats
        );

        // Salva in cache per 5 minuti
        wp_cache_set($cache_key, $result, 'wedding_invites', 5 * MINUTE_IN_SECONDS);

        return $result;
    }

    /**
     * Ottieni tutte le risposte per un invito
     */
    public static function get_responses($invite_id, $args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';

        $defaults = array(
            'status' => null,
            'orderby' => 'responded_at',
            'order' => 'DESC',
            'limit' => null,
            'offset' => 0
        );

        $args = wp_parse_args($args, $defaults);

        $where = $wpdb->prepare("invite_id = %d", $invite_id);

        if ($args['status']) {
            $where .= $wpdb->prepare(" AND status = %s", $args['status']);
        }

        $order = sprintf(
            "ORDER BY %s %s",
            sanitize_sql_orderby($args['orderby']),
            strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC'
        );

        $limit = '';
        if ($args['limit']) {
            $limit = $wpdb->prepare("LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
        }

        $query = "SELECT * FROM $table WHERE $where $order $limit";

        return $wpdb->get_results($query);
    }

    /**
     * Elimina risposta RSVP
     */
    public static function delete_response($response_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';

        return $wpdb->delete($table, array('id' => $response_id), array('%d'));
    }

    /**
     * AJAX: Elimina risposta RSVP
     */
    public static function ajax_delete_rsvp_response() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti');
        }

        $response_id = intval($_POST['response_id']);
        $result = self::delete_response($response_id);

        if ($result) {
            wp_send_json_success('Risposta eliminata');
        } else {
            wp_send_json_error('Errore eliminazione');
        }
    }

    /**
     * Esporta risposte RSVP in CSV
     */
    public static function export_csv($invite_id) {
        $responses = self::get_responses($invite_id);

        $filename = 'rsvp-' . sanitize_file_name(get_the_title($invite_id)) . '-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Intestazioni CSV
        fputcsv($output, array(
            'Nome',
            'Email',
            'Telefono',
            'Status',
            'Numero Ospiti',
            'Scelta Menu',
            'Allergie/Intolleranze',
            'Note',
            'Data Risposta'
        ));

        foreach ($responses as $response) {
            $dietary = json_decode($response->dietary_preferences, true);
            $dietary_str = $dietary ? implode(', ', $dietary) : '';

            fputcsv($output, array(
                $response->guest_name,
                $response->guest_email,
                $response->guest_phone,
                $response->status,
                $response->num_guests,
                $response->menu_choice,
                $dietary_str,
                $response->notes,
                $response->responded_at
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX: Esporta CSV
     */
    public static function ajax_export_rsvp_csv() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti');
        }

        $invite_id = intval($_GET['invite_id']);
        self::export_csv($invite_id);
    }

    /**
     * Render form modifica RSVP (tramite token)
     */
    public static function render_edit_form() {
        $token = sanitize_text_field($_GET['token'] ?? '');

        if (empty($token)) {
            return '<p>Token non valido.</p>';
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_responses';

        $response = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE token = %s",
            $token
        ));

        if (!$response) {
            return '<p>Risposta RSVP non trovata.</p>';
        }

        // Render form pre-compilato
        ob_start();
        include WI_PLUGIN_DIR . 'templates/rsvp-edit-form.php';
        return ob_get_clean();
    }
}
