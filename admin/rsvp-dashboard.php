<?php
/**
 * Dashboard Admin RSVP
 * Visualizza tutte le risposte RSVP per un invito specifico
 */

if (!defined('ABSPATH')) exit;

// Check permessi
if (!current_user_can('manage_options')) {
    wp_die('Non hai i permessi per accedere a questa pagina');
}

// Ottieni invite_id dalla query string
$invite_id = isset($_GET['invite_id']) ? intval($_GET['invite_id']) : 0;

// Se non specificato, mostra lista inviti con RSVP
if (!$invite_id) {
    include WI_PLUGIN_DIR . 'admin/rsvp-invites-list.php';
    return;
}

// Verifica che l'invito esista
$invite = get_post($invite_id);
if (!$invite || $invite->post_type !== 'wi_invite') {
    echo '<div class="notice notice-error"><p>Invito non trovato.</p></div>';
    return;
}

// Ottieni statistiche e risposte
$stats = WI_RSVP::get_stats($invite_id);
$responses = WI_RSVP::get_responses($invite_id);
$settings = WI_RSVP_Database::get_settings($invite_id);

// Prepare menu choices con null check
$menu_choices = !empty($settings->menu_choices) ? json_decode($settings->menu_choices, true) : array();
if (!is_array($menu_choices)) {
    $menu_choices = array('Carne', 'Pesce', 'Vegetariano');
}
?>

<div class="wrap wi-rsvp-dashboard">

    <!-- Header -->
    <div class="wi-rsvp-header">
        <div class="wi-header-top">
            <h1 class="wp-heading-inline">
                📊 Dashboard RSVP
            </h1>
            <a href="<?php echo admin_url('admin.php?page=wi-rsvp'); ?>" class="page-title-action">
                ← Tutti gli Inviti
            </a>
            <a href="<?php echo admin_url('admin.php?page=wi-rsvp&invite_id=' . $invite_id . '&action=export'); ?>"
               class="page-title-action">
                📥 Esporta CSV
            </a>
        </div>

        <h2 class="wi-invite-title"><?php echo esc_html(get_the_title($invite_id)); ?></h2>

        <div class="wi-invite-meta">
            <span>🔗 <a href="<?php echo get_permalink($invite_id); ?>" target="_blank">Visualizza Invito</a></span>
            <?php if ($settings->rsvp_deadline) : ?>
            <span>⏰ Deadline: <strong><?php echo date_i18n('j F Y', strtotime($settings->rsvp_deadline)); ?></strong></span>
            <?php endif; ?>
        </div>
    </div>

    <hr class="wp-header-end">

    <!-- Statistics Cards -->
    <div class="wi-stats-grid">
        <div class="wi-stat-card wi-stat-primary">
            <div class="wi-stat-icon">✅</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo intval($stats['summary']['confirmed']); ?></div>
                <div class="wi-stat-label">Confermati</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-danger">
            <div class="wi-stat-icon">❌</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo intval($stats['summary']['declined']); ?></div>
                <div class="wi-stat-label">Rifiutati</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-warning">
            <div class="wi-stat-icon">❓</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo intval($stats['summary']['maybe']); ?></div>
                <div class="wi-stat-label">In Sospeso</div>
            </div>
        </div>

        <div class="wi-stat-card wi-stat-info">
            <div class="wi-stat-icon">👥</div>
            <div class="wi-stat-content">
                <div class="wi-stat-value"><?php echo intval($stats['summary']['total_guests']); ?></div>
                <div class="wi-stat-label">Totale Ospiti</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="wi-charts-row">

        <!-- Menu Preferences -->
        <?php if (!empty($stats['menu']) && is_array($stats['menu'])) : ?>
        <div class="wi-chart-card">
            <h3>🍽️ Preferenze Menu</h3>
            <table class="wi-stats-table">
                <tbody>
                <?php foreach ($stats['menu'] as $item) : ?>
                    <tr>
                        <td class="wi-stat-name"><?php echo esc_html($item['menu_choice']); ?></td>
                        <td class="wi-stat-bar">
                            <div class="wi-progress-bar">
                                <div class="wi-progress-fill" style="width: <?php echo ($item['count'] / max(1, $stats['summary']['confirmed'])) * 100; ?>%"></div>
                            </div>
                        </td>
                        <td class="wi-stat-count"><?php echo intval($item['count']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Dietary Preferences -->
        <?php if (!empty($stats['dietary']) && is_array($stats['dietary'])) : ?>
        <div class="wi-chart-card">
            <h3>⚠️ Allergie & Intolleranze</h3>
            <table class="wi-stats-table">
                <tbody>
                <?php
                $dietary_labels = [
                    'gluten_free' => 'Senza Glutine',
                    'lactose_free' => 'Senza Lattosio',
                    'vegan' => 'Vegano',
                    'vegetarian' => 'Vegetariano'
                ];
                foreach ($stats['dietary'] as $type => $count) :
                    $label = $dietary_labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
                ?>
                    <tr>
                        <td class="wi-stat-name"><?php echo esc_html($label); ?></td>
                        <td class="wi-stat-bar">
                            <div class="wi-progress-bar">
                                <div class="wi-progress-fill" style="width: <?php echo ($count / max(1, $stats['summary']['confirmed'])) * 100; ?>%"></div>
                            </div>
                        </td>
                        <td class="wi-stat-count"><?php echo intval($count); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Responses Table -->
    <div class="wi-responses-section">
        <div class="wi-section-header">
            <h3>📋 Risposte RSVP (<?php echo count($responses); ?>)</h3>

            <!-- Filters -->
            <div class="wi-filters">
                <select id="wi-filter-status">
                    <option value="">Tutti gli Stati</option>
                    <option value="attending">✅ Confermati</option>
                    <option value="not_attending">❌ Rifiutati</option>
                    <option value="maybe">❓ In Sospeso</option>
                </select>

                <input type="search" id="wi-search-guests" placeholder="Cerca per nome o email...">
            </div>
        </div>

        <?php if (empty($responses)) : ?>
            <div class="wi-empty-state">
                <div class="wi-empty-icon">📭</div>
                <h4>Nessuna risposta ancora</h4>
                <p>Le risposte RSVP appariranno qui non appena gli ospiti confermano.</p>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped wi-rsvp-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefono</th>
                        <th>Stato</th>
                        <th>Ospiti</th>
                        <th>Menu</th>
                        <th>Allergie</th>
                        <th>Data Risposta</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($responses as $response) :
                    $status_labels = [
                        'attending' => '<span class="wi-badge wi-badge-success">✅ Confermato</span>',
                        'not_attending' => '<span class="wi-badge wi-badge-danger">❌ Rifiutato</span>',
                        'maybe' => '<span class="wi-badge wi-badge-warning">❓ In Sospeso</span>'
                    ];

                    $dietary = json_decode($response->dietary_preferences, true);
                    $dietary_str = is_array($dietary) ? implode(', ', $dietary) : '-';
                ?>
                    <tr data-status="<?php echo esc_attr($response->status); ?>"
                        data-search="<?php echo esc_attr(strtolower($response->guest_name . ' ' . $response->guest_email)); ?>">
                        <td class="wi-col-name"><strong><?php echo esc_html($response->guest_name); ?></strong></td>
                        <td class="wi-col-email">
                            <a href="mailto:<?php echo esc_attr($response->guest_email); ?>">
                                <?php echo esc_html($response->guest_email); ?>
                            </a>
                        </td>
                        <td class="wi-col-phone"><?php echo esc_html($response->guest_phone ?: '-'); ?></td>
                        <td class="wi-col-status"><?php echo $status_labels[$response->status]; ?></td>
                        <td class="wi-col-guests"><?php echo intval($response->num_guests); ?></td>
                        <td class="wi-col-menu"><?php echo esc_html($response->menu_choice ?: '-'); ?></td>
                        <td class="wi-col-dietary"><?php echo esc_html($dietary_str); ?></td>
                        <td class="wi-col-date"><?php echo date_i18n('d/m/Y H:i', strtotime($response->responded_at)); ?></td>
                        <td class="wi-col-actions">
                            <button type="button"
                                    class="button button-small wi-btn-view-notes"
                                    data-notes="<?php echo esc_attr($response->notes); ?>"
                                    <?php echo empty($response->notes) ? 'disabled' : ''; ?>>
                                💬 Note
                            </button>
                            <button type="button"
                                    class="button button-small button-link-delete wi-btn-delete"
                                    data-response-id="<?php echo $response->id; ?>"
                                    data-guest-name="<?php echo esc_attr($response->guest_name); ?>">
                                🗑️ Elimina
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Note -->
<div id="wi-notes-modal" class="wi-modal" style="display:none;">
    <div class="wi-modal-overlay"></div>
    <div class="wi-modal-content">
        <div class="wi-modal-header">
            <h3>💬 Note Ospite</h3>
            <button type="button" class="wi-modal-close">×</button>
        </div>
        <div class="wi-modal-body">
            <p id="wi-notes-text"></p>
        </div>
    </div>
</div>

<style>
/* RSVP Dashboard Styles */
.wi-rsvp-dashboard {
    max-width: 1400px;
}

.wi-rsvp-header {
    margin-bottom: 30px;
}

.wi-header-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.wi-invite-title {
    font-size: 20px;
    margin: 0 0 8px 0;
    color: #1e293b;
}

.wi-invite-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #64748b;
}

.wi-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.wi-stat-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border-left: 4px solid #e2e8f0;
}

.wi-stat-primary { border-left-color: #10b981; }
.wi-stat-danger { border-left-color: #ef4444; }
.wi-stat-warning { border-left-color: #f59e0b; }
.wi-stat-info { border-left-color: #3b82f6; }

.wi-stat-icon {
    font-size: 32px;
    line-height: 1;
}

.wi-stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1e293b;
}

.wi-stat-label {
    font-size: 14px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wi-charts-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.wi-chart-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.wi-chart-card h3 {
    margin: 0 0 16px 0;
    font-size: 16px;
    color: #1e293b;
}

.wi-stats-table {
    width: 100%;
    border-collapse: collapse;
}

.wi-stats-table td {
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}

.wi-stat-name {
    width: 40%;
    font-weight: 500;
}

.wi-stat-bar {
    width: 45%;
}

.wi-stat-count {
    width: 15%;
    text-align: right;
    font-weight: 600;
    color: #667eea;
}

.wi-progress-bar {
    height: 8px;
    background: #f1f5f9;
    border-radius: 4px;
    overflow: hidden;
}

.wi-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.wi-responses-section {
    background: #ffffff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.wi-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.wi-section-header h3 {
    margin: 0;
    font-size: 18px;
}

.wi-filters {
    display: flex;
    gap: 12px;
}

.wi-filters select,
.wi-filters input {
    padding: 6px 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}

.wi-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.wi-badge-success {
    background: #d1fae5;
    color: #065f46;
}

.wi-badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.wi-badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.wi-empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.wi-empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
}

.wi-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 100000;
}

.wi-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}

.wi-modal-content {
    position: relative;
    max-width: 500px;
    margin: 100px auto;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.wi-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.wi-modal-header h3 {
    margin: 0;
}

.wi-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #94a3b8;
}

.wi-modal-body {
    padding: 24px;
}

@media (max-width: 768px) {
    .wi-charts-row {
        grid-template-columns: 1fr;
    }

    .wi-section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .wi-filters {
        width: 100%;
        flex-direction: column;
    }

    .wi-filters select,
    .wi-filters input {
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {

    // Filter by status
    $('#wi-filter-status').on('change', function() {
        const status = $(this).val();

        if (!status) {
            $('.wi-rsvp-table tbody tr').show();
        } else {
            $('.wi-rsvp-table tbody tr').hide();
            $(`.wi-rsvp-table tbody tr[data-status="${status}"]`).show();
        }
    });

    // Search guests
    $('#wi-search-guests').on('input', function() {
        const search = $(this).val().toLowerCase();

        $('.wi-rsvp-table tbody tr').each(function() {
            const searchData = $(this).data('search');
            if (searchData.includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // View notes
    $('.wi-btn-view-notes').on('click', function() {
        const notes = $(this).data('notes');
        $('#wi-notes-text').text(notes || 'Nessuna nota');
        $('#wi-notes-modal').fadeIn(200);
    });

    // Close modal
    $('.wi-modal-close, .wi-modal-overlay').on('click', function() {
        $('#wi-notes-modal').fadeOut(200);
    });

    // Delete response
    $('.wi-btn-delete').on('click', function() {
        const responseId = $(this).data('response-id');
        const guestName = $(this).data('guest-name');

        if (!confirm(`Eliminare la risposta di ${guestName}?`)) {
            return;
        }

        $.post(ajaxurl, {
            action: 'wi_delete_rsvp_response',
            nonce: '<?php echo wp_create_nonce(WI_NONCE_ADMIN); ?>',
            response_id: responseId
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Errore eliminazione');
            }
        });
    });
});
</script>

<?php
// Handle export CSV
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    WI_RSVP::export_csv($invite_id);
    exit;
}
?>
