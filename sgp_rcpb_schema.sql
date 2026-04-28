-- =============================================================================
--  SGP-RCPB — Script DDL complet
--  Généré le 2026-04-27
--  Compatible MySQL 8.0+ / MySQL Workbench (import EER)
--  Ordre des tables respectant les contraintes FK (parents avant enfants).
--  Les dépendances circulaires (entites ↔ agents ↔ delegation_techniques)
--  sont résolues via ALTER TABLE à la fin du script.
--
--  Corrections appliquées :
--    • users          : suppression de pca_entite_id (migré vers agents.entite_id)
--    • agents         : ajout de entite_id (PCA + Conseillers_Dg)
--    • evaluations    : suppression agent_id (mort), statut inclut 'refuse'
--    • fiche_objectifs: suppression agent_id (mort) et contrainte unique invalide
--    • villes         : UNIQUE(delegation_technique_id, nom) au lieu de UNIQUE(nom)
--    • caisses        : suppression direction_id (caisse appartient à DT, pas à Direction)
--    • agents         : ajout direction_id (agents des directions fonctionnelles)
--    • agences        : superviseur_caisse_id → caisse_id
--    • roles_has_perm : roles_id → role_id, permissions_id → permission_id
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- =============================================================================
-- 1. TABLES SANS DÉPENDANCE
-- =============================================================================

CREATE TABLE IF NOT EXISTS `annees` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `annee`      SMALLINT UNSIGNED NOT NULL,
  `statut`     ENUM('ouvert','cloture') NOT NULL DEFAULT 'ouvert',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `annees_annee_unique` (`annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255) NOT NULL,
  `slug`        VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `permissions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255) NOT NULL COMMENT 'Slug machine : ex valider-evaluation',
  `slug`        VARCHAR(255) NOT NULL COMMENT 'Libellé lisible : ex Valider une évaluation',
  `description` TEXT NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subjective_criteria_templates` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordre`       INT UNSIGNED NOT NULL DEFAULT 0,
  `titre`       VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255) NOT NULL,
  `payload`      LONGTEXT NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at`   INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_available_index` (`queue`,`reserved_at`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id`             VARCHAR(255) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT NOT NULL,
  `pending_jobs`   INT NOT NULL,
  `failed_jobs`    INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options`        MEDIUMTEXT NULL,
  `cancelled_at`   INT NULL,
  `created_at`     INT NOT NULL,
  `finished_at`    INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue`      TEXT NOT NULL,
  `payload`    LONGTEXT NOT NULL,
  `exception`  LONGTEXT NOT NULL,
  `failed_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `login_failures` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(255) NULL,
  `ip_address`   VARCHAR(45) NULL,
  `user_agent`   TEXT NULL,
  `attempted_at` TIMESTAMP NOT NULL,
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_failures_email_index` (`email`),
  KEY `login_failures_ip_address_index` (`ip_address`),
  KEY `login_failures_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. USERS — couche authentification pure (aucune FK de structure)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id`             BIGINT UNSIGNED NULL    COMMENT 'Lien vers la fiche Agent (FK ajoutée après création de agents)',
  `name`                 VARCHAR(191) NOT NULL   COMMENT 'Nom complet affiché',
  `email`                VARCHAR(191) NOT NULL,
  `password`             VARCHAR(255) NOT NULL,
  `email_verified_at`    TIMESTAMP NULL DEFAULT NULL,
  `remember_token`       VARCHAR(100) NULL,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 1,
  `role`                 VARCHAR(50) NOT NULL DEFAULT 'Agent',
  `theme_preference`     VARCHAR(50) NOT NULL DEFAULT 'reference',
  `manager_id`           BIGINT UNSIGNED NULL    COMMENT 'Supérieur direct N+1',
  `created_at`           TIMESTAMP NULL DEFAULT NULL,
  `updated_at`           TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_agent_id_unique` (`agent_id`),
  KEY `users_manager_id_foreign` (`manager_id`),
  CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255) NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45) NULL,
  `user_agent`    TEXT NULL,
  `payload`       LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. HIÉRARCHIE ORGANISATIONNELLE
--    (entites, delegation_techniques, directions SANS les FK agents — ajoutées via ALTER)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `entites` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                   VARCHAR(255) NOT NULL,
  `ville`                 VARCHAR(255) NOT NULL,
  `region`                VARCHAR(255) NULL,
  `secretariat_telephone` VARCHAR(30) NULL,
  `dg_agent_id`           BIGINT UNSIGNED NULL,
  `dga_agent_id`          BIGINT UNSIGNED NULL,
  `pca_agent_id`          BIGINT UNSIGNED NULL,
  `assistante_agent_id`   BIGINT UNSIGNED NULL,
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entites_nom_unique` (`nom`),
  KEY `entites_dg_agent_id_foreign` (`dg_agent_id`),
  KEY `entites_dga_agent_id_foreign` (`dga_agent_id`),
  KEY `entites_pca_agent_id_foreign` (`pca_agent_id`),
  KEY `entites_assistante_agent_id_foreign` (`assistante_agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `delegation_techniques` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entite_id`             BIGINT UNSIGNED NULL,
  `region`                VARCHAR(255) NOT NULL,
  `ville`                 VARCHAR(255) NOT NULL,
  `secretariat_telephone` VARCHAR(30) NULL,
  `directeur_agent_id`    BIGINT UNSIGNED NULL,
  `secretaire_agent_id`   BIGINT UNSIGNED NULL,
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delegation_techniques_region_ville_unique` (`region`,`ville`),
  KEY `delegation_techniques_entite_id_foreign` (`entite_id`),
  KEY `delegation_techniques_directeur_agent_id_foreign` (`directeur_agent_id`),
  KEY `delegation_techniques_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `delegation_techniques_entite_id_foreign` FOREIGN KEY (`entite_id`) REFERENCES `entites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `villes` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id` BIGINT UNSIGNED NOT NULL,
  `nom`                     VARCHAR(255) NOT NULL,
  `created_at`              TIMESTAMP NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villes_dt_nom_unique` (`delegation_technique_id`,`nom`),
  KEY `villes_delegation_technique_id_foreign` (`delegation_technique_id`),
  CONSTRAINT `villes_delegation_technique_id_foreign` FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `directions` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                 VARCHAR(255) NOT NULL,
  `entite_id`           BIGINT UNSIGNED NULL,
  `directeur_agent_id`  BIGINT UNSIGNED NULL,
  `secretaire_agent_id` BIGINT UNSIGNED NULL,
  `created_at`          TIMESTAMP NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `directions_entite_id_foreign` (`entite_id`),
  KEY `directions_directeur_agent_id_foreign` (`directeur_agent_id`),
  KEY `directions_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `directions_entite_id_foreign` FOREIGN KEY (`entite_id`) REFERENCES `entites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `caisses` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id` BIGINT UNSIGNED NULL,
  `ville_id`                BIGINT UNSIGNED NULL,
  `nom`                     VARCHAR(255) NOT NULL,
  `annee_ouverture`         VARCHAR(4) NULL,
  `quartier`                VARCHAR(255) NULL,
  `secretariat_telephone`   VARCHAR(30) NULL,
  `directeur_agent_id`      BIGINT UNSIGNED NULL,
  `secretaire_agent_id`     BIGINT UNSIGNED NULL,
  `created_at`              TIMESTAMP NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `caisses_nom_unique` (`nom`),
  KEY `caisses_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `caisses_ville_id_foreign` (`ville_id`),
  KEY `caisses_directeur_agent_id_foreign` (`directeur_agent_id`),
  KEY `caisses_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `caisses_delegation_technique_id_foreign` FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `caisses_ville_id_foreign`                FOREIGN KEY (`ville_id`)                REFERENCES `villes`                (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `agences` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                     VARCHAR(255) NOT NULL,
  `delegation_technique_id` BIGINT UNSIGNED NOT NULL,
  `caisse_id`               BIGINT UNSIGNED NOT NULL  COMMENT 'Caisse parente',
  `chef_agent_id`           BIGINT UNSIGNED NULL,
  `secretaire_agent_id`     BIGINT UNSIGNED NULL,
  `created_at`              TIMESTAMP NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agences_delegation_nom_unique` (`delegation_technique_id`,`nom`),
  KEY `agences_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `agences_caisse_id_foreign` (`caisse_id`),
  KEY `agences_chef_agent_id_foreign` (`chef_agent_id`),
  KEY `agences_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `agences_delegation_technique_id_foreign` FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agences_caisse_id_foreign`               FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `guichets` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`           VARCHAR(255) NOT NULL,
  `agence_id`     BIGINT UNSIGNED NOT NULL,
  `chef_agent_id` BIGINT UNSIGNED NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guichets_agence_id_foreign` (`agence_id`),
  KEY `guichets_chef_agent_id_foreign` (`chef_agent_id`),
  CONSTRAINT `guichets_agence_id_foreign` FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `services` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                     VARCHAR(255) NOT NULL,
  `direction_id`            BIGINT UNSIGNED NULL  COMMENT 'Service d une Direction centrale (exclusif)',
  `delegation_technique_id` BIGINT UNSIGNED NULL  COMMENT 'Service d une DT (exclusif)',
  `caisse_id`               BIGINT UNSIGNED NULL  COMMENT 'Service d une Caisse (exclusif)',
  `chef_agent_id`           BIGINT UNSIGNED NULL,
  `created_at`              TIMESTAMP NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_direction_id_foreign` (`direction_id`),
  KEY `services_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `services_caisse_id_foreign` (`caisse_id`),
  KEY `services_chef_agent_id_foreign` (`chef_agent_id`),
  CONSTRAINT `services_direction_id_foreign`            FOREIGN KEY (`direction_id`)            REFERENCES `directions`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_delegation_technique_id_foreign` FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_caisse_id_foreign`               FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 4. AGENTS — table centrale, toutes les FK de structure ici
-- =============================================================================

CREATE TABLE IF NOT EXISTS `agents` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entite_id`               BIGINT UNSIGNED NULL  COMMENT 'Direction Générale — DG, DGA, PCA, Assistante_Dg, Conseillers_Dg, Secrétariat DG',
  `direction_id`            BIGINT UNSIGNED NULL  COMMENT 'Direction fonctionnelle — agents des services centraux (DRH, DAF, DTIC…)',
  `delegation_technique_id` BIGINT UNSIGNED NULL  COMMENT 'DT de rattachement',
  `caisse_id`               BIGINT UNSIGNED NULL  COMMENT 'Caisse de rattachement',
  `agence_id`               BIGINT UNSIGNED NULL  COMMENT 'Agence de rattachement',
  `guichet_id`              BIGINT UNSIGNED NULL  COMMENT 'Guichet de rattachement',
  `service_id`              BIGINT UNSIGNED NULL  COMMENT 'Service de rattachement',
  `nom`                     VARCHAR(100) NOT NULL,
  `prenom`                  VARCHAR(100) NOT NULL,
  `sexe`                    VARCHAR(20) NULL,
  `email`                   VARCHAR(191) NOT NULL  COMMENT 'Email professionnel',
  `numero_telephone`        VARCHAR(30) NULL,
  `photo_path`              VARCHAR(255) NULL,
  `fonction`                VARCHAR(100) NOT NULL,
  `date_debut_fonction`     DATE NULL,
  `created_at`              TIMESTAMP NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agents_email_unique` (`email`),
  KEY `agents_entite_id_foreign` (`entite_id`),
  KEY `agents_direction_id_foreign` (`direction_id`),
  KEY `agents_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `agents_caisse_id_foreign` (`caisse_id`),
  KEY `agents_agence_id_foreign` (`agence_id`),
  KEY `agents_guichet_id_foreign` (`guichet_id`),
  KEY `agents_service_id_foreign` (`service_id`),
  CONSTRAINT `agents_entite_id_foreign`               FOREIGN KEY (`entite_id`)               REFERENCES `entites`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_direction_id_foreign`            FOREIGN KEY (`direction_id`)            REFERENCES `directions`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_delegation_technique_id_foreign` FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_caisse_id_foreign`               FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_agence_id_foreign`               FOREIGN KEY (`agence_id`)               REFERENCES `agences`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_guichet_id_foreign`              FOREIGN KEY (`guichet_id`)              REFERENCES `guichets`              (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_service_id_foreign`              FOREIGN KEY (`service_id`)              REFERENCES `services`              (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 5. FK RETOUR — Résolution des dépendances circulaires via ALTER TABLE
-- =============================================================================

-- FK users → agents
ALTER TABLE `users`
  ADD CONSTRAINT `users_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK entites → agents (responsables)
ALTER TABLE `entites`
  ADD CONSTRAINT `entites_dg_agent_id_foreign`         FOREIGN KEY (`dg_agent_id`)        REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_dga_agent_id_foreign`        FOREIGN KEY (`dga_agent_id`)        REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_pca_agent_id_foreign`        FOREIGN KEY (`pca_agent_id`)        REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_assistante_agent_id_foreign` FOREIGN KEY (`assistante_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK delegation_techniques → agents (responsables)
ALTER TABLE `delegation_techniques`
  ADD CONSTRAINT `delegation_techniques_directeur_agent_id_foreign`  FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delegation_techniques_secretaire_agent_id_foreign` FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK directions → agents (responsables)
ALTER TABLE `directions`
  ADD CONSTRAINT `directions_directeur_agent_id_foreign`  FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `directions_secretaire_agent_id_foreign` FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK caisses → agents (responsables)
ALTER TABLE `caisses`
  ADD CONSTRAINT `caisses_directeur_agent_id_foreign`  FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `caisses_secretaire_agent_id_foreign` FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK agences → agents (responsables)
ALTER TABLE `agences`
  ADD CONSTRAINT `agences_chef_agent_id_foreign`       FOREIGN KEY (`chef_agent_id`)       REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agences_secretaire_agent_id_foreign` FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK guichets → agents (responsable)
ALTER TABLE `guichets`
  ADD CONSTRAINT `guichets_chef_agent_id_foreign` FOREIGN KEY (`chef_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- FK services → agents (chef)
ALTER TABLE `services`
  ADD CONSTRAINT `services_chef_agent_id_foreign` FOREIGN KEY (`chef_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

-- =============================================================================
-- 6. JOURNAL D'ACTIVITÉ
-- =============================================================================

CREATE TABLE IF NOT EXISTS `activites` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `action`      VARCHAR(255) NOT NULL  COMMENT 'ex: CHANGEMENT_ROLE, VALIDATION_EVALUATION',
  `description` TEXT NOT NULL,
  `ip_address`  VARCHAR(45) NULL,
  `user_agent`  VARCHAR(255) NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activites_user_id_foreign` (`user_id`),
  CONSTRAINT `activites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 7. PERMISSIONS & RÔLES (tables pivot)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `permission_user` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_user_user_id_foreign` (`user_id`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_user_id_foreign`       FOREIGN KEY (`user_id`)       REFERENCES `users`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `role_user` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `role_id`    BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles_has_permissions` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id`       BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_permissions_unique` (`role_id`,`permission_id`),
  KEY `roles_has_permissions_role_id_foreign` (`role_id`),
  KEY `roles_has_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `roles_has_permissions_role_id_foreign`       FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE,
  CONSTRAINT `roles_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 8. OBJECTIFS STRATÉGIQUES (polymorphes — assignés à une Direction, Caisse, etc.)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `objectifs` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignable_type`       VARCHAR(255) NOT NULL  COMMENT 'Type polymorphique (ex: App\\Models\\Direction)',
  `assignable_id`         BIGINT UNSIGNED NOT NULL,
  `annee_id`              BIGINT UNSIGNED NOT NULL,
  `date`                  DATE NOT NULL,
  `date_echeance`         DATE NOT NULL,
  `titre`                 VARCHAR(255) NOT NULL,
  `commentaire`           TEXT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objectifs_assignable_index` (`assignable_type`,`assignable_id`),
  KEY `objectifs_annee_id_foreign` (`annee_id`),
  CONSTRAINT `objectifs_annee_id_foreign` FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 9. FICHES OBJECTIFS (contrat d'objectifs individuel)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `fiche_objectifs` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre`                 VARCHAR(255) NOT NULL,
  `annee_id`              BIGINT UNSIGNED NOT NULL,
  `assignable_id`         BIGINT UNSIGNED NOT NULL  COMMENT 'Cible polymorphique (User, Direction…)',
  `assignable_type`       VARCHAR(255) NOT NULL,
  `date`                  DATE NOT NULL,
  `date_echeance`         DATE NOT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `statut`                ENUM('brouillon','en_attente','acceptee','refusee') NOT NULL DEFAULT 'brouillon',
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fiche_objectifs_annee_id_foreign` (`annee_id`),
  KEY `fiche_objectifs_assignable_index` (`assignable_type`,`assignable_id`),
  CONSTRAINT `fiche_objectifs_annee_id_foreign` FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fiche_objectif_objectifs` (
  `id`                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fiche_objectif_id`      BIGINT UNSIGNED NOT NULL,
  `objectif_id`            BIGINT UNSIGNED NULL,
  `libelle`                VARCHAR(255) NOT NULL,
  `indicateur_performance` TEXT NULL,
  `poids`                  INT UNSIGNED NOT NULL DEFAULT 0,
  `note_obtenue`           DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `description`            TEXT NULL,
  `created_at`             TIMESTAMP NULL DEFAULT NULL,
  `updated_at`             TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fiche_objectif_objectifs_fiche_id_foreign` (`fiche_objectif_id`),
  KEY `fiche_objectif_objectifs_objectif_id_foreign` (`objectif_id`),
  CONSTRAINT `fiche_objectif_objectifs_fiche_id_foreign`    FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fiche_objectif_objectifs_objectif_id_foreign` FOREIGN KEY (`objectif_id`)       REFERENCES `objectifs`        (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 10. ÉVALUATIONS (polymorphes)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `evaluations` (
  `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluateur_id`             BIGINT UNSIGNED NOT NULL,
  `evaluable_type`            VARCHAR(255) NOT NULL  COMMENT 'Type polymorphique (ex: App\\Models\\User)',
  `evaluable_id`              BIGINT UNSIGNED NOT NULL,
  `evaluable_role`            VARCHAR(255) NOT NULL DEFAULT 'agent',
  `fiche_objectif_id`         BIGINT UNSIGNED NULL,
  `annee_id`                  BIGINT UNSIGNED NULL,
  `date_debut`                DATE NOT NULL,
  `date_fin`                  DATE NOT NULL,
  `moyenne_subjectifs`        DECIMAL(8,2) NULL,
  `moyenne_objectifs`         DECIMAL(8,2) NULL,
  `note_criteres_subjectifs`  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `note_criteres_objectifs`   DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `note_objectifs`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `note_manuelle`             TINYINT UNSIGNED NULL,
  `note_finale`               DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `commentaire`               TEXT NULL,
  `points_a_ameliorer`        TEXT NULL,
  `strategies_amelioration`   TEXT NULL,
  `commentaires_evalue`       TEXT NULL,
  `statut`                    ENUM('brouillon','soumis','valide','refuse') NOT NULL DEFAULT 'brouillon',
  `signature_evalue_nom`      VARCHAR(255) NULL,
  `date_signature_evalue`     DATE NULL,
  `signature_evaluateur_nom`  VARCHAR(255) NULL,
  `date_signature_evaluateur` DATE NULL,
  `signature_directeur_nom`   VARCHAR(255) NULL,
  `date_signature_directeur`  DATE NULL,
  `created_at`                TIMESTAMP NULL DEFAULT NULL,
  `updated_at`                TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluations_evaluateur_id_foreign` (`evaluateur_id`),
  KEY `evaluations_evaluable_index` (`evaluable_type`,`evaluable_id`),
  KEY `evaluations_fiche_objectif_id_foreign` (`fiche_objectif_id`),
  KEY `evaluations_annee_id_foreign` (`annee_id`),
  CONSTRAINT `evaluations_evaluateur_id_foreign`     FOREIGN KEY (`evaluateur_id`)     REFERENCES `users`           (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_fiche_objectif_id_foreign` FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_annee_id_foreign`          FOREIGN KEY (`annee_id`)          REFERENCES `annees`          (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `evaluation_identifications` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`       BIGINT UNSIGNED NOT NULL,
  `nom_prenom`          VARCHAR(255) NULL,
  `semestre`            VARCHAR(20) NULL,
  `date_evaluation`     DATE NULL,
  `matricule`           VARCHAR(255) NULL,
  `poste`               VARCHAR(255) NULL,
  `emploi`              VARCHAR(255) NULL,
  `niveau`              VARCHAR(255) NULL,
  `direction`           VARCHAR(255) NULL,
  `direction_service`   VARCHAR(255) NULL,
  `date_confirmation`   DATE NULL,
  `categorie`           VARCHAR(255) NULL,
  `anciennete`          VARCHAR(255) NULL,
  `sexe`                VARCHAR(1) NULL,
  `date_recrutement`    DATE NULL,
  `date_titularisation` DATE NULL,
  `date_affectation`    DATE NULL,
  `date_naissance`      DATE NULL,
  `formations`          JSON NULL,
  `experiences`         JSON NULL,
  `created_at`          TIMESTAMP NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evaluation_identifications_evaluation_id_unique` (`evaluation_id`),
  CONSTRAINT `evaluation_identifications_evaluation_id_foreign` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 11. CRITÈRES D'ÉVALUATION
-- =============================================================================

CREATE TABLE IF NOT EXISTS `subjective_subcriteria_templates` (
  `id`                              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjective_criteria_template_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                           INT UNSIGNED NOT NULL DEFAULT 0,
  `libelle`                         VARCHAR(255) NOT NULL,
  `created_at`                      TIMESTAMP NULL DEFAULT NULL,
  `updated_at`                      TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcriterion_template_fk` (`subjective_criteria_template_id`),
  CONSTRAINT `subcriterion_template_fk` FOREIGN KEY (`subjective_criteria_template_id`) REFERENCES `subjective_criteria_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `evaluation_criteres` (
  `id`                                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`                     BIGINT UNSIGNED NOT NULL,
  `type`                              VARCHAR(20) NOT NULL  COMMENT 'objectif ou subjectif',
  `ordre`                             INT UNSIGNED NOT NULL DEFAULT 0,
  `titre`                             VARCHAR(255) NOT NULL,
  `description`                       TEXT NULL,
  `note_globale`                      DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `observation`                       TEXT NULL,
  `source_fiche_objectif_id`          BIGINT UNSIGNED NULL,
  `source_fiche_objectif_objectif_id` BIGINT UNSIGNED NULL,
  `source_template_id`                BIGINT UNSIGNED NULL,
  `created_at`                        TIMESTAMP NULL DEFAULT NULL,
  `updated_at`                        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_criteres_evaluation_id_foreign` (`evaluation_id`),
  KEY `evaluation_criteres_source_fiche_objectif_id_foreign` (`source_fiche_objectif_id`),
  KEY `evaluation_criteres_source_fiche_objectif_objectif_id_foreign` (`source_fiche_objectif_objectif_id`),
  KEY `evaluation_criteres_source_template_id_foreign` (`source_template_id`),
  CONSTRAINT `evaluation_criteres_evaluation_id_foreign`                        FOREIGN KEY (`evaluation_id`)                     REFERENCES `evaluations`              (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_id_foreign`             FOREIGN KEY (`source_fiche_objectif_id`)          REFERENCES `fiche_objectifs`          (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_objectif_id_foreign`    FOREIGN KEY (`source_fiche_objectif_objectif_id`) REFERENCES `fiche_objectif_objectifs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_template_id_foreign`                   FOREIGN KEY (`source_template_id`)                REFERENCES `subjective_criteria_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `evaluation_sous_criteres` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_critere_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                 INT UNSIGNED NOT NULL DEFAULT 0,
  `libelle`               VARCHAR(255) NOT NULL,
  `note`                  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `observation`           TEXT NULL,
  `created_at`            TIMESTAMP NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_sous_criteres_evaluation_critere_id_foreign` (`evaluation_critere_id`),
  CONSTRAINT `evaluation_sous_criteres_evaluation_critere_id_foreign` FOREIGN KEY (`evaluation_critere_id`) REFERENCES `evaluation_criteres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 12. ALERTES
-- =============================================================================

CREATE TABLE IF NOT EXISTS `alertes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       VARCHAR(255) NOT NULL DEFAULT 'personnalisee'  COMMENT 'securite | personnalisee',
  `priorite`   VARCHAR(255) NOT NULL DEFAULT 'moyenne'        COMMENT 'basse | moyenne | haute | critique',
  `titre`      VARCHAR(255) NOT NULL,
  `message`    TEXT NULL,
  `statut`     VARCHAR(255) NOT NULL DEFAULT 'active'         COMMENT 'active | resolue | ignoree',
  `ip_address` VARCHAR(45) NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alertes_created_by_foreign` (`created_by`),
  CONSTRAINT `alertes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `alerte_user` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alerte_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `lu`         TINYINT(1) NOT NULL DEFAULT 0,
  `lu_at`      TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alerte_user_alerte_id_user_id_unique` (`alerte_id`,`user_id`),
  KEY `alerte_user_alerte_id_foreign` (`alerte_id`),
  KEY `alerte_user_user_id_foreign` (`user_id`),
  CONSTRAINT `alerte_user_alerte_id_foreign` FOREIGN KEY (`alerte_id`) REFERENCES `alertes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alerte_user_user_id_foreign`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 13. TABLE SYSTÈME LARAVEL (migrations tracker)
-- =============================================================================

CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- FIN DU SCRIPT — SGP-RCPB
-- 37 tables définies :
--   Système Laravel  : users, password_reset_tokens, sessions, cache,
--                      cache_locks, jobs, job_batches, failed_jobs,
--                      migrations, login_failures
--   Organisation     : entites, delegation_techniques, villes, directions,
--                      caisses, agences, guichets, services, agents
--   Évaluation       : annees, objectifs, fiche_objectifs,
--                      fiche_objectif_objectifs, evaluations,
--                      evaluation_identifications, evaluation_criteres,
--                      evaluation_sous_criteres
--   Critères         : subjective_criteria_templates,
--                      subjective_subcriteria_templates
--   Rôles/Droits     : roles, permissions, role_user, permission_user,
--                      roles_has_permissions
--   Activité/Alertes : activites, alertes, alerte_user
-- =============================================================================
