-- Story Card Database Verification Script
-- Execute questo script per verificare lo stato del database
-- Data: 16 Dicembre 2024

-- ============================================
-- 1. VERIFICA SCHEMA TABELLA
-- ============================================

DESCRIBE wp_wi_story_card_templates;

-- RISULTATO ATTESO: Deve mostrare tutte le colonne incluso 'invite_template_id'


-- ============================================
-- 2. VERIFICA DATI ESISTENTI
-- ============================================

SELECT
    id,
    name,
    category_id,
    invite_template_id,
    CASE
        WHEN invite_template_id IS NOT NULL THEN '✅ Associato'
        ELSE '❌ Non Associato'
    END as status_template,
    is_default,
    created_at
FROM wp_wi_story_card_templates
ORDER BY created_at DESC;

-- RISULTATO: Mostra tutte le Story Card con status associazione template


-- ============================================
-- 3. VERIFICA TEMPLATE INVITO DISPONIBILI
-- ============================================

SELECT
    id,
    name,
    category,
    is_active
FROM wp_wi_templates
WHERE is_active = 1
ORDER BY name ASC;

-- RISULTATO: Lista template invito disponibili per associazione


-- ============================================
-- 4. VERIFICA ASSOCIAZIONI CORRETTE
-- ============================================

SELECT
    sc.id as story_card_id,
    sc.name as story_card_name,
    sc.invite_template_id,
    t.name as template_invito_name,
    CASE
        WHEN sc.invite_template_id IS NOT NULL AND t.id IS NOT NULL THEN '✅ Associazione OK'
        WHEN sc.invite_template_id IS NOT NULL AND t.id IS NULL THEN '⚠️ Template Invito Non Trovato'
        ELSE '—'
    END as status
FROM wp_wi_story_card_templates sc
LEFT JOIN wp_wi_templates t ON sc.invite_template_id = t.id
ORDER BY sc.created_at DESC;

-- RISULTATO: Mostra se le associazioni sono valide (template esiste)


-- ============================================
-- 5. CONTA STORY CARD PER TIPO ASSOCIAZIONE
-- ============================================

SELECT
    COUNT(*) as total_story_cards,
    SUM(CASE WHEN invite_template_id IS NOT NULL THEN 1 ELSE 0 END) as con_template_invito,
    SUM(CASE WHEN category_id IS NOT NULL AND invite_template_id IS NULL THEN 1 ELSE 0 END) as solo_categoria,
    SUM(CASE WHEN is_default = 1 THEN 1 ELSE 0 END) as default_cards
FROM wp_wi_story_card_templates;

-- RISULTATO: Statistica distribuzione associazioni


-- ============================================
-- 6. VERIFICA CASCATA: TROVA STORY CARD PER INVITO
-- ============================================

-- Simula ricerca Story Card per un invito con template_id = 5
-- Modifica il numero 5 con l'ID del template che vuoi testare

-- PRIORITÀ 1: Cerca per template_id specifico
SELECT
    'PRIORITÀ 1: Template ID' as tipo,
    id,
    name,
    invite_template_id,
    category_id
FROM wp_wi_story_card_templates
WHERE invite_template_id = 5
LIMIT 1;

-- PRIORITÀ 2: Fallback a categoria (se priorità 1 non trova nulla)
-- Simula categoria_id = 1 (Matrimonio)
SELECT
    'PRIORITÀ 2: Categoria' as tipo,
    id,
    name,
    invite_template_id,
    category_id
FROM wp_wi_story_card_templates
WHERE category_id = 1
  AND invite_template_id IS NULL
LIMIT 1;

-- PRIORITÀ 3: Template default
SELECT
    'PRIORITÀ 3: Default' as tipo,
    id,
    name,
    invite_template_id,
    category_id,
    is_default
FROM wp_wi_story_card_templates
WHERE is_default = 1
LIMIT 1;


-- ============================================
-- 7. FIX MANUALE (SE NECESSARIO)
-- ============================================

-- Se la colonna invite_template_id NON esiste, esegui:
-- ALTER TABLE wp_wi_story_card_templates
-- ADD COLUMN invite_template_id bigint(20) DEFAULT NULL
-- COMMENT 'Priorità 1: template invito specifico'
-- AFTER category_id;

-- ALTER TABLE wp_wi_story_card_templates
-- ADD KEY invite_template_id (invite_template_id);


-- ============================================
-- 8. TEST INSERT MANUALE
-- ============================================

-- SOLO PER TEST - Crea una Story Card di prova con template associato
-- Decomenta se vuoi testare l'inserimento diretto

-- INSERT INTO wp_wi_story_card_templates
-- (name, category_id, invite_template_id, background_image_url, layout_config, is_default)
-- VALUES
-- (
--     'TEST Manual Insert',
--     1,
--     5,
--     'https://via.placeholder.com/1080x1920/667eea/ffffff?text=Test+Story+Card',
--     '{"title":{"top":30,"left":10,"width":80,"fontSize":42,"fontWeight":700,"color":"#ffffff","textAlign":"center","fontFamily":"Playfair Display, serif","textShadow":true}}',
--     0
-- );

-- SELECT * FROM wp_wi_story_card_templates WHERE name = 'TEST Manual Insert';


-- ============================================
-- 9. CLEANUP TEST DATA
-- ============================================

-- Elimina Story Card di test create manualmente
-- DELETE FROM wp_wi_story_card_templates WHERE name LIKE 'TEST%' OR name LIKE 'Test%';


-- ============================================
-- 10. VERIFICA FINALE
-- ============================================

-- Query riassuntiva per verifica rapida
SELECT
    '✅ Database Status' as status,
    (SELECT COUNT(*) FROM wp_wi_story_card_templates) as total_cards,
    (SELECT COUNT(*) FROM wp_wi_story_card_templates WHERE invite_template_id IS NOT NULL) as cards_con_template,
    (SELECT COUNT(*) FROM wp_wi_templates WHERE is_active = 1) as template_invito_disponibili;
