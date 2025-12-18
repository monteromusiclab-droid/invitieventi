-- Script SQL per creare manualmente le tabelle RSVP
-- Esegui questo script tramite phpMyAdmin o terminale MySQL

-- IMPORTANTE: Sostituisci "wp_" con il tuo prefisso tabelle WordPress se diverso

-- ============================================
-- Tabella 1: Risposte RSVP
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_wi_rsvp_responses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invite_id` bigint(20) UNSIGNED NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_phone` varchar(50) DEFAULT NULL,
  `status` enum('attending','not_attending','maybe') NOT NULL,
  `num_guests` int(11) DEFAULT 1,
  `dietary_preferences` text DEFAULT NULL,
  `menu_choice` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `responded_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_invite` (`invite_id`),
  KEY `idx_email` (`guest_email`),
  KEY `idx_status` (`status`),
  KEY `idx_token` (`token`),
  KEY `idx_responded_at` (`responded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabella 2: Impostazioni RSVP
-- ============================================
CREATE TABLE IF NOT EXISTS `wp_wi_rsvp_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invite_id` bigint(20) UNSIGNED NOT NULL,
  `rsvp_enabled` tinyint(1) DEFAULT 1,
  `rsvp_deadline` date DEFAULT NULL,
  `max_guests_per_response` int(11) DEFAULT 5,
  `menu_choices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`menu_choices`)),
  `custom_questions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_questions`)),
  `confirmation_message` text DEFAULT NULL,
  `notify_admin` tinyint(1) DEFAULT 1,
  `admin_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_id` (`invite_id`),
  KEY `idx_invite` (`invite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Verifica creazione (opzionale)
-- ============================================
SHOW TABLES LIKE 'wp_wi_rsvp%';

-- Se vedi entrambe le tabelle, la creazione è riuscita!
