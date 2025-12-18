<?php
/**
 * Classe per gestione database RSVP
 * Crea e gestisce le tabelle per il sistema di conferma presenza
 */

if (!defined('ABSPATH')) exit;

class WI_RSVP_Database {

    /**
     * Crea le tabelle RSVP se non esistono
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabella risposte RSVP
        $responses_table = $wpdb->prefix . 'wi_rsvp_responses';

        $sql_responses = "CREATE TABLE IF NOT EXISTS $responses_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invite_id BIGINT UNSIGNED NOT NULL,
            guest_name VARCHAR(255) NOT NULL,
            guest_email VARCHAR(255) NOT NULL,
            guest_phone VARCHAR(50) DEFAULT NULL,
            status ENUM('attending', 'not_attending', 'maybe') NOT NULL,
            num_guests INT DEFAULT 1,
            dietary_preferences TEXT DEFAULT NULL,
            menu_choice VARCHAR(100) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            responded_at DATETIME NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            token VARCHAR(64) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_invite (invite_id),
            INDEX idx_email (guest_email),
            INDEX idx_status (status),
            INDEX idx_token (token),
            INDEX idx_responded_at (responded_at)
        ) $charset_collate;";

        // Tabella impostazioni RSVP per ogni invito
        $settings_table = $wpdb->prefix . 'wi_rsvp_settings';

        $sql_settings = "CREATE TABLE IF NOT EXISTS $settings_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            invite_id BIGINT UNSIGNED NOT NULL UNIQUE,
            rsvp_enabled TINYINT(1) DEFAULT 1,
            rsvp_deadline DATE DEFAULT NULL,
            max_guests_per_response INT DEFAULT 5,
            menu_choices JSON DEFAULT NULL,
            custom_questions JSON DEFAULT NULL,
            confirmation_message TEXT DEFAULT NULL,
            notify_admin TINYINT(1) DEFAULT 1,
            admin_email VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_invite (invite_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql_responses);
        dbDelta($sql_settings);

        wi_log('RSVP tables created', 'info');

        return true;
    }

    /**
     * Verifica se le tabelle esistono
     */
    public static function tables_exist() {
        global $wpdb;

        $responses_table = $wpdb->prefix . 'wi_rsvp_responses';
        $settings_table = $wpdb->prefix . 'wi_rsvp_settings';

        $responses_exists = $wpdb->get_var("SHOW TABLES LIKE '$responses_table'") === $responses_table;
        $settings_exists = $wpdb->get_var("SHOW TABLES LIKE '$settings_table'") === $settings_table;

        return $responses_exists && $settings_exists;
    }

    /**
     * Elimina le tabelle RSVP (per testing o disinstallazione)
     */
    public static function drop_tables() {
        global $wpdb;

        $responses_table = $wpdb->prefix . 'wi_rsvp_responses';
        $settings_table = $wpdb->prefix . 'wi_rsvp_settings';

        $wpdb->query("DROP TABLE IF EXISTS $responses_table");
        $wpdb->query("DROP TABLE IF EXISTS $settings_table");

        wi_log('RSVP tables dropped', 'warning');
    }

    /**
     * Ottieni impostazioni RSVP per un invito
     */
    public static function get_settings($invite_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_settings';

        $settings = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE invite_id = %d",
            $invite_id
        ));

        // Se non esistono impostazioni, crea default
        if (!$settings) {
            return self::create_default_settings($invite_id);
        }

        return $settings;
    }

    /**
     * Crea impostazioni RSVP default per un invito
     */
    private static function create_default_settings($invite_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_settings';

        $defaults = array(
            'invite_id' => $invite_id,
            'rsvp_enabled' => 1,
            'rsvp_deadline' => null,
            'max_guests_per_response' => 5,
            'menu_choices' => json_encode(['Carne', 'Pesce', 'Vegetariano']),
            'notify_admin' => 1,
            'admin_email' => get_option('admin_email')
        );

        $wpdb->insert($table, $defaults);

        return (object) $defaults;
    }

    /**
     * Aggiorna impostazioni RSVP
     */
    public static function update_settings($invite_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_rsvp_settings';

        // Controlla se esistono già impostazioni
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE invite_id = %d",
            $invite_id
        ));

        if ($existing) {
            return $wpdb->update($table, $data, array('invite_id' => $invite_id));
        } else {
            $data['invite_id'] = $invite_id;
            return $wpdb->insert($table, $data);
        }
    }

    /**
     * Salva impostazioni RSVP (alias di update_settings per compatibilità)
     * FIX v2.5.2: Aggiunto metodo save_settings() mancante chiamato da edit-invite.php
     */
    public static function save_settings($invite_id, $data) {
        return self::update_settings($invite_id, $data);
    }
}
