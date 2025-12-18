<?php
/**
 * Gestione Categorie Eventi
 * Backend CRUD per le categorie
 */

if (!defined('ABSPATH')) exit;

if (!current_user_can('manage_options')) {
    wp_die(__('Non hai i permessi per accedere a questa pagina.'));
}

global $wpdb;
$table = $wpdb->prefix . 'wi_event_categories';

// Gestione azioni
$message = '';
$message_type = '';

// Aggiungi/Modifica categoria
if (isset($_POST['save_category']) && check_admin_referer('wi_save_category')) {
    $category_id = intval($_POST['category_id'] ?? 0);

    $data = array(
        'name' => sanitize_text_field($_POST['category_name']),
        'slug' => sanitize_title($_POST['category_slug']),
        'icon' => sanitize_text_field($_POST['category_icon']),
        'description' => sanitize_textarea_field($_POST['category_description'] ?? ''),
        'is_active' => isset($_POST['category_active']) ? 1 : 0,
        'sort_order' => intval($_POST['category_order'] ?? 0)
    );

    if ($category_id > 0) {
        // Update
        $wpdb->update($table, $data, array('id' => $category_id));
        $message = 'Categoria aggiornata con successo!';
    } else {
        // Insert
        $wpdb->insert($table, $data);
        $message = 'Categoria creata con successo!';
    }
    $message_type = 'success';
}

// Elimina categoria
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Verifica nonce
    if (check_admin_referer('wi_delete_category_' . $id)) {
        // Elimina relazioni
        $relations_table = $wpdb->prefix . 'wi_template_categories';
        $wpdb->delete($relations_table, array('category_id' => $id));

        // Elimina categoria
        $wpdb->delete($table, array('id' => $id));

        $message = 'Categoria eliminata con successo!';
        $message_type = 'success';
    }
}

// Carica categoria per modifica
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['id'])));
}

// Verifica se tabella esiste
$table_exists = ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== null);

// Se tabella non esiste, mostra alert migrazione
if (!$table_exists) {
    ?>
    <div class="wrap">
        <h1>📂 Gestione Categorie Eventi</h1>
        <div class="notice notice-error" style="padding: 20px; margin-top: 20px;">
            <h2 style="margin-top: 0;">⚠️ Migrazione Database Necessaria</h2>
            <p style="font-size: 16px;">
                La tabella delle categorie non è stata ancora creata. Per utilizzare il sistema categorie avanzato,
                è necessario eseguire la <strong>migrazione database v2.2.0</strong>.
            </p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=wedding-invites&run_migration=v220'); ?>"
                   class="button button-primary button-hero"
                   style="margin-top: 15px;"
                   onclick="return confirm('Eseguire la migrazione database v2.2.0? Questa operazione è sicura e non elimina dati esistenti.');">
                    🚀 Esegui Migrazione Database v2.2.0
                </a>
            </p>
            <details style="margin-top: 20px;">
                <summary style="cursor: pointer; font-weight: bold;">Cosa fa la migrazione?</summary>
                <ul style="margin-top: 10px;">
                    <li>✅ Crea tabella <code>wp_wi_event_categories</code> (12 categorie predefinite)</li>
                    <li>✅ Crea tabella <code>wp_wi_template_categories</code> (relazioni template-categoria)</li>
                    <li>✅ Aggiunge colonne CSS per personalizzazione titoli sezioni</li>
                    <li>✅ Migra categorie esistenti da CSV a tabelle database</li>
                    <li>⚠️ NON elimina dati esistenti (operazione sicura)</li>
                </ul>
            </details>
        </div>
    </div>
    <?php
    return;
}

// Carica tutte le categorie
$categories = $wpdb->get_results("SELECT * FROM $table ORDER BY sort_order ASC, name ASC");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $edit_category ? '✏️ Modifica Categoria' : '📂 Gestione Categorie Eventi'; ?>
    </h1>

    <?php if (!$edit_category) : ?>
    <a href="<?php echo admin_url('admin.php?page=wedding-invites-categories&action=add'); ?>" class="page-title-action">Aggiungi Categoria</a>
    <?php endif; ?>

    <hr class="wp-heading-inline">

    <?php if ($message) : ?>
    <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')) : ?>
        <!-- FORM AGGIUNGI/MODIFICA CATEGORIA -->
        <div class="wi-category-form" style="max-width: 800px; margin-top: 20px;">
            <form method="post" action="">
                <?php wp_nonce_field('wi_save_category'); ?>
                <input type="hidden" name="category_id" value="<?php echo $edit_category ? $edit_category->id : 0; ?>">

                <table class="form-table">
                    <tr>
                        <th><label for="category_name">Nome Categoria *</label></th>
                        <td>
                            <input type="text" name="category_name" id="category_name" class="regular-text"
                                   value="<?php echo $edit_category ? esc_attr($edit_category->name) : ''; ?>" required>
                            <p class="description">Es: Matrimonio, Compleanno, Laurea</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="category_slug">Slug *</label></th>
                        <td>
                            <input type="text" name="category_slug" id="category_slug" class="regular-text"
                                   value="<?php echo $edit_category ? esc_attr($edit_category->slug) : ''; ?>" required>
                            <p class="description">URL-friendly (es: matrimonio, compleanno, laurea)</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="category_icon">Icona Emoji</label></th>
                        <td>
                            <input type="text" name="category_icon" id="category_icon" class="small-text"
                                   value="<?php echo $edit_category ? esc_attr($edit_category->icon) : '🎉'; ?>" maxlength="10">
                            <p class="description">Emoji singola (es: 💍 💐 🎂 🎓)</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="category_description">Descrizione</label></th>
                        <td>
                            <textarea name="category_description" id="category_description" class="large-text" rows="3"><?php echo $edit_category ? esc_textarea($edit_category->description) : ''; ?></textarea>
                            <p class="description">Descrizione opzionale della categoria</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="category_order">Ordine di Visualizzazione</label></th>
                        <td>
                            <input type="number" name="category_order" id="category_order" class="small-text"
                                   value="<?php echo $edit_category ? esc_attr($edit_category->sort_order) : 0; ?>" min="0">
                            <p class="description">Numero più basso = appare prima</p>
                        </td>
                    </tr>

                    <tr>
                        <th>Stato</th>
                        <td>
                            <label>
                                <input type="checkbox" name="category_active" value="1"
                                       <?php checked($edit_category ? $edit_category->is_active : 1, 1); ?>>
                                Categoria attiva (visibile nel frontend)
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" name="save_category" class="button button-primary">
                        <?php echo $edit_category ? '💾 Salva Modifiche' : '➕ Crea Categoria'; ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=wedding-invites-categories'); ?>" class="button">Annulla</a>
                </p>
            </form>
        </div>

    <?php else : ?>
        <!-- LISTA CATEGORIE -->
        <div class="wi-categories-list" style="margin-top: 20px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">Ordine</th>
                        <th style="width: 60px;">Icona</th>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th style="width: 100px;">Template</th>
                        <th style="width: 80px;">Stato</th>
                        <th style="width: 150px;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)) : ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <p style="font-size: 16px; color: #666;">Nessuna categoria trovata.</p>
                            <a href="<?php echo admin_url('admin.php?page=wedding-invites-categories&action=add'); ?>" class="button button-primary">Aggiungi Prima Categoria</a>
                        </td>
                    </tr>
                    <?php else : ?>
                        <?php foreach ($categories as $category) :
                            // Conta template associati
                            $relations_table = $wpdb->prefix . 'wi_template_categories';
                            $template_count = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $relations_table WHERE category_id = %d",
                                $category->id
                            ));
                        ?>
                        <tr>
                            <td><?php echo esc_html($category->sort_order); ?></td>
                            <td style="font-size: 24px; text-align: center;"><?php echo esc_html($category->icon); ?></td>
                            <td><strong><?php echo esc_html($category->name); ?></strong></td>
                            <td><code><?php echo esc_html($category->slug); ?></code></td>
                            <td style="text-align: center;"><?php echo intval($template_count); ?></td>
                            <td>
                                <?php if ($category->is_active) : ?>
                                    <span style="color: #46b450;">● Attiva</span>
                                <?php else : ?>
                                    <span style="color: #dc3232;">● Inattiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wedding-invites-categories&action=edit&id=' . $category->id); ?>" class="button button-small">Modifica</a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wedding-invites-categories&action=delete&id=' . $category->id), 'wi_delete_category_' . $category->id); ?>"
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('Eliminare questa categoria? Le relazioni con i template verranno rimosse.');">Elimina</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p style="margin-top: 20px; padding: 15px; background: #f0f6fc; border-left: 4px solid #2271b1; border-radius: 4px;">
                <strong>💡 Suggerimento:</strong> Le categorie qui definite possono essere assegnate ai template dalla pagina
                <a href="<?php echo admin_url('admin.php?page=wedding-invites-templates'); ?>">Gestione Template</a>.
                Gli utenti potranno poi filtrare i template in base al tipo di evento.
            </p>
        </div>
    <?php endif; ?>
</div>

<style>
.wi-category-form .form-table th {
    padding: 15px 10px 15px 0;
    width: 200px;
}
.wi-category-form .form-table td {
    padding: 15px 10px;
}
</style>
