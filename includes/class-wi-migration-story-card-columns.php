<?php
/**
 * Migrazione Database: Aggiunta colonna invite_template_id a Story Card Templates
 *
 * Questa migrazione aggiunge la colonna invite_template_id alla tabella wp_wi_story_card_templates
 * per supportare il sistema a cascata di priorità (Template ID → Categoria → Default)
 *
 * @package Wedding_Invites_Pro
 * @version 2.5.1
 */

if (!defined('ABSPATH')) exit;

class WI_Migration_Story_Card_Columns {

    /**
     * Esegue la migrazione
     *
     * @return array Risultato della migrazione con 'success' e 'message'
     */
    public static function run() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_story_card_templates';

        // Verifica se la tabella esiste
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;

        if (!$table_exists) {
            return array(
                'success' => false,
                'message' => 'Tabella Story Card non trovata. Assicurati che il plugin sia attivato correttamente.'
            );
        }

        // Verifica se la colonna esiste già
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE 'invite_template_id'");

        if (!empty($column_exists)) {
            return array(
                'success' => true,
                'message' => 'Colonna invite_template_id già presente. Migrazione non necessaria.'
            );
        }

        // Aggiungi colonna invite_template_id
        $sql_add_column = "
            ALTER TABLE `$table`
            ADD COLUMN `invite_template_id` bigint(20) DEFAULT NULL
            COMMENT 'Priorità 1: template invito specifico'
            AFTER `category_id`
        ";

        $result_column = $wpdb->query($sql_add_column);

        if ($result_column === false) {
            return array(
                'success' => false,
                'message' => 'Errore durante l\'aggiunta della colonna invite_template_id: ' . $wpdb->last_error
            );
        }

        // Aggiungi indice per performance
        $sql_add_index = "
            ALTER TABLE `$table`
            ADD KEY `invite_template_id` (`invite_template_id`)
        ";

        $result_index = $wpdb->query($sql_add_index);

        // L'indice può fallire se esiste già, ma non è critico
        // Non blocchiamo la migrazione per questo

        // Marca migrazione come completata
        update_option('wi_story_card_columns_migrated', true);
        update_option('wi_story_card_migration_date', current_time('mysql'));
        update_option('wi_story_card_migration_version', WI_VERSION);

        return array(
            'success' => true,
            'message' => '✅ Migrazione completata! Colonna invite_template_id aggiunta alla tabella Story Card Templates.'
        );
    }

    /**
     * Verifica se la migrazione è necessaria
     *
     * @return array Status della migrazione con 'migration_needed' e 'details'
     */
    public static function get_status() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_story_card_templates';

        // Se già migrato via option
        if (get_option('wi_story_card_columns_migrated')) {
            return array(
                'migration_needed' => false,
                'details' => 'Migrazione già completata in precedenza.'
            );
        }

        // Verifica se la tabella esiste
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;

        if (!$table_exists) {
            return array(
                'migration_needed' => false,
                'details' => 'Tabella Story Card non esiste ancora. Verrà creata automaticamente all\'attivazione.'
            );
        }

        // Verifica se la colonna esiste
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE 'invite_template_id'");

        if (!empty($column_exists)) {
            // Colonna esiste ma option non settata → marca come migrata
            update_option('wi_story_card_columns_migrated', true);
            update_option('wi_story_card_migration_date', current_time('mysql'));

            return array(
                'migration_needed' => false,
                'details' => 'Colonna invite_template_id già presente.'
            );
        }

        // Migrazione necessaria
        return array(
            'migration_needed' => true,
            'details' => 'La colonna invite_template_id deve essere aggiunta alla tabella Story Card.'
        );
    }

    /**
     * Rollback della migrazione (per testing o emergenze)
     *
     * @return array Risultato del rollback
     */
    public static function rollback() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_story_card_templates';

        // Verifica permessi
        if (!current_user_can('manage_options')) {
            return array(
                'success' => false,
                'message' => 'Permessi insufficienti per eseguire il rollback.'
            );
        }

        // Rimuovi colonna
        $sql = "ALTER TABLE `$table` DROP COLUMN `invite_template_id`";
        $result = $wpdb->query($sql);

        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'Errore durante il rollback: ' . $wpdb->last_error
            );
        }

        // Rimuovi opzioni
        delete_option('wi_story_card_columns_migrated');
        delete_option('wi_story_card_migration_date');
        delete_option('wi_story_card_migration_version');

        return array(
            'success' => true,
            'message' => 'Rollback completato. Colonna invite_template_id rimossa.'
        );
    }

    /**
     * Verifica integrità post-migrazione
     *
     * @return array Risultato della verifica
     */
    public static function verify_integrity() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_story_card_templates';

        $issues = array();

        // 1. Verifica colonna esiste
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE 'invite_template_id'");
        if (empty($column_exists)) {
            $issues[] = 'Colonna invite_template_id non trovata';
        }

        // 2. Verifica tipo colonna corretto
        if (!empty($column_exists)) {
            $column_info = $column_exists[0];
            if ($column_info->Type !== 'bigint(20)') {
                $issues[] = 'Tipo colonna non corretto (atteso: bigint(20), trovato: ' . $column_info->Type . ')';
            }
            if ($column_info->Null !== 'YES') {
                $issues[] = 'Colonna non permette NULL (dovrebbe permetterlo)';
            }
        }

        // 3. Verifica indice esiste
        $indexes = $wpdb->get_results("SHOW INDEX FROM `$table` WHERE Column_name = 'invite_template_id'");
        if (empty($indexes)) {
            $issues[] = 'Indice su invite_template_id non trovato (non critico ma consigliato)';
        }

        // 4. Verifica referential integrity (Story Card con template inesistente)
        $invalid_refs = $wpdb->get_results("
            SELECT sc.id, sc.name, sc.invite_template_id
            FROM `$table` sc
            LEFT JOIN `{$wpdb->prefix}wi_templates` t ON sc.invite_template_id = t.id
            WHERE sc.invite_template_id IS NOT NULL
              AND t.id IS NULL
        ");

        if (!empty($invalid_refs)) {
            $issues[] = 'Trovate ' . count($invalid_refs) . ' Story Card con template invito inesistente';
        }

        if (empty($issues)) {
            return array(
                'success' => true,
                'message' => '✅ Integrità database verificata. Tutto OK!',
                'details' => array(
                    'column_exists' => true,
                    'correct_type' => true,
                    'index_exists' => !empty($indexes),
                    'no_orphaned_refs' => true
                )
            );
        } else {
            return array(
                'success' => false,
                'message' => '⚠️ Verificati problemi di integrità',
                'issues' => $issues
            );
        }
    }
}
