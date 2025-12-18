<?php
/**
 * Classe per la gestione del database
 * VERSIONE AGGIORNATA per nuovo schema template
 */

if (!defined('ABSPATH')) exit;

class WI_Database {
    
    /**
     * Crea le tabelle del database
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabella template con nuovo schema
        $table_templates = $wpdb->prefix . 'wi_templates';
        $sql_templates = "CREATE TABLE IF NOT EXISTS $table_templates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            category varchar(255) DEFAULT 'generale',
            html_structure longtext,
            css_styles longtext,
            is_active tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,

            -- Immagine Header
            header_image varchar(500) DEFAULT '',
            
            -- Font e Colori Titolo
            title_font varchar(100) DEFAULT 'Playfair Display',
            title_size int(11) DEFAULT 48,
            title_color varchar(50) DEFAULT '#2c2416',

            -- Decorazioni
            decoration_top varchar(500) DEFAULT '',
            decoration_bottom varchar(500) DEFAULT '',

            -- Font e Colori Countdown
            countdown_font varchar(100) DEFAULT 'Lora',
            countdown_color varchar(50) DEFAULT '#d4af37',
            countdown_bg_color varchar(50) DEFAULT '#ffffff',
            countdown_animated tinyint(1) DEFAULT 1,
            countdown_style int(11) DEFAULT 1,

            -- Immagini - Opacità e Grandezza
            header_opacity decimal(3,2) DEFAULT 1.00,
            header_size int(11) DEFAULT 100,
            decoration_top_opacity decimal(3,2) DEFAULT 1.00,
            decoration_top_size int(11) DEFAULT 100,
            decoration_bottom_opacity decimal(3,2) DEFAULT 1.00,
            decoration_bottom_size int(11) DEFAULT 100,
            background_size int(11) DEFAULT 100,
            user_image_opacity decimal(3,2) DEFAULT 1.00,
            user_image_size int(11) DEFAULT 100,

            -- Font e Colori Messaggio
            message_font varchar(100) DEFAULT 'Lora',
            message_size int(11) DEFAULT 18,
            message_color varchar(50) DEFAULT '#4a4a4a',
            message_bg_color varchar(50) DEFAULT '#f9f9f9',

            -- Font e Colori Dettagli
            details_font varchar(100) DEFAULT 'Lora',
            details_color varchar(50) DEFAULT '#333333',
            details_bg_color varchar(50) DEFAULT '#ffffff',

            -- Colori Mappa e Pulsanti
            map_marker_color varchar(50) DEFAULT '#667eea',
            button_bg_color varchar(50) DEFAULT '#667eea',
            button_text_color varchar(50) DEFAULT '#ffffff',

            -- Sfondo generale
            background_color varchar(50) DEFAULT '#fdfbfb',
            background_image varchar(500) DEFAULT '',
            background_opacity decimal(3,2) DEFAULT 1.00,
            
            -- Logo finale
            footer_logo varchar(500) DEFAULT '',

            -- CSS Personalizzato
            custom_css longtext DEFAULT NULL,

            -- CSS Titoli Sezioni (wi-section-title)
            section_title_font varchar(100) DEFAULT 'inherit',
            section_title_size int(11) DEFAULT 28,
            section_title_color varchar(50) DEFAULT '#2c3e50',
            section_title_weight varchar(10) DEFAULT '600',

            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY  (id),
            KEY is_active (is_active),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        
        // Tabella inviti
        $table_invites = $wpdb->prefix . 'wi_invites';
        $sql_invites = "CREATE TABLE IF NOT EXISTS $table_invites (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            template_id bigint(20) NOT NULL,
            unique_code varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message longtext,
            event_date date NOT NULL,
            event_time time NOT NULL,
            event_location varchar(500),
            event_address varchar(500),
            status varchar(20) DEFAULT 'draft',
            view_count int(11) DEFAULT 0,
            user_id bigint(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_code (unique_code),
            KEY template_id (template_id),
            KEY status (status),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_templates);
        dbDelta($sql_invites);
        
        // Inserisci template di default se la tabella è vuota
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
        if ($count == 0) {
            self::insert_default_templates();
        }
        
        return true;
    }
    
    /**
     * Inserisce template di default
     */
    private static function insert_default_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        // Carica i template HTML/CSS
        require_once WI_PLUGIN_DIR . 'includes/default-templates-content.php';

        $templates = array(
            // Template 1: Elegante Oro (Countdown Style 1)
            array(
                'name' => 'Elegante Oro',
                'description' => 'Template elegante con tonalità oro e font classici',
                'category' => 'matrimonio,anniversario,laurea',
                'html_structure' => get_elegant_template_html(),
                'css_styles' => get_elegant_template_css(),
                'is_active' => 1,
                'sort_order' => 1,
                'countdown_style' => 1,
                'title_font' => 'Playfair Display',
                'title_size' => 48,
                'title_color' => '#2c2416',
                'countdown_font' => 'Lora',
                'countdown_color' => '#d4af37',
                'countdown_bg_color' => '#ffffff',
                'countdown_animated' => 1,
                'message_font' => 'Lora',
                'message_size' => 18,
                'message_color' => '#4a4a4a',
                'message_bg_color' => '#f9f9f9',
                'details_font' => 'Lora',
                'details_color' => '#333333',
                'details_bg_color' => '#ffffff',
                'map_marker_color' => '#d4af37',
                'button_bg_color' => '#d4af37',
                'button_text_color' => '#ffffff',
                'background_color' => '#fdfbfb',
                'background_opacity' => 1.00
            ),

            // Template 2: Moderno Viola (Countdown Style 2)
            array(
                'name' => 'Moderno Viola',
                'description' => 'Template moderno con gradiente viola e design minimal',
                'category' => 'matrimonio,compleanno,festa',
                'html_structure' => get_modern_template_html(),
                'css_styles' => get_modern_template_css(),
                'is_active' => 1,
                'sort_order' => 2,
                'countdown_style' => 2,
                'title_font' => 'Montserrat',
                'title_size' => 52,
                'title_color' => '#667eea',
                'countdown_font' => 'Montserrat',
                'countdown_color' => '#667eea',
                'countdown_bg_color' => '#f8f9ff',
                'countdown_animated' => 1,
                'message_font' => 'Open Sans',
                'message_size' => 18,
                'message_color' => '#4a5568',
                'message_bg_color' => '#ffffff',
                'details_font' => 'Open Sans',
                'details_color' => '#2d3748',
                'details_bg_color' => '#f7fafc',
                'map_marker_color' => '#667eea',
                'button_bg_color' => '#667eea',
                'button_text_color' => '#ffffff',
                'background_color' => '#ffffff',
                'background_opacity' => 1.00
            ),

            // Template 3: Romantico Rosa (Countdown Style 4)
            array(
                'name' => 'Romantico Rosa',
                'description' => 'Template romantico con tonalità rosa e font script',
                'category' => 'matrimonio,anniversario,fidanzamento',
                'html_structure' => get_romantic_template_html(),
                'css_styles' => get_romantic_template_css(),
                'is_active' => 1,
                'sort_order' => 3,
                'countdown_style' => 4,
                'title_font' => 'Dancing Script',
                'title_size' => 56,
                'title_color' => '#d16ba5',
                'countdown_font' => 'Lora',
                'countdown_color' => '#d16ba5',
                'countdown_bg_color' => '#fff5f7',
                'countdown_animated' => 1,
                'message_font' => 'Lora',
                'message_size' => 18,
                'message_color' => '#5a3a4a',
                'message_bg_color' => '#fff0f5',
                'details_font' => 'Lora',
                'details_color' => '#5a3a4a',
                'details_bg_color' => '#fff5f7',
                'map_marker_color' => '#d16ba5',
                'button_bg_color' => '#d16ba5',
                'button_text_color' => '#ffffff',
                'background_color' => '#fff5f7',
                'background_opacity' => 1.00
            ),

            // Template 4: Lussuoso Nero & Oro (Countdown Style 3)
            array(
                'name' => 'Lussuoso Nero & Oro',
                'description' => 'Eleganza dark con accenti dorati e stile luxury',
                'category' => 'matrimonio,gala,laurea',
                'html_structure' => get_luxury_template_html(),
                'css_styles' => get_luxury_template_css(),
                'is_active' => 1,
                'sort_order' => 4,
                'countdown_style' => 3,
                'title_font' => 'Playfair Display',
                'title_size' => 58,
                'title_color' => '#d4af37',
                'countdown_font' => 'Georgia',
                'countdown_color' => '#d4af37',
                'countdown_bg_color' => '#1a1a1a',
                'countdown_animated' => 0,
                'message_font' => 'Georgia',
                'message_size' => 20,
                'message_color' => '#f5f5f5',
                'message_bg_color' => '#1a1a1a',
                'details_font' => 'Georgia',
                'details_color' => '#f5f5f5',
                'details_bg_color' => '#2d2d2d',
                'map_marker_color' => '#d4af37',
                'button_bg_color' => '#d4af37',
                'button_text_color' => '#1a1a1a',
                'background_color' => '#1a1a1a',
                'background_opacity' => 1.00
            ),

            // Template 5: Circolare Azzurro (Countdown Style 5)
            array(
                'name' => 'Circolare Azzurro',
                'description' => 'Countdown circolare con palette blu cielo',
                'category' => 'battesimo,comunione,cresima',
                'html_structure' => get_circular_template_html(),
                'css_styles' => get_circular_template_css(),
                'is_active' => 1,
                'sort_order' => 5,
                'countdown_style' => 5,
                'title_font' => 'Poppins',
                'title_size' => 54,
                'title_color' => '#0369a1',
                'countdown_font' => 'Open Sans',
                'countdown_color' => '#0284c7',
                'countdown_bg_color' => '#e0f2fe',
                'countdown_animated' => 0,
                'message_font' => 'Open Sans',
                'message_size' => 19,
                'message_color' => '#0c4a6e',
                'message_bg_color' => '#ffffff',
                'details_font' => 'Open Sans',
                'details_color' => '#0c4a6e',
                'details_bg_color' => '#ffffff',
                'map_marker_color' => '#0284c7',
                'button_bg_color' => '#0284c7',
                'button_text_color' => '#ffffff',
                'background_color' => '#e0f2fe',
                'background_opacity' => 1.00
            ),

            // Template 6: Gradiente Viola (Countdown Style 6)
            array(
                'name' => 'Gradiente Viola',
                'description' => 'Sfumature viola con effetto glassmorphism',
                'category' => 'compleanno,festa,laurea',
                'html_structure' => get_gradient_template_html(),
                'css_styles' => get_gradient_template_css(),
                'is_active' => 1,
                'sort_order' => 6,
                'countdown_style' => 6,
                'title_font' => 'Montserrat',
                'title_size' => 56,
                'title_color' => '#ffffff',
                'countdown_font' => 'Montserrat',
                'countdown_color' => '#ffffff',
                'countdown_bg_color' => 'rgba(255,255,255,0.15)',
                'countdown_animated' => 0,
                'message_font' => 'Poppins',
                'message_size' => 20,
                'message_color' => '#ffffff',
                'message_bg_color' => 'rgba(255,255,255,0.1)',
                'details_font' => 'Poppins',
                'details_color' => '#ffffff',
                'details_bg_color' => 'rgba(255,255,255,0.15)',
                'map_marker_color' => '#ffffff',
                'button_bg_color' => '#ffffff',
                'button_text_color' => '#667eea',
                'background_color' => '#667eea',
                'background_opacity' => 1.00
            ),

            // Template 7: Neon Futuristico (Countdown Style 7)
            array(
                'name' => 'Neon Futuristico',
                'description' => 'Effetti neon cyberpunk con stile futuristico',
                'category' => 'compleanno,festa,evento-aziendale',
                'html_structure' => get_neon_template_html(),
                'css_styles' => get_neon_template_css(),
                'is_active' => 1,
                'sort_order' => 7,
                'countdown_style' => 7,
                'title_font' => 'Orbitron',
                'title_size' => 54,
                'title_color' => '#00ffff',
                'countdown_font' => 'Courier New',
                'countdown_color' => '#00ffff',
                'countdown_bg_color' => '#0a0e27',
                'countdown_animated' => 1,
                'message_font' => 'Courier New',
                'message_size' => 18,
                'message_color' => '#00ffff',
                'message_bg_color' => 'rgba(0,255,255,0.05)',
                'details_font' => 'Courier New',
                'details_color' => '#00ffff',
                'details_bg_color' => 'rgba(0,255,255,0.05)',
                'map_marker_color' => '#00ffff',
                'button_bg_color' => 'transparent',
                'button_text_color' => '#00ffff',
                'background_color' => '#0a0e27',
                'background_opacity' => 1.00
            ),

            // Template 8: Vintage Marrone (Countdown Style 8)
            array(
                'name' => 'Vintage Marrone',
                'description' => 'Stile retrò con effetto seppia e texture vintage',
                'category' => 'anniversario,matrimonio,reunion',
                'html_structure' => get_vintage_template_html(),
                'css_styles' => get_vintage_template_css(),
                'is_active' => 1,
                'sort_order' => 8,
                'countdown_style' => 8,
                'title_font' => 'Palatino',
                'title_size' => 52,
                'title_color' => '#f5e6d3',
                'countdown_font' => 'Georgia',
                'countdown_color' => '#f5e6d3',
                'countdown_bg_color' => '#8b7355',
                'countdown_animated' => 0,
                'message_font' => 'Georgia',
                'message_size' => 19,
                'message_color' => '#2d2416',
                'message_bg_color' => 'rgba(201,184,163,0.3)',
                'details_font' => 'Georgia',
                'details_color' => '#2d2416',
                'details_bg_color' => 'rgba(201,184,163,0.3)',
                'map_marker_color' => '#c9b8a3',
                'button_bg_color' => '#c9b8a3',
                'button_text_color' => '#2d2416',
                'background_color' => '#8b7355',
                'background_opacity' => 1.00
            ),

            // Template 9: Geometrico Verde (Countdown Style 9)
            array(
                'name' => 'Geometrico Verde',
                'description' => 'Forme geometriche e design moderno verde',
                'category' => 'compleanno,festa,evento-aziendale',
                'html_structure' => get_geometric_template_html(),
                'css_styles' => get_geometric_template_css(),
                'is_active' => 1,
                'sort_order' => 9,
                'countdown_style' => 9,
                'title_font' => 'Roboto',
                'title_size' => 54,
                'title_color' => '#10b981',
                'countdown_font' => 'Arial',
                'countdown_color' => '#10b981',
                'countdown_bg_color' => '#ffffff',
                'countdown_animated' => 0,
                'message_font' => 'Arial',
                'message_size' => 19,
                'message_color' => '#065f46',
                'message_bg_color' => '#ffffff',
                'details_font' => 'Arial',
                'details_color' => '#065f46',
                'details_bg_color' => '#ffffff',
                'map_marker_color' => '#10b981',
                'button_bg_color' => '#10b981',
                'button_text_color' => '#ffffff',
                'background_color' => '#ffffff',
                'background_opacity' => 1.00
            ),

            // Template 10: Cielo Sereno (Countdown Style 10)
            array(
                'name' => 'Cielo Sereno',
                'description' => 'Colori del cielo con sfumature azzurre rilassanti',
                'category' => 'battesimo,comunione,baby-shower',
                'html_structure' => get_sky_template_html(),
                'css_styles' => get_sky_template_css(),
                'is_active' => 1,
                'sort_order' => 10,
                'countdown_style' => 10,
                'title_font' => 'Quicksand',
                'title_size' => 56,
                'title_color' => '#0369a1',
                'countdown_font' => 'Nunito',
                'countdown_color' => '#0369a1',
                'countdown_bg_color' => 'rgba(255,255,255,0.7)',
                'countdown_animated' => 0,
                'message_font' => 'Nunito',
                'message_size' => 20,
                'message_color' => '#075985',
                'message_bg_color' => 'rgba(255,255,255,0.6)',
                'details_font' => 'Nunito',
                'details_color' => '#075985',
                'details_bg_color' => 'rgba(255,255,255,0.7)',
                'map_marker_color' => '#0369a1',
                'button_bg_color' => '#0369a1',
                'button_text_color' => '#ffffff',
                'background_color' => '#87ceeb',
                'background_opacity' => 1.00
            ),

            // Template 11: Oceano Profondo (Countdown Style 11)
            array(
                'name' => 'Oceano Profondo',
                'description' => 'Tonalità oceaniche profonde con effetti luminosi',
                'category' => 'compleanno,festa,pool-party',
                'html_structure' => get_ocean_template_html(),
                'css_styles' => get_ocean_template_css(),
                'is_active' => 1,
                'sort_order' => 11,
                'countdown_style' => 11,
                'title_font' => 'Oswald',
                'title_size' => 58,
                'title_color' => '#7dd3fc',
                'countdown_font' => 'Raleway',
                'countdown_color' => '#7dd3fc',
                'countdown_bg_color' => 'rgba(3,102,214,0.2)',
                'countdown_animated' => 0,
                'message_font' => 'Raleway',
                'message_size' => 19,
                'message_color' => '#e0f2fe',
                'message_bg_color' => 'rgba(3,102,214,0.15)',
                'details_font' => 'Raleway',
                'details_color' => '#e0f2fe',
                'details_bg_color' => 'rgba(3,102,214,0.2)',
                'map_marker_color' => '#0ea5e9',
                'button_bg_color' => '#0ea5e9',
                'button_text_color' => '#0c4a6e',
                'background_color' => '#0c4a6e',
                'background_opacity' => 1.00
            ),

            // Template 12: Tramonto Caldo (Countdown Style 12)
            array(
                'name' => 'Tramonto Caldo',
                'description' => 'Colori caldi del tramonto con sfumature arancioni',
                'category' => 'matrimonio,anniversario,beach-party',
                'html_structure' => get_sunset_template_html(),
                'css_styles' => get_sunset_template_css(),
                'is_active' => 1,
                'sort_order' => 12,
                'countdown_style' => 12,
                'title_font' => 'Cinzel',
                'title_size' => 56,
                'title_color' => '#ffffff',
                'countdown_font' => 'Merriweather',
                'countdown_color' => '#ffffff',
                'countdown_bg_color' => 'rgba(255,255,255,0.15)',
                'countdown_animated' => 0,
                'message_font' => 'Merriweather',
                'message_size' => 20,
                'message_color' => '#ffffff',
                'message_bg_color' => 'rgba(255,255,255,0.1)',
                'details_font' => 'Merriweather',
                'details_color' => '#ffffff',
                'details_bg_color' => 'rgba(255,255,255,0.15)',
                'map_marker_color' => '#fed7aa',
                'button_bg_color' => '#ffffff',
                'button_text_color' => '#7c2d12',
                'background_color' => '#7c2d12',
                'background_opacity' => 1.00
            ),

            // Template 13: Cristallo Trasparente (Countdown Style 13)
            array(
                'name' => 'Cristallo Trasparente',
                'description' => 'Effetto glassmorphism con trasparenze eleganti',
                'category' => 'matrimonio,fidanzamento,gala',
                'html_structure' => get_crystal_template_html(),
                'css_styles' => get_crystal_template_css(),
                'is_active' => 1,
                'sort_order' => 13,
                'countdown_style' => 13,
                'title_font' => 'Poppins',
                'title_size' => 58,
                'title_color' => 'rgba(255,255,255,0.9)',
                'countdown_font' => 'Inter',
                'countdown_color' => 'rgba(255,255,255,0.9)',
                'countdown_bg_color' => 'rgba(255,255,255,0.1)',
                'countdown_animated' => 0,
                'message_font' => 'Inter',
                'message_size' => 19,
                'message_color' => 'rgba(0,0,0,0.7)',
                'message_bg_color' => 'rgba(255,255,255,0.1)',
                'details_font' => 'Inter',
                'details_color' => 'rgba(0,0,0,0.8)',
                'details_bg_color' => 'rgba(255,255,255,0.1)',
                'map_marker_color' => 'rgba(255,255,255,0.5)',
                'button_bg_color' => 'rgba(255,255,255,0.2)',
                'button_text_color' => 'rgba(0,0,0,0.8)',
                'background_color' => '#f0f9ff',
                'background_opacity' => 1.00
            ),

            // Template 14: Ombra 3D (Countdown Style 14)
            array(
                'name' => 'Ombra 3D',
                'description' => 'Effetti ombra tridimensionali e profondità',
                'category' => 'compleanno,festa,evento-aziendale',
                'html_structure' => get_shadow_template_html(),
                'css_styles' => get_shadow_template_css(),
                'is_active' => 1,
                'sort_order' => 14,
                'countdown_style' => 14,
                'title_font' => 'Ubuntu',
                'title_size' => 54,
                'title_color' => '#1e293b',
                'countdown_font' => 'Roboto',
                'countdown_color' => '#1e293b',
                'countdown_bg_color' => '#ffffff',
                'countdown_animated' => 0,
                'message_font' => 'Roboto',
                'message_size' => 19,
                'message_color' => '#475569',
                'message_bg_color' => '#ffffff',
                'details_font' => 'Roboto',
                'details_color' => '#1e293b',
                'details_bg_color' => '#ffffff',
                'map_marker_color' => '#1e293b',
                'button_bg_color' => '#ffffff',
                'button_text_color' => '#1e293b',
                'background_color' => '#f8f9fa',
                'background_opacity' => 1.00
            ),

            // Template 15: Animato Dinamico (Countdown Style 15)
            array(
                'name' => 'Animato Dinamico',
                'description' => 'Animazioni intense con gradiente in movimento',
                'category' => 'compleanno,festa,evento-speciale',
                'html_structure' => get_animated_template_html(),
                'css_styles' => get_animated_template_css(),
                'is_active' => 1,
                'sort_order' => 15,
                'countdown_style' => 15,
                'title_font' => 'Righteous',
                'title_size' => 60,
                'title_color' => '#ffffff',
                'countdown_font' => 'Exo',
                'countdown_color' => '#ffffff',
                'countdown_bg_color' => 'rgba(255,255,255,0.1)',
                'countdown_animated' => 1,
                'message_font' => 'Exo',
                'message_size' => 20,
                'message_color' => '#ffffff',
                'message_bg_color' => 'rgba(255,255,255,0.15)',
                'details_font' => 'Exo',
                'details_color' => '#ffffff',
                'details_bg_color' => 'rgba(255,255,255,0.15)',
                'map_marker_color' => '#ffffff',
                'button_bg_color' => 'rgba(255,255,255,0.2)',
                'button_text_color' => '#ffffff',
                'background_color' => '#667eea',
                'background_opacity' => 1.00
            ),

            // Template 16: Pastello Lavanda
            array(
                'name' => 'Pastello Lavanda',
                'description' => 'Colori pastello soft con tonalità lavanda romantiche',
                'category' => 'baby-shower,battesimo,tea-party',
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
                'category' => 'matrimonio,evento-aziendale,conferenza',
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
                'category' => 'matrimonio,picnic,outdoor-event',
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
                'category' => 'gala,anniversario,matrimonio',
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
                'category' => 'compleanno,pool-party,summer-party',
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

        // Inserisci tutti i template
        foreach ($templates as $template) {
            $wpdb->insert($table, $template);
        }
    }

    /**
     * Crea tabella categorie eventi (v2.2.0)
     */
    public static function create_categories_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_event_categories';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            icon varchar(50) DEFAULT '🎉',
            description text,
            is_active tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Inserisci categorie default se la tabella è vuota
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count == 0) {
            self::insert_default_categories();
        }
    }

    /**
     * Inserisce le categorie predefinite
     */
    private static function insert_default_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_event_categories';

        $categories = array(
            array('name' => 'Matrimonio', 'slug' => 'matrimonio', 'icon' => '💍', 'sort_order' => 1),
            array('name' => 'Battesimo', 'slug' => 'battesimo', 'icon' => '👶', 'sort_order' => 2),
            array('name' => 'Compleanno', 'slug' => 'compleanno', 'icon' => '🎂', 'sort_order' => 3),
            array('name' => 'Anniversario', 'slug' => 'anniversario', 'icon' => '💐', 'sort_order' => 4),
            array('name' => 'Festa', 'slug' => 'festa', 'icon' => '🎉', 'sort_order' => 5),
            array('name' => 'Comunione', 'slug' => 'comunione', 'icon' => '✝️', 'sort_order' => 6),
            array('name' => 'Cresima', 'slug' => 'cresima', 'icon' => '🕊️', 'sort_order' => 7),
            array('name' => 'Laurea', 'slug' => 'laurea', 'icon' => '🎓', 'sort_order' => 8),
            array('name' => 'Gala', 'slug' => 'gala', 'icon' => '🌟', 'sort_order' => 9),
            array('name' => 'Fidanzamento', 'slug' => 'fidanzamento', 'icon' => '💑', 'sort_order' => 10),
            array('name' => 'Baby Shower', 'slug' => 'baby-shower', 'icon' => '🍼', 'sort_order' => 11),
            array('name' => 'Evento Aziendale', 'slug' => 'evento-aziendale', 'icon' => '💼', 'sort_order' => 12),
        );

        foreach ($categories as $cat) {
            $wpdb->insert($table, $cat);
        }
    }

    /**
     * Crea tabella relazioni template-categorie (v2.2.0)
     */
    public static function create_template_categories_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_template_categories';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            template_id bigint(20) NOT NULL,
            category_id bigint(20) NOT NULL,
            PRIMARY KEY (template_id, category_id),
            KEY template_id (template_id),
            KEY category_id (category_id)
        ) $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Elimina le tabelle (per disinstallazione)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $table_templates = $wpdb->prefix . 'wi_templates';
        $table_invites = $wpdb->prefix . 'wi_invites';
        
        $wpdb->query("DROP TABLE IF EXISTS $table_invites");
        $wpdb->query("DROP TABLE IF EXISTS $table_templates");
    }
}
