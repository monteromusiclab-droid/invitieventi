<?php
/**
 * Story Cards Management
 * Gestisce i template Story Card 9:16 per condivisione social
 *
 * @package Wedding_Invites_Pro
 */

if (!defined('ABSPATH')) exit;

class WI_Story_Cards {

    /**
     * Nome tabella Story Card templates
     */
    private static $table_name = 'wi_story_card_templates';

    /**
     * Inizializza la classe
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'create_tables'));
    }

    /**
     * Crea tabelle database
     */
    public static function create_tables() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            category_id bigint(20) DEFAULT NULL COMMENT 'Fallback: categoria evento',
            invite_template_id bigint(20) DEFAULT NULL COMMENT 'Priorità 1: template invito specifico',
            background_image_url text NOT NULL,
            layout_config longtext NOT NULL COMMENT 'JSON con posizioni campi testo',
            is_default tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category_id (category_id),
            KEY invite_template_id (invite_template_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Salva nuovo Story Card template
     */
    public static function save_template($data) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Sanitizza invite_template_id: se vuoto o 0, salva NULL
        $invite_template_id = null;
        if (isset($data['invite_template_id']) && !empty($data['invite_template_id'])) {
            $invite_template_id = intval($data['invite_template_id']);
        }

        $template_data = array(
            'name' => sanitize_text_field($data['name']),
            'category_id' => isset($data['category_id']) ? intval($data['category_id']) : null,
            'invite_template_id' => $invite_template_id, // FIX: Aggiunto campo mancante
            'background_image_url' => esc_url_raw($data['background_image_url']),
            'layout_config' => wp_json_encode($data['layout_config']),
            'is_default' => isset($data['is_default']) ? intval($data['is_default']) : 0
        );

        // DEBUG: Log dati da salvare (solo se WP_DEBUG attivo)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('WI Story Card Save - Template Data:');
            error_log('Name: ' . $template_data['name']);
            error_log('Category ID: ' . ($template_data['category_id'] ?? 'NULL'));
            error_log('Invite Template ID: ' . ($template_data['invite_template_id'] ?? 'NULL'));
            error_log('Is Default: ' . $template_data['is_default']);
        }

        if (isset($data['id']) && $data['id'] > 0) {
            // Update esistente
            $result = $wpdb->update(
                $table,
                $template_data,
                array('id' => intval($data['id'])),
                array('%s', '%d', '%d', '%s', '%s', '%d'), // FIX: Aggiunto %d per invite_template_id
                array('%d')
            );

            // DEBUG: Log risultato update
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WI Story Card Update - Result: ' . ($result !== false ? 'SUCCESS' : 'FAILED'));
                if ($wpdb->last_error) {
                    error_log('WI Story Card Update - Error: ' . $wpdb->last_error);
                }
            }

            return intval($data['id']);
        } else {
            // Inserisci nuovo
            $result = $wpdb->insert($table, $template_data, array('%s', '%d', '%d', '%s', '%s', '%d')); // FIX: Aggiunto format completo

            // DEBUG: Log risultato insert
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WI Story Card Insert - Result: ' . ($result !== false ? 'SUCCESS (ID: ' . $wpdb->insert_id . ')' : 'FAILED'));
                if ($wpdb->last_error) {
                    error_log('WI Story Card Insert - Error: ' . $wpdb->last_error);
                }
            }

            return $wpdb->insert_id;
        }
    }

    /**
     * Ottieni template Story Card per ID
     */
    public static function get_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));

        if ($template && $template->layout_config) {
            $template->layout_config = json_decode($template->layout_config, true);
        }

        return $template;
    }

    /**
     * Ottieni template per categoria (legacy - usa get_template_for_invite)
     */
    public static function get_template_by_category($category_id) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Prima cerca template specifico per categoria
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE category_id = %d LIMIT 1",
            $category_id
        ));

        // Se non trovato, usa il default
        if (!$template) {
            $template = $wpdb->get_row(
                "SELECT * FROM $table WHERE is_default = 1 LIMIT 1"
            );
        }

        if ($template && $template->layout_config) {
            $template->layout_config = json_decode($template->layout_config, true);
        }

        return $template;
    }

    /**
     * Ottieni template per invito con sistema a cascata (HYBRID)
     *
     * Priorità:
     * 1. Template ID specifico dell'invito (invite_template_id)
     * 2. Categoria evento (category_id)
     * 3. Template default
     *
     * @param int $invite_id ID dell'invito
     * @param array $invite_data Dati dell'invito
     * @return object|null Template trovato
     */
    public static function get_template_for_invite($invite_id, $invite_data) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // PRIORITÀ 1: Cerca per template ID specifico dell'invito
        $template_id = get_post_meta($invite_id, '_wi_template_id', true);
        if ($template_id) {
            $template = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE invite_template_id = %d LIMIT 1",
                $template_id
            ));
            if ($template) {
                $template->layout_config = json_decode($template->layout_config, true);
                return $template;
            }
        }

        // PRIORITÀ 2: Fallback a categoria evento
        $category_id = isset($invite_data['category_id']) ? $invite_data['category_id'] : get_post_meta($invite_id, '_wi_category_id', true);
        if ($category_id) {
            $template = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE category_id = %d LIMIT 1",
                $category_id
            ));
            if ($template) {
                $template->layout_config = json_decode($template->layout_config, true);
                return $template;
            }
        }

        // PRIORITÀ 3: Usa template default
        $template = $wpdb->get_row("SELECT * FROM $table WHERE is_default = 1 LIMIT 1");
        if ($template && $template->layout_config) {
            $template->layout_config = json_decode($template->layout_config, true);
        }

        return $template;
    }

    /**
     * Ottieni tutti i template
     */
    public static function get_all_templates() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        $templates = $wpdb->get_results("SELECT * FROM $table ORDER BY is_default DESC, name ASC");

        foreach ($templates as $template) {
            if ($template->layout_config) {
                $template->layout_config = json_decode($template->layout_config, true);
            }
        }

        return $templates;
    }

    /**
     * Elimina template
     */
    public static function delete_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    /**
     * Genera HTML Story Card per invito
     */
    public static function render_story_card($invite_id, $invite_data) {
        // Usa il nuovo sistema a cascata (hybrid) per ottenere il template
        $template = self::get_template_for_invite($invite_id, $invite_data);

        if (!$template) {
            return ''; // Nessun template disponibile
        }

        $layout = $template->layout_config;

        // Normalizza i nomi dei campi (supporta sia 'title' che 'invite_title')
        $title = isset($invite_data['title']) ? $invite_data['title'] : (isset($invite_data['invite_title']) ? $invite_data['invite_title'] : '');
        $message = isset($invite_data['message']) ? $invite_data['message'] : (isset($invite_data['invite_message']) ? $invite_data['invite_message'] : '');

        ob_start();
        ?>
        <div class="wi-story-card-wrapper">
            <div class="wi-story-card" id="wi-story-card-<?php echo $invite_id; ?>">
                <!-- Background Image -->
                <div class="wi-story-background" style="background-image: url('<?php echo esc_url($template->background_image_url); ?>');">
                </div>

                <!-- Text Overlays -->
                <div class="wi-story-overlay">
                    <?php if (isset($layout['title']) && !empty($title)): ?>
                    <div class="wi-story-text wi-story-title" style="<?php echo self::get_text_styles($layout['title']); ?>">
                        <?php echo esc_html($title); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($layout['date']) && !empty($invite_data['event_date'])): ?>
                    <div class="wi-story-text wi-story-date" style="<?php echo self::get_text_styles($layout['date']); ?>">
                        <?php echo date_i18n('j F Y', strtotime($invite_data['event_date'])); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($layout['time']) && !empty($invite_data['event_time'])): ?>
                    <div class="wi-story-text wi-story-time" style="<?php echo self::get_text_styles($layout['time']); ?>">
                        <?php echo esc_html($invite_data['event_time']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($layout['location']) && !empty($invite_data['event_location'])): ?>
                    <div class="wi-story-text wi-story-location" style="<?php echo self::get_text_styles($layout['location']); ?>">
                        <?php echo esc_html($invite_data['event_location']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($layout['address']) && !empty($invite_data['event_address'])): ?>
                    <div class="wi-story-text wi-story-address" style="<?php echo self::get_text_styles($layout['address']); ?>">
                        <?php echo esc_html($invite_data['event_address']); ?>
                    </div>
                    <?php endif; ?>

                    <?php
                    // Ottieni RSVP deadline dal database
                    $rsvp_settings = WI_RSVP_Database::get_settings($invite_id);
                    $rsvp_deadline = $rsvp_settings && !empty($rsvp_settings->rsvp_deadline) ? $rsvp_settings->rsvp_deadline : '';
                    ?>
                    <?php if (isset($layout['rsvp_deadline']) && !empty($rsvp_deadline)): ?>
                    <div class="wi-story-text wi-story-rsvp-deadline" style="<?php echo self::get_text_styles($layout['rsvp_deadline']); ?>">
                        Conferma entro: <?php echo date_i18n('j F Y', strtotime($rsvp_deadline)); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($layout['message']) && !empty($message)): ?>
                    <div class="wi-story-text wi-story-message" style="<?php echo self::get_text_styles($layout['message']); ?>">
                        <?php echo esc_html(wp_trim_words($message, 15)); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="wi-story-actions">
                <button type="button" class="wi-btn wi-btn-primary wi-download-story" data-invite-id="<?php echo $invite_id; ?>">
                    <span class="wi-btn-icon">📥</span>
                    Scarica Story (PNG)
                </button>
                <button type="button" class="wi-btn wi-btn-outline wi-share-story" data-invite-id="<?php echo $invite_id; ?>">
                    <span class="wi-btn-icon">📤</span>
                    Condividi
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Genera stili CSS inline per testo
     */
    private static function get_text_styles($config) {
        $styles = array();

        // Posizione
        if (isset($config['top'])) {
            $styles[] = 'top: ' . floatval($config['top']) . '%';
        }
        if (isset($config['left'])) {
            $styles[] = 'left: ' . floatval($config['left']) . '%';
        }
        if (isset($config['width'])) {
            $styles[] = 'width: ' . floatval($config['width']) . '%';
        }

        // Tipografia
        if (isset($config['fontSize'])) {
            $styles[] = 'font-size: ' . intval($config['fontSize']) . 'px';
        }
        if (isset($config['fontWeight'])) {
            $styles[] = 'font-weight: ' . intval($config['fontWeight']);
        }
        if (isset($config['color'])) {
            $styles[] = 'color: ' . sanitize_hex_color($config['color']);
        }
        if (isset($config['textAlign'])) {
            $styles[] = 'text-align: ' . sanitize_text_field($config['textAlign']);
        }

        // Font family
        if (isset($config['fontFamily'])) {
            $styles[] = 'font-family: ' . sanitize_text_field($config['fontFamily']);
        }

        // Text shadow (per leggibilità)
        if (isset($config['textShadow']) && $config['textShadow']) {
            $styles[] = 'text-shadow: 0 2px 4px rgba(0,0,0,0.3)';
        }

        return implode('; ', $styles);
    }

    /**
     * Crea template default alla prima attivazione
     */
    public static function create_default_template() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;

        // Verifica se esiste già un template default
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE is_default = 1");

        if ($existing > 0) {
            return; // Template default già esiste
        }

        // Layout config di default (centrato, elegante)
        $default_layout = array(
            'title' => array(
                'top' => 30,
                'left' => 10,
                'width' => 80,
                'fontSize' => 42,
                'fontWeight' => 700,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Playfair Display', serif",
                'textShadow' => true
            ),
            'date' => array(
                'top' => 43,
                'left' => 10,
                'width' => 80,
                'fontSize' => 24,
                'fontWeight' => 400,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'time' => array(
                'top' => 49,
                'left' => 10,
                'width' => 80,
                'fontSize' => 20,
                'fontWeight' => 400,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'location' => array(
                'top' => 55,
                'left' => 10,
                'width' => 80,
                'fontSize' => 22,
                'fontWeight' => 500,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'address' => array(
                'top' => 61,
                'left' => 10,
                'width' => 80,
                'fontSize' => 16,
                'fontWeight' => 400,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'rsvp_deadline' => array(
                'top' => 70,
                'left' => 10,
                'width' => 80,
                'fontSize' => 18,
                'fontWeight' => 500,
                'color' => '#ffeb3b',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'message' => array(
                'top' => 78,
                'left' => 10,
                'width' => 80,
                'fontSize' => 16,
                'fontWeight' => 300,
                'color' => '#ffffff',
                'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            )
        );

        // Salva template default con immagine inclusa nel plugin
        $default_bg_url = WI_PLUGIN_URL . 'assets/images/story-templates/default-elegant.svg';

        $wpdb->insert(
            $table,
            array(
                'name' => 'Template Default Elegante',
                'category_id' => null,
                'background_image_url' => $default_bg_url,
                'layout_config' => wp_json_encode($default_layout),
                'is_default' => 1
            )
        );
    }

    /**
     * Installa template predefiniti per categorie comuni
     * Può essere chiamato dall'admin per creare template iniziali
     */
    public static function install_predefined_templates() {
        global $wpdb;
        $table = $wpdb->prefix . self::$table_name;
        $categories_table = $wpdb->prefix . 'wi_event_categories';

        // Layout base condiviso
        $base_layout = array(
            'title' => array(
                'top' => 30, 'left' => 10, 'width' => 80,
                'fontSize' => 42, 'fontWeight' => 700,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Playfair Display', serif",
                'textShadow' => true
            ),
            'date' => array(
                'top' => 43, 'left' => 10, 'width' => 80,
                'fontSize' => 24, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'time' => array(
                'top' => 49, 'left' => 10, 'width' => 80,
                'fontSize' => 20, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'location' => array(
                'top' => 55, 'left' => 10, 'width' => 80,
                'fontSize' => 22, 'fontWeight' => 500,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'address' => array(
                'top' => 61, 'left' => 10, 'width' => 80,
                'fontSize' => 16, 'fontWeight' => 400,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'rsvp_deadline' => array(
                'top' => 70, 'left' => 10, 'width' => 80,
                'fontSize' => 18, 'fontWeight' => 500,
                'color' => '#ffeb3b', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            ),
            'message' => array(
                'top' => 78, 'left' => 10, 'width' => 80,
                'fontSize' => 16, 'fontWeight' => 300,
                'color' => '#ffffff', 'textAlign' => 'center',
                'fontFamily' => "'Lato', sans-serif",
                'textShadow' => true
            )
        );

        // Template per categoria "Matrimonio"
        $matrimonio_cat = $wpdb->get_row("SELECT id FROM $categories_table WHERE slug = 'matrimonio' LIMIT 1");
        if ($matrimonio_cat) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE category_id = %d",
                $matrimonio_cat->id
            ));

            if ($existing == 0) {
                $wpdb->insert($table, array(
                    'name' => 'Matrimonio Classico',
                    'category_id' => $matrimonio_cat->id,
                    'background_image_url' => WI_PLUGIN_URL . 'assets/images/story-templates/wedding-classic.svg',
                    'layout_config' => wp_json_encode($base_layout),
                    'is_default' => 0
                ));
            }
        }

        // Template per categoria "Compleanno"
        $compleanno_cat = $wpdb->get_row("SELECT id FROM $categories_table WHERE slug = 'compleanno' LIMIT 1");
        if ($compleanno_cat) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE category_id = %d",
                $compleanno_cat->id
            ));

            if ($existing == 0) {
                // Layout modificato per compleanno (colori più vivaci)
                $birthday_layout = $base_layout;
                $birthday_layout['rsvp_deadline']['color'] = '#ffd700'; // Gold invece di giallo

                $wpdb->insert($table, array(
                    'name' => 'Compleanno Divertente',
                    'category_id' => $compleanno_cat->id,
                    'background_image_url' => WI_PLUGIN_URL . 'assets/images/story-templates/birthday-fun.svg',
                    'layout_config' => wp_json_encode($birthday_layout),
                    'is_default' => 0
                ));
            }
        }

        return array(
            'success' => true,
            'message' => 'Template predefiniti installati con successo!'
        );
    }
}

// Inizializza la classe
WI_Story_Cards::init();
