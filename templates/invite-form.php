<?php
/**
 * Template del form completo per creare/modificare inviti
 * Supporta modalità edit: ?edit=ID
 */

// Detect Edit Mode
$edit_mode = false;
$invite_id = 0;
$invite_data = null;

if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $invite_id = intval($_GET['edit']);
    $invite_post = get_post($invite_id);

    // Verifica esistenza e ownership
    if ($invite_post && $invite_post->post_type === 'wi_invite' && $invite_post->post_author == get_current_user_id()) {
        $edit_mode = true;

        // Carica tutti i dati dell'invito
        $invite_data = array(
            'id' => $invite_id,
            'title' => $invite_post->post_title,
            'category_id' => intval(get_post_meta($invite_id, '_wi_category_id', true)),
            'event_category' => get_post_meta($invite_id, '_wi_event_category', true), // Slug categoria per dropdown
            'template_id' => intval(get_post_meta($invite_id, '_wi_template_id', true)),
            'event_date' => get_post_meta($invite_id, '_wi_event_date', true),
            'event_time' => get_post_meta($invite_id, '_wi_event_time', true),
            'event_location' => get_post_meta($invite_id, '_wi_event_location', true),
            'event_address' => get_post_meta($invite_id, '_wi_event_address', true),
            'invite_message' => get_post_meta($invite_id, '_wi_invite_message', true),
            'final_message' => get_post_meta($invite_id, '_wi_final_message', true),
            'final_message_button_text' => get_post_meta($invite_id, '_wi_final_message_button_text', true),
            'user_image_url' => get_post_meta($invite_id, '_wi_user_image_url', true),
        );
    }
}

$templates = WI_Templates::get_all_templates();
$user_invites = WI_Invites::get_all_invites(get_current_user_id());

// DEBUG in edit mode
if ($edit_mode && defined('WP_DEBUG') && WP_DEBUG) {
    error_log('WI Form Edit Mode - Dati caricati:');
    error_log('Title: ' . ($invite_data['title'] ?? 'EMPTY'));
    error_log('Event Category: ' . ($invite_data['event_category'] ?? 'EMPTY'));
    error_log('Invite Message: ' . (isset($invite_data['invite_message']) ? substr($invite_data['invite_message'], 0, 50) : 'EMPTY'));
}
?>

<!-- Wrapper con gradiente di sfondo moderno -->
<div class="wi-form-wrapper-modern">

    <!-- Hero Header -->
    <div class="wi-form-hero">
        <div class="wi-hero-content">
            <div class="wi-hero-icon"><?php echo $edit_mode ? '✏️' : '✨'; ?></div>
            <h1 class="wi-hero-title">
                <?php echo $edit_mode ? 'Modifica il Tuo Invito' : 'Crea il Tuo Invito Personalizzato'; ?>
            </h1>
            <p class="wi-hero-subtitle">
                <?php echo $edit_mode ? 'Aggiorna tutti i dettagli del tuo invito in un unico posto' : 'Compila il form completo e scegli il template perfetto per te'; ?>
            </p>
        </div>
        <div class="wi-hero-decoration">
            <div class="wi-floating-shape wi-shape-1"></div>
            <div class="wi-floating-shape wi-shape-2"></div>
            <div class="wi-floating-shape wi-shape-3"></div>
        </div>
    </div>

    <!-- Container principale con glassmorphism -->
    <div class="wi-form-container-modern">

        <form id="wi-invite-form" class="wi-form-modern" enctype="multipart/form-data">
            <input type="hidden" id="invite_id" name="invite_id" value="<?php echo $edit_mode ? $invite_id : ''; ?>">
            <input type="hidden" id="edit_mode" name="edit_mode" value="<?php echo $edit_mode ? '1' : '0'; ?>">

            <?php if (!$edit_mode && !empty($user_invites)) : ?>
            <!-- Sezione Carica Invito Esistente (solo se NON in edit mode) -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">📂</span>
                        Carica Invito Esistente (opzionale)
                    </h3>
                </div>

                <div class="wi-form-group-modern">
                    <label for="load_invite" class="wi-label-modern">Seleziona un invito da modificare</label>
                    <select id="load_invite" name="load_invite" class="wi-input-modern">
                        <option value="">-- Crea Nuovo Invito --</option>
                        <?php foreach ($user_invites as $invite) : ?>
                            <option value="<?php echo $invite->ID; ?>">
                                <?php echo esc_html($invite->post_title); ?> -
                                <?php echo date('d/m/Y', strtotime($invite->post_date)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="wi-field-hint">Seleziona un tuo invito per caricarne i dati e modificarlo</small>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sezione 1: Informazioni Principali -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">📝</span>
                        Informazioni Principali
                    </h3>
                    <p class="wi-section-description">I dettagli fondamentali del tuo invito</p>
                </div>

                <div class="wi-form-group-modern">
                    <label for="invite_title" class="wi-label-modern">
                        <span class="wi-label-icon">✨</span>
                        Titolo Invito
                        <span class="wi-label-required">*</span>
                    </label>
                    <input type="text"
                           id="invite_title"
                           name="invite_title"
                           class="wi-input-modern wi-input-large"
                           value="<?php echo $edit_mode ? esc_attr($invite_data['title']) : ''; ?>"
                           placeholder="es: Il nostro matrimonio, Battesimo di Marco, Festa dei 50 anni"
                           required>
                    <small class="wi-field-hint">Questo sarà il titolo principale dell'invito</small>
                </div>

                <div class="wi-form-group-modern">
                    <label for="event_category" class="wi-label-modern">
                        <span class="wi-label-icon">🎉</span>
                        Tipo di Evento
                    </label>
                    <select id="event_category" name="event_category" class="wi-input-modern">
                        <option value="">-- Tutti i Template --</option>
                        <option value="matrimonio" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'matrimonio') ? 'selected' : ''; ?>>💍 Matrimonio</option>
                        <option value="battesimo" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'battesimo') ? 'selected' : ''; ?>>👶 Battesimo</option>
                        <option value="compleanno" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'compleanno') ? 'selected' : ''; ?>>🎂 Compleanno</option>
                        <option value="anniversario" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'anniversario') ? 'selected' : ''; ?>>💐 Anniversario</option>
                        <option value="festa" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'festa') ? 'selected' : ''; ?>>🎉 Festa</option>
                        <option value="comunione" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'comunione') ? 'selected' : ''; ?>>✝️ Comunione</option>
                        <option value="cresima" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'cresima') ? 'selected' : ''; ?>>🕊️ Cresima</option>
                        <option value="laurea" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'laurea') ? 'selected' : ''; ?>>🎓 Laurea</option>
                        <option value="gala" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'gala') ? 'selected' : ''; ?>>🌟 Gala</option>
                        <option value="fidanzamento" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'fidanzamento') ? 'selected' : ''; ?>>💑 Fidanzamento</option>
                        <option value="baby-shower" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'baby-shower') ? 'selected' : ''; ?>>🍼 Baby Shower</option>
                        <option value="evento-aziendale" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'evento-aziendale') ? 'selected' : ''; ?>>💼 Evento Aziendale</option>
                        <option value="altro" <?php echo ($edit_mode && isset($invite_data['event_category']) && $invite_data['event_category'] === 'altro') ? 'selected' : ''; ?>>🎊 Altro</option>
                    </select>
                    <small class="wi-field-hint">Seleziona il tipo di evento per filtrare i template adatti</small>
                </div>

                <div class="wi-form-group-modern">
                    <label for="invite_message" class="wi-label-modern">
                        <span class="wi-label-icon">💌</span>
                        Messaggio Personalizzato
                        <span class="wi-label-required">*</span>
                    </label>
                    <textarea id="invite_message"
                              name="invite_message"
                              rows="5"
                              class="wi-textarea-modern"
                              placeholder="Scrivi qui il tuo messaggio personale per gli invitati..."
                              required><?php echo ($edit_mode && isset($invite_data['invite_message'])) ? esc_textarea($invite_data['invite_message']) : ''; ?></textarea>
                    <small class="wi-field-hint">Messaggio che apparirà sull'invito</small>
                </div>
            </div>

            <!-- Sezione 2: Dettagli Evento -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">📅</span>
                        Dettagli Evento
                    </h3>
                    <p class="wi-section-description">Quando e dove si svolgerà l'evento</p>
                </div>

                <div class="wi-form-row-modern">
                    <div class="wi-form-group-modern wi-col-6">
                        <label for="event_date" class="wi-label-modern">
                            <span class="wi-label-icon">📅</span>
                            Data Evento
                            <span class="wi-label-required">*</span>
                        </label>
                        <input type="date"
                               id="event_date"
                               name="event_date"
                               class="wi-input-modern"
                               value="<?php echo $edit_mode ? esc_attr($invite_data['event_date']) : ''; ?>"
                               required>
                    </div>

                    <div class="wi-form-group-modern wi-col-6">
                        <label for="event_time" class="wi-label-modern">
                            <span class="wi-label-icon">⏰</span>
                            Ora Evento
                            <span class="wi-label-required">*</span>
                        </label>
                        <input type="time"
                               id="event_time"
                               name="event_time"
                               class="wi-input-modern"
                               value="<?php echo $edit_mode ? esc_attr($invite_data['event_time']) : ''; ?>"
                               required>
                    </div>
                </div>

                <div class="wi-form-group-modern">
                    <label for="event_location" class="wi-label-modern">
                        <span class="wi-label-icon">📍</span>
                        Nome Luogo
                        <span class="wi-label-required">*</span>
                    </label>
                    <input type="text"
                           id="event_location"
                           name="event_location"
                           class="wi-input-modern"
                           value="<?php echo $edit_mode ? esc_attr($invite_data['event_location']) : ''; ?>"
                           placeholder="es: Chiesa di San Marco, Villa Bellini, Ristorante Da Mario"
                           required>
                </div>

                <div class="wi-form-group-modern">
                    <label for="event_address" class="wi-label-modern">
                        <span class="wi-label-icon">🗺️</span>
                        Indirizzo Completo
                        <span class="wi-label-required">*</span>
                    </label>
                    <input type="text"
                           id="event_address"
                           name="event_address"
                           class="wi-input-modern"
                           value="<?php echo $edit_mode ? esc_attr($invite_data['event_address']) : ''; ?>"
                           placeholder="Via Roma 123, 20100 Milano MI"
                           required>
                    <small class="wi-field-hint">Verrà utilizzato per la mappa</small>
                </div>
            </div>

            <!-- Sezione 3: Immagine -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">🖼️</span>
                        Immagine Invito (Opzionale)
                    </h3>
                    <p class="wi-section-description">Carica una foto per personalizzare ulteriormente il tuo invito</p>
                </div>

                <div class="wi-form-group-modern">
                    <label for="user_image" class="wi-label-modern">Carica la tua Immagine</label>
                    <input type="file"
                           id="user_image"
                           name="user_image"
                           class="wi-input-file-modern"
                           accept="image/*">
                    <div id="user_image_preview" class="wi-image-preview-modern">
                        <?php if ($edit_mode && !empty($invite_data['user_image_url'])) : ?>
                            <img src="<?php echo esc_url($invite_data['user_image_url']); ?>" alt="Immagine invito">
                            <button type="button" class="wi-remove-image" title="Rimuovi immagine">✕</button>
                        <?php endif; ?>
                    </div>
                    <small class="wi-field-hint">Formato: JPG/PNG, max 2MB. Se non carichi un'immagine, verrà usato un placeholder elegante.</small>
                </div>
            </div>

            <!-- Sezione 4: Messaggio Finale -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">🎁</span>
                        Messaggio Finale (Opzionale)
                    </h3>
                    <p class="wi-section-description">Un messaggio nascosto che apparirà dopo aver cliccato sul pulsante</p>
                </div>

                <div class="wi-form-group-modern">
                    <label for="final_message_button_text" class="wi-label-modern">
                        <span class="wi-label-icon">🔘</span>
                        Testo Pulsante Messaggio
                    </label>
                    <input type="text"
                           id="final_message_button_text"
                           name="final_message_button_text"
                           class="wi-input-modern"
                           value="<?php echo $edit_mode ? esc_attr($invite_data['final_message_button_text']) : 'Leggi il messaggio'; ?>"
                           placeholder="Es: Leggi il messaggio, Un messaggio per te, Clicca qui">
                    <small class="wi-field-hint">Il testo che apparirà sul pulsante per mostrare il messaggio finale</small>
                </div>

                <div class="wi-form-group-modern">
                    <label for="final_message" class="wi-label-modern">
                        <span class="wi-label-icon">💝</span>
                        Aggiungi un messaggio finale
                    </label>
                    <textarea id="final_message"
                              name="final_message"
                              rows="4"
                              class="wi-textarea-modern"
                              placeholder="Es: Speriamo di vederti presto! Non mancare! ✨"><?php echo $edit_mode ? esc_textarea($invite_data['final_message']) : ''; ?></textarea>
                    <small class="wi-field-hint">Questo messaggio apparirà quando si clicca sul pulsante sopra</small>
                </div>
            </div>

            <!-- Sezione 5: Scegli Template -->
            <div class="wi-form-section-modern">
                <div class="wi-section-header">
                    <h3 class="wi-section-title">
                        <span class="wi-section-icon">🎨</span>
                        Scegli il Template
                    </h3>
                    <p class="wi-section-description">Seleziona il design che preferisci per il tuo invito</p>
                </div>

                <div class="wi-templates-grid-modern">
                    <?php foreach ($templates as $template) : ?>
                    <div class="wi-template-card-modern" data-categories="<?php echo esc_attr($template->category ?? 'generale'); ?>">
                        <input type="radio"
                               name="selected_template"
                               id="template_<?php echo $template->id; ?>"
                               value="<?php echo $template->id; ?>"
                               <?php echo ($edit_mode && $invite_data['template_id'] == $template->id) ? 'checked' : ''; ?>>
                        <label for="template_<?php echo $template->id; ?>">
                            <div class="wi-template-preview-modern">
                                <?php if ($template->preview_image) : ?>
                                    <img src="<?php echo esc_url($template->preview_image); ?>"
                                         alt="<?php echo esc_attr($template->name); ?>">
                                <?php else : ?>
                                    <div class="wi-template-placeholder-modern">
                                        <span>🎨</span>
                                    </div>
                                <?php endif; ?>
                                <div class="wi-template-selected-badge">✓</div>
                            </div>
                            <div class="wi-template-info-modern">
                                <h4><?php echo esc_html($template->name); ?></h4>
                                <p><?php echo esc_html($template->description); ?></p>
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="wi-form-actions-modern">
                <button type="button" id="wi-preview-btn" class="wi-btn-modern wi-btn-secondary-modern">
                    <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Anteprima</span>
                </button>
                <button type="button" id="wi-reset-btn" class="wi-btn-modern wi-btn-outline-modern">
                    <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                    <span>Reset</span>
                </button>
                <button type="button" id="wi-publish-btn" class="wi-btn-modern wi-btn-primary-modern">
                    <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?php echo $edit_mode ? 'Aggiorna Invito' : 'Pubblica Invito'; ?></span>
                </button>
            </div>
        </form>

    </div>

    <!-- Modal Anteprima -->
    <div id="wi-preview-container" class="wi-preview-container-modern" style="display: none;">
        <div class="wi-preview-overlay"></div>
        <div class="wi-preview-modal">
            <div class="wi-preview-header-modern">
                <h3>👁️ Anteprima Invito</h3>
                <button type="button" id="wi-close-preview" class="wi-btn-close-modern">✕</button>
            </div>
            <div id="wi-preview-content" class="wi-preview-content-modern"></div>
            <div class="wi-preview-actions-modern">
                <button type="button" id="wi-edit-btn" class="wi-btn-modern wi-btn-secondary-modern">
                    ✏️ Modifica
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="wi-loading" class="wi-loading-modern" style="display: none;">
        <div class="wi-loading-content">
            <div class="wi-spinner-modern"></div>
            <p class="wi-loading-text">Caricamento in corso...</p>
        </div>
    </div>

    <!-- Decorazioni di sfondo -->
    <div class="wi-bg-decoration">
        <div class="wi-blob wi-blob-1"></div>
        <div class="wi-blob wi-blob-2"></div>
        <div class="wi-blob wi-blob-3"></div>
    </div>
</div>

<style>
/* ===================================
   MODERN FORM STYLES - 2.5.0
   Glassmorphism, modern gradients, animations
   =================================== */

/* Wrapper principale con gradiente animato */
.wi-form-wrapper-modern {
    position: relative;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    background-size: 400% 400%;
    animation: gradientShift 15s ease infinite;
    padding: 40px 20px;
    overflow: hidden;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* Hero Section */
.wi-form-hero {
    text-align: center;
    color: white;
    margin-bottom: 60px;
    position: relative;
    z-index: 2;
}

.wi-hero-icon {
    font-size: 80px;
    margin-bottom: 20px;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.2));
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
}

.wi-hero-title {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 15px;
    text-shadow: 0 4px 12px rgba(0,0,0,0.2);
    letter-spacing: -1px;
}

.wi-hero-subtitle {
    font-size: 1.3rem;
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
    text-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Hero Decorations */
.wi-hero-decoration {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.wi-floating-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    animation: floatingShapes 20s infinite ease-in-out;
}

.wi-shape-1 {
    width: 150px;
    height: 150px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.wi-shape-2 {
    width: 100px;
    height: 100px;
    top: 60%;
    right: 15%;
    animation-delay: 7s;
}

.wi-shape-3 {
    width: 80px;
    height: 80px;
    bottom: 20%;
    left: 20%;
    animation-delay: 14s;
}

@keyframes floatingShapes {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    25% { transform: translate(20px, -20px) rotate(90deg); }
    50% { transform: translate(-15px, 15px) rotate(180deg); }
    75% { transform: translate(15px, 20px) rotate(270deg); }
}

/* Container con Glassmorphism */
.wi-form-container-modern {
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.wi-form-modern {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 24px;
    padding: 50px;
    box-shadow: 0 20px 80px rgba(0, 0, 0, 0.25),
                0 0 100px rgba(255, 255, 255, 0.1) inset;
}

/* Sezioni Form */
.wi-form-section-modern {
    margin-bottom: 50px;
    padding-bottom: 40px;
    border-bottom: 2px solid rgba(102, 126, 234, 0.1);
}

.wi-form-section-modern:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
}

.wi-section-header {
    margin-bottom: 30px;
}

.wi-section-title {
    font-size: 1.8rem;
    color: #2c3e50;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.wi-section-icon {
    font-size: 2rem;
}

.wi-section-description {
    color: #64748b;
    font-size: 1rem;
    margin: 0;
    padding-left: 48px;
}

/* Form Groups */
.wi-form-group-modern {
    margin-bottom: 30px;
}

.wi-label-modern {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    margin-bottom: 10px;
    color: #334155;
    font-size: 1rem;
}

.wi-label-icon {
    font-size: 1.2rem;
}

.wi-label-required {
    color: #ef4444;
    font-weight: 700;
}

.wi-label-optional {
    color: #94a3b8;
    font-weight: 400;
    font-size: 0.9rem;
}

/* Inputs Moderni */
.wi-input-modern,
.wi-textarea-modern {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: #1e293b;
}

.wi-input-modern:focus,
.wi-textarea-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1),
                0 4px 12px rgba(102, 126, 234, 0.15);
    transform: translateY(-1px);
}

.wi-input-large {
    font-size: 1.2rem;
    font-weight: 600;
    padding: 18px 22px;
}

.wi-textarea-modern {
    resize: vertical;
    min-height: 120px;
}

.wi-field-hint {
    display: block;
    margin-top: 8px;
    color: #94a3b8;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Form Row (2 colonne) */
.wi-form-row-modern {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.wi-col-6 {
    flex: 1;
}

/* File Input */
.wi-input-file-modern {
    width: 100%;
    padding: 12px;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.wi-input-file-modern:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.03);
}

/* Image Preview */
.wi-image-preview-modern {
    margin-top: 20px;
    position: relative;
}

.wi-image-preview-modern img {
    max-width: 400px;
    width: 100%;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    border: 3px solid white;
}

.wi-remove-image {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wi-remove-image:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Templates Grid Modern */
.wi-templates-grid-modern {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.wi-template-card-modern {
    position: relative;
}

.wi-template-card-modern input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.wi-template-card-modern label {
    display: block;
    cursor: pointer;
    border: 3px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
}

.wi-template-card-modern label:hover {
    border-color: #667eea;
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.2);
}

.wi-template-card-modern input:checked + label {
    border-color: #10b981;
    box-shadow: 0 12px 40px rgba(16, 185, 129, 0.3);
    transform: translateY(-6px);
}

.wi-template-preview-modern {
    height: 200px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.wi-template-preview-modern img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wi-template-placeholder-modern {
    font-size: 4rem;
    color: white;
    opacity: 0.8;
}

.wi-template-selected-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #10b981;
    color: white;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.wi-template-card-modern input:checked + label .wi-template-selected-badge {
    opacity: 1;
    transform: scale(1);
}

.wi-template-info-modern {
    padding: 24px;
}

.wi-template-info-modern h4 {
    margin: 0 0 10px 0;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 700;
}

.wi-template-info-modern p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
}

/* Hidden template cards */
.wi-template-card-modern.wi-hidden {
    display: none;
}

/* Actions Footer */
.wi-form-actions-modern {
    display: flex;
    gap: 20px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin-top: 50px;
    padding-top: 40px;
    border-top: 2px solid rgba(102, 126, 234, 0.1);
}

/* Buttons Modern */
.wi-btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: inherit;
}

.wi-btn-icon {
    width: 20px;
    height: 20px;
    stroke-width: 2.5px;
}

.wi-btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 14px rgba(102, 126, 234, 0.4);
}

.wi-btn-primary-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
}

.wi-btn-secondary-modern {
    background: #3b82f6;
    color: white;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
}

.wi-btn-secondary-modern:hover {
    background: #2563eb;
    transform: translateY(-3px);
}

.wi-btn-outline-modern {
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
    box-shadow: none;
}

.wi-btn-outline-modern:hover {
    border-color: #667eea;
    color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

/* Preview Modal Modern */
.wi-preview-container-modern {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.wi-preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
}

.wi-preview-modal {
    position: relative;
    max-width: 1200px;
    width: 100%;
    max-height: 90vh;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 80px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
}

.wi-preview-header-modern {
    padding: 24px 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wi-preview-header-modern h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.wi-btn-close-modern {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wi-btn-close-modern:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.wi-preview-content-modern {
    flex: 1;
    overflow-y: auto;
    padding: 40px;
    background: #f8fafc;
}

.wi-preview-actions-modern {
    padding: 24px 30px;
    background: white;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 15px;
    justify-content: center;
}

/* Loading Modern */
.wi-loading-modern {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(10px);
    z-index: 9999999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.wi-loading-content {
    text-align: center;
}

.wi-spinner-modern {
    width: 60px;
    height: 60px;
    border: 5px solid #e2e8f0;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.wi-loading-text {
    font-size: 1.2rem;
    color: #64748b;
    font-weight: 600;
}

/* Background Decorations (Blobs) */
.wi-bg-decoration {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}

.wi-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    animation: blobAnimation 25s infinite ease-in-out;
}

.wi-blob-1 {
    width: 500px;
    height: 500px;
    background: rgba(102, 126, 234, 0.4);
    top: -200px;
    left: -200px;
    animation-delay: 0s;
}

.wi-blob-2 {
    width: 400px;
    height: 400px;
    background: rgba(118, 75, 162, 0.4);
    top: 40%;
    right: -150px;
    animation-delay: 8s;
}

.wi-blob-3 {
    width: 450px;
    height: 450px;
    background: rgba(240, 147, 251, 0.4);
    bottom: -180px;
    left: 30%;
    animation-delay: 16s;
}

@keyframes blobAnimation {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(50px, -50px) scale(1.1); }
    50% { transform: translate(-40px, 40px) scale(0.9); }
    75% { transform: translate(40px, 50px) scale(1.05); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .wi-hero-title {
        font-size: 2rem;
    }

    .wi-hero-subtitle {
        font-size: 1.1rem;
    }

    .wi-form-modern {
        padding: 30px 20px;
    }

    .wi-section-title {
        font-size: 1.4rem;
    }

    .wi-form-row-modern {
        grid-template-columns: 1fr;
    }

    .wi-templates-grid-modern {
        grid-template-columns: 1fr;
    }

    .wi-form-actions-modern {
        flex-direction: column;
        gap: 15px;
    }

    .wi-btn-modern {
        width: 100%;
        justify-content: center;
    }

    .wi-image-preview-modern img {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .wi-form-wrapper-modern {
        padding: 20px 10px;
    }

    .wi-hero-icon {
        font-size: 60px;
    }

    .wi-hero-title {
        font-size: 1.6rem;
    }

    .wi-form-modern {
        padding: 20px 15px;
        border-radius: 16px;
    }

    .wi-section-description {
        padding-left: 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';

    <?php if ($edit_mode) : ?>
    // DEBUG: Log dati caricati in edit mode
    console.log('Edit Mode Active - Dati caricati:', <?php echo json_encode($invite_data); ?>);
    <?php endif; ?>

    // Filtro template per categoria evento
    function filterTemplatesByCategory() {
        var selectedCategory = $('#event_category').val();
        var $templateCards = $('.wi-template-card-modern');

        if (!selectedCategory) {
            $templateCards.removeClass('wi-hidden');
            return;
        }

        $templateCards.each(function() {
            var $card = $(this);
            var categories = $card.data('categories') || '';
            var categoryArray = categories.toString().split(',').map(function(cat) {
                return cat.trim();
            });

            if (categoryArray.indexOf(selectedCategory) !== -1 || selectedCategory === 'altro') {
                $card.removeClass('wi-hidden');
            } else {
                $card.addClass('wi-hidden');
            }
        });

        if (selectedCategory === 'altro') {
            $templateCards.removeClass('wi-hidden');
        }
    }

    // Bind filtro al change
    $('#event_category').on('change', filterTemplatesByCategory);

    // Triggera filtro al caricamento (se c'è una categoria già selezionata in edit mode)
    <?php if ($edit_mode && !empty($invite_data['event_category'])) : ?>
    setTimeout(function() {
        console.log('Trigger filtro template per categoria: <?php echo $invite_data["event_category"]; ?>');
        filterTemplatesByCategory();
    }, 100);
    <?php endif; ?>

    // Load invite esistente (solo se NON in edit mode)
    $('#load_invite').on('change', function() {
        var inviteId = $(this).val();

        if (!inviteId) {
            // Reset form
            $('#wi-invite-form')[0].reset();
            $('#user_image_preview').empty();
            return;
        }

        // Redirect alla pagina con parametro edit
        var currentUrl = window.location.href.split('?')[0];
        window.location.href = currentUrl + '?edit=' + inviteId;
    });

    // Rimuovi immagine
    $(document).on('click', '.wi-remove-image', function() {
        $('#user_image_preview').empty();
        $('#user_image').val('');
    });

    // Image preview on file select
    $('#user_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#user_image_preview').html(
                    '<img src="' + e.target.result + '" alt="Preview">' +
                    '<button type="button" class="wi-remove-image" title="Rimuovi immagine">✕</button>'
                );
            };
            reader.readAsDataURL(file);
        }
    });

    // Preview button
    $('#wi-preview-btn').on('click', function() {
        var $form = $('#wi-invite-form');

        // Validazione base campi obbligatori
        var title = $('#invite_title').val().trim();
        var message = $('#invite_message').val().trim();
        var date = $('#event_date').val();
        var time = $('#event_time').val();
        var location = $('#event_location').val().trim();
        var address = $('#event_address').val().trim();
        var template = $('input[name="selected_template"]:checked').val();

        if (!title || !message || !date || !time || !location || !address || !template) {
            alert('⚠️ Compila tutti i campi obbligatori prima di visualizzare l\'anteprima:\n\n' +
                  '- Titolo Invito\n' +
                  '- Messaggio Personalizzato\n' +
                  '- Data e Ora Evento\n' +
                  '- Location e Indirizzo\n' +
                  '- Template (seleziona un design)');
            return;
        }

        $('#wi-loading').show();

        // Prepara dati per anteprima
        var previewData = {
            action: 'wi_preview_invite',
            nonce: '<?php echo wp_create_nonce(WI_NONCE_PUBLIC); ?>',
            invite_title: title,
            invite_message: message,
            event_date: date,
            event_time: time,
            event_location: location,
            event_address: address,
            template_id: template,
            final_message: $('#final_message').val().trim(),
            final_message_button_text: $('#final_message_button_text').val().trim()
        };

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: previewData,
            success: function(response) {
                $('#wi-loading').hide();

                if (response.success) {
                    // Mostra l'anteprima nel modal
                    $('#wi-preview-content').html(response.data.html);
                    $('#wi-preview-container').fadeIn(300);
                } else {
                    alert('❌ Errore durante la generazione dell\'anteprima:\n' + (response.data || 'Errore sconosciuto'));
                }
            },
            error: function(xhr, status, error) {
                $('#wi-loading').hide();
                console.error('Preview error:', error);
                alert('❌ Errore di connessione durante la generazione dell\'anteprima. Riprova.');
            }
        });
    });

    // Reset button
    $('#wi-reset-btn').on('click', function() {
        if (confirm('Sei sicuro di voler resettare il form? Tutti i dati non salvati verranno persi.')) {
            $('#wi-invite-form')[0].reset();
            $('#user_image_preview').empty();
            $('.wi-template-card-modern').removeClass('wi-hidden');
        }
    });

    // Publish button
    $('#wi-publish-btn').on('click', function() {
        var $form = $('#wi-invite-form');

        // Validazione base
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        // TODO: Implement save/publish logic via AJAX
        var editMode = $('#edit_mode').val() === '1';
        var message = editMode ?
            'Confermi l\'aggiornamento dell\'invito?' :
            'Confermi la pubblicazione del nuovo invito?';

        if (confirm(message)) {
            $('#wi-loading').show();
            // TODO: Submit form via AJAX
            alert('Funzionalità salvataggio in sviluppo');
            $('#wi-loading').hide();
        }
    });

    // Close preview
    $('#wi-close-preview, .wi-preview-overlay').on('click', function() {
        $('#wi-preview-container').fadeOut(300);
    });

    $('#wi-edit-btn').on('click', function() {
        $('#wi-preview-container').fadeOut(300);
    });

    // Previeni chiusura modal quando si clicca sul contenuto
    $('.wi-preview-modal').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>
