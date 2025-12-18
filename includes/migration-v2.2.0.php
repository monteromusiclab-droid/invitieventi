<?php
/**
 * Script di Migrazione Completo v2.2.0
 * Coordina tutte le migrazioni necessarie per aggiornare da v2.1.0 a v2.2.0
 *
 * IMPORTANTE: Esegui questo script UNA SOLA VOLTA dopo l'aggiornamento
 */

if (!defined('ABSPATH')) exit;

class WI_Migration_v220 {

    /**
     * Esegue la migrazione completa v2.2.0
     */
    public static function run() {
        global $wpdb;

        error_log('🚀 Inizio migrazione v2.2.0');

        // Step 1: Crea nuove tabelle categorie
        self::step1_create_category_tables();

        // Step 2: Aggiungi colonne section_title alla tabella templates
        self::step2_add_section_title_columns();

        // Step 3: Migra categorie da CSV a tabelle relazionali
        self::step3_migrate_categories();

        error_log('✅ Migrazione v2.2.0 completata con successo!');

        return true;
    }

    /**
     * Step 1: Crea tabelle categorie
     */
    private static function step1_create_category_tables() {
        error_log('📂 Step 1: Creazione tabelle categorie...');

        // Crea tabella categorie eventi
        WI_Database::create_categories_table();

        // Crea tabella relazioni template-categorie
        WI_Database::create_template_categories_table();

        error_log('✅ Step 1 completato: tabelle categorie create');
    }

    /**
     * Step 2: Aggiungi colonne CSS section_title
     */
    private static function step2_add_section_title_columns() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        error_log('🎨 Step 2: Aggiunta colonne CSS section_title...');

        // Verifica se le colonne esistono già
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'section_title_%'");

        if (count($columns) < 4) {
            // Aggiungi colonne
            $wpdb->query("
                ALTER TABLE $table
                ADD COLUMN IF NOT EXISTS section_title_font VARCHAR(100) DEFAULT 'inherit',
                ADD COLUMN IF NOT EXISTS section_title_size INT(11) DEFAULT 28,
                ADD COLUMN IF NOT EXISTS section_title_color VARCHAR(50) DEFAULT '#2c3e50',
                ADD COLUMN IF NOT EXISTS section_title_weight VARCHAR(10) DEFAULT '600'
            ");

            error_log('✅ Step 2 completato: colonne CSS section_title aggiunte');
        } else {
            error_log('ℹ️ Step 2 skippato: colonne già esistenti');
        }
    }

    /**
     * Step 3: Migra categorie da CSV a tabelle relazionali
     */
    private static function step3_migrate_categories() {
        error_log('🔄 Step 3: Migrazione categorie CSV → tabelle...');

        // Include script migrazione categorie
        require_once WI_PLUGIN_DIR . 'includes/migration-categories-to-table.php';

        // Esegui migrazione
        WI_Migration_Categories_To_Table::run();

        error_log('✅ Step 3 completato: categorie migrate');
    }

    /**
     * Rollback completo migrazione (solo per debug/test)
     */
    public static function rollback() {
        global $wpdb;

        error_log('↩️ Rollback migrazione v2.2.0...');

        // Rimuovi colonne section_title
        $table = $wpdb->prefix . 'wi_templates';
        $wpdb->query("
            ALTER TABLE $table
            DROP COLUMN IF EXISTS section_title_font,
            DROP COLUMN IF EXISTS section_title_size,
            DROP COLUMN IF EXISTS section_title_color,
            DROP COLUMN IF EXISTS section_title_weight
        ");

        // Rimuovi tabelle categorie
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $relations_table = $wpdb->prefix . 'wi_template_categories';

        $wpdb->query("DROP TABLE IF EXISTS $relations_table");
        $wpdb->query("DROP TABLE IF EXISTS $categories_table");

        error_log('✅ Rollback completato');
    }

    /**
     * Verifica se la migrazione è necessaria
     */
    public static function is_migration_needed() {
        global $wpdb;

        // Verifica se tabelle categorie esistono
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$categories_table'");

        return ($table_exists === null);
    }

    /**
     * Verifica stato migrazione
     */
    public static function get_migration_status() {
        global $wpdb;

        $status = array(
            'categories_table_exists' => false,
            'relations_table_exists' => false,
            'section_title_columns_exist' => false,
            'categories_count' => 0,
            'relations_count' => 0
        );

        // Check tabella categorie
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $status['categories_table_exists'] = ($wpdb->get_var("SHOW TABLES LIKE '$categories_table'") !== null);

        if ($status['categories_table_exists']) {
            $status['categories_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $categories_table");
        }

        // Check tabella relazioni
        $relations_table = $wpdb->prefix . 'wi_template_categories';
        $status['relations_table_exists'] = ($wpdb->get_var("SHOW TABLES LIKE '$relations_table'") !== null);

        if ($status['relations_table_exists']) {
            $status['relations_count'] = $wpdb->get_var("SELECT COUNT(*) FROM $relations_table");
        }

        // Check colonne section_title
        $templates_table = $wpdb->prefix . 'wi_templates';
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $templates_table LIKE 'section_title_%'");
        $status['section_title_columns_exist'] = (count($columns) >= 4);

        return $status;
    }
}

// Esecuzione automatica se chiamato via URL
if (isset($_GET['run_migration']) && $_GET['run_migration'] === 'v220') {
    if (!current_user_can('manage_options')) {
        wp_die('Non hai i permessi per eseguire questa migrazione.');
    }

    WI_Migration_v220::run();

    $status = WI_Migration_v220::get_migration_status();

    echo '<div style="padding:30px;background:#f0f9ff;font-family:sans-serif;border-radius:12px;margin:30px;max-width:900px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">';
    echo '<h1 style="margin:0 0 20px 0;color:#0369a1;display:flex;align-items:center;gap:10px;">';
    echo '<span style="font-size:32px;">✅</span> Migrazione v2.2.0 Completata!';
    echo '</h1>';

    echo '<div style="background:white;padding:20px;border-radius:8px;margin-bottom:20px;">';
    echo '<h3 style="margin:0 0 15px 0;color:#334155;">📊 Stato Migrazione:</h3>';
    echo '<ul style="list-style:none;padding:0;margin:0;">';
    echo '<li style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Tabella Categorie:</strong> ' . ($status['categories_table_exists'] ? '✅ Creata (' . $status['categories_count'] . ' categorie)' : '❌ Mancante') . '</li>';
    echo '<li style="padding:8px 0;border-bottom:1px solid #e2e8f0;"><strong>Tabella Relazioni:</strong> ' . ($status['relations_table_exists'] ? '✅ Creata (' . $status['relations_count'] . ' relazioni)' : '❌ Mancante') . '</li>';
    echo '<li style="padding:8px 0;"><strong>Colonne Section Title:</strong> ' . ($status['section_title_columns_exist'] ? '✅ Aggiunte' : '❌ Mancanti') . '</li>';
    echo '</ul>';
    echo '</div>';

    echo '<div style="background:#ecfdf5;padding:20px;border-radius:8px;border-left:4px solid #10b981;margin-bottom:20px;">';
    echo '<h3 style="margin:0 0 15px 0;color:#047857;">✨ Nuove Funzionalità v2.2.0:</h3>';
    echo '<ul style="margin:0;padding-left:20px;">';
    echo '<li><strong>Sistema Categorie Avanzato</strong>: 12 categorie predefinite con gestione backend</li>';
    echo '<li><strong>Checkbox Categorie Template</strong>: Assegna multiple categorie ai template</li>';
    echo '<li><strong>Editor CSS Section Title</strong>: Personalizza font, dimensione, colore e peso</li>';
    echo '<li><strong>5 Nuovi Font</strong>: Poppins, Raleway, Merriweather, Crimson Text, Bebas Neue</li>';
    echo '<li><strong>Countdown Ottimizzato</strong>: Template 16-20 centrati e ingranditi</li>';
    echo '<li><strong>Preview Scrollabile</strong>: Visualizza invito completo prima di pubblicare</li>';
    echo '</ul>';
    echo '</div>';

    echo '<p style="margin:20px 0;display:flex;gap:15px;">';
    echo '<a href="' . admin_url('admin.php?page=wedding-invites-categories') . '" class="button button-primary" style="text-decoration:none;">📂 Gestisci Categorie</a>';
    echo '<a href="' . admin_url('admin.php?page=wedding-invites-templates') . '" class="button" style="text-decoration:none;">🎨 Modifica Template</a>';
    echo '<a href="' . admin_url('admin.php?page=wedding-invites') . '" class="button" style="text-decoration:none;">← Torna agli Inviti</a>';
    echo '</p>';

    echo '</div>';
    exit;
}
