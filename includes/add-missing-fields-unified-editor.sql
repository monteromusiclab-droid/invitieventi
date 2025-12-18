-- Script per aggiungere campi mancanti per Editor Unificato
-- Esegui questo script prima di usare l'editor unificato

ALTER TABLE wp_wi_templates
-- Peso font titolo
ADD COLUMN IF NOT EXISTS title_weight VARCHAR(10) DEFAULT '600',

-- Dimensioni dettagli
ADD COLUMN IF NOT EXISTS details_size INT DEFAULT 16,

-- Pulsanti completi
ADD COLUMN IF NOT EXISTS button_font VARCHAR(100) DEFAULT 'inherit',
ADD COLUMN IF NOT EXISTS button_size INT DEFAULT 16,
ADD COLUMN IF NOT EXISTS button_color VARCHAR(50) DEFAULT '#ffffff',
ADD COLUMN IF NOT EXISTS button_hover_color VARCHAR(50) DEFAULT '#ffffff',
ADD COLUMN IF NOT EXISTS button_hover_bg_color VARCHAR(50) DEFAULT '#b8941f',
ADD COLUMN IF NOT EXISTS button_radius INT DEFAULT 25,
ADD COLUMN IF NOT EXISTS button_padding INT DEFAULT 15,

-- Countdown completo
ADD COLUMN IF NOT EXISTS countdown_size INT DEFAULT 48,
ADD COLUMN IF NOT EXISTS countdown_label_font VARCHAR(100) DEFAULT 'Montserrat',
ADD COLUMN IF NOT EXISTS countdown_label_size INT DEFAULT 14,

-- Logo finale con dimensioni e opacità
ADD COLUMN IF NOT EXISTS footer_logo_size INT DEFAULT 100,
ADD COLUMN IF NOT EXISTS footer_logo_opacity DECIMAL(3,2) DEFAULT 1.00,

-- Sfondo principale opacità
ADD COLUMN IF NOT EXISTS background_main_opacity DECIMAL(3,2) DEFAULT 1.00,

-- Overlay sfondo
ADD COLUMN IF NOT EXISTS overlay_color VARCHAR(50) DEFAULT '#000000',
ADD COLUMN IF NOT EXISTS overlay_opacity DECIMAL(3,2) DEFAULT 0.30;
