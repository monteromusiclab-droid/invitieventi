<?php
/**
 * Shortcodes Guide - Pagina Admin
 * Mostra tutti gli shortcode disponibili con istruzioni per l'uso
 *
 * @package Wedding_Invites_Pro
 */

if (!defined('ABSPATH')) exit;

?>

<div class="wrap wi-shortcodes-guide">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-shortcode" style="font-size: 28px; margin-right: 8px;"></span>
        Shortcodes Disponibili
    </h1>

    <hr class="wp-header-end">

    <div class="wi-intro-box" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0;">📋 Cosa sono gli Shortcode?</h2>
        <p style="font-size: 16px; line-height: 1.6;">
            Gli <strong>shortcode</strong> sono codici brevi che puoi inserire nelle <strong>pagine WordPress</strong> per visualizzare funzionalità specifiche.<br>
            Basta copiare il codice e incollarlo nel contenuto di una pagina per attivare la funzione desiderata.
        </p>
    </div>

    <style>
        .wi-shortcode-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #2271b1;
        }
        .wi-shortcode-card h2 {
            margin-top: 0;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .wi-shortcode-card .shortcode-box {
            background: #f0f0f1;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 12px 16px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin: 16px 0;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .wi-shortcode-card .shortcode-box code {
            color: #d63638;
            font-weight: bold;
        }
        .wi-shortcode-card .copy-btn {
            background: #2271b1;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }
        .wi-shortcode-card .copy-btn:hover {
            background: #135e96;
        }
        .wi-shortcode-card .copy-btn.copied {
            background: #00a32a;
        }
        .wi-features-list {
            background: #f6f7f7;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 16px 0;
        }
        .wi-features-list li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .wi-step-box {
            background: #fff9e6;
            border-left: 3px solid #f0b429;
            padding: 16px;
            margin: 16px 0;
            border-radius: 4px;
        }
        .wi-step-box ol {
            margin: 8px 0;
            padding-left: 20px;
        }
        .wi-step-box li {
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-required {
            background: #fcf0f1;
            color: #d63638;
        }
        .badge-optional {
            background: #eef8ff;
            color: #2271b1;
        }
    </style>

    <!-- SHORTCODE 1: Wizard Creazione Guidata -->
    <div class="wi-shortcode-card">
        <h2>
            🧙 Wizard Creazione Guidata
            <span class="badge badge-required">Login Richiesto</span>
        </h2>

        <p style="font-size: 16px; color: #646970;">
            Il wizard guidato in 5 step per creare inviti in modo semplice e intuitivo. Perfetto per utenti non esperti.
        </p>

        <div class="shortcode-box">
            <code>[wedding_invites_wizard]</code>
            <button class="copy-btn" onclick="copyShortcode('[wedding_invites_wizard]', this)">
                📋 Copia
            </button>
        </div>

        <div class="wi-step-box">
            <strong>📝 Come inserirlo:</strong>
            <ol>
                <li>Vai su <strong>Pagine → Aggiungi Nuova</strong></li>
                <li>Titolo: <em>"Crea il tuo Invito"</em> (o simile)</li>
                <li>Nel contenuto, incolla: <code>[wedding_invites_wizard]</code></li>
                <li>Pubblica la pagina</li>
            </ol>
        </div>

        <div class="wi-features-list">
            <strong>✨ Funzionalità:</strong>
            <ul>
                <li>✅ 5 step guidati (Categoria → Template → Info Evento → Personalizzazione → Anteprima)</li>
                <li>✅ Progress bar animata</li>
                <li>✅ Validazione campi in tempo reale</li>
                <li>✅ Upload immagini personalizzate</li>
                <li>✅ Anteprima finale prima della pubblicazione</li>
                <li>✅ Solo utenti loggati (redirect automatico al login)</li>
            </ul>
        </div>
    </div>

    <!-- SHORTCODE 2: Form Creazione Classico -->
    <div class="wi-shortcode-card">
        <h2>
            📝 Form Creazione Classico
            <span class="badge badge-required">Login Richiesto</span>
        </h2>

        <p style="font-size: 16px; color: #646970;">
            Form tradizionale di creazione invito in un'unica pagina. Per utenti più esperti che preferiscono vedere tutti i campi insieme.
        </p>

        <div class="shortcode-box">
            <code>[wedding_invites_form]</code>
            <button class="copy-btn" onclick="copyShortcode('[wedding_invites_form]', this)">
                📋 Copia
            </button>
        </div>

        <div class="wi-step-box">
            <strong>📝 Come inserirlo:</strong>
            <ol>
                <li>Vai su <strong>Pagine → Aggiungi Nuova</strong></li>
                <li>Titolo: <em>"Crea Invito"</em></li>
                <li>Nel contenuto, incolla: <code>[wedding_invites_form]</code></li>
                <li>Pubblica la pagina</li>
            </ol>
        </div>

        <div class="wi-features-list">
            <strong>✨ Funzionalità:</strong>
            <ul>
                <li>✅ Form completo singola pagina</li>
                <li>✅ Selezione template visuale</li>
                <li>✅ Tutti i campi evento</li>
                <li>✅ Upload immagine personalizzata</li>
                <li>✅ Anteprima in modal</li>
            </ul>
        </div>
    </div>

    <!-- SHORTCODE 3: Dashboard Utente -->
    <div class="wi-shortcode-card">
        <h2>
            📊 Dashboard Utente
            <span class="badge badge-required">Login Richiesto</span>
        </h2>

        <p style="font-size: 16px; color: #646970;">
            Mostra la dashboard personale dell'utente con tutti i suoi inviti creati, statistiche e azioni rapide.
        </p>

        <div class="shortcode-box">
            <code>[my_invites_dashboard]</code>
            <button class="copy-btn" onclick="copyShortcode('[my_invites_dashboard]', this)">
                📋 Copia
            </button>
        </div>

        <div class="wi-step-box">
            <strong>📝 Come inserirlo:</strong>
            <ol>
                <li>Vai su <strong>Pagine → Aggiungi Nuova</strong></li>
                <li>Titolo: <em>"I Miei Inviti"</em> o <em>"Dashboard"</em></li>
                <li>Nel contenuto, incolla: <code>[my_invites_dashboard]</code></li>
                <li>Pubblica la pagina</li>
            </ol>
        </div>

        <div class="wi-features-list">
            <strong>✨ Funzionalità:</strong>
            <ul>
                <li>✅ Lista completa inviti dell'utente</li>
                <li>✅ Filtri per stato (bozza, pubblicato, scaduto)</li>
                <li>✅ Ricerca per titolo</li>
                <li>✅ Azioni rapide: Visualizza, Modifica, Elimina</li>
                <li>✅ Statistiche invito (visualizzazioni, RSVP)</li>
                <li>✅ Link condivisione diretta</li>
                <li>✅ Paginazione automatica</li>
            </ul>
        </div>
    </div>

    <!-- SHORTCODE 4: Modifica RSVP -->
    <div class="wi-shortcode-card">
        <h2>
            ✏️ Modifica RSVP
            <span class="badge badge-optional">No Login</span>
        </h2>

        <p style="font-size: 16px; color: #646970;">
            Permette agli invitati di modificare la loro risposta RSVP usando un codice univoco. Non richiede login.
        </p>

        <div class="shortcode-box">
            <code>[wedding_invites_rsvp_edit]</code>
            <button class="copy-btn" onclick="copyShortcode('[wedding_invites_rsvp_edit]', this)">
                📋 Copia
            </button>
        </div>

        <div class="wi-step-box">
            <strong>📝 Come inserirlo:</strong>
            <ol>
                <li>Vai su <strong>Pagine → Aggiungi Nuova</strong></li>
                <li>Titolo: <em>"Modifica Conferma"</em> o <em>"Modifica RSVP"</em></li>
                <li>Nel contenuto, incolla: <code>[wedding_invites_rsvp_edit]</code></li>
                <li>Pubblica la pagina</li>
            </ol>
        </div>

        <div class="wi-features-list">
            <strong>✨ Funzionalità:</strong>
            <ul>
                <li>✅ Form di modifica RSVP esistente</li>
                <li>✅ Richiede codice univoco RSVP (passato via URL: <code>?rsvp_code=ABC123</code>)</li>
                <li>✅ Carica automaticamente dati RSVP esistenti</li>
                <li>✅ Modifica nome, email, numero partecipanti, note</li>
                <li>✅ Validazione campi</li>
                <li>✅ Conferma salvataggio</li>
                <li>✅ Non richiede login (autenticazione via codice)</li>
            </ul>
        </div>

        <div style="background: #e7f5ff; border-left: 3px solid #2271b1; padding: 16px; margin: 16px 0; border-radius: 4px;">
            <strong>💡 Nota:</strong> Quando un invitato conferma partecipazione, riceve automaticamente un link tipo:<br>
            <code style="color: #d63638;">https://tuosito.it/modifica-rsvp/?rsvp_code=ABC123XYZ</code>
        </div>
    </div>

    <!-- SEZIONE SUGGERIMENTI -->
    <div style="background: white; border-radius: 8px; padding: 24px; margin: 24px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0;">💡 Suggerimenti & Best Practices</h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #f6f7f7; padding: 16px; border-radius: 6px;">
                <h3 style="margin-top: 0;">🎯 Struttura Consigliata</h3>
                <ul style="margin: 0;">
                    <li><strong>Homepage:</strong> Link a "Crea Invito"</li>
                    <li><strong>/crea-invito/:</strong> <code>[wedding_invites_wizard]</code></li>
                    <li><strong>/i-miei-inviti/:</strong> <code>[my_invites_dashboard]</code></li>
                    <li><strong>/modifica-rsvp/:</strong> <code>[wedding_invites_rsvp_edit]</code></li>
                </ul>
            </div>

            <div style="background: #f6f7f7; padding: 16px; border-radius: 6px;">
                <h3 style="margin-top: 0;">📱 Responsive</h3>
                <p style="margin: 0;">
                    Tutti gli shortcode sono completamente responsive e ottimizzati per:
                </p>
                <ul style="margin: 8px 0 0 0;">
                    <li>Desktop (layout completo)</li>
                    <li>Tablet (layout adattato)</li>
                    <li>Mobile (stack verticale)</li>
                </ul>
            </div>

            <div style="background: #f6f7f7; padding: 16px; border-radius: 6px;">
                <h3 style="margin-top: 0;">🔒 Sicurezza</h3>
                <p style="margin: 0;">
                    Gli shortcode che richiedono login reindirizzano automaticamente alla pagina di accesso se l'utente non è autenticato.
                </p>
            </div>
        </div>
    </div>

    <!-- SEZIONE TROUBLESHOOTING -->
    <div style="background: white; border-radius: 8px; padding: 24px; margin: 24px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid #d63638;">
        <h2 style="margin-top: 0;">🐛 Troubleshooting</h2>

        <details style="margin-bottom: 16px;">
            <summary style="font-weight: bold; cursor: pointer; padding: 8px; background: #f6f7f7; border-radius: 4px;">
                ❌ Lo shortcode non appare o mostra solo il codice
            </summary>
            <div style="padding: 16px; background: #fff;">
                <p><strong>Causa:</strong> Shortcode non registrato correttamente o plugin non attivo</p>
                <p><strong>Soluzione:</strong></p>
                <ol>
                    <li>Verifica che il plugin <strong>Wedding Invites</strong> sia attivo</li>
                    <li>Disattiva e riattiva il plugin</li>
                    <li>Svuota cache (browser e plugin cache se presenti)</li>
                    <li>Verifica che lo shortcode sia scritto correttamente (copia da questa pagina)</li>
                </ol>
            </div>
        </details>

        <details style="margin-bottom: 16px;">
            <summary style="font-weight: bold; cursor: pointer; padding: 8px; background: #f6f7f7; border-radius: 4px;">
                ❌ "Devi essere registrato" anche se sono loggato
            </summary>
            <div style="padding: 16px; background: #fff;">
                <p><strong>Causa:</strong> Cache plugin o problemi sessione WordPress</p>
                <p><strong>Soluzione:</strong></p>
                <ol>
                    <li>Logout e login di nuovo</li>
                    <li>Svuota cache browser (Ctrl+Shift+R)</li>
                    <li>Se usi plugin cache, disattiva cache per pagine utenti loggati</li>
                    <li>Verifica cookie WordPress funzionanti</li>
                </ol>
            </div>
        </details>

        <details style="margin-bottom: 16px;">
            <summary style="font-weight: bold; cursor: pointer; padding: 8px; background: #f6f7f7; border-radius: 4px;">
                ❌ Wizard non carica le categorie
            </summary>
            <div style="padding: 16px; background: #fff;">
                <p><strong>Causa:</strong> Tabella categorie vuota o errore JavaScript</p>
                <p><strong>Soluzione:</strong></p>
                <ol>
                    <li>Vai su <strong>Wedding Invites → Categorie</strong> e verifica che esistano categorie</li>
                    <li>Se vuota, il wizard usa categorie predefinite (fallback automatico)</li>
                    <li>Apri Console Browser (F12 → Console) e verifica errori JavaScript</li>
                    <li>Controlla che jQuery sia caricato</li>
                </ol>
            </div>
        </details>

        <details>
            <summary style="font-weight: bold; cursor: pointer; padding: 8px; background: #f6f7f7; border-radius: 4px;">
                ❌ Immagini non caricano
            </summary>
            <div style="padding: 16px; background: #fff;">
                <p><strong>Causa:</strong> Permessi directory upload</p>
                <p><strong>Soluzione:</strong></p>
                <ol>
                    <li>Verifica permessi cartella <code>wp-content/uploads</code> (deve essere 755)</li>
                    <li>Controlla spazio disco disponibile</li>
                    <li>Verifica limiti upload in <code>php.ini</code> (<code>upload_max_filesize</code>, <code>post_max_size</code>)</li>
                </ol>
            </div>
        </details>
    </div>

</div>

<script>
function copyShortcode(shortcode, button) {
    // Copia negli appunti
    navigator.clipboard.writeText(shortcode).then(function() {
        // Feedback visivo
        button.textContent = '✅ Copiato!';
        button.classList.add('copied');

        // Ripristina dopo 2 secondi
        setTimeout(function() {
            button.textContent = '📋 Copia';
            button.classList.remove('copied');
        }, 2000);
    }).catch(function(err) {
        alert('Errore nella copia: ' + err);
    });
}
</script>
