<?php
/**
 * Pagina amministrazione - Lista Inviti
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verifica permessi
if (!current_user_can('manage_options')) {
    wp_die(__('Non hai i permessi per accedere a questa pagina.'));
}

// Ottieni tutti gli inviti
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;

$args = array(
    'post_type' => 'wi_invite',
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
    'post_status' => array('publish', 'draft')
);

// Ricerca
if (isset($_GET['s']) && !empty($_GET['s'])) {
    $args['s'] = sanitize_text_field($_GET['s']);
}

$invites_query = new WP_Query($args);
$invites = $invites_query->posts;
$total_invites = $invites_query->found_posts;
$total_pages = $invites_query->max_num_pages;

// Statistiche
$stats_args = array(
    'post_type' => 'wi_invite',
    'posts_per_page' => -1,
    'fields' => 'ids'
);
$all_invites = new WP_Query($stats_args);
$total_all = $all_invites->found_posts;

// Inviti questo mese
$month_args = $stats_args;
$month_args['date_query'] = array(
    array(
        'year' => date('Y'),
        'month' => date('m')
    )
);
$month_invites = new WP_Query($month_args);
$total_month = $month_invites->found_posts;

// Utenti attivi
$users_args = array(
    'post_type' => 'wi_invite',
    'posts_per_page' => -1
);
$users_query = new WP_Query($users_args);
$unique_authors = array();
if ($users_query->have_posts()) {
    while ($users_query->have_posts()) {
        $users_query->the_post();
        $unique_authors[] = get_the_author_meta('ID');
    }
    wp_reset_postdata();
}
$total_users = count(array_unique($unique_authors));
?>

<div class="wrap wi-admin-wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-email-alt"></span>
        Gestione Inviti
    </h1>
    
    <a href="<?php echo home_url('/crea-invito'); ?>" class="page-title-action">
        <span class="dashicons dashicons-plus-alt"></span> Crea Nuovo Invito
    </a>
    
    <a href="?page=wedding-invites-templates" class="page-title-action">
        <span class="dashicons dashicons-admin-appearance"></span> Template
    </a>
    
    <a href="?page=wedding-invites-settings" class="page-title-action">
        <span class="dashicons dashicons-admin-generic"></span> Impostazioni
    </a>
    
    <hr class="wp-header-end">
    
    <!-- Statistiche rapide -->
    <div class="wi-stats-grid">
        <div class="wi-stat-card">
            <div class="wi-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <span class="dashicons dashicons-tickets-alt"></span>
            </div>
            <div class="wi-stat-content">
                <h3><?php echo number_format($total_all); ?></h3>
                <p>Inviti Totali</p>
            </div>
        </div>
        
        <div class="wi-stat-card">
            <div class="wi-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <span class="dashicons dashicons-calendar"></span>
            </div>
            <div class="wi-stat-content">
                <h3><?php echo number_format($total_month); ?></h3>
                <p>Questo Mese</p>
            </div>
        </div>
        
        <div class="wi-stat-card">
            <div class="wi-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <span class="dashicons dashicons-admin-users"></span>
            </div>
            <div class="wi-stat-content">
                <h3><?php echo number_format($total_users); ?></h3>
                <p>Utenti Attivi</p>
            </div>
        </div>
    </div>
    
    <!-- Form ricerca -->
    <form method="get" class="wi-search-form">
        <input type="hidden" name="page" value="wedding-invites">
        <p class="search-box">
            <input type="search" 
                   name="s" 
                   value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" 
                   placeholder="Cerca inviti...">
            <button type="submit" class="button">
                <span class="dashicons dashicons-search"></span> Cerca
            </button>
        </p>
    </form>
    
    <!-- Tabella inviti -->
    <div class="wi-table-container">
        <?php if (empty($invites)) : ?>
            <div class="wi-empty-state">
                <span class="dashicons dashicons-email-alt"></span>
                <h3>Nessun invito trovato</h3>
                <p>Inizia a creare il tuo primo invito digitale!</p>
                <a href="<?php echo home_url('/crea-invito'); ?>" class="button button-primary button-large">
                    <span class="dashicons dashicons-plus-alt"></span> Crea Primo Invito
                </a>
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped wi-invites-table">
                <thead>
                    <tr>
                        <th class="wi-col-id">ID</th>
                        <th class="wi-col-title">Titolo</th>
                        <th class="wi-col-template">Template</th>
                        <th class="wi-col-event">Evento</th>
                        <th class="wi-col-author">Autore</th>
                        <th class="wi-col-date">Data Creazione</th>
                        <th class="wi-col-actions">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invites as $invite) : 
                        $template_id = get_post_meta($invite->ID, '_wi_template_id', true);
                        $template = WI_Templates::get_template($template_id);
                        $event_date = get_post_meta($invite->ID, '_wi_event_date', true);
                        $event_time = get_post_meta($invite->ID, '_wi_event_time', true);
                        $event_location = get_post_meta($invite->ID, '_wi_event_location', true);
                        $invite_url = get_permalink($invite->ID);
                    ?>
                        <tr>
                            <td class="wi-col-id">
                                <strong>#<?php echo $invite->ID; ?></strong>
                            </td>
                            <td class="wi-col-title">
                                <strong>
                                    <a href="?page=wedding-invites-edit&invite_id=<?php echo $invite->ID; ?>">
                                        <?php echo esc_html($invite->post_title); ?>
                                    </a>
                                </strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="?page=wedding-invites-edit&invite_id=<?php echo $invite->ID; ?>">Modifica</a> |
                                    </span>
                                    <span class="view">
                                        <a href="<?php echo $invite_url; ?>" target="_blank">Visualizza</a> |
                                    </span>
                                    <span class="trash">
                                        <a href="#" class="wi-delete-invite" data-invite-id="<?php echo $invite->ID; ?>">Elimina</a>
                                    </span>
                                </div>
                            </td>
                            <td class="wi-col-template">
                                <?php if ($template) : ?>
                                    <span class="wi-template-badge">
                                        <?php echo esc_html($template->name); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="wi-template-badge wi-template-unknown">N/D</span>
                                <?php endif; ?>
                            </td>
                            <td class="wi-col-event">
                                <div class="wi-event-info">
                                    <?php if ($event_date) : ?>
                                        <div class="wi-event-date">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <?php echo date_i18n('d/m/Y', strtotime($event_date)); ?>
                                            <?php if ($event_time) : ?>
                                                - <?php echo date_i18n('H:i', strtotime($event_time)); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($event_location) : ?>
                                        <div class="wi-event-location">
                                            <span class="dashicons dashicons-location"></span>
                                            <?php echo esc_html($event_location); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="wi-col-author">
                                <?php echo get_the_author_meta('display_name', $invite->post_author); ?>
                            </td>
                            <td class="wi-col-date">
                                <?php echo get_the_date('d/m/Y H:i', $invite->ID); ?>
                            </td>
                            <td class="wi-col-actions">
                                <div class="wi-action-buttons">
                                    <a href="<?php echo $invite_url; ?>" 
                                       class="button button-small wi-btn-view" 
                                       target="_blank"
                                       title="Visualizza invito">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </a>
                                    
                                    <a href="?page=wedding-invites-edit&invite_id=<?php echo $invite->ID; ?>" 
                                       class="button button-small wi-btn-edit" 
                                       title="Modifica invito">
                                        <span class="dashicons dashicons-edit"></span>
                                    </a>
                                    
                                    <button type="button" 
                                            class="button button-small wi-btn-copy" 
                                            data-url="<?php echo $invite_url; ?>"
                                            title="Copia link">
                                        <span class="dashicons dashicons-admin-links"></span>
                                    </button>
                                    
                                    <button type="button" 
                                            class="button button-small wi-btn-delete wi-delete-invite" 
                                            data-invite-id="<?php echo $invite->ID; ?>"
                                            title="Elimina invito">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Paginazione -->
            <?php if ($total_pages > 1) : ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo; Precedente',
                            'next_text' => 'Successivo &raquo;',
                            'total' => $total_pages,
                            'current' => $paged
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.wi-admin-wrap {
    margin: 20px 20px 0 0;
}

.wi-admin-wrap h1 {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.page-title-action {
    display: inline-flex !important;
    align-items: center;
    gap: 5px;
}

.wi-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.wi-stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 20px;
}

.wi-stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
}

.wi-stat-content h3 {
    margin: 0;
    font-size: 2rem;
    color: #2c3e50;
}

.wi-stat-content p {
    margin: 5px 0 0 0;
    color: #7f8c8d;
    font-size: 0.9rem;
}

.wi-search-form {
    margin: 20px 0;
}

.wi-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-top: 20px;
}

.wi-invites-table {
    border: none !important;
}

.wi-col-id {
    width: 60px;
}

.wi-col-title {
    width: 25%;
}

.wi-col-template {
    width: 150px;
}

.wi-col-event {
    width: 20%;
}

.wi-col-author {
    width: 120px;
}

.wi-col-date {
    width: 140px;
}

.wi-col-actions {
    width: 180px;
}

.wi-template-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
}

.wi-template-unknown {
    background: #f5f5f5;
    color: #999;
}

.wi-event-info {
    font-size: 0.9rem;
}

.wi-event-date,
.wi-event-location {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 3px 0;
    color: #555;
}

.wi-event-date .dashicons,
.wi-event-location .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    color: #999;
}

.wi-action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.wi-action-buttons .button {
    min-width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wi-action-buttons .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.wi-btn-view {
    border-color: #2196F3 !important;
    color: #2196F3 !important;
}

.wi-btn-view:hover {
    background: #2196F3 !important;
    color: white !important;
}

.wi-btn-edit {
    border-color: #4CAF50 !important;
    color: #4CAF50 !important;
}

.wi-btn-edit:hover {
    background: #4CAF50 !important;
    color: white !important;
}

.wi-btn-copy {
    border-color: #FF9800 !important;
    color: #FF9800 !important;
}

.wi-btn-copy:hover {
    background: #FF9800 !important;
    color: white !important;
}

.wi-btn-delete {
    border-color: #f44336 !important;
    color: #f44336 !important;
}

.wi-btn-delete:hover {
    background: #f44336 !important;
    color: white !important;
}

.wi-empty-state {
    text-align: center;
    padding: 60px 20px;
}

.wi-empty-state .dashicons {
    font-size: 80px;
    width: 80px;
    height: 80px;
    color: #ddd;
}

.wi-empty-state h3 {
    margin: 20px 0 10px 0;
    color: #555;
}

.wi-empty-state p {
    color: #999;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .wi-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .wi-action-buttons {
        justify-content: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copia link
    $('.wi-btn-copy').on('click', function() {
        var url = $(this).data('url');
        
        // Crea elemento temporaneo
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(url).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Feedback visivo
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<span class="dashicons dashicons-yes"></span>');
        
        setTimeout(function() {
            $btn.html(originalHtml);
        }, 2000);
    });
    
    // Elimina invito
    $('.wi-delete-invite').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Sei sicuro di voler eliminare questo invito? L\'azione è irreversibile!')) {
            return;
        }
        
        var inviteId = $(this).data('invite-id');
        var $row = $(this).closest('tr');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wi_delete_invite',
                nonce: '<?php echo wp_create_nonce("wi_admin_nonce"); ?>',
                invite_id: inviteId
            },
            beforeSend: function() {
                $row.css('opacity', '0.5');
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                        
                        // Ricarica se non ci sono più righe
                        if ($('.wi-invites-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert('Errore durante l\'eliminazione: ' + response.data);
                    $row.css('opacity', '1');
                }
            },
            error: function() {
                alert('Errore di connessione');
                $row.css('opacity', '1');
            }
        });
    });
});
</script>
