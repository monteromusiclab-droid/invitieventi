<?php
/**
 * Template HTML/CSS Content Helper Functions
 * Fornisce l'HTML e CSS completi per i template predefiniti
 * VERSIONE AGGIORNATA con schema standardizzato
 */

if (!defined('ABSPATH')) exit;

/**
 * SCHEMA HTML STANDARDIZZATO
 * Utilizzato da tutti i template per garantire coerenza
 */
function get_standard_template_html() {
    return '<div class="wi-invite">

    {{#if header_image}}
    <div class="wi-header-image">
        <img src="{{header_image}}" alt="Header" />
    </div>
    {{/if}}

    <header class="wi-header">
        <h1 class="wi-title">{{title}}</h1>
        <div class="wi-divider"></div>
    </header>

    {{#if decoration_top}}
    <div class="wi-decoration-top">
        <img src="{{decoration_top}}" alt="Decorazione" />
    </div>
    {{/if}}

    <section class="wi-countdown-section">
        <h2 class="wi-countdown-label">Mancano solo...</h2>
        <div id="countdown" class="wi-countdown"></div>
    </section>

    {{#if decoration_bottom}}
    <div class="wi-decoration-bottom">
        <img src="{{decoration_bottom}}" alt="Decorazione" />
    </div>
    {{/if}}

    <section class="wi-message-section">
        <div class="wi-message-content">
            {{message}}
        </div>
    </section>

    {{#if user_image}}
    <section class="wi-user-image-section">
        <div class="wi-image-container">
            <img src="{{user_image}}" alt="Foto evento" class="wi-user-image" />
        </div>
    </section>
    {{/if}}

    <section class="wi-event-info-section">
        <h2 class="wi-section-title">Dettagli dell\'Evento</h2>
        <div class="wi-event-details">
            <div class="wi-detail-item">
                <div class="wi-detail-icon">📅</div>
                <div class="wi-detail-content">
                    <strong class="wi-detail-label">Data</strong>
                    <p class="wi-detail-value">{{event_date}}</p>
                </div>
            </div>

            <div class="wi-detail-item">
                <div class="wi-detail-icon">🕐</div>
                <div class="wi-detail-content">
                    <strong class="wi-detail-label">Orario</strong>
                    <p class="wi-detail-value">{{event_time}}</p>
                </div>
            </div>

            <div class="wi-detail-item">
                <div class="wi-detail-icon">📍</div>
                <div class="wi-detail-content">
                    <strong class="wi-detail-label">Luogo</strong>
                    <p class="wi-detail-value">{{event_location}}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="wi-calendar-section">
        <button class="wi-btn wi-add-calendar" onclick="addToCalendar()">
            📅 Aggiungi al Calendario
        </button>
    </section>

    <section class="wi-map-section">
        <h2 class="wi-section-title">Come Raggiungerci</h2>
        <div id="invite-map-{{event_date}}" class="wi-map" data-address="{{event_address}}"></div>
        <a href="https://www.google.com/maps/search/?api=1&query={{event_address}}"
           target="_blank"
           class="wi-map-link">
            📍 Apri in Google Maps
        </a>
    </section>

    <section class="wi-share-section" style="display: none;" data-creator-only="true">
        <h3 class="wi-share-title">Condividi l\'invito</h3>
        <div class="wi-share-buttons">
            <button class="wi-share-btn wi-whatsapp" onclick="shareWhatsApp()">
                💬 WhatsApp
            </button>
            <button class="wi-share-btn wi-email" onclick="shareEmail()">
                ✉️ Email
            </button>
            <button class="wi-share-btn wi-copy" onclick="copyLink()">
                🔗 Copia Link
            </button>
        </div>
    </section>

    {{#if final_message}}
    <section class="wi-final-message-section">
        <button class="wi-final-message-btn" onclick="toggleFinalMessage()">
            {{final_message_button_text}}
        </button>
        <div class="wi-final-message-content" id="finalMessageContent" style="display:none;">
            {{final_message}}
        </div>
    </section>
    {{/if}}

    {{#if footer_logo}}
    <footer class="wi-footer">
        <img src="{{footer_logo}}" alt="Logo" class="wi-footer-logo" />
    </footer>
    {{/if}}

</div>';
}

/**
 * Template 1: Elegante Oro - HTML
 */
function get_elegant_template_html() {
    return get_standard_template_html();
}

/**
 * Template 1: Elegante Oro - CSS
 */
function get_elegant_template_css() {
    return '
/* Template Elegante Oro - Enhanced Modern Design */
@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=Lora:wght@400;500;600;700&display=swap");

/* Animazioni Chiave */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.wi-invite {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    font-family: "Lora", serif;
    color: #2c2416;
    position: relative;
    background: linear-gradient(180deg, #fdfaf5 0%, #ffffff 100%);
    box-shadow: 0 0 80px rgba(212, 175, 55, 0.1);
    animation: fadeInUp 0.8s ease;
}

/* Header Image with Overlay */
.wi-header-image {
    position: relative;
    overflow: hidden;
}

.wi-header-image::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(212, 175, 55, 0.1) 0%, rgba(253, 250, 245, 0.9) 100%);
    z-index: 1;
}

.wi-header-image img {
    width: 100%;
    height: 450px;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.wi-header-image:hover img {
    transform: scale(1.05);
}

/* Header e Titolo con Effetti */
.wi-header {
    text-align: center;
    padding: 70px 20px 50px;
    position: relative;
    overflow: hidden;
}

.wi-header::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
    z-index: 0;
}

.wi-title {
    font-family: "Playfair Display", serif;
    font-size: 62px;
    font-weight: 800;
    background: linear-gradient(135deg, #d4af37 0%, #f4e3a1 50%, #d4af37 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 20px;
    line-height: 1.2;
    position: relative;
    z-index: 1;
    animation: shimmer 3s linear infinite;
    text-shadow: 0 4px 20px rgba(212, 175, 55, 0.3);
}

.wi-divider {
    width: 120px;
    height: 4px;
    background: linear-gradient(to right, transparent, #d4af37, #f4e3a1, #d4af37, transparent);
    margin: 25px auto;
    position: relative;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(212, 175, 55, 0.4);
}

.wi-divider::before,
.wi-divider::after {
    content: "✦";
    position: absolute;
    top: -8px;
    font-size: 20px;
    color: #d4af37;
    animation: pulse 2s ease infinite;
}

.wi-divider::before {
    left: -30px;
}

.wi-divider::after {
    right: -30px;
}

/* Decorazioni */
.wi-decoration-top,
.wi-decoration-bottom {
    text-align: center;
    padding: 30px 20px;
}

.wi-decoration-top img,
.wi-decoration-bottom img {
    max-width: 300px;
    width: 100%;
    height: auto;
}

/* Countdown con Glassmorphism */
.wi-countdown-section {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    padding: 60px 30px;
    text-align: center;
    margin: 50px 20px;
    border-radius: 25px;
    box-shadow: 0 15px 60px rgba(212, 175, 55, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
    border: 2px solid rgba(212, 175, 55, 0.2);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 1s ease 0.3s both;
}

.wi-countdown-section::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent 30%, rgba(212, 175, 55, 0.05) 50%, transparent 70%);
    animation: rotate 20s linear infinite;
}

.wi-countdown-label {
    font-family: "Playfair Display", serif;
    font-size: 28px;
    font-weight: 600;
    background: linear-gradient(135deg, #d4af37 0%, #f4e3a1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 40px;
    position: relative;
    z-index: 1;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.countdown-item {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(253, 250, 245, 0.9) 100%);
    backdrop-filter: blur(10px);
    padding: 30px 25px;
    border-radius: 20px;
    min-width: 110px;
    border: 3px solid #d4af37;
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.countdown-item::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.2), transparent);
    transition: left 0.5s;
}

.countdown-item:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.35);
    border-color: #f4e3a1;
}

.countdown-item:hover::before {
    left: 100%;
}

.countdown-value {
    font-size: 52px;
    font-weight: 800;
    background: linear-gradient(135deg, #d4af37 0%, #f4e3a1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
    margin-bottom: 12px;
    font-variant-numeric: tabular-nums;
    position: relative;
}

.countdown-label {
    font-size: 13px;
    color: #6b5d3f;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 600;
}

/* Messaggio con Effetto Carta */
.wi-message-section {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(253, 250, 245, 0.95) 100%);
    padding: 60px 50px;
    margin: 50px 20px;
    border-radius: 25px;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 60px rgba(212, 175, 55, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(212, 175, 55, 0.2);
    animation: fadeInUp 1s ease 0.5s both;
}

.wi-message-section::before {
    content: "\u201C";
    position: absolute;
    top: 20px;
    left: 30px;
    font-size: 80px;
    font-family: "Playfair Display", serif;
    color: rgba(212, 175, 55, 0.15);
    line-height: 1;
}

.wi-message-section::after {
    content: "\u201D";
    position: absolute;
    bottom: 20px;
    right: 30px;
    font-size: 80px;
    font-family: "Playfair Display", serif;
    color: rgba(212, 175, 55, 0.15);
    line-height: 1;
}

.wi-message-content {
    font-size: 20px;
    line-height: 2;
    color: #4a4a4a;
    max-width: 700px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
    font-style: italic;
}

/* Immagine utente con Frame */
.wi-user-image-section {
    padding: 60px 20px;
    text-align: center;
    animation: fadeInUp 1s ease 0.7s both;
}

.wi-image-container {
    display: inline-block;
    max-width: 700px;
    width: 100%;
    position: relative;
    padding: 20px;
}

.wi-image-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 3px solid #d4af37;
    border-radius: 25px;
    transform: rotate(-1deg);
    transition: transform 0.3s ease;
}

.wi-image-container:hover::before {
    transform: rotate(1deg) scale(1.02);
}

.wi-user-image {
    width: 100%;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.wi-image-container:hover .wi-user-image {
    transform: scale(1.02);
}

/* Dettagli Evento con Cards Moderne */
.wi-event-info-section {
    padding: 60px 20px;
    animation: fadeInUp 1s ease 0.9s both;
}

.wi-section-title {
    font-family: "Playfair Display", serif;
    font-size: 42px;
    font-weight: 700;
    background: linear-gradient(135deg, #d4af37 0%, #f4e3a1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
    margin: 0 0 50px;
    position: relative;
}

.wi-section-title::after {
    content: "";
    display: block;
    width: 80px;
    height: 3px;
    background: linear-gradient(to right, transparent, #d4af37, transparent);
    margin: 20px auto 0;
}

.wi-event-details {
    max-width: 750px;
    margin: 0 auto;
}

.wi-detail-item {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 30px;
    margin: 20px 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(253, 250, 245, 0.98) 100%);
    border-radius: 20px;
    border-left: 6px solid #d4af37;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.wi-detail-item::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.1), transparent);
    transition: left 0.6s;
}

.wi-detail-item:hover {
    transform: translateX(10px);
    box-shadow: 0 15px 50px rgba(212, 175, 55, 0.25);
    border-left-width: 8px;
}

.wi-detail-item:hover::before {
    left: 100%;
}

.wi-detail-icon {
    font-size: 48px;
    min-width: 60px;
    text-align: center;
    animation: float 3s ease-in-out infinite;
}

.wi-detail-content {
    flex: 1;
}

.wi-detail-label {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #d4af37;
    margin-bottom: 8px;
    font-weight: 700;
}

.wi-detail-value {
    font-size: 22px;
    color: #2c2416;
    margin: 0;
    font-weight: 700;
}

/* Pulsante Calendario con Effetto 3D */
.wi-calendar-section {
    text-align: center;
    padding: 40px 20px;
    animation: fadeInUp 1s ease 1.1s both;
}

.wi-add-calendar {
    display: inline-block;
    padding: 22px 55px;
    background: linear-gradient(135deg, #d4af37 0%, #f4e3a1 100%);
    color: #fff;
    border: none;
    border-radius: 50px;
    font-size: 19px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4),
                inset 0 -3px 0 rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.wi-add-calendar::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.wi-add-calendar:hover {
    background: linear-gradient(135deg, #f4e3a1 0%, #d4af37 100%);
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 15px 50px rgba(212, 175, 55, 0.6);
}

.wi-add-calendar:hover::before {
    left: 100%;
}

.wi-add-calendar:active {
    transform: translateY(-2px);
}

/* Mappa */
.wi-map-section {
    padding: 50px 20px;
    text-align: center;
}

.wi-map {
    width: 100%;
    max-width: 900px;
    height: 450px;
    border-radius: 15px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    margin: 30px auto 20px;
}

.wi-map-link {
    display: inline-block;
    padding: 15px 35px;
    background: #d4af37;
    color: #fff;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 20px;
}

.wi-map-link:hover {
    background: #b8941f;
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
}

/* Pulsanti Condivisione */
.wi-share-section {
    padding: 40px 20px;
    text-align: center;
    background: #f9f9f9;
    margin: 40px 20px;
    border-radius: 15px;
}

.wi-share-title {
    font-size: 22px;
    color: #2c2416;
    margin: 0 0 25px;
}

.wi-share-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.wi-share-btn {
    padding: 12px 28px;
    border: 2px solid #d4af37;
    background: #fff;
    color: #2c2416;
    border-radius: 50px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.wi-share-btn:hover {
    background: #d4af37;
    color: #fff;
    transform: scale(1.05);
}

/* Messaggio Finale */
.wi-final-message-section {
    padding: 40px 20px;
    text-align: center;
}

.wi-final-message-btn {
    padding: 16px 40px;
    background: linear-gradient(135deg, #d4af37, #b8941f);
    color: #fff;
    border: none;
    border-radius: 50px;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3);
}

.wi-final-message-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(212, 175, 55, 0.4);
}

.wi-final-message-content {
    max-width: 700px;
    margin: 30px auto 0;
    padding: 40px;
    background: #fff0f0;
    border-radius: 15px;
    border: 3px dashed #d4af37;
    font-size: 18px;
    line-height: 1.8;
    color: #4a4a4a;
}

/* Footer */
.wi-footer {
    text-align: center;
    padding: 60px 20px 40px;
    border-top: 2px solid rgba(212, 175, 55, 0.2);
}

.wi-footer-logo {
    max-width: 150px;
    height: auto;
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 768px) {
    .wi-title {
        font-size: 42px;
    }

    .wi-section-title {
        font-size: 28px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 38px;
    }

    .wi-message-section,
    .wi-event-info-section {
        padding: 30px 20px;
    }

    .wi-message-content {
        font-size: 17px;
    }

    .wi-detail-item {
        flex-direction: column;
        text-align: center;
    }

    .wi-share-buttons {
        flex-direction: column;
        max-width: 300px;
        margin: 0 auto;
    }

    .wi-share-btn {
        width: 100%;
    }
}
';
}

/**
 * Template 2-15: Usano lo stesso HTML ma CSS diversi
 */
function get_modern_template_html() {
    return get_standard_template_html();
}

function get_romantic_template_html() {
    return get_standard_template_html();
}

function get_luxury_template_html() {
    return get_standard_template_html();
}

function get_circular_template_html() {
    return get_standard_template_html();
}

function get_gradient_template_html() {
    return get_standard_template_html();
}

function get_neon_template_html() {
    return get_standard_template_html();
}

function get_vintage_template_html() {
    return get_standard_template_html();
}

function get_geometric_template_html() {
    return get_standard_template_html();
}

function get_sky_template_html() {
    return get_standard_template_html();
}

function get_ocean_template_html() {
    return get_standard_template_html();
}

function get_sunset_template_html() {
    return get_standard_template_html();
}

function get_crystal_template_html() {
    return get_standard_template_html();
}

function get_shadow_template_html() {
    return get_standard_template_html();
}

function get_animated_template_html() {
    return get_standard_template_html();
}

/**
 * NUOVI TEMPLATE 16-20
 */

function get_lavender_template_html() {
    return get_standard_template_html();
}

function get_minimal_template_html() {
    return get_standard_template_html();
}

function get_boho_template_html() {
    return get_standard_template_html();
}

function get_gatsby_template_html() {
    return get_standard_template_html();
}

function get_tropical_template_html() {
    return get_standard_template_html();
}

/**
 * CSS per template 2-15 - Base standard con personalizzazioni colore
 */
function get_modern_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_romantic_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_luxury_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_circular_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_gradient_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_neon_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_vintage_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_geometric_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_sky_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_ocean_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_sunset_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_crystal_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_shadow_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

function get_animated_template_css() {
    return get_elegant_template_css(); // Usa CSS base
}

/**
 * Template 16: Pastello Lavanda - CSS
 */
function get_lavender_template_css() {
    return '
/* Template Pastello Lavanda */
.wi-invite {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    font-family: "Quicksand", sans-serif;
    background: linear-gradient(135deg, #e6e6fa 0%, #f8f8ff 50%, #fff0f5 100%);
    color: #6b5b95;
}

.wi-header {
    text-align: center;
    padding: 70px 20px 50px;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(10px);
}

.wi-title {
    font-family: "Playfair Display", serif;
    font-size: 54px;
    font-weight: 600;
    color: #9370db;
    margin: 0 0 20px;
    text-shadow: 2px 2px 4px rgba(147, 112, 219, 0.1);
}

.wi-divider {
    width: 120px;
    height: 2px;
    background: linear-gradient(to right, transparent, #b19cd9, transparent);
    margin: 20px auto;
}

.wi-countdown-section {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(15px);
    padding: 50px 30px;
    margin: 40px 20px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(147, 112, 219, 0.2);
    text-align: center;
}

.wi-countdown-label {
    font-size: 26px;
    color: #9370db;
    margin: 0 0 30px;
    font-weight: 500;
    text-align: center;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin: 30px auto;
    max-width: 600px;
}

.countdown-item {
    background: rgba(255, 255, 255, 0.9);
    border: 3px solid #dda0dd;
    padding: 30px 35px;
    border-radius: 15px;
    min-width: 110px;
    text-align: center;
}

.countdown-value {
    font-size: 3.2rem;
    font-weight: 700;
    color: #9370db;
}

.countdown-label {
    font-size: 13px;
    color: #6b5b95;
    text-transform: lowercase;
    font-style: italic;
}

.wi-message-section {
    background: rgba(255, 255, 255, 0.8);
    padding: 50px 40px;
    margin: 40px 20px;
    border-radius: 20px;
    border: 2px solid rgba(221, 160, 221, 0.3);
}

.wi-message-content {
    font-size: 18px;
    line-height: 1.9;
    color: #6b5b95;
    text-align: center;
}

.wi-user-image {
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(147, 112, 219, 0.25);
    border: 6px solid rgba(255, 255, 255, 0.9);
}

.wi-section-title {
    font-size: 34px;
    color: #9370db;
    text-align: center;
    margin-bottom: 40px;
}

.wi-detail-item {
    background: rgba(255, 255, 255, 0.9);
    border-left: 5px solid #dda0dd;
}

.wi-detail-label {
    color: #9370db;
}

.wi-detail-value {
    color: #6b5b95;
}

.wi-add-calendar {
    background: linear-gradient(135deg, #9370db, #b19cd9);
    color: #fff;
    padding: 18px 45px;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    box-shadow: 0 8px 30px rgba(147, 112, 219, 0.3);
}

.wi-add-calendar:hover {
    background: linear-gradient(135deg, #7b68ee, #9370db);
    transform: translateY(-3px);
}

.wi-map {
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(147, 112, 219, 0.2);
    border: 4px solid rgba(255, 255, 255, 0.9);
}

.wi-map-link {
    background: #b19cd9;
    color: #fff;
}

.wi-share-section {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 20px;
}

.wi-share-btn {
    border: 2px solid #dda0dd;
    color: #6b5b95;
}

.wi-share-btn:hover {
    background: #dda0dd;
    color: #fff;
}

.wi-final-message-btn {
    background: linear-gradient(135deg, #9370db, #dda0dd);
}

.wi-footer {
    background: rgba(255, 255, 255, 0.5);
}

@media (max-width: 768px) {
    .wi-title {
        font-size: 40px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 2.8rem;
    }
}
';
}

/**
 * Template 17: Minimalista Monocromatico - CSS
 */
function get_minimal_template_css() {
    return '
/* Template Minimalista Monocromatico */
.wi-invite {
    max-width: 800px;
    margin: 0 auto;
    padding: 0;
    font-family: "Helvetica Neue", Arial, sans-serif;
    background: #ffffff;
    color: #000000;
}

.wi-header {
    text-align: center;
    padding: 100px 20px 60px;
    border-bottom: 1px solid #e0e0e0;
}

.wi-title {
    font-family: "Helvetica Neue", sans-serif;
    font-size: 48px;
    font-weight: 300;
    color: #000000;
    margin: 0;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.wi-divider {
    display: none;
}

.wi-countdown-section {
    background: #f8f8f8;
    padding: 60px 30px;
    margin: 0;
    text-align: center;
}

.wi-countdown-label {
    font-size: 16px;
    color: #666;
    margin: 0 0 40px;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 400;
    text-align: center;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin: 30px auto;
    max-width: 600px;
}

.countdown-item {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    padding: 35px 30px;
    border-radius: 0;
    min-width: 120px;
    text-align: center;
}

.countdown-value {
    font-size: 3.5rem;
    font-weight: 200;
    color: #000000;
}

.countdown-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.wi-message-section {
    background: #ffffff;
    padding: 80px 60px;
    margin: 0;
    border-top: 1px solid #e0e0e0;
    border-bottom: 1px solid #e0e0e0;
}

.wi-message-content {
    font-size: 18px;
    line-height: 2;
    color: #333;
    text-align: center;
    font-weight: 300;
}

.wi-user-image-section {
    padding: 0;
}

.wi-user-image {
    border-radius: 0;
    box-shadow: none;
    width: 100%;
}

.wi-event-info-section {
    padding: 80px 20px;
    background: #f8f8f8;
}

.wi-section-title {
    font-size: 24px;
    font-weight: 300;
    color: #000;
    text-transform: uppercase;
    letter-spacing: 4px;
    margin-bottom: 50px;
}

.wi-detail-item {
    background: #ffffff;
    border-left: none;
    border: 1px solid #e0e0e0;
    padding: 30px;
    margin: 20px 0;
    border-radius: 0;
    box-shadow: none;
}

.wi-detail-label {
    color: #999;
    font-size: 12px;
    letter-spacing: 2px;
}

.wi-detail-value {
    color: #000;
    font-size: 20px;
    font-weight: 300;
}

.wi-calendar-section {
    padding: 60px 20px;
    background: #ffffff;
}

.wi-add-calendar {
    background: #000000;
    color: #ffffff;
    padding: 18px 50px;
    border: none;
    border-radius: 0;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 400;
}

.wi-add-calendar:hover {
    background: #333;
}

.wi-map-section {
    padding: 80px 0;
    background: #f8f8f8;
}

.wi-map {
    border-radius: 0;
    box-shadow: none;
    border: 1px solid #e0e0e0;
}

.wi-map-link {
    background: #000;
    color: #fff;
    border-radius: 0;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 13px;
    padding: 16px 40px;
}

.wi-share-section {
    background: #ffffff;
    padding: 60px 20px;
    border-top: 1px solid #e0e0e0;
}

.wi-share-btn {
    border: 1px solid #000;
    color: #000;
    background: #fff;
    border-radius: 0;
}

.wi-share-btn:hover {
    background: #000;
    color: #fff;
}

.wi-final-message-btn {
    background: #000;
    border-radius: 0;
}

.wi-footer {
    padding: 80px 20px 60px;
    border-top: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
    .wi-title {
        font-size: 36px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 2.8rem;
    }

    .wi-message-section {
        padding: 50px 30px;
    }
}
';
}

/**
 * Template 18: Boho Naturale - CSS
 */
function get_boho_template_css() {
    return '
/* Template Boho Naturale */
.wi-invite {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    font-family: "Georgia", serif;
    background: linear-gradient(135deg, #f5f3ed 0%, #e8dcc4 50%, #d4c5a3 100%);
    color: #5a4a3a;
}

.wi-header {
    text-align: center;
    padding: 70px 20px 50px;
    position: relative;
}

.wi-title {
    font-family: "Crimson Text", serif;
    font-size: 58px;
    font-weight: 600;
    color: #8b7355;
    margin: 0 0 20px;
    font-style: italic;
}

.wi-divider {
    width: 150px;
    height: 2px;
    background: repeating-linear-gradient(90deg, #a0826d 0px, #a0826d 10px, transparent 10px, transparent 20px);
    margin: 20px auto;
}

.wi-decoration-top img,
.wi-decoration-bottom img {
    max-width: 200px;
    opacity: 0.7;
}

.wi-countdown-section {
    background: rgba(245, 243, 237, 0.9);
    padding: 50px 30px;
    margin: 40px 20px;
    border-radius: 0;
    border: 2px solid #a0826d;
    border-style: dashed;
    text-align: center;
}

.wi-countdown-label {
    font-size: 24px;
    color: #8b7355;
    margin: 0 0 30px;
    font-style: italic;
    text-align: center;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin: 30px auto;
    max-width: 600px;
}

.countdown-item {
    background: rgba(255, 255, 255, 0.7);
    border: 2px solid #a0826d;
    padding: 30px 25px;
    border-radius: 50%;
    min-width: 130px;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
}

.countdown-value {
    font-size: 3rem;
    font-weight: 600;
    color: #8b7355;
}

.countdown-label {
    font-size: 12px;
    color: #5a4a3a;
    font-style: italic;
}

.wi-message-section {
    background: rgba(255, 255, 255, 0.6);
    padding: 50px 40px;
    margin: 40px 20px;
    border: 3px solid #a0826d;
    border-style: double;
}

.wi-message-content {
    font-size: 19px;
    line-height: 1.9;
    color: #5a4a3a;
    text-align: center;
    font-style: italic;
}

.wi-user-image {
    border-radius: 0;
    box-shadow: 0 15px 50px rgba(139, 115, 85, 0.3);
    border: 8px solid rgba(245, 243, 237, 0.9);
    filter: sepia(15%);
}

.wi-section-title {
    font-size: 36px;
    color: #8b7355;
    text-align: center;
    margin-bottom: 40px;
    font-style: italic;
}

.wi-detail-item {
    background: rgba(255, 255, 255, 0.7);
    border-left: 5px solid #a0826d;
    border-style: solid double;
}

.wi-detail-label {
    color: #8b7355;
}

.wi-detail-value {
    color: #5a4a3a;
}

.wi-add-calendar {
    background: #a0826d;
    color: #fff;
    padding: 18px 45px;
    border: none;
    border-radius: 0;
    font-size: 17px;
    box-shadow: 0 8px 30px rgba(160, 130, 109, 0.3);
}

.wi-add-calendar:hover {
    background: #8b7355;
}

.wi-map {
    border-radius: 0;
    box-shadow: 0 10px 40px rgba(139, 115, 85, 0.3);
    border: 6px solid rgba(245, 243, 237, 0.9);
    filter: sepia(10%);
}

.wi-map-link {
    background: #a0826d;
    color: #fff;
    border-radius: 0;
}

.wi-share-section {
    background: rgba(255, 255, 255, 0.6);
    border: 2px dashed #a0826d;
}

.wi-share-btn {
    border: 2px solid #a0826d;
    color: #5a4a3a;
    border-radius: 0;
}

.wi-share-btn:hover {
    background: #a0826d;
    color: #fff;
}

.wi-final-message-btn {
    background: #8b7355;
    border-radius: 0;
}

.wi-final-message-content {
    background: rgba(245, 243, 237, 0.9);
    border: 3px double #a0826d;
}

@media (max-width: 768px) {
    .wi-title {
        font-size: 44px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 2.8rem;
    }
}
';
}

/**
 * Template 19: Art Déco Gatsby - CSS
 */
function get_gatsby_template_css() {
    return '
/* Template Art Déco Gatsby */
.wi-invite {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    font-family: "Cormorant Garamond", serif;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
    color: #f5f5f5;
    position: relative;
}

.wi-invite::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(212, 175, 55, 0.03) 2px,
        rgba(212, 175, 55, 0.03) 4px
    );
    pointer-events: none;
}

.wi-header {
    text-align: center;
    padding: 80px 20px 60px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, transparent 100%);
    border-bottom: 3px solid #d4af37;
    border-image: linear-gradient(90deg, transparent, #d4af37, transparent) 1;
    position: relative;
    z-index: 1;
}

.wi-title {
    font-family: "Cinzel Decorative", serif;
    font-size: 60px;
    font-weight: 700;
    color: #d4af37;
    margin: 0 0 20px;
    text-shadow: 3px 3px 8px rgba(0, 0, 0, 0.7);
    letter-spacing: 4px;
}

.wi-divider {
    width: 200px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #d4af37 20%, #d4af37 80%, transparent);
    margin: 20px auto;
    position: relative;
}

.wi-divider::before,
.wi-divider::after {
    content: "◆";
    position: absolute;
    color: #d4af37;
    font-size: 12px;
    top: -8px;
}

.wi-divider::before {
    left: -20px;
}

.wi-divider::after {
    right: -20px;
}

.wi-countdown-section {
    background: rgba(212, 175, 55, 0.1);
    padding: 50px 30px;
    margin: 40px 0;
    border-top: 2px solid #d4af37;
    border-bottom: 2px solid #d4af37;
    text-align: center;
    position: relative;
    z-index: 1;
}

.wi-countdown-label {
    font-size: 26px;
    color: #d4af37;
    margin: 0 0 30px;
    text-transform: uppercase;
    letter-spacing: 5px;
    font-weight: 400;
    text-align: center;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
    margin: 30px auto;
    max-width: 650px;
}

.countdown-item {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(42, 42, 42, 0.6) 100%);
    border: 3px solid #d4af37;
    padding: 35px 25px;
    border-radius: 0;
    min-width: 120px;
    clip-path: polygon(15% 0%, 85% 0%, 100% 15%, 100% 85%, 85% 100%, 15% 100%, 0% 85%, 0% 15%);
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.3);
    text-align: center;
}

.countdown-value {
    font-size: 3.2rem;
    font-weight: 700;
    color: #d4af37;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
}

.countdown-label {
    font-size: 13px;
    color: #f5f5f5;
    text-transform: uppercase;
    letter-spacing: 3px;
}

.wi-message-section {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(42, 42, 42, 0.5) 100%);
    padding: 60px 50px;
    margin: 40px 20px;
    border: 2px solid #d4af37;
    border-left: 6px solid #d4af37;
    position: relative;
    z-index: 1;
}

.wi-message-content {
    font-size: 20px;
    line-height: 1.9;
    color: #f5f5f5;
    text-align: center;
    font-style: italic;
}

.wi-user-image {
    border-radius: 0;
    box-shadow: 0 0 40px rgba(212, 175, 55, 0.4);
    border: 8px solid #d4af37;
    filter: contrast(1.1) brightness(0.95);
}

.wi-event-info-section {
    padding: 60px 20px;
    position: relative;
    z-index: 1;
}

.wi-section-title {
    font-size: 38px;
    color: #d4af37;
    text-align: center;
    margin-bottom: 50px;
    text-transform: uppercase;
    letter-spacing: 6px;
}

.wi-detail-item {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(42, 42, 42, 0.5) 100%);
    border-left: 5px solid #d4af37;
    border-right: 2px solid rgba(212, 175, 55, 0.3);
}

.wi-detail-label {
    color: #d4af37;
    letter-spacing: 2px;
}

.wi-detail-value {
    color: #f5f5f5;
    font-size: 22px;
}

.wi-add-calendar {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: #1a1a1a;
    padding: 20px 50px;
    border: 2px solid #d4af37;
    border-radius: 0;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 700;
    box-shadow: 0 0 30px rgba(212, 175, 55, 0.4);
}

.wi-add-calendar:hover {
    background: linear-gradient(135deg, #b8941f 0%, #d4af37 100%);
}

.wi-map {
    border-radius: 0;
    box-shadow: 0 0 40px rgba(212, 175, 55, 0.3);
    border: 5px solid #d4af37;
}

.wi-map-link {
    background: #d4af37;
    color: #1a1a1a;
    border-radius: 0;
    font-weight: 700;
    letter-spacing: 2px;
}

.wi-share-section {
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, transparent 100%);
    border-top: 2px solid rgba(212, 175, 55, 0.3);
}

.wi-share-btn {
    border: 2px solid #d4af37;
    color: #d4af37;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 0;
}

.wi-share-btn:hover {
    background: #d4af37;
    color: #1a1a1a;
}

.wi-final-message-btn {
    background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
    color: #1a1a1a;
    border-radius: 0;
}

.wi-footer {
    padding: 80px 20px 50px;
    border-top: 3px solid #d4af37;
}

@media (max-width: 768px) {
    .wi-title {
        font-size: 44px;
        letter-spacing: 2px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 2.8rem;
    }
}
';
}

/**
 * Template 20: Tropicale Vivace - CSS
 */
function get_tropical_template_css() {
    return '
/* Template Tropicale Vivace */
.wi-invite {
    max-width: 900px;
    margin: 0 auto;
    padding: 0;
    font-family: "Poppins", sans-serif;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 50%, #43e97b 100%);
    color: #1a535c;
}

.wi-header {
    text-align: center;
    padding: 70px 20px 50px;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.wi-title {
    font-family: "Pacifico", cursive;
    font-size: 56px;
    font-weight: 400;
    color: #ff6b6b;
    margin: 0 0 20px;
    text-shadow: 3px 3px 6px rgba(255, 107, 107, 0.3);
}

.wi-divider {
    width: 150px;
    height: 4px;
    background: linear-gradient(to right, #ff6b6b, #feca57, #48dbfb, #ff9ff3);
    margin: 20px auto;
    border-radius: 10px;
}

.wi-decoration-top img,
.wi-decoration-bottom img {
    max-width: 250px;
    filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
}

.wi-countdown-section {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(15px);
    padding: 50px 30px;
    margin: 40px 20px;
    border-radius: 30px;
    text-align: center;
    box-shadow: 0 15px 50px rgba(79, 172, 254, 0.3);
}

.wi-countdown-label {
    font-size: 28px;
    color: #ff6b6b;
    margin: 0 0 30px;
    font-weight: 600;
    text-align: center;
}

.wi-countdown {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin: 30px auto;
    max-width: 600px;
}

.countdown-item {
    background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
    border: none;
    padding: 32px 28px;
    border-radius: 20px;
    min-width: 120px;
    box-shadow: 0 8px 25px rgba(254, 202, 87, 0.4);
    text-align: center;
}

.countdown-value {
    font-size: 3.2rem;
    font-weight: 800;
    color: #fff;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.countdown-label {
    font-size: 13px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.wi-message-section {
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    padding: 50px 40px;
    margin: 40px 20px;
    border-radius: 25px;
    border: 3px solid rgba(255, 107, 107, 0.3);
}

.wi-message-content {
    font-size: 19px;
    line-height: 1.9;
    color: #1a535c;
    text-align: center;
}

.wi-user-image {
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(79, 172, 254, 0.4);
    border: 8px solid rgba(255, 255, 255, 0.9);
}

.wi-section-title {
    font-size: 38px;
    color: #ff6b6b;
    text-align: center;
    margin-bottom: 40px;
    font-weight: 700;
}

.wi-event-details {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    padding: 30px;
    border-radius: 20px;
    max-width: 700px;
    margin: 0 auto;
}

.wi-detail-item {
    background: transparent;
    border-left: 5px solid #feca57;
    border-radius: 10px;
    box-shadow: none;
}

.wi-detail-label {
    color: #ff6b6b;
}

.wi-detail-value {
    color: #1a535c;
    font-weight: 600;
}

.wi-add-calendar {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff9ff3 100%);
    color: #fff;
    padding: 20px 50px;
    border: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 700;
    box-shadow: 0 10px 35px rgba(255, 107, 107, 0.4);
}

.wi-add-calendar:hover {
    background: linear-gradient(135deg, #ff9ff3 0%, #ff6b6b 100%);
    transform: translateY(-3px) scale(1.02);
}

.wi-map {
    border-radius: 25px;
    box-shadow: 0 15px 50px rgba(79, 172, 254, 0.3);
    border: 6px solid rgba(255, 255, 255, 0.9);
}

.wi-map-link {
    background: linear-gradient(135deg, #48dbfb 0%, #00f2fe 100%);
    color: #fff;
    border-radius: 50px;
    font-weight: 600;
}

.wi-share-section {
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border-radius: 25px;
}

.wi-share-title {
    color: #ff6b6b;
}

.wi-share-btn {
    border: 3px solid #feca57;
    color: #1a535c;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 50px;
    font-weight: 600;
}

.wi-share-btn:hover {
    background: #feca57;
    color: #fff;
    transform: scale(1.1);
}

.wi-final-message-btn {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    border-radius: 50px;
    font-weight: 700;
}

.wi-final-message-content {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 4px solid rgba(254, 202, 87, 0.5);
    border-radius: 20px;
}

.wi-footer {
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

@media (max-width: 768px) {
    .wi-title {
        font-size: 42px;
    }

    .wi-countdown {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin: 0 auto;
        max-width: 100%;
        padding: 30px 10px;
    }

    .countdown-item {
        min-width: 85px;
        padding: 20px 15px;
    }

    .countdown-value {
        font-size: 2.8rem;
    }
}
';
}