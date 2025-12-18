<?php
/**
 * Migrazione: Aggiungi colonne mancanti alla tabella templates
 * Per permettere modifica/creazione template da editor unificato
 */

if (!defined('ABSPATH')) exit;

class WI_Migration_Template_Columns {

    /**
     * Esegue la migrazione aggiungendo tutte le colonne mancanti
     */
    public static function run() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        error_log("=== WI Template Columns Migration START ===");

        // Ottieni le colonne esistenti
        $existing_columns = $wpdb->get_col("DESCRIBE {$table}", 0);
        error_log("Colonne esistenti: " . implode(', ', $existing_columns));

        // Mappa delle nuove colonne da aggiungere
        $new_columns = array(
            // Font Titolo
            'title_weight' => "varchar(10) DEFAULT '600' AFTER title_color",

            // Font Pulsanti
            'button_font' => "varchar(100) DEFAULT 'inherit' AFTER button_text_color",
            'button_size' => "int(11) DEFAULT 16 AFTER button_font",
            'button_color' => "varchar(50) DEFAULT '#ffffff' AFTER button_size",
            'button_hover_color' => "varchar(50) DEFAULT '#ffffff' AFTER button_bg_color",
            'button_hover_bg_color' => "varchar(50) DEFAULT '#b8941f' AFTER button_hover_color",
            'button_radius' => "int(11) DEFAULT 25 AFTER button_hover_bg_color",
            'button_padding' => "int(11) DEFAULT 15 AFTER button_radius",

            // Countdown avanzato
            'countdown_size' => "int(11) DEFAULT 48 AFTER countdown_font",
            'countdown_border_color' => "varchar(50) DEFAULT '#e2e8f0' AFTER countdown_bg_color",
            'countdown_label_font' => "varchar(100) DEFAULT 'Montserrat' AFTER countdown_border_color",
            'countdown_label_size' => "int(11) DEFAULT 14 AFTER countdown_label_font",
            'countdown_label_color' => "varchar(50) DEFAULT '#64748b' AFTER countdown_label_size",

            // Dettagli evento avanzato
            'details_size' => "int(11) DEFAULT 16 AFTER details_font",
            'details_border_color' => "varchar(50) DEFAULT '#d4af37' AFTER details_bg_color",
            'details_align' => "varchar(10) DEFAULT 'left' AFTER details_border_color",
            'details_label_color' => "varchar(50) DEFAULT '#333333' AFTER details_align",
            'details_value_color' => "varchar(50) DEFAULT '#666666' AFTER details_label_color",
            'hide_event_icons' => "tinyint(1) DEFAULT 0 AFTER details_value_color",

            // Footer logo avanzato
            'footer_logo_size' => "int(11) DEFAULT 100 AFTER footer_logo",
            'footer_logo_opacity' => "decimal(3,2) DEFAULT 1.00 AFTER footer_logo_size",

            // Background avanzato
            'background_main_opacity' => "decimal(3,2) DEFAULT 1.00 AFTER background_opacity",
            'overlay_color' => "varchar(50) DEFAULT '#000000' AFTER background_main_opacity",
            'overlay_opacity' => "decimal(3,2) DEFAULT 0.30 AFTER overlay_color",

            // Elementi UI
            'divider_color' => "varchar(50) DEFAULT '#d4af37' AFTER title_color",
            'final_message_btn_bg_color' => "varchar(50) DEFAULT '#d4af37' AFTER button_text_color",
            'final_message_btn_text_color' => "varchar(50) DEFAULT '#ffffff' AFTER final_message_btn_bg_color",
            'final_message_text_color' => "varchar(50) DEFAULT '#333333' AFTER final_message_btn_text_color",
        );

        $added_count = 0;
        $skipped_count = 0;

        foreach ($new_columns as $column_name => $column_definition) {
            // Verifica se la colonna esiste già
            if (in_array($column_name, $existing_columns)) {
                error_log("✓ Colonna '$column_name' già esistente, skip");
                $skipped_count++;
                continue;
            }

            // Aggiungi la colonna
            $sql = "ALTER TABLE {$table} ADD COLUMN {$column_name} {$column_definition}";
            error_log("Esecuzione: $sql");

            $result = $wpdb->query($sql);

            if ($result === false) {
                error_log("✗ ERRORE aggiunta colonna '$column_name': " . $wpdb->last_error);
            } else {
                error_log("✓ Colonna '$column_name' aggiunta con successo");
                $added_count++;
            }
        }

        error_log("=== WI Template Columns Migration END ===");
        error_log("Totale colonne aggiunte: $added_count");
        error_log("Totale colonne già presenti: $skipped_count");

        return array(
            'success' => true,
            'added' => $added_count,
            'skipped' => $skipped_count,
            'message' => "Migrazione completata: $added_count colonne aggiunte, $skipped_count già presenti"
        );
    }

    /**
     * Verifica lo stato della migrazione
     */
    public static function get_status() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        $required_columns = array(
            'title_weight', 'button_font', 'button_size', 'button_color',
            'button_hover_color', 'button_hover_bg_color', 'button_radius', 'button_padding',
            'countdown_size', 'countdown_border_color', 'countdown_label_font', 'countdown_label_size', 'countdown_label_color',
            'details_size', 'details_border_color', 'details_align', 'details_label_color', 'details_value_color', 'hide_event_icons',
            'footer_logo_size', 'footer_logo_opacity',
            'background_main_opacity', 'overlay_color', 'overlay_opacity',
            'divider_color', 'final_message_btn_bg_color', 'final_message_btn_text_color', 'final_message_text_color'
        );

        $existing_columns = $wpdb->get_col("DESCRIBE {$table}", 0);
        $missing_columns = array_diff($required_columns, $existing_columns);

        return array(
            'migration_needed' => !empty($missing_columns),
            'missing_columns' => $missing_columns,
            'total_required' => count($required_columns),
            'total_existing' => count(array_intersect($required_columns, $existing_columns))
        );
    }
}
