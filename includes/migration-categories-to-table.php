<?php
/**
 * Script di Migrazione - Conversione Categorie da CSV a Tabelle Relazionali
 * Versione: 2.2.0
 *
 * IMPORTANTE: Esegui questo script UNA SOLA VOLTA dopo l'aggiornamento da v2.1.0 a v2.2.0
 */

if (!defined('ABSPATH')) exit;

class WI_Migration_Categories_To_Table {

    /**
     * Esegue la migrazione completa
     */
    public static function run() {
        global $wpdb;

        // Step 1: Crea nuove tabelle se non esistono
        WI_Database::create_categories_table();
        WI_Database::create_template_categories_table();

        // Step 2: Migra categorie da stringa CSV a relazioni
        self::migrate_template_categories();

        // Step 3: Log completamento
        error_log('✅ Migrazione v2.2.0 completata: categorie migrate in tabella relazionale');

        return true;
    }

    /**
     * Migra le categorie dai template (CSV) alle tabelle relazionali
     */
    private static function migrate_template_categories() {
        global $wpdb;

        $templates_table = $wpdb->prefix . 'wi_templates';
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $relations_table = $wpdb->prefix . 'wi_template_categories';

        // Ottieni tutti i template con categorie
        $templates = $wpdb->get_results("SELECT id, category FROM $templates_table WHERE category IS NOT NULL AND category != ''");

        $migrated_count = 0;

        foreach ($templates as $template) {
            if (empty($template->category)) continue;

            // Split categorie da CSV
            $category_slugs = array_map('trim', explode(',', $template->category));

            foreach ($category_slugs as $slug) {
                // Trova ID categoria dal slug
                $category = $wpdb->get_row($wpdb->prepare(
                    "SELECT id FROM $categories_table WHERE slug = %s",
                    $slug
                ));

                if ($category) {
                    // Verifica se relazione esiste già
                    $exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM $relations_table WHERE template_id = %d AND category_id = %d",
                        $template->id,
                        $category->id
                    ));

                    if (!$exists) {
                        // Inserisci relazione
                        $inserted = $wpdb->insert($relations_table, array(
                            'template_id' => $template->id,
                            'category_id' => $category->id
                        ));

                        if ($inserted) {
                            $migrated_count++;
                        }
                    }
                } else {
                    error_log("⚠️ Categoria slug '$slug' non trovata per template ID {$template->id}");
                }
            }
        }

        error_log("✅ Migrate $migrated_count relazioni template-categoria");
    }

    /**
     * Rollback della migrazione (opzionale per debug)
     */
    public static function rollback() {
        global $wpdb;

        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $relations_table = $wpdb->prefix . 'wi_template_categories';

        // Elimina tabelle
        $wpdb->query("DROP TABLE IF EXISTS $relations_table");
        $wpdb->query("DROP TABLE IF EXISTS $categories_table");

        error_log('↩️ Rollback: tabelle categorie rimosse');
    }
}

// Esecuzione automatica se chiamato via URL
if (isset($_GET['run_migration']) && $_GET['run_migration'] === 'categories_to_table') {
    if (!current_user_can('manage_options')) {
        wp_die('Non hai i permessi per eseguire questa migrazione.');
    }

    WI_Migration_Categories_To_Table::run();

    echo '<div style="padding:20px;background:#10b981;color:white;font-family:sans-serif;border-radius:8px;margin:20px;max-width:800px;">';
    echo '<h2 style="margin:0 0 15px 0;">✅ Migrazione Completata con Successo!</h2>';
    echo '<p>Le tabelle categorie sono state create e le relazioni template-categoria sono state migrate.</p>';
    echo '<ul style="margin:15px 0;padding-left:20px;">';
    echo '<li>Tabella <code>wp_wi_event_categories</code> creata con 12 categorie predefinite</li>';
    echo '<li>Tabella <code>wp_wi_template_categories</code> creata per le relazioni</li>';
    echo '<li>Categorie esistenti migrate da formato CSV a tabella relazionale</li>';
    echo '</ul>';
    echo '<p style="margin-top:20px;"><a href="admin.php?page=wedding-invites-templates" style="color:white;text-decoration:underline;">← Torna alla gestione template</a></p>';
    echo '</div>';
    exit;
}
