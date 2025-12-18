<?php
/**
 * Script di aggiornamento per i template
 * Aggiorna tutti i template esistenti con il nuovo schema HTML standardizzato
 * e aggiunge i 5 nuovi template
 *
 * ESEGUIRE UNA SOLA VOLTA dopo l'aggiornamento del plugin
 */

if (!defined('ABSPATH')) exit;

// Carica le funzioni dei template
require_once WI_PLUGIN_DIR . 'includes/default-templates-content.php';

/**
 * Aggiorna tutti i template esistenti con il nuovo schema HTML
 */
function wi_update_existing_templates_html() {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_templates';

    // Ottieni tutti i template esistenti
    $templates = $wpdb->get_results("SELECT id FROM $table WHERE id <= 15");

    $updated_count = 0;

    foreach ($templates as $template) {
        // Tutti i template ora usano lo schema standardizzato
        $result = $wpdb->update(
            $table,
            array('html_structure' => get_standard_template_html()),
            array('id' => $template->id)
        );

        if ($result !== false) {
            $updated_count++;
        }
    }

    return $updated_count;
}

/**
 * Inserisci i 5 nuovi template se non esistono già
 */
function wi_insert_new_templates() {
    global $wpdb;
    $table = $wpdb->prefix . 'wi_templates';

    // Verifica se i template 16-20 esistono già
    $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE id BETWEEN 16 AND 20");

    if ($existing > 0) {
        return 0; // Template già presenti
    }

    $new_templates = array(
        // Template 16: Pastello Lavanda
        array(
            'name' => 'Pastello Lavanda',
            'description' => 'Colori pastello soft con tonalità lavanda romantiche',
            'html_structure' => get_lavender_template_html(),
            'css_styles' => get_lavender_template_css(),
            'is_active' => 1,
            'sort_order' => 16,
            'countdown_style' => 16,
            'title_font' => 'Playfair Display',
            'title_size' => 54,
            'title_color' => '#9370db',
            'countdown_font' => 'Quicksand',
            'countdown_color' => '#9370db',
            'countdown_bg_color' => 'rgba(255,255,255,0.9)',
            'countdown_animated' => 1,
            'message_font' => 'Quicksand',
            'message_size' => 18,
            'message_color' => '#6b5b95',
            'message_bg_color' => 'rgba(255,255,255,0.8)',
            'details_font' => 'Quicksand',
            'details_color' => '#6b5b95',
            'details_bg_color' => 'rgba(255,255,255,0.9)',
            'map_marker_color' => '#b19cd9',
            'button_bg_color' => '#9370db',
            'button_text_color' => '#ffffff',
            'background_color' => '#e6e6fa',
            'background_opacity' => 1.00
        ),

        // Template 17: Minimalista Monocromatico
        array(
            'name' => 'Minimalista Monocromatico',
            'description' => 'Design ultra-pulito bianco e nero minimalista',
            'html_structure' => get_minimal_template_html(),
            'css_styles' => get_minimal_template_css(),
            'is_active' => 1,
            'sort_order' => 17,
            'countdown_style' => 17,
            'title_font' => 'Helvetica Neue',
            'title_size' => 48,
            'title_color' => '#000000',
            'countdown_font' => 'Helvetica Neue',
            'countdown_color' => '#000000',
            'countdown_bg_color' => '#ffffff',
            'countdown_animated' => 0,
            'message_font' => 'Helvetica Neue',
            'message_size' => 18,
            'message_color' => '#333333',
            'message_bg_color' => '#ffffff',
            'details_font' => 'Helvetica Neue',
            'details_color' => '#000000',
            'details_bg_color' => '#ffffff',
            'map_marker_color' => '#000000',
            'button_bg_color' => '#000000',
            'button_text_color' => '#ffffff',
            'background_color' => '#ffffff',
            'background_opacity' => 1.00
        ),

        // Template 18: Boho Naturale
        array(
            'name' => 'Boho Naturale',
            'description' => 'Stile bohémien con toni terra e decorazioni naturali',
            'html_structure' => get_boho_template_html(),
            'css_styles' => get_boho_template_css(),
            'is_active' => 1,
            'sort_order' => 18,
            'countdown_style' => 18,
            'title_font' => 'Crimson Text',
            'title_size' => 58,
            'title_color' => '#8b7355',
            'countdown_font' => 'Georgia',
            'countdown_color' => '#8b7355',
            'countdown_bg_color' => 'rgba(255,255,255,0.7)',
            'countdown_animated' => 0,
            'message_font' => 'Georgia',
            'message_size' => 19,
            'message_color' => '#5a4a3a',
            'message_bg_color' => 'rgba(255,255,255,0.6)',
            'details_font' => 'Georgia',
            'details_color' => '#5a4a3a',
            'details_bg_color' => 'rgba(255,255,255,0.7)',
            'map_marker_color' => '#a0826d',
            'button_bg_color' => '#a0826d',
            'button_text_color' => '#ffffff',
            'background_color' => '#f5f3ed',
            'background_opacity' => 1.00
        ),

        // Template 19: Art Déco Gatsby
        array(
            'name' => 'Art Déco Gatsby',
            'description' => 'Stile anni \'20 elegante e lussuoso con accenti oro',
            'html_structure' => get_gatsby_template_html(),
            'css_styles' => get_gatsby_template_css(),
            'is_active' => 1,
            'sort_order' => 19,
            'countdown_style' => 19,
            'title_font' => 'Cinzel Decorative',
            'title_size' => 60,
            'title_color' => '#d4af37',
            'countdown_font' => 'Cormorant Garamond',
            'countdown_color' => '#d4af37',
            'countdown_bg_color' => 'rgba(0,0,0,0.6)',
            'countdown_animated' => 0,
            'message_font' => 'Cormorant Garamond',
            'message_size' => 20,
            'message_color' => '#f5f5f5',
            'message_bg_color' => 'rgba(0,0,0,0.5)',
            'details_font' => 'Cormorant Garamond',
            'details_color' => '#f5f5f5',
            'details_bg_color' => 'rgba(0,0,0,0.5)',
            'map_marker_color' => '#d4af37',
            'button_bg_color' => '#d4af37',
            'button_text_color' => '#1a1a1a',
            'background_color' => '#1a1a1a',
            'background_opacity' => 1.00
        ),

        // Template 20: Tropicale Vivace
        array(
            'name' => 'Tropicale Vivace',
            'description' => 'Colori vivaci ispirati ai tropici con gradient brillanti',
            'html_structure' => get_tropical_template_html(),
            'css_styles' => get_tropical_template_css(),
            'is_active' => 1,
            'sort_order' => 20,
            'countdown_style' => 20,
            'title_font' => 'Pacifico',
            'title_size' => 56,
            'title_color' => '#ff6b6b',
            'countdown_font' => 'Poppins',
            'countdown_color' => '#ffffff',
            'countdown_bg_color' => '#feca57',
            'countdown_animated' => 1,
            'message_font' => 'Poppins',
            'message_size' => 19,
            'message_color' => '#1a535c',
            'message_bg_color' => 'rgba(255,255,255,0.5)',
            'details_font' => 'Poppins',
            'details_color' => '#1a535c',
            'details_bg_color' => 'rgba(255,255,255,0.4)',
            'map_marker_color' => '#48dbfb',
            'button_bg_color' => '#ff6b6b',
            'button_text_color' => '#ffffff',
            'background_color' => '#4facfe',
            'background_opacity' => 1.00
        )
    );

    $inserted_count = 0;

    foreach ($new_templates as $template) {
        $result = $wpdb->insert($table, $template);
        if ($result !== false) {
            $inserted_count++;
        }
    }

    return $inserted_count;
}

/**
 * Esegui l'aggiornamento completo
 */
function wi_run_template_migration() {
    $results = array(
        'updated' => 0,
        'inserted' => 0,
        'errors' => array()
    );

    try {
        // Aggiorna i template esistenti
        $results['updated'] = wi_update_existing_templates_html();

        // Inserisci i nuovi template
        $results['inserted'] = wi_insert_new_templates();

    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
    }

    return $results;
}

// Se chiamato direttamente (per testing), esegui la migrazione
if (isset($_GET['wi_update_templates']) && current_user_can('manage_options')) {
    $results = wi_run_template_migration();

    echo '<div style="padding: 20px; background: #fff; border: 1px solid #ddd; margin: 20px;">';
    echo '<h2>Aggiornamento Template Completato</h2>';
    echo '<p>✅ Template esistenti aggiornati: ' . $results['updated'] . '</p>';
    echo '<p>✅ Nuovi template inseriti: ' . $results['inserted'] . '</p>';

    if (!empty($results['errors'])) {
        echo '<p style="color: red;">❌ Errori: ' . implode(', ', $results['errors']) . '</p>';
    }

    echo '<p><a href="' . admin_url('admin.php?page=wedding-invites-templates') . '">Vai ai Template</a></p>';
    echo '</div>';
    exit;
}
