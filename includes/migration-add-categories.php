<?php
/**
 * Script di Migrazione - Aggiunta Categorie Template
 * Versione: 2.1.0
 *
 * IMPORTANTE: Esegui questo script UNA SOLA VOLTA dopo l'aggiornamento
 */

if (!defined('ABSPATH')) exit;

class WI_Migration_Categories {

    public static function run() {
        global $wpdb;

        $table = $wpdb->prefix . 'wi_templates';

        // Step 1: Aggiungi colonna category se non esiste
        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = %s
                AND TABLE_NAME = %s
                AND COLUMN_NAME = 'category'",
                DB_NAME,
                $table
            )
        );

        if (empty($column_exists)) {
            $wpdb->query("ALTER TABLE $table ADD COLUMN category VARCHAR(255) DEFAULT 'generale' AFTER description");
            error_log('✅ Colonna category aggiunta alla tabella ' . $table);
        } else {
            error_log('ℹ️ Colonna category già esistente');
        }

        // Step 2: Assegna categorie ai template esistenti
        self::assign_categories();

        return true;
    }

    /**
     * Assegna categorie ai 20 template esistenti in base al loro stile
     */
    private static function assign_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        // Mapping template -> categorie (multi-categoria con virgole)
        $categories_map = array(
            'Elegante Oro' => 'matrimonio,anniversario,laurea',
            'Moderno Viola' => 'matrimonio,compleanno,festa',
            'Romantico Rosa' => 'matrimonio,anniversario,fidanzamento',
            'Lussuoso Nero & Oro' => 'matrimonio,gala,laurea',
            'Circolare Azzurro' => 'battesimo,comunione,cresima',
            'Gradiente Viola' => 'compleanno,festa,graduation',
            'Neon Futuristico' => 'compleanno,festa,evento-aziendale',
            'Vintage Marrone' => 'anniversario,matrimonio,reunion',
            'Geometrico Verde' => 'compleanno,festa,evento-aziendale',
            'Cielo Sereno' => 'battesimo,comunione,baby-shower',
            'Oceano Profondo' => 'compleanno,festa,pool-party',
            'Tramonto Caldo' => 'matrimonio,anniversario,beach-party',
            'Cristallo Trasparente' => 'matrimonio,fidanzamento,gala',
            'Ombra 3D' => 'compleanno,festa,evento-aziendale',
            'Animato Dinamico' => 'compleanno,festa,evento-speciale',
            'Pastello Lavanda' => 'baby-shower,battesimo,tea-party',
            'Minimalista Monocromatico' => 'matrimonio,evento-aziendale,conferenza',
            'Boho Naturale' => 'matrimonio,picnic,outdoor-event',
            'Art Déco Gatsby' => 'gala,anniversario,matrimonio',
            'Tropicale Vivace' => 'compleanno,pool-party,summer-party'
        );

        foreach ($categories_map as $template_name => $categories) {
            $wpdb->update(
                $table,
                array('category' => $categories),
                array('name' => $template_name),
                array('%s'),
                array('%s')
            );
        }

        error_log('✅ Categorie assegnate a tutti i template');
    }

    /**
     * Rollback della migrazione (opzionale)
     */
    public static function rollback() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        $wpdb->query("ALTER TABLE $table DROP COLUMN IF EXISTS category");
        error_log('↩️ Rollback: colonna category rimossa');
    }
}

// Esecuzione automatica se chiamato direttamente
if (isset($_GET['run_migration']) && $_GET['run_migration'] === 'categories') {
    WI_Migration_Categories::run();
    echo '<div style="padding:20px;background:#10b981;color:white;font-family:sans-serif;border-radius:8px;margin:20px;"><strong>✅ Migrazione completata con successo!</strong><br>La colonna "category" è stata aggiunta e le categorie sono state assegnate ai template.</div>';
}
