<?php
/**
 * Lista Inviti con RSVP
 * Mostra tutti gli inviti con conteggio risposte
 */

if (!defined('ABSPATH')) exit;

// Ottieni tutti gli inviti
$args = array(
    'post_type' => 'wi_invite',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
);

$invites = get_posts($args);

// Ottieni statistiche per tutti gli inviti in una sola query (ottimizzazione N+1)
$invite_ids = array_map(function($invite) { return $invite->ID; }, $invites);
$bulk_stats = WI_RSVP::get_bulk_stats($invite_ids);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">📊 RSVP - Tutti gli Inviti</h1>
    <hr class="wp-header-end">

    <?php if (empty($invites)) : ?>
        <div class="wi-empty-state">
            <p>Nessun invito trovato. <a href="<?php echo admin_url('post-new.php?post_type=wedding_invite'); ?>">Crea il tuo primo invito</a></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Invito</th>
                    <th>Data Creazione</th>
                    <th>Confermati</th>
                    <th>Rifiutati</th>
                    <th>In Sospeso</th>
                    <th>Totale Ospiti</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($invites as $invite) :
                $stats = $bulk_stats[$invite->ID] ?? array('summary' => array('total_responses' => 0, 'confirmed' => 0, 'declined' => 0, 'maybe' => 0, 'total_guests' => 0));
                $total_responses = $stats['summary']['total_responses'];
            ?>
                <tr>
                    <td>
                        <strong><a href="<?php echo admin_url('admin.php?page=wi-rsvp&invite_id=' . $invite->ID); ?>">
                            <?php echo esc_html($invite->post_title); ?>
                        </a></strong>
                    </td>
                    <td><?php echo get_the_date('d/m/Y', $invite); ?></td>
                    <td><span class="wi-badge wi-badge-success"><?php echo intval($stats['summary']['confirmed']); ?></span></td>
                    <td><span class="wi-badge wi-badge-danger"><?php echo intval($stats['summary']['declined']); ?></span></td>
                    <td><span class="wi-badge wi-badge-warning"><?php echo intval($stats['summary']['maybe']); ?></span></td>
                    <td><strong><?php echo intval($stats['summary']['total_guests']); ?></strong></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=wi-rsvp&invite_id=' . $invite->ID); ?>" class="button button-primary button-small">
                            📊 Visualizza
                        </a>
                        <?php if ($total_responses > 0) : ?>
                        <a href="<?php echo admin_url('admin.php?page=wi-rsvp&invite_id=' . $invite->ID . '&action=export'); ?>" class="button button-small">
                            📥 CSV
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
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
    padding: 60px 20px;
    text-align: center;
    color: #94a3b8;
}
</style>
