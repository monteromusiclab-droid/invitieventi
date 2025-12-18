<?php
/**
 * Plugin Name: Wedding Invites Pro
 * Plugin URI: https://tuosito.it
 * Description: Plugin professionale per la creazione di inviti digitali per matrimoni, battesimi, compleanni e altri eventi
 * Version: 2.5.2
 * Author: Il Tuo Nome
 * Author URI: https://tuosito.it
 * Text Domain: wedding-invites
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definizioni costanti
define('WI_VERSION', '2.5.2');
define('WI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WI_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Nonce actions - centralizzati per consistenza
define('WI_NONCE_PUBLIC', 'wi_public_action');
define('WI_NONCE_ADMIN', 'wi_admin_action');
define('WI_NONCE_TEMPLATE', 'wi_template_action');
define('WI_NONCE_MIGRATION', 'wi_migration_action');

/**
 * Classe principale del plugin
 */
class Wedding_Invites_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();

        // Inizializza sistema RSVP
        WI_RSVP::init();
    }
    
    private function init_hooks() {
        // NOTA: Activation/Deactivation hooks sono registrati fuori dalla classe (fine file)
        // perché devono essere registrati prima dell'istanziazione della classe

        // Caricamento script e stili
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Inizializzazione
        add_action('init', array($this, 'init'));

        // Menu amministrazione
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Custom Login Page
        add_action('login_enqueue_scripts', array($this, 'custom_login_styles'));
        add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        add_filter('login_headertext', array($this, 'custom_login_logo_title'));

        // Gestione migrazione database
        add_action('admin_init', array($this, 'handle_migration'));
        add_action('admin_init', array($this, 'handle_template_columns_migration'));
        add_action('admin_notices', array($this, 'show_template_migration_notice'));

        // Gestione migrazione Story Card invite_template_id (v2.5.1)
        add_action('admin_init', array($this, 'handle_story_card_migration'));
        add_action('admin_notices', array($this, 'show_story_card_migration_notice'));

        // AJAX handlers
        add_action('wp_ajax_wi_preview_invite', array($this, 'ajax_preview_invite'));
        add_action('wp_ajax_wi_live_preview', array($this, 'ajax_live_preview')); // NEW: Live preview
        add_action('wp_ajax_nopriv_wi_live_preview', array($this, 'ajax_live_preview')); // FIX v2.5.2: Nopriv per wizard
        add_action('wp_ajax_wi_publish_invite', array($this, 'ajax_publish_invite'));
        add_action('wp_ajax_wi_delete_invite', array($this, 'ajax_delete_invite'));
        add_action('wp_ajax_wi_get_invite', array($this, 'ajax_get_invite'));
        add_action('wp_ajax_wi_upload_logo', array($this, 'ajax_upload_logo'));
        add_action('wp_ajax_wi_save_logo', array($this, 'ajax_save_logo'));
        add_action('wp_ajax_wi_remove_logo', array($this, 'ajax_remove_logo'));
        add_action('wp_ajax_wi_upload_user_image', array($this, 'ajax_upload_user_image'));
        add_action('wp_ajax_wi_preview_edited_invite', array($this, 'ajax_preview_edited_invite'));
        add_action('wp_ajax_save_css_styles', array($this, 'ajax_save_css_styles'));
        add_action('wp_ajax_wi_delete_template', array($this, 'ajax_delete_template'));

        // Wizard AJAX handlers
        add_action('wp_ajax_wi_get_event_categories', array($this, 'ajax_get_event_categories'));
        add_action('wp_ajax_nopriv_wi_get_event_categories', array($this, 'ajax_get_event_categories'));
        add_action('wp_ajax_wi_get_templates_by_category', array($this, 'ajax_get_templates_by_category'));
        add_action('wp_ajax_nopriv_wi_get_templates_by_category', array($this, 'ajax_get_templates_by_category'));
        add_action('wp_ajax_wi_wizard_create_invite', array($this, 'ajax_wizard_create_invite'));
        add_action('wp_ajax_nopriv_wi_wizard_create_invite', array($this, 'ajax_wizard_create_invite')); // FIX v2.5.2: Nopriv per creazione

        // QR Code AJAX handlers
        add_action('wp_ajax_wi_generate_qr_code', array($this, 'ajax_generate_qr_code'));
        add_action('wp_ajax_wi_get_qr_code', array($this, 'ajax_get_qr_code'));
        add_action('wp_ajax_wi_preview_qr_code', array($this, 'ajax_preview_qr_code'));

        // Shortcode per il form
        add_shortcode('wedding_invites_form', array($this, 'render_invite_form'));
        add_shortcode('wedding_invites_wizard', array($this, 'render_wizard_shortcode'));
        add_shortcode('my_invites_dashboard', array($this, 'render_user_dashboard'));

        // AJAX handlers per User Dashboard
        add_action('wp_ajax_wi_get_invite_guests', array($this, 'ajax_get_invite_guests'));
        add_action('wp_ajax_wi_export_guests_csv', array($this, 'ajax_export_guests_csv'));

        // AJAX handlers per Wizard Step 0 (selezione modalità)
        add_action('wp_ajax_wi_get_user_invites_count', array($this, 'ajax_get_user_invites_count'));
        add_action('wp_ajax_nopriv_wi_get_user_invites_count', array($this, 'ajax_get_user_invites_count'));
        add_action('wp_ajax_wi_get_user_invites', array($this, 'ajax_get_user_invites'));
        add_action('wp_ajax_nopriv_wi_get_user_invites', array($this, 'ajax_get_user_invites'));

        // Template personalizzato per la visualizzazione inviti
        add_filter('template_include', array($this, 'invite_template'));
    }
    
    private function load_dependencies() {
        require_once WI_PLUGIN_DIR . 'includes/class-wi-database.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-validator.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-templates.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-invites.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-countdown-styles.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-qrcode.php';

        // RSVP System
        require_once WI_PLUGIN_DIR . 'includes/class-wi-rsvp-database.php';
        require_once WI_PLUGIN_DIR . 'includes/class-wi-rsvp.php';

        // Story Cards System
        require_once WI_PLUGIN_DIR . 'includes/class-wi-story-cards.php';

        // Carica script migrazione v2.2.0
        require_once WI_PLUGIN_DIR . 'includes/migration-v2.2.0.php';

        // Carica script aggiornamento database per editor unificato
        require_once WI_PLUGIN_DIR . 'includes/update-database-for-unified-editor.php';

        // Carica script migrazione colonne template
        require_once WI_PLUGIN_DIR . 'includes/migration-add-template-columns.php';

        // Carica script migrazione Story Card invite_template_id (v2.5.1)
        require_once WI_PLUGIN_DIR . 'includes/class-wi-migration-story-card-columns.php';
    }

    public function activate() {
        WI_Database::create_tables();

        // Crea tabelle RSVP
        WI_RSVP_Database::create_tables();

        // Crea tabelle Story Card
        WI_Story_Cards::create_tables();
        WI_Story_Cards::create_default_template();

        // Aggiungi campi mancanti per editor unificato
        wi_add_missing_fields_for_unified_editor();

        // Aggiungi colonne mancanti per template editor
        WI_Migration_Template_Columns::run();

        // Crea pagina inviti se non esiste
        $page_check = get_page_by_path('crea-invito');
        if (!$page_check) {
            $page = array(
                'post_title'    => 'Crea Invito',
                'post_content'  => '[wedding_invites_form]',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => 'crea-invito'
            );
            wp_insert_post($page);
        }

        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function init() {
        // Registra custom post type per gli inviti
        register_post_type('wi_invite', array(
            'labels' => array(
                'name' => 'Inviti',
                'singular_name' => 'Invito'
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => array('slug' => 'invito'),
            'supports' => array('title', 'thumbnail'),
            'show_in_menu' => false,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'edit_posts',
            ),
            'map_meta_cap' => true,
        ));
    }

    /**
     * Gestisce l'esecuzione della migrazione colonne template
     */
    public function handle_template_columns_migration() {
        // Verifica se è stata richiesta la migrazione via URL
        if (!isset($_GET['run_template_migration'])) {
            return;
        }

        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per eseguire questa migrazione.', 'wedding-invites'));
        }

        // Verifica nonce per sicurezza CSRF
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], WI_NONCE_MIGRATION)) {
            wp_die(__('Verifica di sicurezza fallita. Riprova.', 'wedding-invites'));
        }

        // Esegui migrazione
        $result = WI_Migration_Template_Columns::run();

        // Redirect con messaggio
        $redirect_url = admin_url('admin.php?page=wedding-invites-templates&migration_result=' . urlencode($result['message']));
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Mostra admin notice per migrazione template columns
     */
    public function show_template_migration_notice() {
        // Verifica se siamo nella pagina template
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wedding-invites') === false) {
            return;
        }

        // Mostra messaggio di successo migrazione
        if (isset($_GET['migration_result'])) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>Wedding Invites:</strong> ' . esc_html($_GET['migration_result']);
            echo '</p></div>';
            return;
        }

        // Verifica se serve migrazione
        $status = WI_Migration_Template_Columns::get_status();
        if (!$status['migration_needed']) {
            return;
        }

        $nonce = wp_create_nonce(WI_NONCE_MIGRATION);
        $migration_url = admin_url('admin.php?page=wedding-invites-templates&run_template_migration=1&_wpnonce=' . $nonce);

        echo '<div class="notice notice-warning">';
        echo '<p><strong>Wedding Invites - Aggiornamento Database Richiesto</strong></p>';
        echo '<p>Il database dei template necessita di un aggiornamento per abilitare tutte le funzionalità dell\'editor.</p>';
        echo '<p>Colonne mancanti: <strong>' . count($status['missing_columns']) . '</strong></p>';
        echo '<p><a href="' . esc_url($migration_url) . '" class="button button-primary">Esegui Migrazione Ora</a></p>';
        echo '</div>';
    }

    /**
     * Gestisce l'esecuzione della migrazione Story Card invite_template_id
     */
    public function handle_story_card_migration() {
        // Verifica se è stata richiesta la migrazione via URL
        if (!isset($_GET['run_story_card_migration'])) {
            return;
        }

        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per eseguire questa migrazione.', 'wedding-invites'));
        }

        // Verifica nonce per sicurezza CSRF
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], WI_NONCE_MIGRATION)) {
            wp_die(__('Verifica di sicurezza fallita. Riprova.', 'wedding-invites'));
        }

        // Esegui migrazione
        $result = WI_Migration_Story_Card_Columns::run();

        // Redirect con messaggio
        $redirect_url = admin_url('admin.php?page=wedding-invites-story-cards&story_card_migration_result=' . urlencode($result['message']));
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Mostra admin notice per migrazione Story Card
     */
    public function show_story_card_migration_notice() {
        // Verifica se siamo nelle pagine Wedding Invites
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'wedding-invites') === false) {
            return;
        }

        // Mostra messaggio di successo migrazione
        if (isset($_GET['story_card_migration_result'])) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>Wedding Invites:</strong> ' . esc_html($_GET['story_card_migration_result']);
            echo '</p></div>';
            return;
        }

        // Verifica se serve migrazione
        $status = WI_Migration_Story_Card_Columns::get_status();
        if (!$status['migration_needed']) {
            return;
        }

        $nonce = wp_create_nonce(WI_NONCE_MIGRATION);
        $migration_url = admin_url('admin.php?page=wedding-invites-story-cards&run_story_card_migration=1&_wpnonce=' . $nonce);

        echo '<div class="notice notice-warning">';
        echo '<p><strong>🔧 Wedding Invites - Aggiornamento Database Story Card Richiesto</strong></p>';
        echo '<p>La tabella Story Card Templates necessita di un aggiornamento per abilitare il sistema a cascata (Template ID → Categoria → Default).</p>';
        echo '<p><strong>Dettagli:</strong> ' . esc_html($status['details']) . '</p>';
        echo '<p><a href="' . esc_url($migration_url) . '" class="button button-primary">✅ Esegui Migrazione Ora</a> ';
        echo '<span style="margin-left: 10px; color: #666;">La migrazione richiede pochi secondi e non comporta perdita di dati.</span></p>';
        echo '</div>';
    }

    /**
     * Gestisce l'esecuzione della migrazione database v2.2.0
     */
    public function handle_migration() {
        // Verifica se è stata richiesta la migrazione via URL
        if (!isset($_GET['run_migration']) || $_GET['run_migration'] !== 'v220') {
            return;
        }

        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per eseguire questa migrazione.', 'wedding-invites'));
        }

        // Verifica nonce per sicurezza CSRF
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], WI_NONCE_MIGRATION)) {
            wp_die(__('Verifica di sicurezza fallita. Riprova.', 'wedding-invites'));
        }

        // Esegui migrazione
        WI_Migration_v220::run();

        // Ottieni stato migrazione
        $status = WI_Migration_v220::get_migration_status();

        // Mostra pagina di successo
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
        echo '<li><strong>Countdown Ottimizzato</strong>: Template 16-20 centrati e ingranditi su mobile</li>';
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

    public function add_admin_menu() {
        add_menu_page(
            'Wedding Invites',
            'Wedding Invites',
            'manage_options',
            'wedding-invites',
            array($this, 'admin_invites_page'),
            'dashicons-email-alt',
            30
        );

        add_submenu_page(
            'wedding-invites',
            'Tutti gli Inviti',
            'Tutti gli Inviti',
            'manage_options',
            'wedding-invites',
            array($this, 'admin_invites_page')
        );

        // Nuovo sottomenu per creare invito
        add_submenu_page(
            'wedding-invites',
            'Nuovo Invito',
            '➕ Nuovo Invito',
            'manage_options',
            'wedding-invites-new',
            array($this, 'admin_new_invite_page')
        );

        // Wizard creazione guidata
        add_submenu_page(
            'wedding-invites',
            'Creazione Guidata',
            '🧙 Wizard Guidato',
            'manage_options',
            'wedding-invites-wizard',
            array($this, 'admin_wizard_page')
        );

        // Pagina nascosta per modifica invito
        add_submenu_page(
            null, // Nascosto dal menu
            'Modifica Invito',
            'Modifica Invito',
            'manage_options',
            'wedding-invites-edit',
            array($this, 'admin_edit_invite_page')
        );

        add_submenu_page(
            'wedding-invites',
            'Template',
            '🎨 Template',
            'manage_options',
            'wedding-invites-templates',
            array($this, 'admin_templates_page')
        );

        // Pagina nascosta per editor template unificato
        add_submenu_page(
            null, // Nascosto dal menu
            'Modifica Template',
            'Modifica Template',
            'manage_options',
            'wedding-invites-template-edit',
            array($this, 'admin_template_unified_edit')
        );

        add_submenu_page(
            'wedding-invites',
            'Categorie Eventi',
            '📂 Categorie',
            'manage_options',
            'wedding-invites-categories',
            array($this, 'admin_categories_page')
        );

        add_submenu_page(
            'wedding-invites',
            'RSVP Dashboard',
            '📊 RSVP',
            'manage_options',
            'wi-rsvp',
            array($this, 'admin_rsvp_page')
        );

        add_submenu_page(
            'wedding-invites',
            'Story Card Templates',
            '📱 Story Card',
            'manage_options',
            'wedding-invites-story-cards',
            array($this, 'admin_story_cards_page')
        );

        add_submenu_page(
            'wedding-invites',
            'Impostazioni',
            'Impostazioni',
            'manage_options',
            'wedding-invites-settings',
            array($this, 'admin_settings_page')
        );

        add_submenu_page(
            'wedding-invites',
            'Shortcodes',
            '📋 Shortcodes',
            'manage_options',
            'wedding-invites-shortcodes',
            array($this, 'admin_shortcodes_page')
        );

        // TEMPORANEO: Fix tabelle RSVP (rimuovere dopo aver creato le tabelle)
        if (file_exists(WI_PLUGIN_DIR . 'fix-rsvp-tables.php')) {
            add_submenu_page(
                'wedding-invites',
                'Fix Tabelle RSVP',
                '🔧 Fix RSVP',
                'manage_options',
                'wi-fix-rsvp-tables',
                array($this, 'admin_fix_rsvp_tables_page')
            );
        }
    }
    
    public function enqueue_public_assets() {
        // CSS
        wp_enqueue_style('wi-public-style', WI_PLUGIN_URL . 'assets/css/public-style.css', array(), WI_VERSION);
        wp_enqueue_style('wi-countdown', WI_PLUGIN_URL . 'assets/css/countdown.css', array(), WI_VERSION);
        wp_enqueue_style('wi-form-validations', WI_PLUGIN_URL . 'assets/css/form-validations.css', array(), WI_VERSION);

        // JavaScript
        wp_enqueue_script('wi-countdown', WI_PLUGIN_URL . 'assets/js/countdown.js', array('jquery'), WI_VERSION, true);
        wp_enqueue_script('wi-public', WI_PLUGIN_URL . 'assets/js/public.js', array('jquery'), WI_VERSION, true);
        wp_enqueue_script('wi-invite-functions', WI_PLUGIN_URL . 'assets/js/invite-functions.js', array('jquery'), WI_VERSION, true);
        wp_enqueue_script('wi-form-validations', WI_PLUGIN_URL . 'assets/js/form-validations.js', array('jquery'), WI_VERSION, true);

        // Wizard frontend assets (solo se presente shortcode)
        // Usa get_queried_object() invece di $post globale per maggiore affidabilità
        $current_post = get_queried_object();
        $should_load_wizard = false;

        if (is_a($current_post, 'WP_Post') && has_shortcode($current_post->post_content, 'wedding_invites_wizard')) {
            $should_load_wizard = true;
        }

        // Fallback: controlla anche se esiste il parametro 'edit' nell'URL (indica wizard di modifica)
        if (!$should_load_wizard && isset($_GET['edit']) && intval($_GET['edit']) > 0) {
            // Trova pagina wizard
            global $wpdb;
            $wizard_page = $wpdb->get_var(
                "SELECT ID FROM {$wpdb->posts}
                WHERE post_content LIKE '%[wedding_invites_wizard]%'
                AND post_status = 'publish'
                AND post_type = 'page'
                LIMIT 1"
            );
            if ($wizard_page && is_page($wizard_page)) {
                $should_load_wizard = true;
            }
        }

        if ($should_load_wizard) {
            wp_enqueue_media(); // Media uploader
            wp_enqueue_style('wi-wizard', WI_PLUGIN_URL . 'assets/css/creation-wizard.css', array(), WI_VERSION);
            wp_enqueue_script('wi-wizard', WI_PLUGIN_URL . 'assets/js/creation-wizard.js', array('jquery'), WI_VERSION, true);

            // Prepara dati per modalità edit
            $wizard_data = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(WI_NONCE_PUBLIC),
                'edit_mode' => false,
                'invite_data' => null
            );

            // Se c'è parametro edit nell'URL, carica i dati
            if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
                $edit_invite_id = intval($_GET['edit']);
                $edit_invite = get_post($edit_invite_id);

                if ($edit_invite && $edit_invite->post_type === 'wi_invite' &&
                    is_user_logged_in() && $edit_invite->post_author == get_current_user_id()) {

                    $wizard_data['edit_mode'] = true;
                    $wizard_data['invite_data'] = array(
                        'id' => $edit_invite_id,
                        'category_id' => intval(get_post_meta($edit_invite_id, '_wi_category_id', true)),
                        'template_id' => intval(get_post_meta($edit_invite_id, '_wi_template_id', true)),
                        'event_date' => get_post_meta($edit_invite_id, '_wi_event_date', true),
                        'event_time' => get_post_meta($edit_invite_id, '_wi_event_time', true),
                        'event_location' => get_post_meta($edit_invite_id, '_wi_event_location', true),
                        'event_address' => get_post_meta($edit_invite_id, '_wi_event_address', true),
                        'invite_title' => $edit_invite->post_title,
                        'invite_message' => get_post_meta($edit_invite_id, '_wi_invite_message', true),
                        'final_message' => get_post_meta($edit_invite_id, '_wi_final_message', true),
                        'final_message_button_text' => get_post_meta($edit_invite_id, '_wi_final_message_button_text', true),
                        'user_image_url' => get_post_meta($edit_invite_id, '_wi_user_image_url', true),
                    );

                    // Carica RSVP settings (oggetto, non array)
                    $rsvp_settings = WI_RSVP_Database::get_settings($edit_invite_id);
                    if ($rsvp_settings) {
                        $wizard_data['invite_data']['rsvp_enabled'] = (bool)$rsvp_settings->rsvp_enabled;
                        $wizard_data['invite_data']['rsvp_deadline'] = $rsvp_settings->rsvp_deadline;
                        $wizard_data['invite_data']['rsvp_max_guests'] = $rsvp_settings->max_guests_per_response;

                        // Decodifica menu choices se JSON
                        $menu_choices = $rsvp_settings->menu_choices;
                        if ($menu_choices && is_string($menu_choices)) {
                            $menu_array = json_decode($menu_choices, true);
                            if (is_array($menu_array)) {
                                $wizard_data['invite_data']['rsvp_menu_choices'] = implode(', ', $menu_array);
                            }
                        }

                        $wizard_data['invite_data']['rsvp_notify_admin'] = (bool)$rsvp_settings->notify_admin;
                        $wizard_data['invite_data']['rsvp_admin_email'] = $rsvp_settings->admin_email;
                    }
                }
            }

            wp_localize_script('wi-wizard', 'wiWizard', $wizard_data);
        }

        // RSVP assets (solo su single invite)
        // IMPORTANTE: Il post type è 'wi_invite' non 'wedding_invite'
        if (is_singular('wi_invite')) {
            wp_enqueue_style('wi-rsvp', WI_PLUGIN_URL . 'assets/css/rsvp.css', array(), WI_VERSION);
            wp_enqueue_script('wi-rsvp', WI_PLUGIN_URL . 'assets/js/rsvp.js', array('jquery'), WI_VERSION, true);

            wp_localize_script('wi-rsvp', 'wiRSVP', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(WI_NONCE_PUBLIC)
            ));

            // Story Card assets
            wp_enqueue_style('wi-story-card', WI_PLUGIN_URL . 'assets/css/story-card.css', array(), WI_VERSION);
            wp_enqueue_script('wi-story-card', WI_PLUGIN_URL . 'assets/js/story-card.js', array('jquery'), WI_VERSION, true);

            wp_localize_script('wi-story-card', 'wiStoryCard', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(WI_NONCE_PUBLIC)
            ));
        }

        // User Dashboard assets (solo se presente shortcode)
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'my_invites_dashboard')) {
            wp_enqueue_style('wi-user-dashboard', WI_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), WI_VERSION);
            wp_enqueue_script('wi-user-dashboard', WI_PLUGIN_URL . 'assets/js/user-dashboard.js', array('jquery'), WI_VERSION, true);

            wp_localize_script('wi-user-dashboard', 'wiUserDashboard', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(WI_NONCE_PUBLIC)
            ));
        }

        // Localizzazione
        wp_localize_script('wi-public', 'wiPublic', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(WI_NONCE_PUBLIC)
        ));

        // Google Maps API Key DISABILITATO - Ora usiamo OpenStreetMap
        // $google_maps_key = get_option('wi_google_maps_api_key', '');
        // if (!empty($google_maps_key)) {
        //     wp_localize_script('wi-invite-functions', 'wiGoogleMapsKey', $google_maps_key);
        // }
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wedding-invites') === false) {
            return;
        }
        
        // Media uploader
        wp_enqueue_media();
        
        // Color picker
        wp_enqueue_style('wp-color-picker');
        
        // CSS
        wp_enqueue_style('wi-admin-style', WI_PLUGIN_URL . 'assets/css/admin-style.css', array(), WI_VERSION);
        wp_enqueue_style('wi-live-preview', WI_PLUGIN_URL . 'assets/css/live-preview.css', array(), WI_VERSION);

        // JavaScript
        wp_enqueue_script('wi-admin', WI_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), WI_VERSION, true);
        wp_enqueue_script('wi-live-preview', WI_PLUGIN_URL . 'assets/js/live-preview.js', array('jquery'), WI_VERSION, true);

        // QR Code assets (on edit page)
        if (strpos($hook, 'wedding-invites') !== false && isset($_GET['invite_id'])) {
            wp_enqueue_style('wi-qr-generator', WI_PLUGIN_URL . 'assets/css/qr-generator.css', array(), WI_VERSION);
            wp_enqueue_script('wi-qr-generator', WI_PLUGIN_URL . 'assets/js/qr-generator.js', array('jquery'), WI_VERSION, true);
        }

        // Wizard-specific assets
        if (strpos($hook, 'wedding-invites-wizard') !== false) {
            wp_enqueue_style('wi-wizard', WI_PLUGIN_URL . 'assets/css/creation-wizard.css', array(), WI_VERSION);
            wp_enqueue_script('wi-wizard', WI_PLUGIN_URL . 'assets/js/creation-wizard.js', array('jquery'), WI_VERSION, true);
        }

        // Localizzazione
        wp_localize_script('wi-admin', 'wiAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(WI_NONCE_ADMIN)
        ));
    }
    
    public function admin_invites_page() {
        require_once WI_PLUGIN_DIR . 'admin/invites-list.php';
    }

    public function admin_new_invite_page() {
        // Reindirizza alla pagina di modifica senza ID (nuovo invito)
        require_once WI_PLUGIN_DIR . 'admin/edit-invite.php';
    }

    public function admin_edit_invite_page() {
        require_once WI_PLUGIN_DIR . 'admin/edit-invite.php';
    }

    public function admin_wizard_page() {
        require_once WI_PLUGIN_DIR . 'admin/creation-wizard.php';
    }

    public function admin_templates_page() {
        require_once WI_PLUGIN_DIR . 'admin/templates-manager.php';
    }

    public function admin_template_unified_edit() {
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        require_once WI_PLUGIN_DIR . 'admin/template-unified-editor.php';
    }

    public function admin_categories_page() {
        require_once WI_PLUGIN_DIR . 'admin/categories-manager.php';
    }

    public function admin_settings_page() {
        require_once WI_PLUGIN_DIR . 'admin/settings.php';
    }

    public function admin_story_cards_page() {
        require_once WI_PLUGIN_DIR . 'admin/story-card-manager.php';
    }

    public function admin_shortcodes_page() {
        require_once WI_PLUGIN_DIR . 'admin/shortcodes-guide.php';
    }

    /**
     * Custom Login Page - Styles
     */
    public function custom_login_styles() {
        wp_enqueue_style('wi-custom-login', WI_PLUGIN_URL . 'assets/css/custom-login.css', array(), WI_VERSION);

        // Logo dinamico del sito
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo_url = '';

        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        }

        // CSS inline per logo dinamico
        $custom_css = "
            #login h1 a::before {
                background-image: url('" . esc_url($logo_url) . "');
            }
            #login h1 a {
                " . ($logo_url ? "text-indent: -9999px;" : "") . "
            }
        ";
        wp_add_inline_style('wi-custom-login', $custom_css);
    }

    /**
     * Custom Login Page - Logo URL
     */
    public function custom_login_logo_url() {
        return home_url();
    }

    /**
     * Custom Login Page - Logo Title
     */
    public function custom_login_logo_title() {
        return get_bloginfo('name');
    }

    /**
     * TEMPORANEO: Pagina fix tabelle RSVP
     */
    public function admin_fix_rsvp_tables_page() {
        require_once WI_PLUGIN_DIR . 'fix-rsvp-tables.php';
    }

    public function render_invite_form($atts) {
        if (!is_user_logged_in()) {
            return '<div class="wi-login-required">
                <p>Devi essere registrato per creare un invito.</p>
                <a href="' . wp_login_url(get_permalink()) . '" class="wi-btn">Accedi</a>
                <a href="' . wp_registration_url() . '" class="wi-btn wi-btn-secondary">Registrati</a>
            </div>';
        }

        ob_start();
        require WI_PLUGIN_DIR . 'templates/invite-form.php';
        return ob_get_clean();
    }

    public function render_wizard_shortcode($atts) {
        // FIX v2.5.2: Rimosso check login obbligatorio - wizard accessibile a tutti
        // Gli utenti non loggati possono creare inviti, salvati come post_author=0
        // Il sistema AJAX nopriv permette creazione senza autenticazione

        // IMPORTANTE: Enqueue media uploader per upload immagini nel wizard
        wp_enqueue_media();

        // Passa dati wizard via wp_localize_script DENTRO lo shortcode
        // per garantire che venga eseguito quando lo shortcode viene renderizzato
        $wizard_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(WI_NONCE_PUBLIC),
            'edit_mode' => false,
            'invite_data' => null
        );

        // Se c'è parametro edit nell'URL, carica i dati
        if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
            $edit_invite_id = intval($_GET['edit']);
            $edit_invite = get_post($edit_invite_id);

            if ($edit_invite && $edit_invite->post_type === 'wi_invite' &&
                is_user_logged_in() && $edit_invite->post_author == get_current_user_id()) {

                $wizard_data['edit_mode'] = true;
                $wizard_data['invite_data'] = array(
                    'id' => $edit_invite_id,
                    'category_id' => intval(get_post_meta($edit_invite_id, '_wi_category_id', true)),
                    'template_id' => intval(get_post_meta($edit_invite_id, '_wi_template_id', true)),
                    'event_date' => get_post_meta($edit_invite_id, '_wi_event_date', true),
                    'event_time' => get_post_meta($edit_invite_id, '_wi_event_time', true),
                    'event_location' => get_post_meta($edit_invite_id, '_wi_event_location', true),
                    'event_address' => get_post_meta($edit_invite_id, '_wi_event_address', true),
                    'invite_title' => $edit_invite->post_title,
                    'invite_message' => get_post_meta($edit_invite_id, '_wi_invite_message', true),
                    'final_message' => get_post_meta($edit_invite_id, '_wi_final_message', true),
                    'final_message_button_text' => get_post_meta($edit_invite_id, '_wi_final_message_button_text', true),
                    'user_image_url' => get_post_meta($edit_invite_id, '_wi_user_image_url', true),
                );

                // Carica RSVP settings (oggetto, non array)
                $rsvp_settings = WI_RSVP_Database::get_settings($edit_invite_id);
                if ($rsvp_settings) {
                    $wizard_data['invite_data']['rsvp_enabled'] = (bool)$rsvp_settings->rsvp_enabled;
                    $wizard_data['invite_data']['rsvp_deadline'] = $rsvp_settings->rsvp_deadline;
                    $wizard_data['invite_data']['rsvp_max_guests'] = $rsvp_settings->max_guests_per_response;

                    // Decodifica menu choices se JSON
                    $menu_choices = $rsvp_settings->menu_choices;
                    if ($menu_choices && is_string($menu_choices)) {
                        $menu_array = json_decode($menu_choices, true);
                        if (is_array($menu_array)) {
                            $wizard_data['invite_data']['rsvp_menu_choices'] = implode(', ', $menu_array);
                        }
                    }

                    $wizard_data['invite_data']['rsvp_notify_admin'] = (bool)$rsvp_settings->notify_admin;
                    $wizard_data['invite_data']['rsvp_admin_email'] = $rsvp_settings->admin_email;
                }
            }
        }

        // IMPORTANTE: Inietta wiWizard direttamente nell'HTML prima del template
        // perché wp_localize_script() potrebbe essere troppo tardi se lo script è già stato stampato
        ob_start();
        ?>
        <script>
        // Definisci wiWizard PRIMA che il template venga caricato
        var wiWizard = <?php echo json_encode($wizard_data); ?>;
        console.log('WI DEBUG (Shortcode): wiWizard initialized', wiWizard);
        </script>
        <?php

        // Carica templates
        $templates = WI_Templates::get_all_templates();
        require WI_PLUGIN_DIR . 'admin/creation-wizard.php';
        return ob_get_clean();
    }

    /**
     * Render User Dashboard Shortcode
     * Shortcode: [my_invites_dashboard]
     */
    public function render_user_dashboard($atts) {
        ob_start();
        include WI_PLUGIN_DIR . 'templates/user-dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Get Invite Guests
     * Carica lista ospiti per un invito specifico
     */
    public function ajax_get_invite_guests() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Non autorizzato. Effettua il login.'));
        }

        $invite_id = intval($_POST['invite_id'] ?? 0);

        if (!$invite_id) {
            wp_send_json_error(array('message' => 'ID invito non valido.'));
        }

        // Verifica che l'invito appartenga all'utente corrente
        $invite = get_post($invite_id);
        if (!$invite || $invite->post_type !== 'wi_invite') {
            wp_send_json_error(array('message' => 'Invito non trovato.'));
        }

        if ($invite->post_author != get_current_user_id()) {
            wp_send_json_error(array('message' => 'Non hai i permessi per visualizzare questo invito.'));
        }

        // Carica risposte RSVP
        $guests = WI_RSVP::get_responses($invite_id);

        if (empty($guests)) {
            wp_send_json_success(array());
        }

        wp_send_json_success($guests);
    }

    /**
     * AJAX: Export Guests CSV
     * Esporta risposte RSVP in formato CSV
     */
    public function ajax_export_guests_csv() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_die('Non autorizzato. Effettua il login.');
        }

        $invite_id = intval($_POST['invite_id'] ?? 0);

        if (!$invite_id) {
            wp_die('ID invito non valido.');
        }

        // Verifica ownership
        $invite = get_post($invite_id);
        if (!$invite || $invite->post_type !== 'wi_invite') {
            wp_die('Invito non trovato.');
        }

        if ($invite->post_author != get_current_user_id()) {
            wp_die('Non hai i permessi per esportare questo invito.');
        }

        // Carica risposte
        $guests = WI_RSVP::get_responses($invite_id);

        if (empty($guests)) {
            wp_die('Nessun ospite da esportare.');
        }

        // Headers CSV
        $filename = 'rsvp-' . sanitize_title($invite->post_title) . '-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV
        $output = fopen('php://output', 'w');

        // BOM per supporto UTF-8 in Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header columns
        fputcsv($output, array(
            'Nome Ospite',
            'Email',
            'Telefono',
            'Stato',
            'Numero Ospiti',
            'Scelta Menu',
            'Note',
            'Data Risposta'
        ));

        // Data rows
        foreach ($guests as $guest) {
            $status_text = $guest['status'] === 'attending' ? 'Confermato' :
                          ($guest['status'] === 'not_attending' ? 'Rifiutato' : 'Forse');

            fputcsv($output, array(
                $guest['guest_name'],
                $guest['guest_email'],
                $guest['guest_phone'] ?? '',
                $status_text,
                $guest['num_guests'],
                $guest['menu_choice'] ?? '',
                $guest['notes'] ?? '',
                date('d/m/Y H:i', strtotime($guest['responded_at']))
            ));
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX: Get User Invites Count
     * Ritorna il numero di inviti dell'utente corrente
     */
    public function ajax_get_user_invites_count() {
        // Verifica nonce (permissivo per debug)
        // FIX v2.5.2: Nonce opzionale per utenti non loggati (nopriv)
        // Se l'utente è loggato, valida la nonce. Se non è loggato, skip nonce check.
        if (is_user_logged_in()) {
            $nonce_valid = check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false) ||
                           check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false);

            if (!$nonce_valid) {
                wp_send_json_error(array('message' => 'Nonce non valido'));
            }
        }

        // Utenti non loggati possono usare questa funzione (creeranno inviti con author=0)

        $current_user_id = get_current_user_id();

        $args = array(
            'post_type' => 'wi_invite',
            'author' => $current_user_id,
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft'),
            'fields' => 'ids'
        );

        $invites = get_posts($args);
        $count = count($invites);

        wp_send_json_success(array('count' => $count));
    }

    /**
     * AJAX: Get User Invites
     * Ritorna lista completa inviti dell'utente con dettagli
     */
    public function ajax_get_user_invites() {
        // Cattura TUTTI gli errori e warning PHP
        ob_start();

        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
                ob_clean();
                wp_send_json_error(array(
                    'message' => 'Fatal PHP Error',
                    'error' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ));
            }
        });

        try {
            error_log('WI DEBUG: ajax_get_user_invites called');

            // Verifica nonce (permissivo per debug)
            $nonce_valid = check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false) ||
                           check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false);

            if (!$nonce_valid) {
                ob_end_clean();
                wp_send_json_error(array('message' => 'Nonce non valido'));
            }

            if (!is_user_logged_in()) {
                ob_end_clean();
                wp_send_json_error(array('message' => 'Non autorizzato'));
            }

            error_log('WI DEBUG: Starting to load invites for user ' . get_current_user_id());
        } catch (Throwable $e) {
            ob_end_clean();
            error_log('WI ERROR in pre-checks: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Errore pre-verifica',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }

        try {
            $current_user_id = get_current_user_id();

            $args = array(
                'post_type' => 'wi_invite',
                'author' => $current_user_id,
                'posts_per_page' => -1,
                'post_status' => array('publish', 'draft'),
                'orderby' => 'modified',
                'order' => 'DESC'
            );

            $invites_posts = get_posts($args);

            if (empty($invites_posts)) {
                wp_send_json_success(array('invites' => array()));
            }

            $invites = array();

            foreach ($invites_posts as $post) {
                $invite_id = $post->ID;

                // Dati base
                $invite_data = array(
                    'id' => $invite_id,
                    'title' => $post->post_title,
                    'event_date' => get_post_meta($invite_id, '_wi_event_date', true),
                    'event_time' => get_post_meta($invite_id, '_wi_event_time', true),
                    'event_location' => get_post_meta($invite_id, '_wi_event_location', true),
                    'modified_date' => $post->post_modified,
                    'rsvp_enabled' => false,
                    'rsvp_stats' => null
                );

                // Verifica RSVP - FIX: get_settings() ritorna un oggetto, non un array
                try {
                    $rsvp_settings = WI_RSVP_Database::get_settings($invite_id);
                    if ($rsvp_settings && isset($rsvp_settings->rsvp_enabled) && $rsvp_settings->rsvp_enabled) {
                        $invite_data['rsvp_enabled'] = true;

                        // Carica statistiche RSVP
                        $responses = WI_RSVP::get_responses($invite_id);
                        $stats = array(
                            'confirmed' => 0,
                            'declined' => 0,
                            'maybe' => 0,
                            'total' => count($responses)
                        );

                        foreach ($responses as $response) {
                            // FIX: $response è un oggetto, non array - usa -> invece di []
                            if (isset($response->status)) {
                                if ($response->status === 'attending') {
                                    $stats['confirmed']++;
                                } elseif ($response->status === 'not_attending') {
                                    $stats['declined']++;
                                } elseif ($response->status === 'maybe') {
                                    $stats['maybe']++;
                                }
                            }
                        }

                        $invite_data['rsvp_stats'] = $stats;
                    }
                } catch (Exception $e) {
                    // Se errore RSVP, continua con i dati base
                    error_log('WI Error loading RSVP for invite ' . $invite_id . ': ' . $e->getMessage());
                }

                $invites[] = $invite_data;
            }

            ob_end_clean();
            wp_send_json_success(array('invites' => $invites));

        } catch (Throwable $e) {
            ob_end_clean();
            error_log('WI Error in ajax_get_user_invites: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'Errore nel caricamento degli inviti',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }

    public function ajax_preview_invite() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Non autorizzato');
        }

        $data = $_POST['invite_data'];
        $template_id = intval($_POST['template_id']);

        $preview_html = WI_Invites::generate_invite_html($data, $template_id, true);

        wp_send_json_success(array('html' => $preview_html));
    }

    /**
     * AJAX handler per anteprima LIVE in tempo reale
     * Usato dal sistema di live preview
     */
    public function ajax_live_preview() {
        // Verifica nonce (può essere chiamato sia da admin che da public)
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], WI_NONCE_ADMIN) ||
                          wp_verify_nonce($_POST['nonce'], WI_NONCE_PUBLIC);
        }

        if (!$nonce_valid) {
            wp_send_json_error('Verifica di sicurezza fallita');
        }

        if (!is_user_logged_in()) {
            wp_send_json_error('Non autorizzato');
        }

        $data = isset($_POST['invite_data']) ? $_POST['invite_data'] : array();
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 1;

        // Genera HTML anteprima
        $preview_html = WI_Invites::generate_invite_html($data, $template_id, true);

        // Log per debug (solo se WP_DEBUG attivo)
        wi_log('Live preview generated', 'info', array(
            'template_id' => $template_id,
            'has_title' => !empty($data['title'])
        ));

        wp_send_json_success(array('html' => $preview_html));
    }

    /**
     * AJAX: Get event categories for wizard
     */
    public function ajax_get_event_categories() {
        // Dual nonce verification (admin or public)
        $nonce_valid = check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false) ||
                       check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false);

        if (!$nonce_valid) {
            wp_send_json_error('Nonce verification failed');
        }

        global $wpdb;
        $categories_table = $wpdb->prefix . 'wi_event_categories';

        // Verifica se la tabella esiste
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$categories_table}'") === $categories_table;

        $categories = array();

        if ($table_exists) {
            // Prova a leggere con campo active
            $categories = $wpdb->get_results(
                "SELECT id, name, slug, icon FROM {$categories_table} WHERE active = 1 ORDER BY name ASC"
            );

            // Se errore (campo active non esiste), prova senza
            if ($wpdb->last_error) {
                $categories = $wpdb->get_results(
                    "SELECT id, name, slug, icon FROM {$categories_table} ORDER BY name ASC"
                );
            }
        }

        if (empty($categories)) {
            // Fallback se non ci sono categorie nel database
            $categories = array(
                (object) array('id' => 1, 'slug' => 'matrimonio', 'name' => 'Matrimonio', 'icon' => '💒'),
                (object) array('id' => 2, 'slug' => 'compleanno', 'name' => 'Compleanno', 'icon' => '🎂'),
                (object) array('id' => 3, 'slug' => 'laurea', 'name' => 'Laurea', 'icon' => '🎓'),
                (object) array('id' => 4, 'slug' => 'anniversario', 'name' => 'Anniversario', 'icon' => '💕'),
                (object) array('id' => 5, 'slug' => 'battesimo', 'name' => 'Battesimo', 'icon' => '👶'),
                (object) array('id' => 6, 'slug' => 'comunione', 'name' => 'Comunione', 'icon' => '🕊️'),
                (object) array('id' => 7, 'slug' => 'cresima', 'name' => 'Cresima', 'icon' => '✝️'),
                (object) array('id' => 8, 'slug' => 'addio-celibato', 'name' => 'Addio al Celibato/Nubilato', 'icon' => '🎉'),
                (object) array('id' => 9, 'slug' => 'baby-shower', 'name' => 'Baby Shower', 'icon' => '🍼'),
                (object) array('id' => 10, 'slug' => 'pensionamento', 'name' => 'Pensionamento', 'icon' => '🎊'),
                (object) array('id' => 11, 'slug' => 'festa-aziendale', 'name' => 'Festa Aziendale', 'icon' => '🏢'),
                (object) array('id' => 12, 'slug' => 'altro', 'name' => 'Altro Evento', 'icon' => '🎈'),
            );

            wi_log('Using fallback categories (table not found or empty)', 'info', array(
                'table_exists' => $table_exists,
                'count' => count($categories)
            ));
        } else {
            wi_log('Event categories loaded from database', 'info', array('count' => count($categories)));
        }

        wp_send_json_success($categories);
    }

    /**
     * AJAX: Get templates filtered by category for wizard
     */
    public function ajax_get_templates_by_category() {
        // Dual nonce verification (admin or public)
        $nonce_valid = check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false) ||
                       check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false);

        if (!$nonce_valid) {
            wp_send_json_error('Nonce verification failed');
        }

        $category_slug = sanitize_text_field($_POST['category_slug'] ?? '');

        wi_log('Loading templates by category', 'info', array('category' => $category_slug));

        // Usa il metodo della classe Templates
        $templates = WI_Templates::get_templates_by_category($category_slug);

        // Prepara i dati per il frontend (solo campi necessari)
        $templates_data = array();
        foreach ($templates as $template) {
            $templates_data[] = array(
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description ?? 'Template elegante e raffinato',
                'preview_url' => $template->preview_url ?? ''
            );
        }

        wp_send_json_success($templates_data);
    }

    /**
     * AJAX: Create invite from wizard
     */
    public function ajax_wizard_create_invite() {
        // Dual nonce verification (admin or public)
        $nonce_valid = check_ajax_referer(WI_NONCE_ADMIN, 'nonce', false) ||
                       check_ajax_referer(WI_NONCE_PUBLIC, 'nonce', false);

        if (!$nonce_valid) {
            wp_send_json_error(array('message' => 'Nonce verification failed'));
        }

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Non autorizzato'));
        }

        // Controlla se è un update
        $is_update = false;
        $invite_id = 0;

        if (isset($_POST['invite_id']) && intval($_POST['invite_id']) > 0) {
            $invite_id = intval($_POST['invite_id']);
            $existing_invite = get_post($invite_id);

            // Verifica ownership per utenti non admin
            if ($existing_invite && $existing_invite->post_type === 'wi_invite') {
                if (current_user_can('manage_options') ||
                    (is_user_logged_in() && $existing_invite->post_author == get_current_user_id())) {
                    $is_update = true;
                } else {
                    wp_send_json_error(array('message' => 'Non hai i permessi per modificare questo invito'));
                }
            }
        }

        // Raccogli dati dal wizard
        $data = array(
            'title' => sanitize_text_field($_POST['invite_title'] ?? ''),
            'message' => sanitize_textarea_field($_POST['invite_message'] ?? ''),
            'final_message' => sanitize_textarea_field($_POST['final_message'] ?? ''),
            'final_message_button_text' => sanitize_text_field($_POST['final_message_button_text'] ?? ''),
            'event_date' => sanitize_text_field($_POST['event_date'] ?? ''),
            'event_time' => sanitize_text_field($_POST['event_time'] ?? ''),
            'event_location' => sanitize_text_field($_POST['event_location'] ?? ''),
            'event_address' => sanitize_text_field($_POST['event_address'] ?? ''),
            'user_image_id' => intval($_POST['user_image_id'] ?? 0),
            'user_image_url' => esc_url_raw($_POST['user_image_url'] ?? ''),
        );

        $template_id = intval($_POST['template_id'] ?? 1);
        $event_category = sanitize_text_field($_POST['event_category'] ?? '');

        // Validazione base
        if (empty($data['title']) || empty($data['message']) || empty($data['event_date'])) {
            wp_send_json_error(array('message' => 'Campi obbligatori mancanti'));
        }

        // Crea o aggiorna l'invito
        if ($is_update) {
            $invite_id = WI_Invites::save_invite($data, $template_id, $invite_id);
        } else {
            $invite_id = WI_Invites::save_invite($data, $template_id, 0);
        }

        if ($invite_id) {
            // Salva categoria evento
            if (!empty($event_category)) {
                update_post_meta($invite_id, '_wi_event_category', $event_category);
            }

            // Salva RSVP settings nella tabella wi_rsvp_settings
            $rsvp_enabled = filter_var($_POST['rsvp_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($rsvp_enabled) {
                // Prepara dati per tabella RSVP
                $rsvp_data = array(
                    'rsvp_enabled' => 1,
                    'rsvp_deadline' => !empty($_POST['rsvp_deadline']) ? sanitize_text_field($_POST['rsvp_deadline']) : null,
                    'max_guests_per_response' => intval($_POST['rsvp_max_guests'] ?? 1), // Campo corretto della tabella
                    'notify_admin' => filter_var($_POST['rsvp_notify_admin'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'admin_email' => !empty($_POST['rsvp_admin_email']) ? sanitize_email($_POST['rsvp_admin_email']) : get_option('admin_email')
                );

                // Menu choices
                if (!empty($_POST['rsvp_menu_choices'])) {
                    $menu_choices = sanitize_text_field($_POST['rsvp_menu_choices']);
                    $choices_array = array_map('trim', explode(',', $menu_choices));
                    $rsvp_data['menu_choices'] = json_encode($choices_array);
                } else {
                    $rsvp_data['menu_choices'] = json_encode(array('Carne', 'Pesce', 'Vegetariano'));
                }

                // Salva nella tabella wi_rsvp_settings
                WI_RSVP_Database::update_settings($invite_id, $rsvp_data);
            } else {
                // RSVP disabilitato - salva solo flag
                WI_RSVP_Database::update_settings($invite_id, array('rsvp_enabled' => 0));
            }

            wi_log($is_update ? 'Invite updated via wizard' : 'Invite created via wizard', 'info', array(
                'invite_id' => $invite_id,
                'template_id' => $template_id,
                'category' => $event_category,
                'rsvp_enabled' => $rsvp_enabled,
                'is_update' => $is_update
            ));

            // Determina URL redirect in base ai permessi utente
            $view_url = get_permalink($invite_id);

            if (current_user_can('manage_options')) {
                // Admin: vai alla pagina di modifica backend
                $edit_url = admin_url('admin.php?page=wedding-invites-edit&invite_id=' . $invite_id);
            } else {
                // Utente normale: vai all'invito pubblico
                $edit_url = $view_url;
            }

            wp_send_json_success(array(
                'message' => $is_update ? 'Invito aggiornato con successo!' : 'Invito creato con successo!',
                'invite_id' => $invite_id,
                'edit_url' => $edit_url,
                'view_url' => $view_url
            ));
        } else {
            wp_send_json_error(array('message' => 'Errore durante la creazione dell\'invito'));
        }
    }

    /**
     * AJAX: Generate QR Code for invite
     */
    public function ajax_generate_qr_code() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Non autorizzato'));
        }

        $invite_id = intval($_POST['invite_id'] ?? 0);

        if (!$invite_id) {
            wp_send_json_error(array('message' => 'ID invito non valido'));
        }

        // Parse options
        $options = array();
        if (!empty($_POST['options'])) {
            $options = json_decode(stripslashes($_POST['options']), true);
        }

        // Generate QR code
        $qr_url = WI_QRCode::generate($invite_id, $options);

        if ($qr_url) {
            // Salva URL in meta per recupero veloce
            update_post_meta($invite_id, '_wi_qr_code_url', $qr_url);

            wp_send_json_success(array(
                'message' => 'QR Code generato con successo',
                'qr_url' => $qr_url
            ));
        } else {
            wp_send_json_error(array('message' => 'Errore durante la generazione del QR Code'));
        }
    }

    /**
     * AJAX: Get existing QR Code
     */
    public function ajax_get_qr_code() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Non autorizzato'));
        }

        $invite_id = intval($_POST['invite_id'] ?? 0);

        if (!$invite_id) {
            wp_send_json_error(array('message' => 'ID invito non valido'));
        }

        $qr_url = get_post_meta($invite_id, '_wi_qr_code_url', true);

        if ($qr_url) {
            wp_send_json_success(array('qr_url' => $qr_url));
        } else {
            wp_send_json_success(array('qr_url' => null));
        }
    }

    /**
     * AJAX: Preview QR Code with custom options (for modal preview)
     */
    public function ajax_preview_qr_code() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Non autorizzato'));
        }

        $invite_id = intval($_POST['invite_id'] ?? 0);

        if (!$invite_id) {
            wp_send_json_error(array('message' => 'ID invito non valido'));
        }

        // Parse options
        $options = array();
        if (!empty($_POST['options'])) {
            $options = json_decode(stripslashes($_POST['options']), true);
        }

        // Generate temporary preview QR (non salvato in meta)
        $qr_url = WI_QRCode::generate($invite_id, $options);

        if ($qr_url) {
            wp_send_json_success(array('qr_url' => $qr_url));
        } else {
            wp_send_json_error(array('message' => 'Errore preview QR'));
        }
    }

    public function ajax_publish_invite() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Non autorizzato');
        }

        $data = $_POST['invite_data'];
        $template_id = intval($_POST['template_id']);
        $invite_id = isset($_POST['invite_id']) ? intval($_POST['invite_id']) : 0;

        $result = WI_Invites::save_invite($data, $template_id, $invite_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Invito pubblicato con successo!',
                'invite_id' => $result,
                'url' => get_permalink($result)
            ));
        } else {
            wp_send_json_error('Errore nel salvataggio dell\'invito');
        }
    }
    
    public function ajax_delete_invite() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }
        
        $invite_id = intval($_POST['invite_id']);
        
        if (wp_delete_post($invite_id, true)) {
            wp_send_json_success('Invito eliminato');
        } else {
            wp_send_json_error('Errore nell\'eliminazione');
        }
    }
    
    public function ajax_get_invite() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Non autorizzato');
        }

        $invite_id = intval($_POST['invite_id']);
        $data = WI_Invites::get_invite_data($invite_id);

        if ($data) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error('Invito non trovato');
        }
    }

    public function ajax_upload_logo() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }
        
        if (!empty($_FILES['logo'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('logo', 0);
            
            if (is_wp_error($attachment_id)) {
                wp_send_json_error($attachment_id->get_error_message());
            } else {
                update_option('wi_site_logo', $attachment_id);
                wp_send_json_success(array(
                    'url' => wp_get_attachment_url($attachment_id),
                    'id' => $attachment_id
                ));
            }
        }
        
        wp_send_json_error('Nessun file caricato');
    }
    
    public function ajax_save_logo() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }

        $logo_id = intval($_POST['logo_id']);
        update_option('wi_site_logo', $logo_id);

        wp_send_json_success();
    }

    public function ajax_remove_logo() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }

        delete_option('wi_site_logo');
        wp_send_json_success();
    }

    /**
     * RSVP Dashboard Admin Page
     */
    public function admin_rsvp_page() {
        // Determina quale sottopagina RSVP mostrare
        $invite_id = isset($_GET['invite_id']) ? intval($_GET['invite_id']) : 0;

        if ($invite_id) {
            // Dashboard dettagliato per singolo invito
            include WI_PLUGIN_DIR . 'admin/rsvp-dashboard.php';
        } else {
            // Lista inviti con riepilogo RSVP
            include WI_PLUGIN_DIR . 'admin/rsvp-invites-list.php';
        }
    }

    public function ajax_upload_user_image() {
        check_ajax_referer(WI_NONCE_PUBLIC, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Non autorizzato');
        }
        
        if (!empty($_FILES['user_image'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('user_image', 0);
            
            if (is_wp_error($attachment_id)) {
                wp_send_json_error($attachment_id->get_error_message());
            } else {
                wp_send_json_success(array(
                    'url' => wp_get_attachment_url($attachment_id),
                    'id' => $attachment_id
                ));
            }
        }
        
        wp_send_json_error('Nessun file caricato');
    }
    
    public function ajax_preview_edited_invite() {
        check_ajax_referer(WI_NONCE_ADMIN, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }

        $data = $_POST['invite_data'];
        $template_id = intval($_POST['template_id']);

        // Ottieni URL immagine se disponibile
        if (!empty($data['user_image_id'])) {
            $data['user_image_url'] = wp_get_attachment_url($data['user_image_id']);
        }

        $preview_html = WI_Invites::generate_invite_html($data, $template_id, true);

        wp_send_json_success(array('html' => $preview_html));
    }

    public function ajax_save_css_styles() {
        check_ajax_referer(WI_NONCE_TEMPLATE, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Non autorizzato');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        $template_id = intval($_POST['template_id']);

        // Verifica che il template esista
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE id = %d", $template_id));
        if (!$exists) {
            wp_send_json_error('Template non trovato');
        }

        // Prepara i dati per l'aggiornamento - TUTTI i campi CSS disponibili
        $update_data = array(
            // Font e Colori Titolo
            'title_font' => sanitize_text_field($_POST['title_font']),
            'title_size' => intval($_POST['title_size']),
            'title_color' => wi_sanitize_hex_color($_POST['title_color']),
            'divider_color' => wi_sanitize_hex_color($_POST['divider_color'] ?? '#d4af37'),

            // Countdown
            'countdown_style' => sanitize_text_field($_POST['countdown_style']),
            'countdown_font' => sanitize_text_field($_POST['countdown_font'] ?? 'Lora'),
            'countdown_color' => wi_sanitize_hex_color($_POST['countdown_color']),
            'countdown_bg_color' => wi_sanitize_hex_color($_POST['countdown_bg_color']),
            'countdown_border_color' => wi_sanitize_hex_color($_POST['countdown_border_color'] ?? '#e2e8f0'),
            'countdown_label_font' => sanitize_text_field($_POST['countdown_label_font'] ?? 'Montserrat'),
            'countdown_label_size' => intval($_POST['countdown_label_size'] ?? 14),
            'countdown_label_color' => wi_sanitize_hex_color($_POST['countdown_label_color'] ?? '#64748b'),
            'countdown_animated' => isset($_POST['countdown_animated']) ? 1 : 0,

            // Font e Colori Messaggio
            'message_font' => sanitize_text_field($_POST['message_font']),
            'message_size' => intval($_POST['message_size']),
            'message_color' => wi_sanitize_hex_color($_POST['message_color']),
            'message_bg_color' => wi_sanitize_hex_color($_POST['message_bg_color'] ?? '#f9f9f9'),

            // Messaggio Finale
            'final_message_btn_bg_color' => wi_sanitize_hex_color($_POST['final_message_btn_bg_color'] ?? '#d4af37'),
            'final_message_btn_text_color' => wi_sanitize_hex_color($_POST['final_message_btn_text_color'] ?? '#ffffff'),
            'final_message_text_color' => wi_sanitize_hex_color($_POST['final_message_text_color'] ?? '#333333'),

            // Font e Colori Dettagli
            'details_font' => sanitize_text_field($_POST['details_font'] ?? 'Lora'),
            'details_label_color' => wi_sanitize_hex_color($_POST['details_label_color'] ?? '#333333'),
            'details_value_color' => wi_sanitize_hex_color($_POST['details_value_color'] ?? '#666666'),
            'details_bg_color' => wi_sanitize_hex_color($_POST['details_bg_color'] ?? '#ffffff'),
            'details_border_color' => wi_sanitize_hex_color($_POST['details_border_color'] ?? '#d4af37'),
            'details_align' => in_array($_POST['details_align'] ?? '', ['left', 'center', 'right']) ? $_POST['details_align'] : 'left',
            'hide_event_icons' => isset($_POST['hide_event_icons']) ? 1 : 0,

            // Colori Pulsanti e Mappa
            'button_bg_color' => wi_sanitize_hex_color($_POST['button_bg_color'] ?? '#667eea'),
            'button_text_color' => wi_sanitize_hex_color($_POST['button_text_color'] ?? '#ffffff'),
            'map_marker_color' => wi_sanitize_hex_color($_POST['map_marker_color'] ?? '#667eea'),

            // Immagini - Opacità e Grandezza
            'header_opacity' => min(1.00, max(0, floatval($_POST['header_opacity'] ?? 1.00))),
            'header_size' => min(200, max(10, intval($_POST['header_size'] ?? 100))),
            'decoration_top_opacity' => min(1.00, max(0, floatval($_POST['decoration_top_opacity'] ?? 1.00))),
            'decoration_top_size' => min(200, max(10, intval($_POST['decoration_top_size'] ?? 100))),
            'decoration_bottom_opacity' => min(1.00, max(0, floatval($_POST['decoration_bottom_opacity'] ?? 1.00))),
            'decoration_bottom_size' => min(200, max(10, intval($_POST['decoration_bottom_size'] ?? 100))),
            'user_image_opacity' => min(1.00, max(0, floatval($_POST['user_image_opacity'] ?? 1.00))),
            'user_image_size' => min(200, max(10, intval($_POST['user_image_size'] ?? 100))),

            // Sfondo generale
            'background_color' => wi_sanitize_hex_color($_POST['background_color']),
            'background_opacity' => min(1.00, max(0, floatval($_POST['background_opacity']))),
            'background_size' => min(200, max(10, intval($_POST['background_size'] ?? 100))),

            // CSS Personalizzato - SICURO: sanitizzazione avanzata
            'custom_css' => isset($_POST['custom_css']) ? wi_sanitize_css($_POST['custom_css']) : '',
        );

        // Aggiorna solo i campi che abbiamo nel database
        $result = $wpdb->update(
            $table,
            $update_data,
            array('id' => $template_id),
            array(
                // Titolo (4) - aggiunto divider_color
                '%s', '%d', '%s', '%s',
                // Countdown (9)
                '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d',
                // Messaggio (4)
                '%s', '%d', '%s', '%s',
                // Messaggio Finale (3) - NUOVO
                '%s', '%s', '%s',
                // Dettagli (8) - aggiunti details_label_color, details_value_color
                '%s', '%s', '%s', '%s', '%s', '%s', '%d',
                // Pulsanti e Mappa (3)
                '%s', '%s', '%s',
                // Immagini (8)
                '%f', '%d', '%f', '%d', '%f', '%d', '%f', '%d',
                // Sfondo (3)
                '%s', '%f', '%d',
                // CSS Personalizzato (1)
                '%s'
            ),
            array('%d')
        );

        // $wpdb->update ritorna FALSE solo in caso di errore, 0 se nessuna modifica, >0 se modificato
        if ($result === false) {
            $error = $wpdb->last_error;
            wp_send_json_error('Errore database: ' . $error);
        } else {
            wp_send_json_success('Stili salvati con successo (' . $result . ' righe aggiornate)');
        }
    }

    public function ajax_delete_template() {
        // Verifica nonce per sicurezza CSRF
        check_ajax_referer(WI_NONCE_TEMPLATE, 'nonce');

        // Verifica permessi utente
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => 'Non hai i permessi per eliminare template.'
            ));
        }

        $template_id = intval($_POST['template_id']);

        if ($template_id <= 0) {
            wp_send_json_error(array(
                'message' => 'ID template non valido.'
            ));
        }

        // Verifica che il template esista
        $template = WI_Templates::get_template($template_id);
        if (!$template) {
            wp_send_json_error(array(
                'message' => 'Template non trovato.'
            ));
        }

        // Tenta l'eliminazione (il metodo verifica automaticamente se ci sono inviti associati)
        $deleted = WI_Templates::delete_template($template_id);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => 'Template eliminato con successo!'
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Impossibile eliminare il template. Probabilmente è utilizzato da uno o più inviti esistenti. Elimina prima gli inviti che lo utilizzano.'
            ));
        }
    }

    public function invite_template($template) {
        if (is_singular('wi_invite')) {
            $custom_template = WI_PLUGIN_DIR . 'templates/single-invite.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        return $template;
    }
}

// Inizializza il plugin
function wedding_invites_init() {
    return Wedding_Invites_Plugin::get_instance();
}

// ===== OTTIMIZZAZIONE AUTOMATICA IMMAGINI =====

// Aumenta limite upload a 7MB
add_filter('upload_size_limit', function($size) {
    return 7 * 1024 * 1024; // 7MB
});

// Ottimizza immagini dopo upload (riduce peso mantenendo qualità)
add_filter('wp_handle_upload', function($upload) {
    // Solo per immagini
    if (strpos($upload['type'], 'image') === false) {
        return $upload;
    }

    $file_path = $upload['file'];

    // Carica l'editor immagini di WordPress
    $image_editor = wp_get_image_editor($file_path);

    if (is_wp_error($image_editor)) {
        return $upload; // Se errore, restituisci originale
    }

    $size = $image_editor->get_size();
    $max_width = 1920;  // Larghezza massima per web
    $max_height = 1920; // Altezza massima per web

    // Ridimensiona se troppo grande
    if ($size['width'] > $max_width || $size['height'] > $max_height) {
        $image_editor->resize($max_width, $max_height, false);
    }

    // Imposta qualità ottimale (82% = buon compromesso qualità/peso)
    $image_editor->set_quality(82);

    // Salva l'immagine ottimizzata
    $saved = $image_editor->save($file_path);

    if (is_wp_error($saved)) {
        return $upload; // Se errore, restituisci originale
    }

    // Aggiorna dimensione file
    $upload['file'] = $saved['path'];

    return $upload;
});

// Qualità JPEG per thumbnails (75% - miglior compromesso)
add_filter('jpeg_quality', function($quality, $context) {
    return 75;
}, 10, 2);

// Qualità WebP (se supportato)
add_filter('wp_editor_set_quality', function($quality, $mime_type) {
    if ($mime_type === 'image/webp') {
        return 75;
    }
    return $quality;
}, 10, 2);

/**
 * Helper function: Sanitize hex color con supporto rgba/rgb
 *
 * @param string $color Colore da sanitizzare
 * @return string Colore sanitizzato o default
 */
function wi_sanitize_hex_color($color) {
    // Se vuoto, restituisci trasparente
    if (empty($color)) {
        return 'transparent';
    }

    // Rimuovi spazi
    $color = trim($color);

    // Supporto rgba()/rgb()
    if (preg_match('/^rgba?\([\d\s,\.]+\)$/i', $color)) {
        return $color; // Valido rgba/rgb
    }

    // Hex color standard (#FFF o #FFFFFF)
    if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
        return $color;
    }

    // Keyword CSS valide
    $valid_keywords = array('transparent', 'inherit', 'currentColor', 'white', 'black');
    if (in_array(strtolower($color), $valid_keywords)) {
        return strtolower($color);
    }

    // Fallback: usa sanitize_hex_color di WP (solo hex)
    $sanitized = sanitize_hex_color($color);
    return $sanitized ? $sanitized : '#000000';
}

/**
 * Logger helper per debug e monitoraggio
 * Scrive solo se WP_DEBUG è attivo
 *
 * @param string $message Messaggio da loggare
 * @param string $level Livello: 'info', 'warning', 'error'
 * @param array $context Contesto aggiuntivo
 */
function wi_log($message, $level = 'info', $context = array()) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    $log_message = sprintf(
        '[Wedding Invites %s] [%s] %s',
        WI_VERSION,
        strtoupper($level),
        $message
    );

    if (!empty($context)) {
        $log_message .= ' | Context: ' . wp_json_encode($context);
    }

    error_log($log_message);
}

/**
 * Sanitize CSS personalizzato in modo sicuro
 * Rimuove script JavaScript e codice pericoloso mantenendo CSS valido
 *
 * @param string $css CSS da sanitizzare
 * @return string CSS sanitizzato
 */
function wi_sanitize_css($css) {
    if (empty($css)) {
        return '';
    }

    // Rimuovi tag <style>, <script>, HTML
    $css = wp_strip_all_tags($css);

    // Rimuovi commenti HTML che potrebbero contenere codice
    $css = preg_replace('/<!--(.*)-->/Uis', '', $css);

    // Rimuovi expression() (IE vulnerability)
    $css = preg_replace('/expression\s*\(/i', '/* blocked */', $css);

    // Rimuovi javascript: protocol
    $css = preg_replace('/javascript\s*:/i', '/* blocked */', $css);

    // Rimuovi vbscript: protocol
    $css = preg_replace('/vbscript\s*:/i', '/* blocked */', $css);

    // Rimuovi -moz-binding (XUL vulnerability)
    $css = preg_replace('/-moz-binding\s*:/i', '/* blocked */', $css);

    // Rimuovi @import con url() pericolosi
    $css = preg_replace('/@import\s+url\s*\(\s*["\']?javascript:/i', '/* blocked */', $css);

    // Sanitizza url() - blocca javascript:, data: con script
    $css = preg_replace_callback(
        '/url\s*\(\s*(["\']?)([^"\')]+)\1\s*\)/i',
        function($matches) {
            $url = trim($matches[2]);

            // Blocca javascript:, vbscript:, data:text/html
            if (preg_match('/^(javascript|vbscript|data:text\/html)/i', $url)) {
                return '/* blocked unsafe url */';
            }

            // Permetti data:image/, https://, http://, /path
            if (preg_match('/^(data:image\/|https?:\/\/|\/)/i', $url)) {
                return 'url(' . esc_url_raw($url) . ')';
            }

            // Default: permetti URL relativi
            return 'url(' . $matches[1] . esc_attr($url) . $matches[1] . ')';
        },
        $css
    );

    return $css;
}

// Carica la classe database prima dell'attivazione
require_once plugin_dir_path(__FILE__) . 'includes/class-wi-database.php';

/**
 * Activation hook - deve essere registrato PRIMA di istanziare la classe
 */
register_activation_hook(__FILE__, function() {
    // Crea tabelle principali
    WI_Database::create_tables();

    // Carica classe RSVP Database se non ancora caricata
    if (!class_exists('WI_RSVP_Database')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wi-rsvp-database.php';
    }

    // Crea tabelle RSVP
    WI_RSVP_Database::create_tables();

    // Carica classe Story Cards se non ancora caricata
    if (!class_exists('WI_Story_Cards')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wi-story-cards.php';
    }

    // Crea tabelle Story Card e template default
    WI_Story_Cards::create_tables();
    WI_Story_Cards::create_default_template();

    // Crea pagina di default
    $page_check = get_page_by_path('crea-invito');
    if (!$page_check) {
        $page = array(
            'post_title'    => 'Crea Invito',
            'post_content'  => '[wedding_invites_form]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'crea-invito'
        );
        wp_insert_post($page);
    }

    flush_rewrite_rules();
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

add_action('plugins_loaded', 'wedding_invites_init');