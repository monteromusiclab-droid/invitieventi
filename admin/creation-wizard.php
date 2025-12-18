<?php
/**
 * Pagina Wizard Creazione Guidata - Modern Design
 * Può essere usata in backend (admin) o frontend (shortcode)
 * Supporta editing inviti esistenti: ?edit=ID
 */

// Check permessi solo se siamo in admin (non shortcode frontend)
if (is_admin() && !current_user_can('manage_options')) {
    wp_die('Non hai i permessi per accedere a questa pagina');
}

// Detect Edit Mode - REDIRECT AL FORM COMPLETO
$edit_mode = false;
$invite_id = 0;

if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $invite_id = intval($_GET['edit']);
    $invite_post = get_post($invite_id);

    // Verifica esistenza e permessi
    if ($invite_post && $invite_post->post_type === 'wi_invite') {
        // Se frontend, verifica ownership
        if (!is_admin()) {
            if (is_user_logged_in() && $invite_post->post_author == get_current_user_id()) {
                $edit_mode = true;
            }
        } else {
            // Admin può modificare tutto
            $edit_mode = true;
        }

        if ($edit_mode) {
            // REDIRECT al form completo con parametro edit
            // Trova la pagina con shortcode [wedding_invites_form]
            $pages = get_posts(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                's' => '[wedding_invites_form]',
                'posts_per_page' => 1
            ));

            if (!empty($pages)) {
                $form_url = get_permalink($pages[0]->ID) . '?edit=' . $invite_id;
                wp_redirect($form_url);
                exit;
            } else {
                // Fallback: se non esiste pagina con shortcode, mostra nel wizard stesso
                // ma con avviso per l'utente
                echo '<div class="notice notice-warning" style="margin: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <p><strong>⚠️ Avviso:</strong> Per modificare inviti esistenti è consigliato utilizzare il form completo.
                    Crea una pagina con lo shortcode <code>[wedding_invites_form]</code> per abilitare la modifica diretta.</p>
                </div>';
            }
        }
    }
}

// I template verranno caricati dinamicamente via AJAX in base alla categoria selezionata
?>

<!-- Wrapper con gradiente di sfondo -->
<div class="wi-wizard-wrapper" data-edit-mode="<?php echo $edit_mode ? 'true' : 'false'; ?>" data-invite-id="<?php echo $invite_id; ?>">

    <!-- Hero Header -->
    <div class="wi-wizard-hero">
        <div class="wi-hero-content">
            <div class="wi-hero-icon"><?php echo $edit_mode ? '✏️' : '✨'; ?></div>
            <h1 class="wi-hero-title"><?php echo $edit_mode ? 'Modifica il Tuo Invito' : 'Crea il Tuo Invito Perfetto'; ?></h1>
            <p class="wi-hero-subtitle"><?php echo $edit_mode ? 'Aggiorna i dettagli del tuo invito' : 'Ti guideremo passo dopo passo nella creazione di un invito indimenticabile'; ?></p>
        </div>
        <div class="wi-hero-decoration">
            <div class="wi-floating-shape wi-shape-1"></div>
            <div class="wi-floating-shape wi-shape-2"></div>
            <div class="wi-floating-shape wi-shape-3"></div>
        </div>
    </div>

    <!-- Container principale con glassmorphism -->
    <div class="wi-wizard-container-modern">

        <!-- PROGRESS BAR MODERNA -->
        <div class="wi-wizard-progress-modern">
            <div class="wi-progress-track">
                <div class="wi-progress-fill" style="width: 0%"></div>
            </div>
            <div class="wi-progress-steps-modern">
                <div class="wi-step-item active" data-step="1">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">1</span>
                        <span class="wi-step-icon">🎉</span>
                        <div class="wi-step-pulse"></div>
                    </div>
                    <div class="wi-step-label">Tipo Evento</div>
                </div>

                <div class="wi-step-connector"></div>

                <div class="wi-step-item" data-step="2">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">2</span>
                        <span class="wi-step-icon">🎨</span>
                    </div>
                    <div class="wi-step-label">Design</div>
                </div>

                <div class="wi-step-connector"></div>

                <div class="wi-step-item" data-step="3">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">3</span>
                        <span class="wi-step-icon">📅</span>
                    </div>
                    <div class="wi-step-label">Dettagli</div>
                </div>

                <div class="wi-step-connector"></div>

                <div class="wi-step-item" data-step="4">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">4</span>
                        <span class="wi-step-icon">✍️</span>
                    </div>
                    <div class="wi-step-label">Messaggio</div>
                </div>

                <div class="wi-step-connector"></div>

                <div class="wi-step-item" data-step="5">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">5</span>
                        <span class="wi-step-icon">📮</span>
                    </div>
                    <div class="wi-step-label">RSVP</div>
                </div>

                <div class="wi-step-connector"></div>

                <div class="wi-step-item" data-step="6">
                    <div class="wi-step-circle">
                        <span class="wi-step-number">6</span>
                        <span class="wi-step-icon">👁️</span>
                    </div>
                    <div class="wi-step-label">Anteprima</div>
                </div>
            </div>
        </div>

        <!-- WIZARD BODY -->
        <div class="wi-wizard-body-modern">

            <!-- STEP 0: SELEZIONE MODALITÀ (solo se non in edit mode) -->
            <?php if (!$edit_mode) : ?>
            <div class="wi-wizard-step-modern active" data-step="0" id="wi-mode-selection-step">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Cosa vuoi fare?</h2>
                    <p class="wi-step-description-modern">Crea un nuovo invito o modifica uno esistente</p>
                </div>

                <div class="wi-mode-selection-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px;">
                    <!-- Opzione 1: Crea Nuovo -->
                    <div class="wi-mode-card" data-mode="create" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);">
                        <div class="wi-mode-icon" style="font-size: 80px; margin-bottom: 20px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">✨</div>
                        <h3 style="color: white; font-size: 28px; margin-bottom: 15px; font-weight: 700;">Crea Nuovo Invito</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 16px; line-height: 1.6; margin-bottom: 25px;">Inizia da zero e personalizza ogni dettaglio del tuo invito perfetto</p>
                        <div class="wi-mode-badge" style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; backdrop-filter: blur(10px);">Consigliato</div>
                        <div class="wi-mode-glow" style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>
                    </div>

                    <!-- Opzione 2: Modifica Esistente -->
                    <div class="wi-mode-card" data-mode="edit" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); border-radius: 20px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden; box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3);">
                        <div class="wi-mode-icon" style="font-size: 80px; margin-bottom: 20px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">✏️</div>
                        <h3 style="color: white; font-size: 28px; margin-bottom: 15px; font-weight: 700;">Modifica Esistente</h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 16px; line-height: 1.6; margin-bottom: 25px;">Aggiorna un invito che hai già creato con nuovi dettagli</p>
                        <div class="wi-mode-badge" style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; backdrop-filter: blur(10px);">
                            <span id="wi-user-invites-count">Caricamento...</span>
                        </div>
                        <div class="wi-mode-glow" style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;"></div>
                    </div>
                </div>

                <!-- Lista Inviti Esistenti (nascosta inizialmente) -->
                <div id="wi-existing-invites-list" style="display: none; margin-top: 40px;">
                    <div class="wi-step-header" style="margin-bottom: 30px;">
                        <h3 class="wi-step-title-modern" style="font-size: 24px;">Seleziona l'Invito da Modificare</h3>
                        <p class="wi-step-description-modern">Clicca su un invito per caricarlo nel wizard</p>
                    </div>

                    <div id="wi-invites-grid" class="wi-invites-selection-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <!-- Caricato via AJAX -->
                        <div class="wi-loading-invites" style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                            <div style="font-size: 48px; margin-bottom: 15px; opacity: 0.6;">⏳</div>
                            <p style="color: #64748b; font-size: 18px;">Caricamento inviti...</p>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 30px;">
                        <button type="button" class="wi-btn-back-to-mode" style="background: #e2e8f0; color: #475569; padding: 12px 30px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 15px; transition: all 0.3s ease;">
                            ← Torna alla Selezione
                        </button>
                    </div>
                </div>

                <style>
                    .wi-mode-card:hover {
                        transform: translateY(-8px) scale(1.02);
                        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
                    }

                    .wi-mode-card:active {
                        transform: translateY(-4px) scale(1.01);
                    }

                    .wi-invite-selection-card {
                        background: white;
                        border-radius: 16px;
                        padding: 25px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                        cursor: pointer;
                        transition: all 0.3s ease;
                        border: 2px solid transparent;
                    }

                    .wi-invite-selection-card:hover {
                        transform: translateY(-4px);
                        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                        border-color: #667eea;
                    }

                    .wi-btn-back-to-mode:hover {
                        background: #cbd5e1;
                    }
                </style>
            </div>
            <?php endif; ?>

            <!-- STEP 1: CATEGORIA EVENTO -->
            <div class="wi-wizard-step-modern <?php echo $edit_mode ? 'active' : ''; ?>" data-step="1">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Quale evento vuoi celebrare?</h2>
                    <p class="wi-step-description-modern">Scegli la categoria che meglio rappresenta la tua occasione speciale</p>
                </div>

                <div id="wi-categories-grid" class="wi-categories-grid-modern">
                    <!-- Caricato dinamicamente via AJAX -->
                    <div class="wi-category-card-modern wi-loading-card">
                        <div class="wi-category-shimmer"></div>
                        <div class="wi-category-icon-modern">⏳</div>
                        <div class="wi-category-name-modern">Caricamento...</div>
                    </div>
                </div>
            </div>

            <!-- STEP 2: SCELTA TEMPLATE -->
            <div class="wi-wizard-step-modern" data-step="2">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Scegli il Design Perfetto</h2>
                    <p class="wi-step-description-modern">Seleziona il template che più ti piace - potrai personalizzarlo dopo</p>
                </div>

                <div class="wi-templates-grid-modern">
                    <!-- I template verranno caricati dinamicamente via AJAX quando selezioni una categoria -->
                    <div class="wi-templates-placeholder" style="grid-column: 1/-1; text-align: center; padding: 80px 20px;">
                        <div style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;">🎨</div>
                        <h3 style="color: #64748b; font-size: 20px; margin-bottom: 10px;">I template appariranno qui</h3>
                        <p style="color: #94a3b8; font-size: 16px;">Seleziona prima una categoria nello step precedente per vedere i template disponibili</p>
                    </div>
                </div>
            </div>

            <!-- STEP 3: INFORMAZIONI EVENTO -->
            <div class="wi-wizard-step-modern" data-step="3">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Quando e Dove?</h2>
                    <p class="wi-step-description-modern">Inserisci i dettagli fondamentali del tuo evento</p>
                </div>

                <div class="wi-form-grid-modern">
                    <div class="wi-form-group-modern wi-form-col-2">
                        <label for="wizard_event_date" class="wi-label-modern">
                            <span class="wi-label-icon">📅</span>
                            Data Evento
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="date"
                                   id="wizard_event_date"
                                   name="event_date"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   class="wi-input-modern"
                                   required>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-2">
                        <label for="wizard_event_time" class="wi-label-modern">
                            <span class="wi-label-icon">⏰</span>
                            Orario
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="time"
                                   id="wizard_event_time"
                                   name="event_time"
                                   class="wi-input-modern"
                                   required>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-1">
                        <label for="wizard_event_location" class="wi-label-modern">
                            <span class="wi-label-icon">📍</span>
                            Nome Location
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="text"
                                   id="wizard_event_location"
                                   name="event_location"
                                   placeholder="Es: Villa Borghese, Grand Hotel..."
                                   class="wi-input-modern"
                                   required>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-1">
                        <label for="wizard_event_address" class="wi-label-modern">
                            <span class="wi-label-icon">🗺️</span>
                            Indirizzo Completo
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="text"
                                   id="wizard_event_address"
                                   name="event_address"
                                   placeholder="Via/Piazza, Numero Civico, CAP Città (Provincia)"
                                   class="wi-input-modern"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 4: PERSONALIZZAZIONE CONTENUTO -->
            <div class="wi-wizard-step-modern" data-step="4">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Personalizza il Messaggio</h2>
                    <p class="wi-step-description-modern">Aggiungi il tuo tocco personale all'invito</p>
                </div>

                <div class="wi-form-grid-modern">
                    <div class="wi-form-group-modern wi-form-col-1">
                        <label for="wizard_invite_title" class="wi-label-modern">
                            <span class="wi-label-icon">✨</span>
                            Titolo Invito
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="text"
                                   id="wizard_invite_title"
                                   name="invite_title"
                                   placeholder="Es: Il Matrimonio di Mario e Giulia"
                                   class="wi-input-modern wi-input-large"
                                   required>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-1">
                        <label for="wizard_invite_message" class="wi-label-modern">
                            <span class="wi-label-icon">💌</span>
                            Messaggio di Benvenuto
                            <span class="wi-label-required">*</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <textarea id="wizard_invite_message"
                                      name="invite_message"
                                      placeholder="Scrivi un messaggio speciale per i tuoi invitati..."
                                      class="wi-textarea-modern"
                                      rows="5"
                                      required></textarea>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-2">
                        <label for="wizard_final_message" class="wi-label-modern">
                            <span class="wi-label-icon">🎁</span>
                            Messaggio Finale
                            <span class="wi-label-optional">(Opzionale)</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <textarea id="wizard_final_message"
                                      name="final_message"
                                      placeholder="Un messaggio nascosto che apparirà dopo aver cliccato..."
                                      class="wi-textarea-modern"
                                      rows="3"></textarea>
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-2">
                        <label for="wizard_final_message_button" class="wi-label-modern">
                            <span class="wi-label-icon">🔘</span>
                            Testo Pulsante
                            <span class="wi-label-optional">(Opzionale)</span>
                        </label>
                        <div class="wi-input-wrapper">
                            <input type="text"
                                   id="wizard_final_message_button"
                                   name="final_message_button_text"
                                   placeholder="Es: Scopri la sorpresa"
                                   class="wi-input-modern">
                        </div>
                    </div>

                    <div class="wi-form-group-modern wi-form-col-1">
                        <label class="wi-label-modern">
                            <span class="wi-label-icon">🖼️</span>
                            Immagine Invito
                            <span class="wi-label-optional">(Opzionale)</span>
                        </label>
                        <div class="wi-image-upload-modern" id="wizard_upload_image">
                            <div class="wi-upload-area">
                                <div class="wi-upload-icon">📸</div>
                                <div class="wi-upload-text">
                                    <strong>Clicca per caricare</strong>
                                    <span>o trascina qui la tua immagine</span>
                                </div>
                                <div class="wi-upload-hint">Consigliato: foto degli sposi o del festeggiato</div>
                            </div>
                        </div>
                        <div id="wizard_image_preview" class="wi-image-preview-modern"></div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: RSVP SETTINGS -->
            <div class="wi-wizard-step-modern" data-step="5">
                <div class="wi-step-header-modern">
                    <h2 class="wi-step-title-modern">
                        <span class="wi-title-icon">📮</span>
                        Impostazioni RSVP
                    </h2>
                    <p class="wi-step-description-modern">
                        Abilita la conferma presenza per gli invitati
                    </p>
                </div>

                <div class="wi-step-content-modern">
                    <!-- Toggle RSVP Enable -->
                    <div class="wi-form-group-modern">
                        <label class="wi-toggle-label">
                            <input type="checkbox" id="wizard_rsvp_enabled" class="wi-toggle-input">
                            <span class="wi-toggle-slider"></span>
                            <span class="wi-toggle-text">Abilita RSVP per questo invito</span>
                        </label>
                        <p class="wi-field-hint-modern">Gli ospiti potranno confermare direttamente dall'invito</p>
                    </div>

                    <!-- RSVP Options (mostrate solo se enabled) -->
                    <div id="rsvp_options_wrapper" style="display: none;">

                        <div class="wi-form-group-modern">
                            <label class="wi-label-modern">
                                Scadenza Conferma
                                <span class="wi-label-optional">(Opzionale)</span>
                            </label>
                            <input type="date"
                                   id="wizard_rsvp_deadline"
                                   class="wi-input-modern"
                                   placeholder="Lascia vuoto per nessuna scadenza">
                            <p class="wi-field-hint-modern">Data limite entro cui confermare</p>
                        </div>

                        <div class="wi-form-group-modern">
                            <label class="wi-label-modern">Numero Massimo Ospiti</label>
                            <select id="wizard_max_guests" class="wi-input-modern">
                                <option value="1">Solo invitato (1 persona)</option>
                                <option value="2">Coppia (2 persone)</option>
                                <option value="3">Coppia + 1 (3 persone)</option>
                                <option value="4" selected>Famiglia (4 persone)</option>
                                <option value="5">Gruppo (5 persone)</option>
                                <option value="6">Gruppo grande (6+ persone)</option>
                            </select>
                            <p class="wi-field-hint-modern">Quanti accompagnatori può portare</p>
                        </div>

                        <div class="wi-form-group-modern">
                            <label class="wi-label-modern">
                                Opzioni Menu
                                <span class="wi-label-optional">(Separate da virgola)</span>
                            </label>
                            <input type="text"
                                   id="wizard_menu_choices"
                                   class="wi-input-modern"
                                   value="Carne, Pesce, Vegetariano"
                                   placeholder="Es: Carne, Pesce, Vegetariano">
                            <p class="wi-field-hint-modern">Le scelte di menu disponibili per gli ospiti</p>
                        </div>

                        <div class="wi-form-group-modern">
                            <label class="wi-toggle-label">
                                <input type="checkbox" id="wizard_notify_admin" class="wi-toggle-input" checked>
                                <span class="wi-toggle-slider"></span>
                                <span class="wi-toggle-text">Ricevi email per ogni conferma</span>
                            </label>
                        </div>

                        <div class="wi-form-group-modern">
                            <label class="wi-label-modern">Email Notifiche</label>
                            <input type="email"
                                   id="wizard_admin_email"
                                   class="wi-input-modern"
                                   placeholder="tua@email.com">
                            <p class="wi-field-hint-modern">Dove ricevere le notifiche (lascia vuoto per usare l'email admin)</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- STEP 6: ANTEPRIMA FINALE -->
            <div class="wi-wizard-step-modern" data-step="6">
                <div class="wi-step-header">
                    <h2 class="wi-step-title-modern">Anteprima Finale</h2>
                    <p class="wi-step-description-modern">Controlla tutti i dettagli prima di creare il tuo invito</p>
                </div>

                <div class="wi-preview-layout">
                    <!-- Sidebar riepilogo -->
                    <div class="wi-preview-sidebar">
                        <div class="wi-summary-card">
                            <h4 class="wi-summary-title">📋 Riepilogo</h4>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">Tipo Evento</span>
                                <span class="wi-summary-value" id="preview_event_category">-</span>
                            </div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">Template</span>
                                <span class="wi-summary-value" id="preview_template">-</span>
                            </div>

                            <div class="wi-summary-divider"></div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">📅 Data</span>
                                <span class="wi-summary-value" id="preview_date">-</span>
                            </div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">⏰ Orario</span>
                                <span class="wi-summary-value" id="preview_time">-</span>
                            </div>

                            <div class="wi-summary-divider"></div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">📍 Location</span>
                                <span class="wi-summary-value" id="preview_location">-</span>
                            </div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">🗺️ Indirizzo</span>
                                <span class="wi-summary-value wi-summary-multiline" id="preview_address">-</span>
                            </div>

                            <div class="wi-summary-divider"></div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">✨ Titolo</span>
                                <span class="wi-summary-value wi-summary-highlight" id="preview_title">-</span>
                            </div>

                            <div class="wi-summary-divider"></div>

                            <div class="wi-summary-item">
                                <span class="wi-summary-label">📮 RSVP</span>
                                <span class="wi-summary-value" id="preview_rsvp_status">-</span>
                            </div>

                            <div class="wi-summary-item" id="preview_rsvp_deadline_wrapper" style="display: none;">
                                <span class="wi-summary-label">📅 Scadenza RSVP</span>
                                <span class="wi-summary-value" id="preview_rsvp_deadline">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Anteprima principale -->
                    <div class="wi-preview-main">
                        <div class="wi-preview-device">
                            <div class="wi-device-header">
                                <div class="wi-device-camera"></div>
                                <div class="wi-device-speaker"></div>
                            </div>
                            <div class="wi-device-screen">
                                <div id="wizard_preview_loading" class="wi-preview-loading">
                                    <div class="wi-loading-spinner"></div>
                                    <p>Generazione anteprima...</p>
                                </div>
                                <iframe id="wizard_final_preview_frame" class="wi-preview-iframe"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- NAVIGATION FOOTER MODERNA -->
        <div class="wi-wizard-footer-modern">
            <button type="button" class="wi-btn-modern wi-btn-secondary" disabled>
                <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <span>Indietro</span>
            </button>

            <div class="wi-step-counter">
                <span class="wi-current-step">1</span> / <span class="wi-total-steps">6</span>
            </div>

            <button type="button" class="wi-btn-modern wi-btn-primary wi-wizard-next" disabled>
                <span>Avanti</span>
                <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <button type="button" id="wizard_create_invite" class="wi-btn-modern wi-btn-success" style="display: none;">
                <svg class="wi-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span>Crea Invito</span>
            </button>
        </div>

    </div>

    <!-- Decorazioni di sfondo -->
    <div class="wi-bg-decoration">
        <div class="wi-blob wi-blob-1"></div>
        <div class="wi-blob wi-blob-2"></div>
        <div class="wi-blob wi-blob-3"></div>
    </div>
</div>

<?php
// IMPORTANTE: In modalità admin, wp_localize_script non è disponibile qui
// quindi dobbiamo definire wiWizard inline. Nel frontend, wp_localize_script
// viene chiamato PRIMA che questo template venga renderizzato, quindi wiWizard
// sarà già definito e non dobbiamo sovrascriverlo.
if (is_admin()) :
?>
<script>
// Solo per admin: passa dati PHP a JavaScript
var wiWizard = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce(WI_NONCE_ADMIN); ?>',
    edit_mode: <?php echo $edit_mode ? 'true' : 'false'; ?>,
    invite_data: <?php echo $edit_mode && $invite_data ? json_encode($invite_data) : 'null'; ?>
};
console.log('WI DEBUG (Admin): wiWizard initialized from inline script', wiWizard);
</script>
<?php else : ?>
<script>
// Frontend: wiWizard dovrebbe essere già stato definito da wp_localize_script
// Verifica che esista, altrimenti crea fallback
if (typeof wiWizard === 'undefined') {
    console.error('WI ERROR: wiWizard not defined! wp_localize_script may have failed.');
    var wiWizard = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce(WI_NONCE_PUBLIC); ?>',
        edit_mode: false,
        invite_data: null
    };
} else {
    console.log('WI DEBUG (Frontend): wiWizard already defined from wp_localize_script', wiWizard);
}
</script>
<?php endif; ?>
