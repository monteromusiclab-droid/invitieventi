<?php
/**
 * Classe per la gestione dei template
 */

if (!defined('ABSPATH')) exit;

class WI_Templates {
    
    /**
     * Ottieni tutti i template attivi (con caching)
     */
    public static function get_all_templates() {
        // Prova cache prima
        $cache_key = 'wi_active_templates';
        $templates = wp_cache_get($cache_key, 'wedding_invites');

        if (false !== $templates) {
            return $templates;
        }

        // Se non in cache, query database
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        $templates = $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
        );

        // Salva in cache per 1 ora
        wp_cache_set($cache_key, $templates, 'wedding_invites', HOUR_IN_SECONDS);

        return $templates;
    }
    
    /**
     * Ottieni tutti i template (inclusi inattivi) - Per admin
     */
    public static function get_all_templates_admin() {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        return $wpdb->get_results(
            "SELECT * FROM $table ORDER BY sort_order ASC, id ASC"
        );
    }
    
    /**
     * Ottieni template filtrati per categoria (con caching)
     *
     * @param string $category_slug Slug della categoria
     * @return array Array di template
     */
    public static function get_templates_by_category($category_slug) {
        if (empty($category_slug)) {
            return self::get_all_templates();
        }

        // Prova cache prima
        $cache_key = 'wi_templates_cat_' . $category_slug;
        $templates = wp_cache_get($cache_key, 'wedding_invites');

        if (false !== $templates) {
            return $templates;
        }

        // Query con JOIN sulla tabella relazionale
        global $wpdb;
        $templates_table = $wpdb->prefix . 'wi_templates';
        $categories_table = $wpdb->prefix . 'wi_event_categories';
        $relations_table = $wpdb->prefix . 'wi_template_categories';

        // Verifica se le tabelle esistono
        $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$relations_table}'") === $relations_table;

        if (!$tables_exist) {
            // Fallback: usa tutti i template se tabella non esiste
            wi_log('Template categories table does not exist, returning all templates', 'warning');
            return self::get_all_templates();
        }

        // Query con JOIN
        $templates = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT t.*
            FROM {$templates_table} t
            INNER JOIN {$relations_table} tc ON t.id = tc.template_id
            INNER JOIN {$categories_table} c ON tc.category_id = c.id
            WHERE c.slug = %s AND t.is_active = 1
            ORDER BY t.sort_order ASC, t.id ASC
        ", $category_slug));

        // Se non ci sono template per questa categoria, mostra tutti
        if (empty($templates)) {
            wi_log('No templates found for category, returning all templates', 'info', array(
                'category' => $category_slug
            ));
            return self::get_all_templates();
        }

        // Salva in cache per 1 ora
        wp_cache_set($cache_key, $templates, 'wedding_invites', HOUR_IN_SECONDS);

        wi_log('Templates loaded by category', 'info', array(
            'category' => $category_slug,
            'count' => count($templates)
        ));

        return $templates;
    }

    /**
     * Ottieni un template specifico (con caching)
     */
    public static function get_template($id) {
        $id = intval($id);

        // Prova cache prima
        $cache_key = 'wi_template_' . $id;
        $template = wp_cache_get($cache_key, 'wedding_invites');

        if (false !== $template) {
            return $template;
        }

        // Se non in cache, query database
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)
        );

        // Salva in cache per 1 ora
        if ($template) {
            wp_cache_set($cache_key, $template, 'wedding_invites', HOUR_IN_SECONDS);
        }

        return $template;
    }
    
    /**
     * Salva un template (nuovo o aggiornamento)
     */
    public static function save_template($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        $template_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => sanitize_textarea_field($data['description']),
            'html_structure' => $data['html_structure'],
            'css_styles' => $data['css_styles'],
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 0,
            'sort_order' => intval($data['sort_order']),
            'header_image' => isset($data['header_image']) ? esc_url($data['header_image']) : '',
            'decoration_top' => isset($data['decoration_top']) ? esc_url($data['decoration_top']) : '',
            'decoration_bottom' => isset($data['decoration_bottom']) ? esc_url($data['decoration_bottom']) : '',
            'background_image' => isset($data['background_image']) ? esc_url($data['background_image']) : '',
            'background_opacity' => isset($data['background_opacity']) ? floatval($data['background_opacity']) : 0.9,
            'countdown_animated' => isset($data['countdown_animated']) ? intval($data['countdown_animated']) : 0
        );
        
        if (isset($data['id']) && $data['id'] > 0) {
            // Update
            $result = $wpdb->update(
                $table,
                $template_data,
                array('id' => intval($data['id']))
            );

            // Invalida cache
            if ($result !== false) {
                self::clear_template_cache($data['id']);
            }

            return $result !== false;
        } else {
            // Insert
            $result = $wpdb->insert($table, $template_data);

            // Invalida cache lista template
            if ($result !== false) {
                self::clear_all_templates_cache();
            }

            return $result !== false;
        }
    }

    /**
     * Invalida cache per un template specifico
     *
     * @param int $template_id
     */
    private static function clear_template_cache($template_id) {
        wp_cache_delete('wi_template_' . intval($template_id), 'wedding_invites');
        self::clear_all_templates_cache();

        wi_log('Cache cleared for template', 'info', array('template_id' => $template_id));
    }

    /**
     * Invalida cache lista template
     */
    private static function clear_all_templates_cache() {
        wp_cache_delete('wi_active_templates', 'wedding_invites');
        wi_log('All templates cache cleared', 'info');
    }
    
    /**
     * Aggiorna solo le immagini del template
     */
    public static function update_template_images($template_id, $images) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        $update_data = array();
        
        if (isset($images['header_image'])) {
            $update_data['header_image'] = esc_url($images['header_image']);
        }
        if (isset($images['decoration_top'])) {
            $update_data['decoration_top'] = esc_url($images['decoration_top']);
        }
        if (isset($images['decoration_bottom'])) {
            $update_data['decoration_bottom'] = esc_url($images['decoration_bottom']);
        }
        if (isset($images['background_image'])) {
            $update_data['background_image'] = esc_url($images['background_image']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $table,
            $update_data,
            array('id' => intval($template_id))
        ) !== false;
    }
    
    /**
     * Elimina un template
     */
    public static function delete_template($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        // Verifica che non ci siano inviti che usano questo template
        $invites_table = $wpdb->prefix . 'wi_invites';
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $invites_table WHERE template_id = %d",
                intval($id)
            )
        );
        
        if ($count > 0) {
            return false; // Non eliminare se ci sono inviti associati
        }
        
        return $wpdb->delete($table, array('id' => intval($id))) !== false;
    }
    
    /**
     * Renderizza un template con i dati dell'invito
     */
    public static function render_template($template_id, $data) {
        $template = self::get_template($template_id);
        
        if (!$template || empty($template->html_structure)) {
            return '<div class="wi-error">Template non trovato o incompleto</div>';
        }
        
        $html = $template->html_structure;
        
        // Prepara immagini
        $header_image = !empty($template->header_image) ? $template->header_image : '';
        $decoration_top = !empty($template->decoration_top) ? $template->decoration_top : '';
        $decoration_bottom = !empty($template->decoration_bottom) ? $template->decoration_bottom : '';
        $footer_logo = !empty($template->footer_logo) ? $template->footer_logo : get_option('wi_site_logo_url', '');
        $user_image = !empty($data['user_image']) ? $data['user_image'] : '';
        $final_message = !empty($data['final_message']) ? $data['final_message'] : '';

        // Gestisci blocchi condizionali {{#if}}
        $html = self::process_conditionals($html, array(
            'header_image' => $header_image,
            'decoration_top' => $decoration_top,
            'decoration_bottom' => $decoration_bottom,
            'footer_logo' => $footer_logo,
            'user_image' => $user_image,
            'final_message' => $final_message
        ));
        
        // Sostituisci i placeholder semplici - SICURO: escaping corretto
        $replacements = array(
            '{{header_image}}' => $header_image,
            '{{decoration_top}}' => $decoration_top,
            '{{decoration_bottom}}' => $decoration_bottom,
            '{{footer_logo}}' => $footer_logo,
            '{{title}}' => esc_html($data['title'] ?? ''),
            '{{message}}' => wp_kses_post(wpautop($data['message'] ?? '')),
            '{{final_message}}' => !empty($data['final_message'] ?? '') ? wp_kses_post(wpautop($data['final_message'])) : '',
            '{{final_message_button_text}}' => esc_html(!empty($data['final_message_button_text']) ? $data['final_message_button_text'] : 'Leggi il messaggio'),
            '{{event_date}}' => esc_html(self::format_date_italian($data['event_date'] ?? '')),
            '{{event_time}}' => esc_html($data['event_time'] ?? ''),
            '{{event_location}}' => esc_html($data['event_location'] ?? ''),
            '{{event_address}}' => esc_attr($data['event_address'] ?? $data['event_location'] ?? ''),
            '{{user_image}}' => $user_image,
            '{{background_color}}' => esc_attr($template->background_color ?? '#ffffff')
        );
        
        foreach ($replacements as $placeholder => $value) {
            $html = str_replace($placeholder, $value, $html);
        }
        
        // Aggiungi CSS (statico + dinamico)
        $css_output = '<style>';

        // CSS statico dal template (base)
        if (!empty($template->css_styles)) {
            $css_output .= $template->css_styles;
        }

        // CSS dinamico generato dai valori (sovrascrive il CSS statico)
        $dynamic_css = self::generate_dynamic_css($template);
        $css_output .= $dynamic_css;

        $css_output .= '</style>';

        $output = $css_output . $html;

        // Aggiungi script countdown se presente
        if (strpos($html, 'id="countdown"') !== false && !empty($data['event_date'])) {
            $event_time = !empty($data['event_time']) ? $data['event_time'] : '00:00:00';
            $output .= self::get_countdown_script($data['event_date'], $event_time, $template->countdown_animated);
        }

        // Aggiungi script mappa OpenStreetMap se presente
        if (strpos($html, 'class="wi-map"') !== false && !empty($data['event_address'])) {
            $output .= self::get_map_script($data['event_address'], $data['event_date']);
        }

        return $output;
    }
    
    /**
     * Processa blocchi condizionali {{#if variable}}...{{/if}}
     */
    private static function process_conditionals($html, $variables) {
        // Pattern per trovare blocchi {{#if variable}}...{{/if}}
        $pattern = '/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/s';
        
        $html = preg_replace_callback($pattern, function($matches) use ($variables) {
            $var_name = $matches[1];
            $content = $matches[2];
            
            // Se la variabile esiste ed è non vuota, mostra il contenuto
            if (isset($variables[$var_name]) && !empty($variables[$var_name])) {
                return $content;
            }
            
            return ''; // Altrimenti rimuovi il blocco
        }, $html);
        
        // Gestisci anche blocchi {{#else}}
        $pattern_else = '/\{\{#if\s+(\w+)\}\}(.*?)\{\{#else\}\}(.*?)\{\{\/if\}\}/s';
        
        $html = preg_replace_callback($pattern_else, function($matches) use ($variables) {
            $var_name = $matches[1];
            $if_content = $matches[2];
            $else_content = $matches[3];
            
            if (isset($variables[$var_name]) && !empty($variables[$var_name])) {
                return $if_content;
            }
            
            return $else_content;
        }, $html);
        
        return $html;
    }
    
    /**
     * Genera script countdown
     */
    private static function get_countdown_script($event_date, $event_time, $animated = false) {
        // Formatta la data per JavaScript
        $event_datetime = $event_date . ' ' . $event_time;

        return '
        <script>
        (function() {
            const eventDate = new Date("' . esc_js($event_datetime) . '").getTime();
            const countdownEl = document.getElementById("countdown");

            if (!countdownEl) return;

            // Aggiungi data attributes per altre funzioni
            countdownEl.setAttribute("data-event-date", "' . esc_js($event_date) . '");
            countdownEl.setAttribute("data-event-time", "' . esc_js($event_time) . '");

            let isInitialized = false;

            function pad(num) {
                return num < 10 ? "0" + num : num;
            }

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = eventDate - now;

                if (distance < 0) {
                    countdownEl.innerHTML = "<div class=\"countdown-expired\" style=\"font-size: 24px; color: #d4af37; padding: 20px;\">L\'evento è iniziato! 🎉</div>";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // First time: create full HTML structure with IDs
                if (!isInitialized) {
                    countdownEl.innerHTML = `
                        <div class="countdown-container">
                            <div class="countdown-item">
                                <div class="countdown-value" id="countdown-days">${pad(days)}</div>
                                <div class="countdown-label">Giorni</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-value" id="countdown-hours">${pad(hours)}</div>
                                <div class="countdown-label">Ore</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-value" id="countdown-minutes">${pad(minutes)}</div>
                                <div class="countdown-label">Minuti</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-value" id="countdown-seconds">${pad(seconds)}</div>
                                <div class="countdown-label">Secondi</div>
                            </div>
                        </div>
                    `;
                    isInitialized = true;
                } else {
                    // Update only text content without recreating HTML
                    const daysEl = document.getElementById("countdown-days");
                    const hoursEl = document.getElementById("countdown-hours");
                    const minutesEl = document.getElementById("countdown-minutes");
                    const secondsEl = document.getElementById("countdown-seconds");

                    if (daysEl) {
                        const newDays = pad(days);
                        if (daysEl.textContent !== newDays) daysEl.textContent = newDays;
                    }
                    if (hoursEl) {
                        const newHours = pad(hours);
                        if (hoursEl.textContent !== newHours) hoursEl.textContent = newHours;
                    }
                    if (minutesEl) {
                        const newMinutes = pad(minutes);
                        if (minutesEl.textContent !== newMinutes) minutesEl.textContent = newMinutes;
                    }
                    if (secondsEl) {
                        const newSeconds = pad(seconds);
                        if (secondsEl.textContent !== newSeconds) secondsEl.textContent = newSeconds;
                    }
                }
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
        </script>
        ';
    }

    /**
     * Genera script mappa OpenStreetMap
     */
    private static function get_map_script($event_address, $event_date) {
        $map_id = 'invite-map-' . esc_js($event_date);

        return '
        <!-- Leaflet CSS e JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>

        <script>
        (function() {
            const mapElement = document.getElementById("' . $map_id . '");
            if (!mapElement) return;

            const address = mapElement.getAttribute("data-address");
            if (!address) return;

            // Geocoding con Nominatim (OpenStreetMap)
            fetch("https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(address))
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        // Inizializza mappa Leaflet
                        const map = L.map(mapElement).setView([lat, lon], 15);

                        // Tile layer OpenStreetMap
                        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                            attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>",
                            maxZoom: 19
                        }).addTo(map);

                        // Marker personalizzato
                        const customIcon = L.icon({
                            iconUrl: "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iNDgiIHZpZXdCb3g9IjAgMCAzMiA0OCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE2IDQ4QzE2IDQ4IDMyIDMwIDMyIDE2QzMyIDcuMTYzNDQgMjQuODM2NiAwIDE2IDBDNy4xNjM0NCAwIDAgNy4xNjM0NCAwIDE2QzAgMzAgMTYgNDggMTYgNDhaIiBmaWxsPSIjNjY3ZWVhIi8+CjxjaXJjbGUgY3g9IjE2IiBjeT0iMTYiIHI9IjgiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPg==",
                            iconSize: [32, 48],
                            iconAnchor: [16, 48],
                            popupAnchor: [0, -48]
                        });

                        // Aggiungi marker
                        L.marker([lat, lon], { icon: customIcon })
                            .addTo(map)
                            .bindPopup("<strong>" + address + "</strong>")
                            .openPopup();

                        // Disabilita zoom scroll di default
                        map.scrollWheelZoom.disable();

                        // Abilita zoom scroll dopo click
                        map.on("click", function() {
                            map.scrollWheelZoom.enable();
                        });

                        console.log("✅ Mappa OpenStreetMap caricata");
                    } else {
                        mapElement.innerHTML = "<div style=\"padding: 40px; text-align: center; color: #64748b;\">📍 Mappa non disponibile<br><small>Usa il pulsante Google Maps sotto</small></div>";
                    }
                })
                .catch(error => {
                    console.error("❌ Errore geocoding:", error);
                    mapElement.innerHTML = "<div style=\"padding: 40px; text-align: center; color: #64748b;\">⚠️ Errore caricamento mappa<br><small>Usa il pulsante Google Maps sotto</small></div>";
                });
        })();
        </script>
        ';
    }

    /**
     * Attiva/Disattiva template
     */
    public static function toggle_active($template_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        $template = self::get_template($template_id);
        if (!$template) {
            return false;
        }
        
        $new_status = $template->is_active ? 0 : 1;
        
        return $wpdb->update(
            $table,
            array('is_active' => $new_status),
            array('id' => intval($template_id))
        ) !== false;
    }
    
    /**
     * Duplica template
     */
    public static function duplicate_template($template_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wi_templates';
        
        $template = self::get_template($template_id);
        if (!$template) {
            return false;
        }
        
        $new_template = array(
            'name' => $template->name . ' (Copia)',
            'description' => $template->description,
            'html_structure' => $template->html_structure,
            'css_styles' => $template->css_styles,
            'is_active' => 0, // Disattivato di default
            'sort_order' => $template->sort_order + 1,
            'header_image' => $template->header_image,
            'decoration_top' => $template->decoration_top,
            'decoration_bottom' => $template->decoration_bottom,
            'background_image' => $template->background_image,
            'background_opacity' => $template->background_opacity,
            'countdown_animated' => $template->countdown_animated
        );
        
        return $wpdb->insert($table, $new_template) !== false;
    }

    /**
     * Genera CSS dinamico dai valori del template
     * Questo CSS viene generato al volo dai valori salvati nell'editor Stili
     */
    public static function generate_dynamic_css($template) {
        if (!$template) {
            return '';
        }

        $css = "\n/* CSS Dinamico Generato dai Valori Template */\n";

        // Valori con fallback
        $title_font = $template->title_font ?? 'Playfair Display';
        $title_size = $template->title_size ?? 48;
        $title_color = $template->title_color ?? '#2c2416';

        $countdown_font = $template->countdown_font ?? 'Lora';
        $countdown_color = $template->countdown_color ?? '#d4af37';
        $countdown_bg_color = $template->countdown_bg_color ?? '#ffffff';
        $countdown_border_color = $template->countdown_border_color ?? '#e2e8f0';
        $countdown_label_font = isset($template->countdown_label_font) ? $template->countdown_label_font : 'Montserrat';
        $countdown_label_size = isset($template->countdown_label_size) ? $template->countdown_label_size : 14;
        $countdown_label_color = isset($template->countdown_label_color) ? $template->countdown_label_color : '#64748b';

        $message_font = $template->message_font ?? 'Lora';
        $message_size = $template->message_size ?? 18;
        $message_color = $template->message_color ?? '#4a4a4a';
        $message_bg_color = $template->message_bg_color ?? '#f9f9f9';

        $details_font = $template->details_font ?? 'Lora';
        $details_size = $template->details_size ?? 16;
        $details_label_color = isset($template->details_label_color) ? $template->details_label_color : '#333333';
        $details_value_color = isset($template->details_value_color) ? $template->details_value_color : '#666666';
        $details_bg_color = $template->details_bg_color ?? '#ffffff';
        $details_border_color = isset($template->details_border_color) ? $template->details_border_color : '#d4af37';
        $details_align = isset($template->details_align) ? $template->details_align : 'left';
        $hide_event_icons = isset($template->hide_event_icons) ? $template->hide_event_icons : 0;

        $divider_color = isset($template->divider_color) ? $template->divider_color : '#d4af37';
        $final_message_btn_bg_color = isset($template->final_message_btn_bg_color) ? $template->final_message_btn_bg_color : '#d4af37';
        $final_message_btn_text_color = isset($template->final_message_btn_text_color) ? $template->final_message_btn_text_color : '#ffffff';
        $final_message_text_color = isset($template->final_message_text_color) ? $template->final_message_text_color : '#333333';

        $button_bg_color = $template->button_bg_color ?? '#667eea';
        $button_text_color = $template->button_text_color ?? '#ffffff';
        $map_marker_color = $template->map_marker_color ?? '#667eea';

        $background_color = $template->background_color ?? '#fdfbfb';
        $background_image = $template->background_image ?? '';
        $background_opacity = $template->background_opacity ?? 1.00;
        $background_size = $template->background_size ?? 100;

        $header_opacity = $template->header_opacity ?? 1.00;
        $header_size = $template->header_size ?? 100;
        $decoration_top_opacity = $template->decoration_top_opacity ?? 1.00;
        $decoration_top_size = $template->decoration_top_size ?? 100;
        $decoration_bottom_opacity = $template->decoration_bottom_opacity ?? 1.00;
        $decoration_bottom_size = $template->decoration_bottom_size ?? 100;
        $user_image_opacity = $template->user_image_opacity ?? 1.00;
        $user_image_size = $template->user_image_size ?? 100;

        // Genera CSS
        $css .= "
/* Titolo */
.wi-title {
    font-family: '{$title_font}', serif !important;
    font-size: {$title_size}px !important;
    color: {$title_color} !important;
}

/* Divider */
.wi-divider {
    background-color: {$divider_color} !important;
    border-color: {$divider_color} !important;
}

/* Countdown */
.wi-countdown-label {
    font-family: '{$countdown_label_font}', sans-serif !important;
    font-size: {$countdown_label_size}px !important;
    color: {$countdown_label_color} !important;
}

.countdown-value {
    font-family: '{$countdown_font}', sans-serif !important;
    color: {$countdown_color} !important;
}

.countdown-item {
    background-color: {$countdown_bg_color} !important;
    border: 2px solid {$countdown_border_color} !important;
}

.countdown-label {
    font-family: '{$countdown_label_font}', sans-serif !important;
    font-size: {$countdown_label_size}px !important;
    color: {$countdown_label_color} !important;
}

/* Messaggio */
.wi-message {
    font-family: '{$message_font}', sans-serif !important;
    font-size: {$message_size}px !important;
    color: {$message_color} !important;
    background-color: {$message_bg_color};
    padding: 30px;
    border-radius: 10px;
}

/* Dettagli Evento */
.wi-event-info,
.wi-event-details {
    font-family: '{$details_font}', sans-serif !important;
    font-size: {$details_size}px !important;
}

.wi-event-item,
.wi-detail-item {
    background-color: {$details_bg_color} !important;
    border-left: 4px solid {$details_border_color} !important;
}

.wi-detail-label,
.wi-detail-item strong {
    color: {$details_label_color} !important;
    text-align: {$details_align} !important;
}

.wi-detail-value,
.wi-detail-item p {
    color: {$details_value_color} !important;
    text-align: {$details_align} !important;
}

.wi-detail-content {
    text-align: {$details_align} !important;
}" . ($hide_event_icons ? "

/* Nascondi icone evento */
.wi-event-item::before,
.wi-detail-icon {
    display: none !important;
}" : "") . "


/* Pulsanti */
.wi-add-calendar,
.wi-share-btn {
    background-color: {$button_bg_color} !important;
    color: {$button_text_color} !important;
}

.wi-share-btn.wi-whatsapp {
    background-color: #25D366 !important;
}

.wi-share-btn.wi-email {
    background-color: #3498db !important;
}

.wi-share-btn.wi-copy {
    background-color: #95a5a6 !important;
}

/* Pulsanti Navigazione (Google Maps, Waze, Apple Maps) */
.wi-nav-btn.google-maps,
.wi-map-link {
    background-color: {$map_marker_color} !important;
    color: #ffffff !important;
}

.wi-nav-btn.google-maps:hover,
.wi-map-link:hover {
    opacity: 0.9 !important;
}

/* Marker Mappa */
.leaflet-marker-icon {
    filter: hue-rotate(0deg) !important;
}

/* Messaggio Finale */
.wi-final-message-btn {
    background-color: {$final_message_btn_bg_color} !important;
    color: {$final_message_btn_text_color} !important;
}

.wi-final-message-content {
    color: {$final_message_text_color} !important;
}

/* Background generale */
.wi-single-invite-wrapper,
.wi-template,
.wi-invite {
    background-color: {$background_color};" .
    (!empty($background_image) ? "
    background-image: url('{$background_image}');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-attachment: fixed;" : "") . "
}

/* Immagini - Desktop */
.wi-header-image img {
    opacity: {$header_opacity};
    width: " . $header_size . "%;
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.wi-decoration-top img {
    opacity: {$decoration_top_opacity};
    width: " . $decoration_top_size . "%;
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.wi-decoration-bottom img {
    opacity: {$decoration_bottom_opacity};
    width: " . $decoration_bottom_size . "%;
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.wi-user-image img {
    opacity: {$user_image_opacity};
    width: " . $user_image_size . "%;
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 50%;
}

/* Responsive */
@media (max-width: 768px) {
    /* Testi */
    .wi-title {
        font-size: " . ($title_size * 0.6) . "px !important;
    }

    .wi-message {
        font-size: " . ($message_size * 0.9) . "px !important;
        padding: 20px 15px !important;
    }

    /* Immagini Mobile - Auto-scaling intelligente */
    .wi-header-image img {
        width: " . min($header_size * 0.9, 90) . "% !important;
        max-width: 90% !important;
    }

    .wi-decoration-top img {
        width: " . min($decoration_top_size * 0.8, 80) . "% !important;
        max-width: 80% !important;
    }

    .wi-decoration-bottom img {
        width: " . min($decoration_bottom_size * 0.8, 80) . "% !important;
        max-width: 80% !important;
    }

    .wi-user-image img {
        width: " . min($user_image_size * 0.7, 70) . "% !important;
        max-width: 200px !important;
    }

    /* Countdown Responsive - Centrato e Ingrandito */
    .wi-countdown {
        display: flex !important;
        justify-content: center !important;
        gap: 12px !important;
        margin: 0 auto !important;
        max-width: 100% !important;
        padding: 30px 10px !important;
    }

    .countdown-item {
        min-width: 85px !important;
        padding: 20px 15px !important;
    }

    .countdown-value {
        font-size: 2.8rem !important;
    }

    .countdown-label {
        font-size: 0.75rem !important;
        letter-spacing: 1px !important;
        margin-top: 8px !important;
    }

    /* Dettagli evento - Migliore leggibilità mobile */
    .wi-detail-item {
        padding: 15px 12px !important;
        margin-bottom: 12px !important;
    }

    .wi-detail-label,
    .wi-detail-value {
        font-size: " . ($details_size * 0.9) . "px !important;
    }

    /* Pulsanti - Touch friendly */
    .wi-add-calendar,
    .wi-share-btn,
    .wi-nav-btn,
    .wi-final-message-btn {
        padding: 14px 24px !important;
        font-size: 0.95rem !important;
        min-height: 48px !important;
    }

    /* Mappa - Altezza fissa mobile */
    .wi-map {
        height: 300px !important;
        margin: 20px 0 !important;
    }
}
";

        // Aggiungi CSS personalizzato se presente
        if (!empty($template->custom_css)) {
            $css .= "\n/* CSS Personalizzato */\n";
            $css .= $template->custom_css . "\n";
        }

        return $css;
    }

    /**
     * Formatta una data dal formato YYYY-MM-DD al formato italiano GG/MM/AAAA
     *
     * @param string $date Data in formato YYYY-MM-DD
     * @return string Data in formato GG/MM/AAAA o stringa vuota se data non valida
     */
    private static function format_date_italian($date) {
        if (empty($date)) {
            return '';
        }

        // Verifica che la data sia nel formato corretto YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date; // Ritorna la data così com'è se non è nel formato atteso
        }

        // Converte da YYYY-MM-DD a GG/MM/AAAA
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date; // Se la conversione fallisce, ritorna la data originale
        }

        return date('d/m/Y', $timestamp);
    }
}