# 🔐 Wedding Invites Pro - Custom Login Page

**Versione**: 2.4.0
**Data**: 16 Dicembre 2024

---

## 📋 Cosa Include

Il plugin Wedding Invites Pro include una **pagina di login personalizzata** che sostituisce automaticamente il form di login standard di WordPress (`wp-login.php`) con un design moderno e branded.

---

## ✨ Caratteristiche

### 🎨 Design Moderno
- ✅ Background gradiente animato (viola/rosa)
- ✅ Form glassmorphism con backdrop blur
- ✅ Animazioni smooth e transizioni eleganti
- ✅ Icone e visual feedback
- ✅ Completamente responsive (mobile/tablet/desktop)

### 🏢 Branding Automatico
- ✅ **Logo del sito caricato automaticamente** (da Aspetto → Personalizza → Identità del sito)
- ✅ Se non c'è logo, mostra il nome del sito in testo elegante
- ✅ Link logo punta alla homepage del sito
- ✅ Tooltip mostra nome del sito

### 🔒 Funzionalità Complete
- ✅ Login form
- ✅ Registrazione utenti
- ✅ Password dimenticata
- ✅ Remember me checkbox
- ✅ Messaggi errore/successo styled
- ✅ Link privacy policy

---

## 🚀 Come Funziona

### Attivazione Automatica

La pagina di login personalizzata si attiva **automaticamente** quando il plugin è attivo. Non serve configurazione!

**URL Login**: `https://tuosito.com/wp-login.php`

### Logo Dinamico

Il logo viene caricato automaticamente da:

1. **Aspetto → Personalizza → Identità del sito → Logo**
2. Se non hai caricato un logo, vai su:
   - WordPress Admin → **Aspetto → Personalizza**
   - Clicca **"Identità del sito"**
   - Clicca **"Seleziona logo"**
   - Carica logo (dimensioni consigliate: 320x120px, trasparente PNG)
   - Clicca **"Pubblica"**

3. Il logo apparirà automaticamente nella pagina login!

---

## 🎨 Preview Design

### Desktop
```
┌────────────────────────────────────────┐
│     🌈 Background gradiente animato    │
│                                        │
│         ┌──────────────────┐          │
│         │   [LOGO SITO]   │          │
│         └──────────────────┘          │
│                                        │
│    ┌──────────────────────────┐      │
│    │  Nome utente o e-mail   │      │
│    │  [________________]     │      │
│    │                         │      │
│    │  Password               │      │
│    │  [________________]     │      │
│    │                         │      │
│    │  ☑ Ricordami           │      │
│    │                         │      │
│    │  [   ACCEDI   ]        │      │
│    └──────────────────────────┘      │
│                                        │
│    Password dimenticata? | Registrati │
│         ← Torna a NomeSito            │
└────────────────────────────────────────┘
```

### Mobile
- Layout verticale
- Form 95% larghezza
- Pulsanti touch-friendly
- Logo ridimensionato automaticamente

---

## 🎨 Personalizzazione CSS

### Cambiare Colori Gradiente

Se vuoi personalizzare i colori del background, aggiungi questo CSS:

**Metodo 1: Tramite Customizer**
1. **Aspetto → Personalizza → CSS Aggiuntivo**
2. Aggiungi:

```css
body.login {
    background: linear-gradient(135deg, #tuo-colore1 0%, #tuo-colore2 100%) !important;
}
```

**Esempi colori**:
```css
/* Blu/Azzurro */
body.login {
    background: linear-gradient(135deg, #667eea 0%, #48dbfb 100%) !important;
}

/* Verde/Turchese */
body.login {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
}

/* Arancione/Rosa */
body.login {
    background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%) !important;
}

/* Nero/Grigio (elegante) */
body.login {
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%) !important;
}
```

### Cambiare Stile Form

```css
/* Form background più trasparente */
#loginform,
#registerform,
#lostpasswordform {
    background: rgba(255, 255, 255, 0.7) !important;
}

/* Bordo form colorato */
#loginform {
    border: 3px solid #667eea !important;
}

/* Pulsante accedi personalizzato */
.login .button-primary {
    background: #your-color !important;
    border-radius: 25px !important; /* Bordi più arrotondati */
}
```

### Logo Dimensioni Custom

Se il logo appare troppo grande o piccolo:

```css
#login h1 a::before {
    max-width: 250px !important; /* Imposta larghezza massima */
    max-height: 100px !important; /* Imposta altezza massima */
}
```

---

## 🔧 Configurazione Registrazione Utenti

Per abilitare la registrazione utenti:

1. **WordPress Admin → Impostazioni → Generali**
2. Abilita **"Chiunque può registrarsi"**
3. Ruolo predefinito: **Sottoscrittore**
4. Clicca **"Salva modifiche"**

Ora il link **"Registrati"** apparirà nella pagina login.

---

## 📱 URL Utili

| Pagina | URL |
|--------|-----|
| **Login** | `https://tuosito.com/wp-login.php` |
| **Registrazione** | `https://tuosito.com/wp-login.php?action=register` |
| **Password dimenticata** | `https://tuosito.com/wp-login.php?action=lostpassword` |
| **Logout** | `https://tuosito.com/wp-login.php?action=logout` |

---

## 🌐 Redirect Dopo Login

### Redirect Automatico

Per impostare dove l'utente viene reindirizzato dopo il login:

**Aggiungi in `functions.php` del tema**:

```php
add_filter('login_redirect', 'custom_login_redirect', 10, 3);
function custom_login_redirect($redirect_to, $request, $user) {
    // Se è un utente normale (non admin)
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('subscriber', $user->roles)) {
            // Redirect a Dashboard Inviti
            return home_url('/i-miei-inviti/');
        }
    }
    // Admin va al pannello admin
    return $redirect_to;
}
```

---

## 🎯 Esempi Avanzati

### Aggiungere Testo Custom Sopra Form

```php
add_action('login_message', 'custom_login_message');
function custom_login_message() {
    if (isset($_GET['action']) && $_GET['action'] == 'register') {
        return '<p class="message">Benvenuto! Crea il tuo account per iniziare.</p>';
    }
    return '';
}
```

### Aggiungere Footer Custom

```php
add_action('login_footer', 'custom_login_footer');
function custom_login_footer() {
    echo '<style>
        .custom-login-footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
        }
    </style>
    <p class="custom-login-footer">
        © ' . date('Y') . ' ' . get_bloginfo('name') . ' - Tutti i diritti riservati
    </p>';
}
```

### Logo Alternativo per Login

Se vuoi usare un logo diverso per la pagina login:

```php
add_action('login_enqueue_scripts', 'custom_login_logo_override');
function custom_login_logo_override() {
    $logo_url = 'https://tuosito.com/wp-content/uploads/logo-login.png';
    echo '<style>
        #login h1 a::before {
            background-image: url(' . $logo_url . ') !important;
        }
    </style>';
}
```

---

## 🐛 Troubleshooting

### Problema: Logo non appare

**Causa**: Logo non caricato in WordPress

**Soluzione**:
1. Vai su **Aspetto → Personalizza → Identità del sito**
2. Clicca **"Seleziona logo"**
3. Carica immagine (PNG trasparente consigliato)
4. Clicca **"Pubblica"**
5. Svuota cache browser (Ctrl+Shift+R)

### Problema: Background non si vede

**Causa**: CSS non caricato o conflitto tema

**Soluzione**:
1. Verifica che il plugin sia attivo
2. Svuota cache (browser + plugin cache se presenti)
3. Disattiva plugin cache per pagina wp-login.php
4. Controlla console browser (F12) per errori CSS

### Problema: Form non centrato

**Causa**: Conflitto CSS tema

**Soluzione**:
Aggiungi in CSS Aggiuntivo:
```css
body.login #login {
    width: 400px !important;
    margin: auto !important;
}
```

### Problema: Stile vecchio appare ancora

**Causa**: Cache browser o plugin

**Soluzione**:
```bash
# Hard refresh browser
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)

# Oppure svuota cache plugin (se usi WP Super Cache, W3 Total Cache, ecc.)
```

---

## 📊 Compatibilità

### Plugin Compatibili
- ✅ WooCommerce
- ✅ BuddyPress
- ✅ bbPress
- ✅ WP Super Cache
- ✅ W3 Total Cache
- ✅ Jetpack
- ✅ Wordfence
- ✅ Yoast SEO

### Plugin con Conflitti Potenziali
- ⚠️ **Theme My Login** (sostituisce completamente wp-login.php)
- ⚠️ **Custom Login Page Customizer** (stesso scopo)
- ⚠️ **LoginPress** (sovrascrive stili)

**Soluzione**: Disattiva plugin di login personalizzato alternativi.

---

## 🔐 Sicurezza

### Best Practices

Il custom login mantiene tutte le funzionalità di sicurezza WordPress:

- ✅ Nonce verification
- ✅ CSRF protection
- ✅ Brute force protection (con plugin come Wordfence)
- ✅ Two-factor authentication (con plugin dedicati)
- ✅ reCAPTCHA (aggiungibile con plugin)

### Aggiungere reCAPTCHA

Consigliato: Plugin **Google Captcha (reCAPTCHA) by BestWebSoft**

1. Installa plugin reCAPTCHA
2. Configura API keys
3. Abilita su form login/registrazione
4. Il design custom si adatterà automaticamente!

---

## 📸 Screenshot

Per vedere degli screenshot del custom login:

1. Apri browser in modalità incognito
2. Vai su `https://tuosito.com/wp-login.php`
3. Dovresti vedere:
   - Background gradiente animato
   - Logo del sito centrato
   - Form glassmorphism elegante
   - Pulsante colorato con hover effect

---

## 🎨 File Coinvolti

| File | Descrizione |
|------|-------------|
| `assets/css/custom-login.css` | Stili custom login |
| `wedding-invites.php` (linee 70-73) | Hook registrazione |
| `wedding-invites.php` (linee 687-708) | Logica logo dinamico |

---

## 🚀 Prossimi Sviluppi

Funzionalità future (opzionali):

- [ ] Social login (Google, Facebook)
- [ ] Magic link (login senza password via email)
- [ ] OTP via SMS
- [ ] Registrazione con verifica email obbligatoria
- [ ] Custom fields registrazione
- [ ] Avatar upload durante registrazione

---

## 💡 Suggerimenti

### Per Logo Perfetto

**Dimensioni ideali**:
- Larghezza: 320px
- Altezza: 80-120px
- Formato: PNG trasparente
- Risoluzione: 2x per Retina (@2x)

**Dove creare logo gratis**:
- Canva (https://www.canva.com/)
- Logo Maker (https://logomakr.com/)
- Hatchful di Shopify (https://hatchful.shopify.com/)

---

**Data**: 16 Dicembre 2024
**Versione Plugin**: 2.4.0
**Compatibile con**: WordPress 5.8+ | PHP 7.4+
**Testato fino a**: WordPress 6.4
