/*M!999999\- enable the sandbox mode */ 
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=484 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iso_download_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `download_url` text NOT NULL,
  `target_path` varchar(512) NOT NULL,
  `status` enum('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
  `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `size_downloaded` bigint(20) unsigned NOT NULL DEFAULT 0,
  `total_size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `message` varchar(255) DEFAULT NULL,
  `error_text` text DEFAULT NULL,
  `iso_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `form_payload` longtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_iso_download_jobs_iso` (`iso_id`),
  KEY `fk_iso_download_jobs_user` (`created_by`),
  CONSTRAINT `fk_iso_download_jobs_iso` FOREIGN KEY (`iso_id`) REFERENCES `iso_images` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_iso_download_jobs_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iso_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `source_type` enum('local','remote_nfs','remote_cifs') NOT NULL DEFAULT 'local',
  `file_path` varchar(255) NOT NULL,
  `local_path` varchar(255) DEFAULT NULL,
  `remote_host` varchar(255) DEFAULT NULL,
  `remote_path` varchar(255) DEFAULT NULL,
  `remote_username` varchar(255) DEFAULT NULL,
  `remote_password` text DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `size` bigint(20) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `iso_images_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kvm_tokens` (
  `token` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitoring_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitoring_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `action_taken` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitoring_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vps_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `proactive_enabled` tinyint(1) DEFAULT 0,
  `advanced_monitoring_enabled` tinyint(1) DEFAULT 0,
  `ports_to_check` varchar(255) DEFAULT '',
  `last_status` varchar(10) DEFAULT 'unknown',
  `downtime_duration` int(11) DEFAULT 0,
  `last_checked` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vps_id` (`vps_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limits` (
  `scope_key` char(32) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `window_start` int(11) NOT NULL,
  `hit_count` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`scope_key`),
  KEY `idx_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `netmask` varchar(45) DEFAULT NULL,
  `gateway` varchar(45) DEFAULT NULL,
  `rdns` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `server_ips_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `action` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_media_mounts` (
  `server_id` int(11) NOT NULL,
  `iso_id` int(11) DEFAULT NULL,
  `is_mounted` tinyint(1) NOT NULL DEFAULT 0,
  `mounted_label` varchar(255) DEFAULT NULL,
  `checked_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`server_id`),
  KEY `fk_server_media_mounts_iso` (`iso_id`),
  CONSTRAINT `fk_server_media_mounts_iso` FOREIGN KEY (`iso_id`) REFERENCES `iso_images` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_server_media_mounts_server` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `client_label` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `ipmi_username` varchar(50) NOT NULL,
  `ipmi_password` varchar(255) NOT NULL,
  `ipmi_port` int(11) DEFAULT 623,
  `ipmi_type` varchar(20) NOT NULL DEFAULT 'supermicro',
  `kvm_mode` varchar(32) DEFAULT 'html5',
  `location` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `cpu_info` varchar(255) DEFAULT NULL,
  `ram_gb` smallint(5) unsigned DEFAULT NULL,
  `disk_info` varchar(255) DEFAULT NULL,
  `switch_port` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('online','offline','maintenance') DEFAULT 'offline',
  `last_checked` timestamp NULL DEFAULT NULL,
  `status_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `api_token` varchar(64) DEFAULT NULL,
  `tls_fingerprint` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `ft_search` (`name`,`ip_address`,`location`,`serial_number`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_servers` (
  `user_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `access_level` enum('readonly','restart','full') DEFAULT 'readonly',
  PRIMARY KEY (`user_id`,`server_id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `user_servers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_servers_ibfk_2` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `tfa_secret` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Default settings
INSERT INTO `settings` (`name`, `value`) VALUES
('api_enabled',               '1'),
('app_theme',                 'panel'),
('default_language',          'en'),
('default_monitored_ports',   '22,80,443,3389'),
('monitoring_check_interval', '5'),
('monitoring_enabled',        '1'),
('tfa_enabled_admin',         '0'),
('virtualizor_api_key',       ''),
('virtualizor_api_pass',      ''),
('virtualizor_api_url',       '');

-- Default admin user
-- Username: admin  |  Password: Admin1234!
-- Change this password immediately after first login
INSERT INTO `users` (`username`, `email`, `password`, `role`, `created_at`) VALUES
('admin', 'admin@example.com', '$2y$12$H6bUL1eLbwL0FzxTpb7Wuu.YyEpFjTdSzmx8P.3YMoH6xCJV.WFRO', 'admin', NOW());
