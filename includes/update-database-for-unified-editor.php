<?php
/**
 * Script di migrazione database per Editor Unificato
 * Aggiunge tutti i campi mancanti necessari per l'editor unificato
 */

if (!defined('ABSPATH')) exit;

function wi_add_missing_fields_for_unified_editor() {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_templates';

    // Lista campi da aggiungere
    $fields_to_add = array(
        // Peso font titolo
        array(
            'name' => 'title_weight',
            'definition' => "VARCHAR(10) DEFAULT '600'"
        ),

        // Dimensioni dettagli
        array(
            'name' => 'details_size',
            'definition' => "INT DEFAULT 16"
        ),

        // Pulsanti completi
        array(
            'name' => 'button_font',
            'definition' => "VARCHAR(100) DEFAULT 'inherit'"
        ),
        array(
            'name' => 'button_size',
            'definition' => "INT DEFAULT 16"
        ),
        array(
            'name' => 'button_color',
            'definition' => "VARCHAR(50) DEFAULT '#ffffff'"
        ),
        array(
            'name' => 'button_hover_color',
            'definition' => "VARCHAR(50) DEFAULT '#ffffff'"
        ),
        array(
            'name' => 'button_hover_bg_color',
            'definition' => "VARCHAR(50) DEFAULT '#b8941f'"
        ),
        array(
            'name' => 'button_radius',
            'definition' => "INT DEFAULT 25"
        ),
        array(
            'name' => 'button_padding',
            'definition' => "INT DEFAULT 15"
        ),

        // Countdown completo
        array(
            'name' => 'countdown_size',
            'definition' => "INT DEFAULT 48"
        ),
        array(
            'name' => 'countdown_label_font',
            'definition' => "VARCHAR(100) DEFAULT 'Montserrat'"
        ),
        array(
            'name' => 'countdown_label_size',
            'definition' => "INT DEFAULT 14"
        ),

        // Logo finale con dimensioni e opacità
        array(
            'name' => 'footer_logo_size',
            'definition' => "INT DEFAULT 100"
        ),
        array(
            'name' => 'footer_logo_opacity',
            'definition' => "DECIMAL(3,2) DEFAULT 1.00"
        ),

        // Sfondo principale opacità
        array(
            'name' => 'background_main_opacity',
            'definition' => "DECIMAL(3,2) DEFAULT 1.00"
        ),

        // Overlay sfondo
        array(
            'name' => 'overlay_color',
            'definition' => "VARCHAR(50) DEFAULT '#000000'"
        ),
        array(
            'name' => 'overlay_opacity',
            'definition' => "DECIMAL(3,2) DEFAULT 0.30"
        ),
    );

    // Verifica e aggiunge ogni campo se mancante
    foreach ($fields_to_add as $field) {
        // Non possiamo usare prepared statement con SHOW COLUMNS
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$table} LIKE '{$field['name']}'"
        );

        if (empty($column_exists)) {
            $sql = "ALTER TABLE {$table} ADD COLUMN {$field['name']} {$field['definition']}";
            $result = $wpdb->query($sql);

            if ($wpdb->last_error) {
                error_log("WI Editor Unificato - Errore aggiunta campo {$field['name']}: " . $wpdb->last_error);
            } else {
                error_log("WI Editor Unificato - Campo {$field['name']} aggiunto con successo");
            }
        } else {
            error_log("WI Editor Unificato - Campo {$field['name']} già esistente");
        }
    }

    return true;
}

// Esegui migrazione se richiesto
if (isset($_GET['wi_update_db_unified_editor']) && current_user_can('manage_options')) {
    wi_add_missing_fields_for_unified_editor();
    wp_redirect(admin_url('admin.php?page=wedding-invites-templates&db_updated=1'));
    exit;
}
