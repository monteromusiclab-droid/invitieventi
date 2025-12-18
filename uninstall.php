<?php
/**
 * Uninstall script
 * Eseguito quando il plugin viene disinstallato
 */

// Se l'uninstall non è chiamato da WordPress, esci
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Elimina le tabelle
$table_templates = $wpdb->prefix . 'wi_templates';
$wpdb->query("DROP TABLE IF EXISTS $table_templates");

// Elimina le opzioni
delete_option('wi_google_maps_key');
delete_option('wi_primary_color');
delete_option('wi_secondary_color');
delete_option('wi_font_family');
delete_option('wi_enable_social_share');
delete_option('wi_enable_calendar');
delete_option('wi_site_logo');

// Elimina tutti i post di tipo wi_invite
$invites = get_posts(array(
    'post_type' => 'wi_invite',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($invites as $invite) {
    wp_delete_post($invite->ID, true);
}

// Elimina i metadati associati
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_wi_%'");

// Elimina la pagina "Crea Invito" se esiste
$page = get_page_by_path('crea-invito');
if ($page) {
    wp_delete_post($page->ID, true);
}

// Pulisci la cache
wp_cache_flush();
