<?php
/**
 * Template sezione RSVP - Incluso in fondo agli inviti
 *
 * Variabili disponibili:
 * @var int $invite_id ID dell'invito
 * @var object $settings Impostazioni RSVP per questo invito
 */

if (!defined('ABSPATH')) exit;

// Ottieni impostazioni RSVP per questo invito
$settings = WI_RSVP_Database::get_settings($invite_id);

// Se RSVP disabilitato, non mostrare nulla
if (!$settings->rsvp_enabled) {
    return;
}

// Decodifica menu choices
$menu_choices = json_decode($settings->menu_choices, true);
if (!is_array($menu_choices)) {
    $menu_choices = ['Carne', 'Pesce', 'Vegetariano'];
}

// Verifica deadline
$deadline_passed = false;
$deadline_text = '';
if ($settings->rsvp_deadline) {
    $deadline = strtotime($settings->rsvp_deadline);
    $deadline_passed = $deadline < time();
    $deadline_text = date_i18n('j F Y', $deadline);
}
?>

<!-- Sezione RSVP -->
<div class="wi-rsvp-section" id="rsvp-section">
    <div class="wi-rsvp-container">

        <?php if ($deadline_passed) : ?>
            <!-- Deadline scaduta -->
            <div class="wi-rsvp-expired">
                <div class="wi-expired-icon">⏰</div>
                <h2>Il termine per confermare è scaduto</h2>
                <p>Non è più possibile inviare la risposta RSVP per questo evento.</p>
                <p>Per maggiori informazioni, contatta l'organizzatore.</p>
            </div>
        <?php else : ?>

            <!-- Header RSVP -->
            <div class="wi-rsvp-header">
                <h2 class="wi-rsvp-title">
                    <span class="wi-rsvp-icon">📮</span>
                    Conferma la tua Presenza
                </h2>
                <p class="wi-rsvp-subtitle">Ci piacerebbe sapere se parteciperai!</p>

                <?php if ($settings->rsvp_deadline) : ?>
                <div class="wi-rsvp-deadline">
                    <span class="wi-deadline-icon">⏰</span>
                    Rispondi entro il <strong><?php echo esc_html($deadline_text); ?></strong>
                </div>
                <?php endif; ?>
            </div>

            <!-- Form RSVP -->
            <form id="wi-rsvp-form" class="wi-rsvp-form" data-invite-id="<?php echo esc_attr($invite_id); ?>">

                <!-- STEP 1: Conferma Partecipazione -->
                <div class="wi-rsvp-step active" data-step="1">
                    <div class="wi-step-content">
                        <h3 class="wi-step-title">Parteciperai all'evento?</h3>

                        <div class="wi-rsvp-options">
                            <label class="wi-rsvp-option">
                                <input type="radio" name="status" value="attending" required>
                                <div class="wi-option-card">
                                    <span class="wi-option-icon">✅</span>
                                    <span class="wi-option-text">Sì, ci sarò!</span>
                                </div>
                            </label>

                            <label class="wi-rsvp-option">
                                <input type="radio" name="status" value="not_attending">
                                <div class="wi-option-card">
                                    <span class="wi-option-icon">❌</span>
                                    <span class="wi-option-text">No, mi dispiace</span>
                                </div>
                            </label>

                            <label class="wi-rsvp-option">
                                <input type="radio" name="status" value="maybe">
                                <div class="wi-option-card">
                                    <span class="wi-option-icon">❓</span>
                                    <span class="wi-option-text">Non sono sicuro</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Dettagli Ospite (mostrato solo se attending) -->
                <div class="wi-rsvp-step" data-step="2" style="display:none;">
                    <div class="wi-step-content">
                        <h3 class="wi-step-title">I tuoi Dettagli</h3>

                        <div class="wi-rsvp-grid">
                            <div class="wi-form-group wi-form-full">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">👤</span>
                                    Nome Completo <span class="wi-required">*</span>
                                </label>
                                <input type="text"
                                       name="guest_name"
                                       class="wi-form-input"
                                       placeholder="Es: Mario Rossi"
                                       required>
                            </div>

                            <div class="wi-form-group">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">📧</span>
                                    Email <span class="wi-required">*</span>
                                </label>
                                <input type="email"
                                       name="guest_email"
                                       class="wi-form-input"
                                       placeholder="mario@esempio.it"
                                       required>
                            </div>

                            <div class="wi-form-group">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">📱</span>
                                    Telefono
                                </label>
                                <input type="tel"
                                       name="guest_phone"
                                       class="wi-form-input"
                                       placeholder="+39 123 456 7890">
                            </div>

                            <div class="wi-form-group wi-form-full">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">👥</span>
                                    Numero di Ospiti
                                </label>
                                <select name="num_guests" class="wi-form-select">
                                    <?php for ($i = 1; $i <= $settings->max_guests_per_response; $i++) : ?>
                                        <option value="<?php echo $i; ?>">
                                            <?php echo $i === 1 ? 'Solo io' : "$i persone"; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Scelta Menu -->
                        <?php if (!empty($menu_choices)) : ?>
                        <div class="wi-form-group wi-form-full">
                            <label class="wi-form-label">
                                <span class="wi-label-icon">🍽️</span>
                                Preferenza Menu
                            </label>
                            <div class="wi-menu-choices">
                                <?php foreach ($menu_choices as $choice) : ?>
                                <label class="wi-menu-option">
                                    <input type="radio" name="menu_choice" value="<?php echo esc_attr($choice); ?>">
                                    <span><?php echo esc_html($choice); ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Allergie/Intolleranze -->
                        <div class="wi-form-group wi-form-full">
                            <label class="wi-form-label">
                                <span class="wi-label-icon">⚠️</span>
                                Allergie o Intolleranze
                            </label>
                            <div class="wi-dietary-preferences">
                                <label class="wi-checkbox-option">
                                    <input type="checkbox" name="dietary[]" value="gluten_free">
                                    <span>Senza Glutine</span>
                                </label>
                                <label class="wi-checkbox-option">
                                    <input type="checkbox" name="dietary[]" value="lactose_free">
                                    <span>Senza Lattosio</span>
                                </label>
                                <label class="wi-checkbox-option">
                                    <input type="checkbox" name="dietary[]" value="vegan">
                                    <span>Vegano</span>
                                </label>
                                <label class="wi-checkbox-option">
                                    <input type="checkbox" name="dietary[]" value="vegetarian">
                                    <span>Vegetariano</span>
                                </label>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="wi-form-group wi-form-full">
                            <label class="wi-form-label">
                                <span class="wi-label-icon">💬</span>
                                Note o Richieste Speciali
                            </label>
                            <textarea name="notes"
                                      class="wi-form-textarea"
                                      rows="3"
                                      placeholder="Es: Sedia a rotelle, seggiolone bambini, intolleranze particolari..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Messaggio Declino (mostrato solo se not_attending) -->
                <div class="wi-rsvp-step" data-step="3" style="display:none;">
                    <div class="wi-step-content">
                        <div class="wi-decline-message">
                            <div class="wi-decline-icon">😔</div>
                            <h3>Ci mancherai!</h3>
                            <p>Grazie per averci fatto sapere. Speriamo di vederti in un'altra occasione!</p>

                            <div class="wi-form-group">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">👤</span>
                                    Il tuo nome <span class="wi-required">*</span>
                                </label>
                                <input type="text"
                                       name="guest_name_decline"
                                       class="wi-form-input"
                                       placeholder="Nome Cognome">
                            </div>

                            <div class="wi-form-group">
                                <label class="wi-form-label">
                                    <span class="wi-label-icon">📧</span>
                                    Email <span class="wi-required">*</span>
                                </label>
                                <input type="email"
                                       name="guest_email_decline"
                                       class="wi-form-input"
                                       placeholder="email@esempio.it">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="wi-rsvp-actions">
                    <button type="button" class="wi-btn wi-btn-back" style="display:none;">
                        ← Indietro
                    </button>

                    <button type="button" class="wi-btn wi-btn-next wi-btn-primary">
                        Continua →
                    </button>

                    <button type="submit" class="wi-btn wi-btn-submit wi-btn-primary" style="display:none;">
                        <span class="wi-btn-icon">✓</span>
                        Conferma RSVP
                    </button>
                </div>

                <!-- Loading State -->
                <div class="wi-rsvp-loading" style="display:none;">
                    <div class="wi-loader"></div>
                    <p>Invio in corso...</p>
                </div>
            </form>

            <!-- Success Message (nascosto inizialmente) -->
            <div class="wi-rsvp-success" style="display:none;">
                <div class="wi-success-animation">
                    <div class="wi-success-icon">🎉</div>
                </div>
                <h3 class="wi-success-title">Grazie per la conferma!</h3>
                <p class="wi-success-message">
                    Ti abbiamo inviato un'email di riepilogo a <strong id="confirmed-email"></strong>
                </p>
                <p class="wi-success-note">
                    Puoi modificare la tua risposta in qualsiasi momento usando il link nell'email.
                </p>
                <button type="button" class="wi-btn wi-btn-primary" onclick="location.reload();">
                    Invia un'altra risposta
                </button>
            </div>

        <?php endif; ?>
    </div>
</div>
