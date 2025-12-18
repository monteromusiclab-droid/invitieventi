# 📱 Story Card Feature - Guida Testing

## Cosa Sono le Story Card?

Le **Story Card** sono immagini in formato **9:16** (Instagram Stories format) generate automaticamente per ogni invito. Gli utenti possono scaricarle come PNG ad alta risoluzione e condividerle facilmente sui social media.

---

## ✅ Implementazione Completata

### 1. Database & Backend
- ✅ Tabella `wp_wi_story_card_templates` creata
- ✅ Classe `WI_Story_Cards` implementata
- ✅ Template default creato automaticamente all'attivazione

### 2. Frontend
- ✅ Story Card integrata in `single-invite.php`
- ✅ CSS responsive per mobile/desktop (`story-card.css`)
- ✅ JavaScript per download PNG (`story-card.js`)
- ✅ Integrazione html2canvas per conversione HTML→PNG
- ✅ Bottoni download e condivisione

### 3. Pannello Admin
- ✅ Menu "📱 Story Card" nel backend WordPress
- ✅ Gestione template con CRUD completo
- ✅ Upload immagine background 9:16 via Media Library
- ✅ Editor visuale posizionamento testi
- ✅ Live preview in tempo reale
- ✅ Supporto template per categoria o default

---

## 🧪 Come Testare

### Test 1: Attivazione Plugin
1. **Disattiva e riattiva il plugin** per creare le tabelle:
   ```
   WordPress Admin → Plugin → Disattiva "Wedding Invites"
   WordPress Admin → Plugin → Attiva "Wedding Invites"
   ```

2. **Verifica creazione tabella**:
   - Vai su phpMyAdmin o usa query SQL:
   ```sql
   SHOW TABLES LIKE '%wi_story_card_templates%';
   ```
   - Dovresti vedere: `wp_wi_story_card_templates`

3. **Verifica template default**:
   ```sql
   SELECT * FROM wp_wi_story_card_templates WHERE is_default = 1;
   ```
   - Dovrebbe esserci 1 template "Template Default Elegante"

---

### Test 2: Pannello Admin Story Card

1. **Accedi al pannello**:
   - WordPress Admin → Wedding Invites → **📱 Story Card**

2. **Verifica lista template**:
   - Dovresti vedere il template default creato automaticamente
   - Se la tabella è vuota, clicca "Crea il Primo Template"

3. **Crea nuovo template**:
   - Clicca "Aggiungi Nuovo Template"
   - **Nome Template**: "Matrimonio Elegante"
   - **Categoria**: Seleziona una categoria (es. "Matrimonio")
   - **Immagine Background**:
     - Clicca "Carica Immagine"
     - Carica un'immagine **1080x1920px** (9:16)
     - Suggerimento: Usa un'immagine con sfondo uniforme e spazio per i testi
   - **Configura posizioni testi**:
     - Modifica i valori `top`, `left`, `width` (in percentuale)
     - Cambia `fontSize`, `fontWeight`, `color`
     - Abilita/disabilita `textShadow`
   - **Osserva l'anteprima live** che si aggiorna automaticamente
   - Clicca "Salva Template"

4. **Testa editor visuale**:
   - Modifica template esistente
   - Cambia i valori numerici e osserva l'anteprima aggiornarsi
   - Testa color picker per i colori
   - Testa diversi font family (es: `'Playfair Display', serif`)

5. **Testa template default**:
   - Crea un template senza categoria associata
   - Abilita "Template Default"
   - Salva
   - Verifica che appaia come default nella lista

---

### Test 3: Visualizzazione Story Card su Invito

1. **Crea o apri un invito esistente**:
   - WordPress Admin → Wedding Invites → Tutti gli Inviti
   - Oppure crea nuovo invito via wizard

2. **Visualizza l'invito sul frontend**:
   - Clicca "Visualizza" sull'invito
   - URL: `https://tuosito.com/invito/nome-invito/`

3. **Verifica Story Card**:
   - La Story Card dovrebbe apparire **sopra l'invito completo**
   - Formato 9:16 (verticale, come Instagram Stories)
   - Background image caricato nell'admin
   - Testi sovrapposti con le informazioni dell'invito:
     - Titolo invito
     - Data evento
     - Ora evento
     - Località
     - Messaggio (primi 15 parole)

4. **Verifica layout**:
   - I testi devono essere posizionati correttamente
   - I colori e font devono corrispondere al template
   - Text shadow attivo se abilitato nel template

---

### Test 4: Download PNG

1. **Clicca "📥 Scarica Story (PNG)"**:
   - Bottone sotto la Story Card
   - Dovrebbe mostrare "⏳ Generazione..."
   - Loading spinner sulla Story Card

2. **Verifica download**:
   - File PNG scaricato automaticamente
   - Nome file: `story-invite-{ID}-{timestamp}.png`
   - Dimensioni: Alta risoluzione (2x scale per Retina)
   - Formato: 9:16 aspect ratio

3. **Verifica qualità immagine**:
   - Apri il PNG scaricato
   - Testi nitidi e leggibili
   - Background image caricato correttamente
   - Nessun elemento UI (bottoni) incluso nell'immagine

4. **Test su browser diversi**:
   - Chrome/Edge (✅ html2canvas)
   - Firefox (✅ html2canvas)
   - Safari (✅ html2canvas)
   - Mobile Safari (⚠️ potrebbe avere limitazioni)

---

### Test 5: Condivisione

1. **Clicca "📤 Condividi"**:
   - Su **mobile con Web Share API**: si apre il picker nativo del sistema
   - Su **desktop o browser senza Web Share**: copia il link negli appunti

2. **Test Web Share API (mobile)**:
   - Apri l'invito su smartphone
   - Clicca "Condividi"
   - Verifica che si apra il picker di condivisione nativo
   - Testa condivisione su WhatsApp, Instagram, Facebook

3. **Test fallback (desktop)**:
   - Clicca "Condividi"
   - Verifica messaggio: "Link copiato negli appunti!"
   - Incolla (Ctrl+V) per verificare che il link sia stato copiato

---

### Test 6: Responsive Design

#### Desktop
1. **Apri invito su desktop** (1920x1080)
2. **Verifica Story Card**:
   - Max-width: 500px
   - Centrata nella pagina
   - Bottoni affiancati (flex-row)
   - Border radius e shadow corretti

#### Tablet (768px)
1. **Riduci finestra browser a 768px**
2. **Verifica**:
   - Story Card: max-width 100%
   - Padding ridotto
   - Bottoni ancora affiancati

#### Mobile (480px)
1. **Riduci finestra browser a 480px** o usa emulatore mobile
2. **Verifica**:
   - Story Card: width 100%
   - Bottoni impilati verticalmente (flex-column)
   - Font-size testi ridotto se necessario
   - Touch-friendly (bottoni grandi)

---

### Test 7: Integrazione con Categorie

1. **Crea template per categoria specifica**:
   - Admin → Story Card → Aggiungi Nuovo
   - Nome: "Template Compleanno"
   - Categoria: **Compleanno**
   - Background: immagine festosa
   - Salva

2. **Crea invito categoria Compleanno**:
   - Wizard → Seleziona categoria "Compleanno"
   - Completa wizard

3. **Visualizza invito**:
   - Story Card dovrebbe usare "Template Compleanno"
   - Non il template default

4. **Crea invito categoria senza template**:
   - Wizard → Seleziona categoria "Laurea" (senza template)
   - Completa wizard
   - Visualizza invito
   - Story Card dovrebbe usare il **template default**

---

### Test 8: Edge Cases

#### Nessun Template Disponibile
1. **Elimina tutti i template** (incluso default)
2. **Visualizza un invito**
3. **Verifica**:
   - Story Card **non deve apparire** (nessun errore PHP)
   - Invito completo visibile normalmente

#### Background Image Mancante
1. **Modifica template**
2. **Rimuovi URL background image** (lascia vuoto)
3. **Salva**
4. **Visualizza invito**
5. **Verifica**:
   - Fallback: gradient background viola (#667eea → #764ba2)

#### Testi Lunghi
1. **Crea invito con**:
   - Titolo molto lungo (50+ caratteri)
   - Messaggio lunghissimo (500+ caratteri)
2. **Visualizza Story Card**
3. **Verifica**:
   - Messaggio troncato a 15 parole (`wp_trim_words`)
   - Testi con `word-wrap: break-word`
   - Nessun overflow fuori dalla Story Card

#### Font Esterni Non Caricato
1. **Usa Google Font** nel template:
   - Font Family: `'Dancing Script', cursive`
2. **Visualizza invito**
3. **Verifica**:
   - Font caricato correttamente
   - Se non disponibile: fallback a font di sistema

---

## 🐛 Possibili Problemi e Soluzioni

### Problema: Story Card non appare
**Causa**: Template non trovato o database non creato
**Soluzione**:
```php
// Verifica in phpMyAdmin:
SELECT * FROM wp_wi_story_card_templates;

// Se vuota, riattiva plugin o esegui manualmente:
WI_Story_Cards::create_tables();
WI_Story_Cards::create_default_template();
```

### Problema: Download PNG non funziona
**Causa**: html2canvas non caricato
**Soluzione**:
- Verifica console browser (F12)
- Controlla che `story-card.js` sia caricato
- Controlla errori CORS su background image
- Usa immagini dallo stesso dominio o con CORS abilitato

### Problema: Immagini non visibili nel PNG
**Causa**: CORS policy blocca immagini esterne
**Soluzione**:
- Carica tutte le immagini nella Media Library di WordPress
- Non usare link esterni (Unsplash, Pexels, ecc.)
- Oppure configura CORS headers sul server esterno

### Problema: Testi sfocati nel PNG
**Causa**: Scale factor troppo basso
**Soluzione**:
- In `story-card.js` linea 88, aumenta `scale: 2` a `scale: 3`

### Problema: Anteprima admin non si aggiorna
**Causa**: JavaScript non collegato agli input
**Soluzione**:
- Verifica console browser per errori
- Controlla che jQuery sia caricato
- Verifica che `wp_enqueue_script('wp-color-picker')` sia presente

---

## 📊 Checklist Testing Completo

- [ ] Database table creata
- [ ] Template default esiste
- [ ] Pannello admin accessibile
- [ ] Upload immagine funziona
- [ ] Editor posizioni funziona
- [ ] Live preview si aggiorna
- [ ] Story Card appare su invito
- [ ] Download PNG funziona
- [ ] PNG ad alta qualità
- [ ] Condivisione funziona
- [ ] Responsive mobile corretto
- [ ] Template per categoria funziona
- [ ] Fallback template default funziona
- [ ] Nessun errore PHP/JS console
- [ ] Cross-browser compatibilità

---

## 🚀 Prossimi Miglioramenti (Opzionali)

1. **Drag & Drop Editor**:
   - Editor visuale con trascinamento testi
   - Come Canva o Figma

2. **Template Predefiniti**:
   - Galleria template pronti all'uso
   - Importazione template JSON

3. **Stickers & Decorazioni**:
   - Aggiungi icone, emoji, forme
   - Layer system

4. **Video Story Card**:
   - Esporta come MP4 (15 secondi)
   - Animazioni testi

5. **Condivisione Diretta Social**:
   - Instagram API integration
   - Facebook Share Dialog

6. **Analytics**:
   - Traccia download Story Card
   - Statistiche condivisioni

---

## 📞 Supporto

Se riscontri problemi durante il testing:

1. **Controlla console browser** (F12 → Console)
2. **Controlla log errori PHP** (wp-content/debug.log)
3. **Verifica permessi file** (775 per cartelle, 664 per file)
4. **Verifica versione WordPress** (minimo 5.0)
5. **Disabilita altri plugin** per escludere conflitti

---

**Data implementazione**: 2024-12-16
**Versione plugin**: 2.3.0
**Stato**: ✅ Implementazione Completa - Pronto per Testing
