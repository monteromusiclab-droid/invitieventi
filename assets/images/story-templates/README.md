# Story Card Templates - Background Images

Questa cartella contiene le immagini di background predefinite per i Story Card templates (formato 9:16 - 1080x1920px).

## 🎨 Template Disponibili

### 1. `default-elegant.svg`
- **Uso**: Template default per tutte le categorie
- **Stile**: Gradiente elegante viola/rosa con pattern geometrico
- **Colori**: `#667eea` → `#764ba2` → `#f093fb`
- **Decorazioni**: Linee curve superiori/inferiori, pattern dots, frame centrale

### 2. `wedding-classic.svg`
- **Uso**: Categoria "Matrimonio"
- **Stile**: Elegante, classico, minimalista
- **Colori**: Crema/beige (`#fdfcfb`, `#e2d1c3`) con accenti oro (`#d4af37`)
- **Decorazioni**: Pattern floreale, ornamenti circolari, bordi dorati

### 3. `birthday-fun.svg`
- **Uso**: Categoria "Compleanno"
- **Stile**: Colorato, festoso, divertente
- **Colori**: Multicolor (`#ff6b6b`, `#feca57`, `#48dbfb`, `#ff9ff3`, `#54a0ff`)
- **Decorazioni**: Coriandoli, palloncini, stelle

## 📐 Specifiche Tecniche

- **Formato**: SVG (vettoriale scalabile)
- **Dimensioni**: 1080x1920 pixel (ratio 9:16)
- **Ottimizzato per**: Instagram Stories, Facebook Stories, WhatsApp Status
- **Compatibilità**: Tutti i browser moderni

## 🔧 Come Aggiungere Nuovi Template

1. Crea un file SVG con dimensioni **1080x1920px**
2. Usa gradienti e pattern per sfondo
3. Lascia spazio centrale libero per i testi (circa 30-80% verticale)
4. Assicurati che i colori di sfondo abbiano buon contrasto con testo bianco
5. Salva il file in questa cartella
6. Aggiorna `WI_Story_Cards::install_predefined_templates()` per usare il nuovo template

## 💡 Best Practices

### Colori
- Usa gradienti morbidi per eleganza
- Testi bianchi (`#ffffff`) devono avere `text-shadow` per leggibilità
- Colori vivaci per eventi festosi (compleanno, laurea)
- Colori tenui per eventi formali (matrimonio, battesimo)

### Layout
- **Top 20%**: Decorazioni superiori
- **Centro 30-80%**: Area testi (LASCIARE LIBERA!)
- **Bottom 20%**: Decorazioni inferiori

### Decorazioni
- Pattern ripetuti con bassa opacità (0.1-0.3)
- Elementi geometrici semplici
- Evitare dettagli troppo complessi (difficili da vedere su mobile)

## 🚀 Utilizzo nel Plugin

Le immagini vengono referenziate automaticamente tramite:

```php
WI_PLUGIN_URL . 'assets/images/story-templates/default-elegant.svg'
```

Quando il plugin viene trasferito su un altro sito, tutte le immagini vengono copiate automaticamente e i link funzionano senza modifiche.

## 📦 Portabilità

✅ **Vantaggi di usare immagini interne al plugin**:
- Nessuna dipendenza da URL esterni
- Funziona anche offline
- Trasferimento plugin = trasferimento immagini
- Nessun problema CORS
- Velocità di caricamento ottimale

❌ **Evitare**:
- Link a CDN esterni (Unsplash, Pexels, ecc.)
- Immagini caricate nella Media Library WordPress (non portabili)
- URL assoluti hardcoded

## 🎯 Esempi Categorie Future

Template che potrebbero essere aggiunti:

- `baptism-soft.svg` - Battesimo (colori pastello azzurro/rosa)
- `graduation-formal.svg` - Laurea (colori accademici blu/oro)
- `anniversary-romantic.svg` - Anniversario (cuori, rose, romantico)
- `baby-shower-cute.svg` - Baby Shower (giocattoli, colori teneri)
- `engagement-elegant.svg` - Fidanzamento (anelli, fiori, elegante)

## 📄 Licenza

Questi file SVG sono parte del plugin Wedding Invites Pro e possono essere modificati liberamente per uso interno.
