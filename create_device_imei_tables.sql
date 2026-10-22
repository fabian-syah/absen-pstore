-- ==========================================================
-- Tabel Device IMEIs & Riwayat Koreksi Spesifikasi
-- Fitur Khusus Super Admin: Rubah Tipe & Lacak IMEI
-- ==========================================================

CREATE TABLE IF NOT EXISTS `device_imeis` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `imei` varchar(30) NOT NULL,
  `brand` varchar(60) NOT NULL DEFAULT 'Apple',
  `type` varchar(120) NOT NULL,
  `storage` varchar(60) NOT NULL DEFAULT '128 GB',
  `color` varchar(60) DEFAULT NULL,
  `last_cs_name` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_imeis_imei_unique` (`imei`),
  KEY `device_imeis_brand_index` (`brand`),
  KEY `device_imeis_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `device_imei_histories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_imei_id` bigint(20) UNSIGNED DEFAULT NULL,
  `imei` varchar(30) NOT NULL,
  `action_type` varchar(40) NOT NULL DEFAULT 'koreksi_tipe',
  `old_brand` varchar(60) DEFAULT NULL,
  `old_type` varchar(120) DEFAULT NULL,
  `old_storage` varchar(60) DEFAULT NULL,
  `old_color` varchar(60) DEFAULT NULL,
  `new_brand` varchar(60) NOT NULL,
  `new_type` varchar(120) NOT NULL,
  `new_storage` varchar(60) NOT NULL,
  `new_color` varchar(60) DEFAULT NULL,
  `cs_name_mistake` varchar(120) DEFAULT NULL,
  `correction_reason` text DEFAULT NULL,
  `corrected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_imei_histories_device_imei_id_index` (`device_imei_id`),
  KEY `device_imei_histories_imei_index` (`imei`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
