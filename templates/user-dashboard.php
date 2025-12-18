<?php
/**
 * Dashboard Personale Utente
 * Mostra gli inviti creati dall'utente e le statistiche RSVP
 *
 * Shortcode: [my_invites_dashboard]
 */

if (!defined('ABSPATH')) exit;

// Verifica login
if (!is_user_logged_in()) {
    ?>
    <div class="wi-user-dashboard wi-not-logged-in">
        <div class="wi-login-prompt">
            <div class="wi-prompt-icon">🔒</div>
            <h2>Accesso Richiesto</h2>
            <p>Devi essere registrato per accedere alla tua dashboard personale.</p>
            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="wi-btn wi-btn-primary">
                Accedi
            </a>
        </div>
    </div>
    <?php
    return;
}

$current_user_id = get_current_user_id();

// Ottieni inviti dell'utente corrente
$args = array(
    'post_type' => 'wi_invite',
    'author' => $current_user_id,
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
);

$user_invites = get_posts($args);

// Ottieni statistiche bulk
$invite_ids = array_map(function($invite) { return $invite->ID; }, $user_invites);
$bulk_stats = !empty($invite_ids) ? WI_RSVP::get_bulk_stats($invite_ids) : array();

// Calcola statistiche totali
$total_invites = count($user_invites);
$total_confirmed = 0;
$total_declined = 0;
$total_pending = 0;
$total_guests = 0;

foreach ($bulk_stats as $stats) {
    $total_confirmed += intval($stats['summary']['confirmed'] ?? 0);
    $total_declined += intval($stats['summary']['declined'] ?? 0);
    $total_pending += intval($stats['summary']['maybe'] ?? 0);
    $total_guests += intval($stats['summary']['total_guests'] ?? 0);
}

$user_info = wp_get_current_user();
?>

<div class="wi-user-dashboard">

    <!-- Header Dashboard -->
    <div class="wi-dashboard-header">
        <div class="wi-header-content">
            <h1 class="wi-dashboard-title">
                <span class="wi-title-icon">📊</span>
                La Mia Dashboard
            </h1>
            <p class="wi-dashboard-subtitle">Benvenuto, <strong><?php echo esc_html($user_info->display_name); ?></strong>!</p>
        </div>

        <div class="wi-header-actions">
            <a href="<?php echo home_url('/crea-invito/'); ?>" class="wi-btn wi-btn-primary">
                <span class="wi-btn-icon">✨</span>
                Crea Nuovo Invito
            </a>
        </div>
    </div>

    <!-- Statistiche Riepilogo -->
    <div class="wi-stats-grid">
        <div class="wi-stat-card wi-stat-invites">
            <div class="wi-stat-icon">📮</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo $total_invites; ?></div>
                <div class="wi-stat-label">Inviti Creati</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-confirmed">
            <div class="wi-stat-icon">✅</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo $total_confirmed; ?></div>
                <div class="wi-stat-label">Confermati</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-declined">
            <div class="wi-stat-icon">❌</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo $total_declined; ?></div>
                <div class="wi-stat-label">Rifiutati</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-guests">
            <div class="wi-stat-icon">👥</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo $total_guests; ?></div>
                <div class="wi-stat-label">Ospiti Totali</div>
            </div>
        </div>
    </div>

    <?php if (empty($user_invites)) : ?>
        <!-- Empty State -->
        <div class="wi-empty-state">
            <div class="wi-empty-icon">📭</div>
            <h3>Nessun Invito Creato</h3>
            <p>Non hai ancora creato nessun invito. Inizia ora a creare il tuo primo invito digitale!</p>
            <a href="<?php echo home_url('/crea-invito/'); ?>" class="wi-btn wi-btn-primary wi-btn-large">
                <span class="wi-btn-icon">✨</span>
                Crea il Tuo Primo Invito
            </a>
        </div>
    <?php else : ?>

        <!-- Lista Inviti -->
        <div class="wi-invites-section">
            <h2 class="wi-section-title">I Tuoi Inviti</h2>

            <div class="wi-invites-list">
                <?php foreach ($user_invites as $invite) :
                    $stats = $bulk_stats[$invite->ID] ?? array('summary' => array(
                        'total_responses' => 0,
                        'confirmed' => 0,
                        'declined' => 0,
                        'maybe' => 0,
                        'total_guests' => 0
                    ));

                    $invite_data = WI_Invites::get_invite_data($invite->ID);
                    $settings = WI_RSVP_Database::get_settings($invite->ID);
                    $rsvp_enabled = $settings && $settings->rsvp_enabled;

                    $total_responses = intval($stats['summary']['total_responses']);
                    $confirmed = intval($stats['summary']['confirmed']);
                    $declined = intval($stats['summary']['declined']);
                    $maybe = intval($stats['summary']['maybe']);
                    $total_guests_invite = intval($stats['summary']['total_guests']);
                ?>

                <div class="wi-invite-card" data-invite-id="<?php echo $invite->ID; ?>">
                    <div class="wi-card-header">
                        <div class="wi-card-title-section">
                            <h3 class="wi-card-title"><?php echo esc_html($invite->post_title); ?></h3>
                            <div class="wi-card-meta">
                                <span class="wi-meta-date">
                                    <span class="wi-meta-icon">📅</span>
                                    <?php echo date_i18n('j F Y', strtotime($invite_data['event_date'])); ?>
                                </span>
                                <span class="wi-meta-location">
                                    <span class="wi-meta-icon">📍</span>
                                    <?php echo esc_html($invite_data['event_location']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="wi-card-actions">
                            <a href="<?php echo get_permalink($invite->ID); ?>"
                               class="wi-btn wi-btn-small wi-btn-outline"
                               target="_blank"
                               title="Visualizza invito">
                                <span class="wi-btn-icon">👁️</span>
                                Visualizza
                            </a>
                        </div>
                    </div>

                    <?php if ($rsvp_enabled) : ?>
                    <!-- Statistiche RSVP -->
                    <div class="wi-card-stats">
                        <div class="wi-stat-mini wi-stat-confirmed">
                            <div class="wi-mini-icon">✅</div>
                            <div class="wi-mini-content">
                                <div class="wi-mini-value"><?php echo $confirmed; ?></div>
                                <div class="wi-mini-label">Confermati</div>
                            </div>
                        </div>

                        <div class="wi-stat-mini wi-stat-declined">
                            <div class="wi-mini-icon">❌</div>
                            <div class="wi-mini-content">
                                <div class="wi-mini-value"><?php echo $declined; ?></div>
                                <div class="wi-mini-label">Rifiutati</div>
                            </div>
                        </div>

                        <div class="wi-stat-mini wi-stat-pending">
                            <div class="wi-mini-icon">❓</div>
                            <div class="wi-mini-content">
                                <div class="wi-mini-value"><?php echo $maybe; ?></div>
                                <div class="wi-mini-label">Forse</div>
                            </div>
                        </div>

                        <div class="wi-stat-mini wi-stat-guests">
                            <div class="wi-mini-icon">👥</div>
                            <div class="wi-mini-content">
                                <div class="wi-mini-value"><?php echo $total_guests_invite; ?></div>
                                <div class="wi-mini-label">Ospiti</div>
                            </div>
                        </div>
                    </div>

                    <!-- Azioni RSVP -->
                    <div class="wi-card-footer">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; width: 100%;">
                            <button type="button"
                                    class="wi-btn wi-btn-small wi-btn-primary wi-btn-view-guests"
                                    data-invite-id="<?php echo $invite->ID; ?>">
                                <span class="wi-btn-icon">👥</span>
                                Vedi Lista Ospiti (<?php echo $total_responses; ?>)
                            </button>

                            <?php if ($total_responses > 0) : ?>
                            <a href="#"
                               class="wi-btn wi-btn-small wi-btn-outline wi-btn-export-csv"
                               data-invite-id="<?php echo $invite->ID; ?>">
                                <span class="wi-btn-icon">📥</span>
                                Esporta CSV
                            </a>
                            <?php endif; ?>

                            <?php
                            // Link Modifica Invito
                            global $wpdb;
                            $wizard_page_id = $wpdb->get_var(
                                "SELECT ID FROM {$wpdb->posts}
                                WHERE post_content LIKE '%[wedding_invites_wizard]%'
                                AND post_status = 'publish'
                                AND post_type = 'page'
                                LIMIT 1"
                            );

                            if ($wizard_page_id) {
                                $edit_url = add_query_arg('edit', $invite->ID, get_permalink($wizard_page_id));
                            } else {
                                $edit_url = admin_url('admin.php?page=wedding-invites-edit&invite_id=' . $invite->ID);
                            }
                            ?>

                            <a href="<?php echo esc_url($edit_url); ?>"
                               class="wi-btn wi-btn-small"
                               style="background: #f59e0b; color: white; margin-left: auto;">
                                <span class="wi-btn-icon">✏️</span>
                                Modifica
                            </a>
                        </div>
                    </div>

                    <?php else : ?>
                    <!-- RSVP Disabilitato -->
                    <div class="wi-card-footer">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div class="wi-rsvp-disabled">
                                <span class="wi-disabled-icon">🔒</span>
                                RSVP non abilitato per questo invito
                            </div>

                            <?php
                            // Link Modifica Invito (anche se RSVP disabilitato)
                            global $wpdb;
                            $wizard_page_id = $wpdb->get_var(
                                "SELECT ID FROM {$wpdb->posts}
                                WHERE post_content LIKE '%[wedding_invites_wizard]%'
                                AND post_status = 'publish'
                                AND post_type = 'page'
                                LIMIT 1"
                            );

                            if ($wizard_page_id) {
                                $edit_url = add_query_arg('edit', $invite->ID, get_permalink($wizard_page_id));
                            } else {
                                $edit_url = admin_url('admin.php?page=wedding-invites-edit&invite_id=' . $invite->ID);
                            }
                            ?>

                            <a href="<?php echo esc_url($edit_url); ?>"
                               class="wi-btn wi-btn-small"
                               style="background: #f59e0b; color: white;">
                                <span class="wi-btn-icon">✏️</span>
                                Modifica
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Modal Lista Ospiti (nascosto inizialmente) -->
                    <div class="wi-guests-modal" id="guests-modal-<?php echo $invite->ID; ?>" style="display:none;">
                        <div class="wi-modal-content">
                            <div class="wi-modal-header">
                                <h3 class="wi-modal-title">Lista Ospiti - <?php echo esc_html($invite->post_title); ?></h3>
                                <button type="button" class="wi-modal-close" data-invite-id="<?php echo $invite->ID; ?>">×</button>
                            </div>
                            <div class="wi-modal-body">
                                <div class="wi-guests-loading">
                                    <div class="wi-loader"></div>
                                    <p>Caricamento ospiti...</p>
                                </div>
                                <div class="wi-guests-list"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

</div>
