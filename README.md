# Wedding Invites Pro - Plugin WordPress

Plugin professionale per la creazione di inviti digitali personalizzati per matrimoni, battesimi, compleanni e altri eventi speciali.

## 🎯 Caratteristiche Principali

- ✨ **Form Intuitivo**: Interfaccia user-friendly per la creazione di inviti
- 🎨 **20+ Template Predefiniti**: Design moderni, eleganti, vintage e tematici
- 🎭 **12 Categorie Eventi**: Matrimonio, Battesimo, Compleanno, Laurea e altro
- ⏰ **Countdown Dinamico**: 20 stili diversi con animazioni personalizzabili
- 📍 **Mappa Integrata**: OpenStreetMap (NESSUNA API KEY RICHIESTA!)
- 📱 **Condivisione Social**: WhatsApp, Email, Copia Link
- 📅 **Aggiungi al Calendario**: Google, Outlook, Apple Calendar, file ICS
- 🔒 **Area Riservata**: Solo utenti registrati possono creare inviti
- 🎛️ **Pannello Admin Completo**: Gestione inviti, template e impostazioni
- 🎨 **Personalizzazione Avanzata**: Editor CSS completo, colori, font, opacità immagini
- 🖼️ **Ottimizzazione Immagini**: Ridimensionamento automatico e compressione
- 📱 **NEW! Story Card 9:16**: Genera immagini formato Instagram Stories scaricabili come PNG

## 📋 Requisiti

- WordPress 5.8 o superiore
- PHP 7.4 o superiore
- MySQL 5.6 o superiore
- Tema WordPress compatibile (testato con WeddingDir)

## 🚀 Installazione

### Metodo 1: Upload manuale

1. Scarica il plugin come file ZIP
2. Accedi alla dashboard di WordPress
3. Vai su **Plugin → Aggiungi nuovo**
4. Clicca su **Carica plugin**
5. Seleziona il file ZIP e clicca **Installa ora**
6. Attiva il plugin

### Metodo 2: Upload FTP

1. Estrai il contenuto del file ZIP
2. Carica la cartella `wedding-invites-plugin` in `/wp-content/plugins/`
3. Accedi alla dashboard di WordPress
4. Vai su **Plugin** e attiva **Wedding Invites Pro**

## ⚙️ Configurazione Iniziale

### 1. Impostazioni Base

1. Vai su **Wedding Invites → Impostazioni**
2. ~~Inserisci la **Google Maps API Key**~~ **NON PIÙ NECESSARIA!** Il plugin usa OpenStreetMap gratuito
3. Personalizza i **colori** del brand
4. Seleziona il **font principale**
5. Carica il **logo del sito** (apparirà nel footer degli inviti)
6. Salva le impostazioni

### 2. Gestione Template

1. Vai su **Wedding Invites → Template**
2. Visualizza i template predefiniti
3. Modifica o crea nuovi template personalizzati
4. Usa i placeholder per i dati dinamici:
   - `{{title}}` - Titolo dell'invito
   - `{{message}}` - Messaggio personalizzato
   - `{{event_date}}` - Data evento
   - `{{event_time}}` - Ora evento
   - `{{event_location}}` - Nome luogo
   - `{{user_image}}` - Immagine caricata dall'utente
   - `{{header_image}}` - Immagine header template
   - `{{site_logo}}` - Logo del sito

### 3. Pagina Creazione Inviti

Il plugin crea automaticamente la pagina **"Crea Invito"** con lo shortcode:
```
[wedding_invites_form]
```

Puoi inserire questo shortcode in qualsiasi pagina o post.

## 👥 Utilizzo Utenti

### Per gli Utenti del Sito

1. **Accedi o Registrati** al sito
2. Vai alla pagina **"Crea Invito"**
3. **Compila il form**:
   - Titolo dell'invito
   - Messaggio personalizzato
   - Data e ora evento
   - Luogo e indirizzo completo
   - Carica un'immagine
   - Scegli un template
4. Clicca su **"Anteprima"** per vedere il risultato
5. Se soddisfatto, clicca su **"Pubblica Invito"**
6. **Condividi** l'invito tramite il link generato

### Funzionalità Invito Pubblicato

Gli invitati possono:
- ✅ Vedere il **countdown** in tempo reale
- 📍 Visualizzare la **location su mappa**
- 📱 **Condividere** l'invito (WhatsApp, Email, Link)
- 📅 **Aggiungere al calendario** Google
- 📲 Visualizzare su **mobile e desktop**

## 🔧 Amministrazione

### Dashboard Inviti

Vai su **Wedding Invites → Tutti gli Inviti** per:
- Visualizzare tutti gli inviti creati
- Vedere statistiche in tempo reale
- Modificare o eliminare inviti
- Filtrare per utente, data o template

### Gestione Template

Vai su **Wedding Invites → Template** per:
- Creare nuovi template
- Modificare template esistenti
- Attivare/disattivare template
- Personalizzare HTML e CSS
- Riordinare i template

### Impostazioni Avanzate

Vai su **Wedding Invites → Impostazioni** per:
- Configurare API esterne (Google Maps)
- Personalizzare colori e font
- Gestire il logo del sito
- Abilitare/disabilitare funzionalità
- Visualizzare info plugin

## 🎨 Personalizzazione

### CSS Personalizzato

Puoi aggiungere CSS personalizzato in:
1. **Appearance → Customize → Additional CSS** (WordPress)
2. Oppure nel CSS di ogni template

Esempio:
```css
/* Personalizza il countdown */
.wi-countdown .countdown-value {
    color: #ff6b6b;
    font-size: 4rem;
}

/* Personalizza i pulsanti */
.wi-share-btn {
    border-radius: 25px;
}
```

### Modificare i Template

1. Vai su **Wedding Invites → Template**
2. Clicca su **Modifica** del template desiderato
3. Modifica HTML e CSS
4. Salva e testa

### Creare Template Personalizzati

1. Vai su **Wedding Invites → Template**
2. Clicca su **Nuovo Template**
3. Inserisci:
   - Nome e descrizione
   - Struttura HTML (usa i placeholder)
   - Stili CSS personalizzati
4. Salva e attiva

## 📱 Story Card (Nuovo!)

Le **Story Card** sono immagini formato 9:16 (Instagram Stories) generate automaticamente per ogni invito.

### Caratteristiche
- ✅ Formato ottimizzato 1080x1920px (9:16)
- ✅ Download diretto come PNG ad alta risoluzione
- ✅ Condivisione diretta sui social (WhatsApp, Instagram, Facebook)
- ✅ Template personalizzabili per categoria
- ✅ Editor visuale con live preview
- ✅ Posizionamento testi drag-free con percentuali
- ✅ Supporto Google Fonts e text shadow

### Come Usare
1. **Crea Template**: Vai su **Wedding Invites → 📱 Story Card**
2. **Carica Background**: Upload immagine 9:16 (1080x1920px)
3. **Configura Layout**: Posiziona titolo, data, ora, location, messaggio
4. **Assegna Categoria**: Collega il template a una categoria evento
5. **Salva**: Il template sarà applicato automaticamente agli inviti

### Per Gli Utenti
Gli utenti vedranno la Story Card in cima all'invito con:
- **📥 Scarica Story (PNG)**: Download immagine ad alta qualità
- **📤 Condividi**: Condivisione rapida sui social

**Guida completa testing**: Vedi [STORY-CARD-TESTING.md](STORY-CARD-TESTING.md)

---

## 🔌 Shortcode Disponibili

```php
// Form creazione invito
[wedding_invites_form]

// Lista inviti utente (coming soon)
[wedding_invites_list]

// Countdown compatto (coming soon)
[wedding_invites_countdown id="123"]
```

## 🐛 Troubleshooting

### Le mappe non si visualizzano
- ✅ Il plugin usa **OpenStreetMap** (Leaflet.js) - NESSUNA API KEY necessaria
- ✅ Verifica che l'**indirizzo sia completo e corretto** (es: "Via Roma 123, 00100 Roma RM, Italia")
- ✅ Controlla la **console browser** per errori JavaScript
- ✅ Assicurati che il sito possa accedere a `https://nominatim.openstreetmap.org` (geocoding)

### Gli utenti non possono creare inviti
- ✅ Verifica che gli utenti siano **registrati e loggati**
- ✅ Controlla i permessi utente in WordPress

### Il countdown non si aggiorna
- ✅ Controlla che JavaScript sia abilitato
- ✅ Verifica la **console browser** per errori
- ✅ Assicurati che data e ora siano corrette

### Errore upload immagini
- ✅ Verifica i **permessi cartella uploads**
- ✅ Controlla il **limite dimensione** file in PHP
- ✅ Verifica formati supportati (JPG, PNG, GIF)

## 📞 Supporto

Per supporto, bug report o richieste di funzionalità:

- 📧 Email: support@tuosito.it
- 📝 Documentazione: https://tuosito.it/docs
- 🐛 Issue Tracker: https://github.com/tuoaccount/wedding-invites

## 📝 Changelog

### Versione 2.3.0 (Dicembre 2024)
- 📱 **NEW! Story Card 9:16**: Sistema completo per generare immagini formato Instagram Stories
  - Editor visuale con live preview
  - Template personalizzabili per categoria
  - Download PNG ad alta risoluzione (html2canvas)
  - Condivisione diretta social (Web Share API)
  - Posizionamento testi con percentuali
  - Supporto Google Fonts e text shadow
  - Responsive design mobile/desktop
  - Database table `wi_story_card_templates`
  - Pannello admin dedicato
  - Template default automatico

### Versione 2.2.0 (2025)
- ✨ **20 template predefiniti** con stili diversificati
- 🎭 **Sistema categorie eventi** con 12 categorie predefinite
- 🎨 **Editor CSS avanzato** con controlli granulari
- 📍 **Migrazione a OpenStreetMap** (nessuna API key richiesta!)
- ⏰ **20 stili countdown** personalizzabili
- 🖼️ **Controlli opacità e dimensione** per tutte le immagini
- 🎯 **Messaggi finali espandibili** con pulsante personalizzabile
- 📅 **Esportazione calendario** per Google, Outlook, Apple
- 🔧 **Sistema di migrazione database** robusto
- 🚀 **Ottimizzazione automatica immagini** (resize + compress)

### Versione 1.0.0 (2024)
- ✨ Release iniziale
- 🎨 3 template predefiniti
- ⏰ Countdown dinamico
- 📍 Integrazione Google Maps
- 📱 Condivisione social
- 🎛️ Pannello amministrazione completo
- 📊 Dashboard con statistiche
- 🔒 Area riservata utenti

## 📄 Licenza

Questo plugin è rilasciato sotto licenza GPL v2 o superiore.

## 👨‍💻 Autore

Sviluppato con ❤️ da [Il Tuo Nome]

## 🙏 Credits

- Font: Google Fonts
- Icons: Dashicons
- Maps: OpenStreetMap + Leaflet.js
- Geocoding: Nominatim (OpenStreetMap)
