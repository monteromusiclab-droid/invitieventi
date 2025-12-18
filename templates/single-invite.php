<?php
/**
 * Template per la visualizzazione di un singolo invito
 *
 * Questo file mostra semplicemente l'HTML generato dal template.
 * Tutte le sezioni (mappa, calendario, condivisione) sono già incluse nel template HTML.
 */

get_header();

$invite_id = get_the_ID();
$invite_data = WI_Invites::get_invite_data($invite_id);
$template_id = $invite_data['template_id'];

// Genera HTML completo dell'invito dal template
// Il template include già: countdown, mappa, calendario, condivisione social
$invite_html = WI_Invites::generate_invite_html($invite_data, $template_id);
?>

<!-- Leaflet CSS per mappa OpenStreetMap -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<div class="wi-single-invite-wrapper">

    <!-- Story Card 9:16 per condivisione social -->
    <?php
    $story_card_html = WI_Story_Cards::render_story_card($invite_id, $invite_data);
    if (!empty($story_card_html)) {
        echo $story_card_html;
    }
    ?>

    <!-- Contenuto completo dell'invito (generato dal template) -->
    <?php echo $invite_html; ?>

    <!-- Sezione RSVP -->
    <?php
    // Include RSVP section se abilitata
    include WI_PLUGIN_DIR . 'templates/rsvp-section.php';
    ?>

    <!-- Pulsanti Gestione Invito (solo per autore) -->
    <?php
    $current_user_id = get_current_user_id();
    $invite_author_id = get_post_field('post_author', $invite_id);

    if (is_user_logged_in() && ($current_user_id == $invite_author_id || current_user_can('manage_options'))) :
        // Trova URL della pagina con shortcode [my_invites_dashboard]
        $dashboard_page = get_pages(array(
            'meta_key' => '_wp_page_template',
            'hierarchical' => 0
        ));

        $dashboard_url = '';
        foreach ($dashboard_page as $page) {
            if (has_shortcode($page->post_content, 'my_invites_dashboard')) {
                $dashboard_url = get_permalink($page->ID);
                break;
            }
        }

        // Fallback: cerca qualsiasi pagina con lo shortcode
        if (empty($dashboard_url)) {
            global $wpdb;
            $dashboard_page_id = $wpdb->get_var(
                "SELECT ID FROM {$wpdb->posts}
                WHERE post_content LIKE '%[my_invites_dashboard]%'
                AND post_status = 'publish'
                AND post_type = 'page'
                LIMIT 1"
            );
            if ($dashboard_page_id) {
                $dashboard_url = get_permalink($dashboard_page_id);
            }
        }

        // URL wizard per modifica
        $wizard_page_id = $wpdb->get_var(
            "SELECT ID FROM {$wpdb->posts}
            WHERE post_content LIKE '%[wedding_invites_wizard]%'
            AND post_status = 'publish'
            AND post_type = 'page'
            LIMIT 1"
        );

        $edit_url = '';
        if ($wizard_page_id) {
            $edit_url = add_query_arg('edit', $invite_id, get_permalink($wizard_page_id));
        } else {
            // Fallback: admin edit
            $edit_url = admin_url('admin.php?page=wedding-invites-edit&invite_id=' . $invite_id);
        }
    ?>

    <div class="wi-invite-author-actions" style="margin-top: 40px; padding: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; text-align: center; box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);">
        <h3 style="color: white; margin: 0 0 20px 0; font-size: 22px; font-weight: 600;">
            ⚙️ Gestisci il Tuo Invito
        </h3>
        <p style="color: rgba(255,255,255,0.9); margin: 0 0 25px 0; font-size: 15px;">
            Modifica i dettagli, visualizza le conferme RSVP o gestisci le impostazioni
        </p>
        <div class="wi-action-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo esc_url($edit_url); ?>"
               class="wi-btn-edit"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: white; color: #667eea; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                <span style="font-size: 18px;">✏️</span>
                <span>Modifica Invito</span>
            </a>

            <?php if (!empty($dashboard_url)) : ?>
            <a href="<?php echo esc_url($dashboard_url); ?>"
               class="wi-btn-dashboard"
               style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: rgba(255,255,255,0.2); color: white; border: 2px solid white; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 15px; backdrop-filter: blur(10px); transition: all 0.3s ease;">
                <span style="font-size: 18px;">📊</span>
                <span>Dashboard</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .wi-btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2) !important;
            background: #f8f9ff !important;
        }

        .wi-btn-dashboard:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.3) !important;
        }

        @media (max-width: 600px) {
            .wi-action-buttons {
                flex-direction: column;
            }

            .wi-invite-author-actions {
                padding: 20px !important;
            }

            .wi-btn-edit, .wi-btn-dashboard {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <?php endif; ?>
</div>

<!-- Leaflet JS per mappa OpenStreetMap -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<style>
/* Stili Enhanced Wrapper con Background Dinamico */
body {
    background: linear-gradient(135deg, #fdfaf5 0%, #f8f4eb 50%, #fdfaf5 100%);
    background-attachment: fixed;
}

.wi-single-invite-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    position: relative;
}

/* Decorazioni di sfondo */
.wi-single-invite-wrapper::before {
    content: "";
    position: fixed;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 50%, rgba(212, 175, 55, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(244, 227, 161, 0.05) 0%, transparent 50%);
    animation: backgroundFloat 30s ease-in-out infinite;
    pointer-events: none;
    z-index: -1;
}

@keyframes backgroundFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(30px, 30px) scale(1.1); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .wi-single-invite-wrapper {
        padding: 20px 15px;
    }

    body {
        background: linear-gradient(180deg, #fdfaf5 0%, #f8f4eb 50%, #fdfaf5 100%);
    }
}

@media (max-width: 480px) {
    .wi-single-invite-wrapper {
        padding: 15px 10px;
    }
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Scrollbar personalizzata */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1ede3;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #d4af37 0%, #f4e3a1 100%);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(180deg, #f4e3a1 0%, #d4af37 100%);
}
</style>

<?php
get_footer();

/* NOTA IMPORTANTE PER SVILUPPATORI:
 * ====================================
 * NON aggiungere qui sezioni aggiuntive (mappa, calendario, condivisione, ecc.)!
 *
 * Tutto il contenuto dell'invito è generato dinamicamente dal template selezionato
 * dall'utente. Ogni template include già tutte le sezioni necessarie:
 *
 * - Countdown animato
 * - Mappa OpenStreetMap con pulsanti navigazione
 * - Aggiungi al calendario (Google, Outlook, Apple, ICS)
 * - Condivisione social (WhatsApp, Email, Copia Link)
 * - Messaggio finale espandibile
 *
 * Se vuoi modificare il layout o aggiungere sezioni:
 * 1. Modifica i template in: includes/default-templates-content.php
 * 2. Oppure crea template personalizzati via pannello admin
 * 3. Usa i filtri WordPress: wi_before_render_invite, wi_after_render_invite
 *
 * Esempio hook per estendere:
 *
 * add_filter('wi_after_render_invite', function($html, $data, $template_id, $is_preview) {
 *     $custom_section = '<div class="custom-section">Il mio contenuto</div>';
 *     return $html . $custom_section;
 * }, 10, 4);
 *
 * Per maggiori informazioni consulta: DEVELOPER-HOOKS.md
 */
