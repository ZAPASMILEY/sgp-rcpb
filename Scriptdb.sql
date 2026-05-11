-- SGP-RCPB — Script de création de la base de données
-- Généré le 2026-05-03 — synchronisé avec les migrations Laravel
-- RBAC : Spatie Laravel-Permission v6 (remplace l'ancien système custom)

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema sgp_rcpb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `sgp_rcpb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sgp_rcpb`;


-- =====================================================
-- SECTION 1 — INFRASTRUCTURE LARAVEL
-- =====================================================

-- -----------------------------------------------------
-- Table `migrations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `cache`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `cache_locks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`),
  INDEX `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `jobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)    NOT NULL,
  `payload`      LONGTEXT        NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED    NULL DEFAULT NULL,
  `available_at` INT UNSIGNED    NOT NULL,
  `created_at`   INT UNSIGNED    NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `jobs_queue_reserved_available_index` (`queue`, `reserved_at`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `job_batches`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id`             VARCHAR(255) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT          NOT NULL,
  `pending_jobs`   INT          NOT NULL,
  `failed_jobs`    INT          NOT NULL,
  `failed_job_ids` LONGTEXT     NOT NULL,
  `options`        MEDIUMTEXT   NULL DEFAULT NULL,
  `cancelled_at`   INT          NULL DEFAULT NULL,
  `created_at`     INT          NOT NULL,
  `finished_at`    INT          NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `failed_jobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       VARCHAR(255)    NOT NULL,
  `connection` TEXT            NOT NULL,
  `queue`      TEXT            NOT NULL,
  `payload`    LONGTEXT        NOT NULL,
  `exception`  LONGTEXT        NOT NULL,
  `failed_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `password_reset_tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id`            VARCHAR(255)    NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address`    VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`    TEXT            NULL DEFAULT NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `sessions_user_id_index` (`user_id`),
  INDEX `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 2 — AUTHENTIFICATION & SÉCURITÉ
-- =====================================================

-- -----------------------------------------------------
-- Table `login_failures`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `login_failures`;
CREATE TABLE IF NOT EXISTS `login_failures` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(255)    NULL DEFAULT NULL,
  `ip_address`   VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`   TEXT            NULL DEFAULT NULL,
  `attempted_at` TIMESTAMP       NOT NULL,
  `created_at`   TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `login_failures_email_index`        (`email`),
  INDEX `login_failures_ip_address_index`   (`ip_address`),
  INDEX `login_failures_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 3 — STRUCTURE ORGANISATIONNELLE
-- (ordre : entites → directions/DT → caisses → agences → guichets → services → agents → users)
-- =====================================================

-- -----------------------------------------------------
-- Table `annees`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `annees`;
CREATE TABLE IF NOT EXISTS `annees` (
  `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `annee`      SMALLINT UNSIGNED NOT NULL,
  `statut`     ENUM('ouvert','cloture') NOT NULL DEFAULT 'ouvert',
  `created_at` TIMESTAMP        NULL DEFAULT NULL,
  `updated_at` TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `annees_annee_unique` (`annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `entites`
-- singleton = 1 (contrainte UNIQUE = une seule ligne par faîtière)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `entites`;
CREATE TABLE IF NOT EXISTS `entites` (
  `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `singleton`                 TINYINT UNSIGNED NOT NULL DEFAULT 1
                                COMMENT 'Toujours 1 — contrainte UNIQUE garantit une seule ligne',
  `nom`                       VARCHAR(255)    NOT NULL,
  `ville`                     VARCHAR(255)    NOT NULL,
  `region`                    VARCHAR(255)    NULL DEFAULT NULL,
  `secretariat_telephone`     VARCHAR(30)     NULL DEFAULT NULL,
  `dg_agent_id`               BIGINT UNSIGNED NULL DEFAULT NULL,
  `dga_agent_id`              BIGINT UNSIGNED NULL DEFAULT NULL,
  `dga_secretaire_agent_id`   BIGINT UNSIGNED NULL DEFAULT NULL,
  `pca_agent_id`              BIGINT UNSIGNED NULL DEFAULT NULL,
  `assistante_agent_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`                TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entites_singleton_unique`          (`singleton`),
  UNIQUE KEY `entites_nom_unique`                (`nom`),
  INDEX `entites_dg_agent_id_foreign`            (`dg_agent_id`),
  INDEX `entites_dga_agent_id_foreign`           (`dga_agent_id`),
  INDEX `entites_dga_secretaire_agent_id_foreign`(`dga_secretaire_agent_id`),
  INDEX `entites_pca_agent_id_foreign`           (`pca_agent_id`),
  INDEX `entites_assistante_agent_id_foreign`    (`assistante_agent_id`),
  CONSTRAINT `entites_dg_agent_id_foreign`
    FOREIGN KEY (`dg_agent_id`)               REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entites_dga_agent_id_foreign`
    FOREIGN KEY (`dga_agent_id`)              REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entites_dga_secretaire_agent_id_foreign`
    FOREIGN KEY (`dga_secretaire_agent_id`)   REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entites_pca_agent_id_foreign`
    FOREIGN KEY (`pca_agent_id`)              REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entites_assistante_agent_id_foreign`
    FOREIGN KEY (`assistante_agent_id`)       REFERENCES `agents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `directions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `directions`;
CREATE TABLE IF NOT EXISTS `directions` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                   VARCHAR(255)    NOT NULL,
  `entite_id`             BIGINT UNSIGNED NULL DEFAULT NULL,
  `directeur_agent_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`   BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`            TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `directions_entite_id_foreign`            (`entite_id`),
  INDEX `directions_directeur_agent_id_foreign`   (`directeur_agent_id`),
  INDEX `directions_secretaire_agent_id_foreign`  (`secretaire_agent_id`),
  CONSTRAINT `directions_entite_id_foreign`
    FOREIGN KEY (`entite_id`)           REFERENCES `entites`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `directions_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `directions_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `delegation_techniques`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `delegation_techniques`;
CREATE TABLE IF NOT EXISTS `delegation_techniques` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entite_id`             BIGINT UNSIGNED NULL DEFAULT NULL,
  `region`                VARCHAR(255)    NOT NULL,
  `ville`                 VARCHAR(255)    NOT NULL,
  `secretariat_telephone` VARCHAR(30)     NULL DEFAULT NULL,
  `directeur_agent_id`    BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`   BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`            TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delegation_techniques_region_ville_unique` (`region`, `ville`),
  INDEX `delegation_techniques_entite_id_foreign`           (`entite_id`),
  INDEX `delegation_techniques_directeur_agent_id_foreign`  (`directeur_agent_id`),
  INDEX `delegation_techniques_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `delegation_techniques_entite_id_foreign`
    FOREIGN KEY (`entite_id`)           REFERENCES `entites`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `delegation_techniques_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents`   (`id`) ON DELETE SET NULL,
  CONSTRAINT `delegation_techniques_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents`   (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `villes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `villes`;
CREATE TABLE IF NOT EXISTS `villes` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id`  BIGINT UNSIGNED NOT NULL,
  `nom`                      VARCHAR(255)    NOT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villes_dt_nom_unique` (`delegation_technique_id`, `nom`),
  INDEX `villes_delegation_technique_id_foreign` (`delegation_technique_id`),
  CONSTRAINT `villes_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `caisses`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `caisses`;
CREATE TABLE IF NOT EXISTS `caisses` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id`  BIGINT UNSIGNED NULL DEFAULT NULL,
  `ville_id`                 BIGINT UNSIGNED NULL DEFAULT NULL,
  `nom`                      VARCHAR(255)    NOT NULL,
  `annee_ouverture`          VARCHAR(4)      NULL DEFAULT NULL,
  `quartier`                 VARCHAR(255)    NULL DEFAULT NULL,
  `secretariat_telephone`    VARCHAR(30)     NULL DEFAULT NULL,
  `directeur_agent_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `caisses_nom_unique` (`nom`),
  INDEX `caisses_delegation_technique_id_foreign` (`delegation_technique_id`),
  INDEX `caisses_ville_id_foreign`                (`ville_id`),
  INDEX `caisses_directeur_agent_id_foreign`      (`directeur_agent_id`),
  INDEX `caisses_secretaire_agent_id_foreign`     (`secretaire_agent_id`),
  CONSTRAINT `caisses_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `caisses_ville_id_foreign`
    FOREIGN KEY (`ville_id`)                REFERENCES `villes`                (`id`) ON DELETE SET NULL,
  CONSTRAINT `caisses_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)      REFERENCES `agents`                (`id`) ON DELETE SET NULL,
  CONSTRAINT `caisses_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`)     REFERENCES `agents`                (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `agences`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `agences`;
CREATE TABLE IF NOT EXISTS `agences` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                      VARCHAR(255)    NOT NULL,
  `delegation_technique_id`  BIGINT UNSIGNED NOT NULL,
  `caisse_id`                BIGINT UNSIGNED NOT NULL COMMENT 'Caisse parente',
  `chef_agent_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agences_delegation_nom_unique` (`delegation_technique_id`, `nom`),
  INDEX `agences_delegation_technique_id_foreign` (`delegation_technique_id`),
  INDEX `agences_caisse_id_foreign`               (`caisse_id`),
  INDEX `agences_chef_agent_id_foreign`           (`chef_agent_id`),
  INDEX `agences_secretaire_agent_id_foreign`     (`secretaire_agent_id`),
  CONSTRAINT `agences_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agences_caisse_id_foreign`
    FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE CASCADE,
  CONSTRAINT `agences_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`)           REFERENCES `agents`                (`id`) ON DELETE SET NULL,
  CONSTRAINT `agences_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`)     REFERENCES `agents`                (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `guichets`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `guichets`;
CREATE TABLE IF NOT EXISTS `guichets` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`            VARCHAR(255)    NOT NULL,
  `agence_id`      BIGINT UNSIGNED NOT NULL,
  `chef_agent_id`  BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`     TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `guichets_agence_id_foreign`     (`agence_id`),
  INDEX `guichets_chef_agent_id_foreign` (`chef_agent_id`),
  CONSTRAINT `guichets_agence_id_foreign`
    FOREIGN KEY (`agence_id`)     REFERENCES `agences` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guichets_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`) REFERENCES `agents`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `services`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                      VARCHAR(255)    NOT NULL,
  `direction_id`             BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Service d\'une Direction centrale (exclusif)',
  `delegation_technique_id`  BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Service d\'une DT (exclusif)',
  `caisse_id`                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Service d\'une Caisse (exclusif)',
  `chef_agent_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `services_direction_id_foreign`            (`direction_id`),
  INDEX `services_delegation_technique_id_foreign` (`delegation_technique_id`),
  INDEX `services_caisse_id_foreign`               (`caisse_id`),
  INDEX `services_chef_agent_id_foreign`           (`chef_agent_id`),
  CONSTRAINT `services_direction_id_foreign`
    FOREIGN KEY (`direction_id`)            REFERENCES `directions`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_caisse_id_foreign`
    FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`)           REFERENCES `agents`                (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `agents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entite_id`                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'DG, DGA, PCA, Assistante, Conseillers rattachés à la faîtière',
  `direction_id`             BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'une Direction fonctionnelle',
  `delegation_technique_id`  BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'une DT',
  `caisse_id`                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'une Caisse',
  `agence_id`                BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'une Agence',
  `guichet_id`               BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'un Guichet',
  `service_id`               BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Agent d\'un Service',
  `nom`                      VARCHAR(100)    NOT NULL,
  `prenom`                   VARCHAR(100)    NOT NULL,
  `sexe`                     VARCHAR(20)     NULL DEFAULT NULL,
  `email`                    VARCHAR(191)    NOT NULL COMMENT 'Email professionnel',
  `numero_telephone`         VARCHAR(30)     NULL DEFAULT NULL,
  `photo_path`               VARCHAR(255)    NULL DEFAULT NULL,
  `fonction`                 VARCHAR(100)    NOT NULL,
  `date_debut_fonction`      DATE            NULL DEFAULT NULL,
  `created_at`               TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`               TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agents_email_unique` (`email`),
  INDEX `agents_entite_id_foreign`               (`entite_id`),
  INDEX `agents_direction_id_foreign`            (`direction_id`),
  INDEX `agents_delegation_technique_id_foreign` (`delegation_technique_id`),
  INDEX `agents_caisse_id_foreign`               (`caisse_id`),
  INDEX `agents_agence_id_foreign`               (`agence_id`),
  INDEX `agents_guichet_id_foreign`              (`guichet_id`),
  INDEX `agents_service_id_foreign`              (`service_id`),
  CONSTRAINT `agents_entite_id_foreign`
    FOREIGN KEY (`entite_id`)               REFERENCES `entites`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_direction_id_foreign`
    FOREIGN KEY (`direction_id`)            REFERENCES `directions`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_caisse_id_foreign`
    FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_agence_id_foreign`
    FOREIGN KEY (`agence_id`)               REFERENCES `agences`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_guichet_id_foreign`
    FOREIGN KEY (`guichet_id`)              REFERENCES `guichets`              (`id`) ON DELETE SET NULL,
  CONSTRAINT `agents_service_id_foreign`
    FOREIGN KEY (`service_id`)              REFERENCES `services`              (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id`            BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Lien vers la fiche Agent',
  `name`                VARCHAR(191)    NOT NULL COMMENT 'Nom complet affiché',
  `email`               VARCHAR(191)    NOT NULL,
  `password`            VARCHAR(255)    NOT NULL,
  `email_verified_at`   TIMESTAMP       NULL DEFAULT NULL,
  `remember_token`      VARCHAR(100)    NULL DEFAULT NULL,
  `must_change_password` TINYINT(1)     NOT NULL DEFAULT 1,
  `role`                VARCHAR(50)     NOT NULL DEFAULT 'Agent',
  `theme_preference`    VARCHAR(50)     NOT NULL DEFAULT 'reference',
  `manager_id`          BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Supérieur direct N+1',
  `created_at`          TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique`    (`email`),
  UNIQUE KEY `users_agent_id_unique` (`agent_id`),
  INDEX `users_manager_id_foreign`   (`manager_id`),
  CONSTRAINT `users_agent_id_foreign`
    FOREIGN KEY (`agent_id`)   REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_manager_id_foreign`
    FOREIGN KEY (`manager_id`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `activites`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `activites`;
CREATE TABLE IF NOT EXISTS `activites` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `action`      VARCHAR(255)    NOT NULL COMMENT 'ex: CHANGEMENT_ROLE, VALIDATION_EVALUATION',
  `description` TEXT            NOT NULL,
  `ip_address`  VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`  VARCHAR(255)    NULL DEFAULT NULL,
  `created_at`  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `activites_user_id_foreign` (`user_id`),
  CONSTRAINT `activites_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 4 — RBAC (Spatie Laravel-Permission v6)
-- Remplace l'ancien système custom roles/permissions
-- =====================================================

-- -----------------------------------------------------
-- Table `permissions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL COMMENT 'ex: evaluations.valider',
  `guard_name` VARCHAR(255)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL COMMENT 'ex: PCA, DG, DGA, Directeur',
  `guard_name` VARCHAR(255)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `model_has_permissions`  (permissions directes sur un modèle)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type`    VARCHAR(255)    NOT NULL,
  `model_id`      BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  INDEX `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `model_has_roles`  (rôles attribués à un modèle)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id`    BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255)    NOT NULL,
  `model_id`   BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  INDEX `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `role_has_permissions`  (permissions attribuées à un rôle)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id`       BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign`
    FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 5 — OBJECTIFS
-- =====================================================

-- -----------------------------------------------------
-- Table `objectifs`  (objectifs stratégiques, assignés à une entité organisationnelle)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `objectifs`;
CREATE TABLE IF NOT EXISTS `objectifs` (
  `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `assignable_type`       VARCHAR(255)     NOT NULL COMMENT 'Type polymorphique (ex: App\\Models\\Direction)',
  `assignable_id`         BIGINT UNSIGNED  NOT NULL,
  `annee_id`              BIGINT UNSIGNED  NOT NULL,
  `date`                  DATE             NOT NULL,
  `date_echeance`         DATE             NOT NULL,
  `titre`                 VARCHAR(255)     NOT NULL,
  `commentaire`           TEXT             NULL DEFAULT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`            TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `objectifs_assignable_index`    (`assignable_type`, `assignable_id`),
  INDEX `objectifs_annee_id_foreign`    (`annee_id`),
  CONSTRAINT `objectifs_annee_id_foreign`
    FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `fiche_objectifs`  (contrats d'objectifs assignés à un user/entité)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fiche_objectifs`;
CREATE TABLE IF NOT EXISTS `fiche_objectifs` (
  `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `titre`                 VARCHAR(255)     NOT NULL,
  `annee_id`              BIGINT UNSIGNED  NOT NULL,
  `assignable_id`         BIGINT UNSIGNED  NOT NULL COMMENT 'Cible polymorphique (User, Direction…)',
  `assignable_type`       VARCHAR(255)     NOT NULL,
  `date`                  DATE             NOT NULL,
  `date_echeance`         DATE             NOT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `statut`                ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
  `created_at`            TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fiche_objectifs_annee_id_foreign`    (`annee_id`),
  INDEX `fiche_objectifs_assignable_index`    (`assignable_type`, `assignable_id`),
  CONSTRAINT `fiche_objectifs_annee_id_foreign`
    FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `lignes_fiche_objectif`  (lignes d'une fiche — description + note)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `lignes_fiche_objectif`;
CREATE TABLE IF NOT EXISTS `lignes_fiche_objectif` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fiche_objectif_id` BIGINT UNSIGNED NOT NULL,
  `description`       TEXT            NOT NULL,
  `note_obtenue`      DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
  `created_at`        TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `lignes_fiche_objectif_fiche_id_foreign` (`fiche_objectif_id`),
  CONSTRAINT `lignes_fiche_objectif_fiche_id_foreign`
    FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 6 — ÉVALUATIONS
-- =====================================================

-- -----------------------------------------------------
-- Table `subjective_criteria_templates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subjective_criteria_templates`;
CREATE TABLE IF NOT EXISTS `subjective_criteria_templates` (
  `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `ordre`       INT UNSIGNED     NOT NULL DEFAULT 0,
  `titre`       VARCHAR(255)     NOT NULL,
  `description` TEXT             NULL DEFAULT NULL,
  `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `subjective_subcriteria_templates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subjective_subcriteria_templates`;
CREATE TABLE IF NOT EXISTS `subjective_subcriteria_templates` (
  `id`                              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjective_criteria_template_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                           INT UNSIGNED    NOT NULL DEFAULT 0,
  `libelle`                         VARCHAR(255)    NOT NULL,
  `created_at`                      TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                      TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `subcriterion_template_fk` (`subjective_criteria_template_id`),
  CONSTRAINT `subcriterion_template_fk`
    FOREIGN KEY (`subjective_criteria_template_id`)
    REFERENCES `subjective_criteria_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `evaluations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id`                         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `evaluateur_id`              BIGINT UNSIGNED  NOT NULL,
  `evaluable_type`             VARCHAR(255)     NOT NULL COMMENT 'Type polymorphique (ex: App\\Models\\User)',
  `evaluable_id`               BIGINT UNSIGNED  NOT NULL,
  `evaluable_role`             VARCHAR(255)     NOT NULL DEFAULT 'agent',
  `fiche_objectif_id`          BIGINT UNSIGNED  NULL DEFAULT NULL,
  `annee_id`                   BIGINT UNSIGNED  NULL DEFAULT NULL,
  `date_debut`                 DATE             NOT NULL,
  `date_fin`                   DATE             NOT NULL,
  `moyenne_subjectifs`         DECIMAL(8,2)     NULL DEFAULT NULL,
  `moyenne_objectifs`          DECIMAL(8,2)     NULL DEFAULT NULL,
  `note_criteres_subjectifs`   DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `note_criteres_objectifs`    DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `note_objectifs`             TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `note_manuelle`              TINYINT UNSIGNED NULL DEFAULT NULL,
  `note_finale`                DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `commentaire`                TEXT             NULL DEFAULT NULL,
  `points_a_ameliorer`         TEXT             NULL DEFAULT NULL,
  `strategies_amelioration`    TEXT             NULL DEFAULT NULL,
  `commentaires_evalue`        TEXT             NULL DEFAULT NULL,
  `statut`                     ENUM('brouillon','soumis','valide','refuse') NOT NULL DEFAULT 'brouillon',
  `signature_evalue_nom`       VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_evalue`      DATE             NULL DEFAULT NULL,
  `signature_evaluateur_nom`   VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_evaluateur`  DATE             NULL DEFAULT NULL,
  `signature_directeur_nom`    VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_directeur`   DATE             NULL DEFAULT NULL,
  `created_at`                 TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`                 TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `evaluations_evaluateur_id_foreign`      (`evaluateur_id`),
  INDEX `evaluations_evaluable_index`            (`evaluable_type`, `evaluable_id`),
  INDEX `evaluations_fiche_objectif_id_foreign`  (`fiche_objectif_id`),
  INDEX `evaluations_annee_id_foreign`           (`annee_id`),
  CONSTRAINT `evaluations_evaluateur_id_foreign`
    FOREIGN KEY (`evaluateur_id`)     REFERENCES `users`          (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_fiche_objectif_id_foreign`
    FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs`(`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_annee_id_foreign`
    FOREIGN KEY (`annee_id`)          REFERENCES `annees`         (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `evaluation_identifications`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `evaluation_identifications`;
CREATE TABLE IF NOT EXISTS `evaluation_identifications` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`       BIGINT UNSIGNED NOT NULL,
  `nom_prenom`          VARCHAR(255)    NULL DEFAULT NULL,
  `semestre`            VARCHAR(20)     NULL DEFAULT NULL,
  `date_evaluation`     DATE            NULL DEFAULT NULL,
  `matricule`           VARCHAR(255)    NULL DEFAULT NULL,
  `poste`               VARCHAR(255)    NULL DEFAULT NULL,
  `emploi`              VARCHAR(255)    NULL DEFAULT NULL,
  `niveau`              VARCHAR(255)    NULL DEFAULT NULL,
  `direction`           VARCHAR(255)    NULL DEFAULT NULL,
  `direction_service`   VARCHAR(255)    NULL DEFAULT NULL,
  `date_confirmation`   DATE            NULL DEFAULT NULL,
  `categorie`           VARCHAR(255)    NULL DEFAULT NULL,
  `anciennete`          VARCHAR(255)    NULL DEFAULT NULL,
  `sexe`                VARCHAR(1)      NULL DEFAULT NULL,
  `date_recrutement`    DATE            NULL DEFAULT NULL,
  `date_titularisation` DATE            NULL DEFAULT NULL,
  `date_affectation`    DATE            NULL DEFAULT NULL,
  `date_naissance`      DATE            NULL DEFAULT NULL,
  `formations`          JSON            NULL DEFAULT NULL,
  `experiences`         JSON            NULL DEFAULT NULL,
  `created_at`          TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evaluation_identifications_evaluation_id_unique` (`evaluation_id`),
  CONSTRAINT `evaluation_identifications_evaluation_id_foreign`
    FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `evaluation_criteres`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `evaluation_criteres`;
CREATE TABLE IF NOT EXISTS `evaluation_criteres` (
  `id`                                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`                       BIGINT UNSIGNED NOT NULL,
  `type`                                VARCHAR(20)     NOT NULL COMMENT 'objectif ou subjectif',
  `ordre`                               INT UNSIGNED    NOT NULL DEFAULT 0,
  `titre`                               VARCHAR(255)    NOT NULL,
  `description`                         TEXT            NULL DEFAULT NULL,
  `note_globale`                        DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
  `observation`                         TEXT            NULL DEFAULT NULL,
  `source_fiche_objectif_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
  `source_fiche_objectif_objectif_id`   BIGINT UNSIGNED NULL DEFAULT NULL,
  `source_template_id`                  BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`                          TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `evaluation_criteres_evaluation_id_foreign`                    (`evaluation_id`),
  INDEX `evaluation_criteres_source_fiche_objectif_id_foreign`         (`source_fiche_objectif_id`),
  INDEX `evaluation_criteres_source_fiche_objectif_objectif_id_foreign`(`source_fiche_objectif_objectif_id`),
  INDEX `evaluation_criteres_source_template_id_foreign`               (`source_template_id`),
  CONSTRAINT `evaluation_criteres_evaluation_id_foreign`
    FOREIGN KEY (`evaluation_id`)                     REFERENCES `evaluations`              (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_id_foreign`
    FOREIGN KEY (`source_fiche_objectif_id`)          REFERENCES `fiche_objectifs`          (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_objectif_id_foreign`
    FOREIGN KEY (`source_fiche_objectif_objectif_id`) REFERENCES `lignes_fiche_objectif`    (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_template_id_foreign`
    FOREIGN KEY (`source_template_id`)                REFERENCES `subjective_criteria_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `evaluation_sous_criteres`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `evaluation_sous_criteres`;
CREATE TABLE IF NOT EXISTS `evaluation_sous_criteres` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_critere_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                 INT UNSIGNED    NOT NULL DEFAULT 0,
  `libelle`               VARCHAR(255)    NOT NULL,
  `note`                  DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
  `observation`           TEXT            NULL DEFAULT NULL,
  `created_at`            TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `evaluation_sous_criteres_evaluation_critere_id_foreign` (`evaluation_critere_id`),
  CONSTRAINT `evaluation_sous_criteres_evaluation_critere_id_foreign`
    FOREIGN KEY (`evaluation_critere_id`) REFERENCES `evaluation_criteres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SECTION 7 — ALERTES
-- =====================================================

-- -----------------------------------------------------
-- Table `alertes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `alertes`;
CREATE TABLE IF NOT EXISTS `alertes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       VARCHAR(255)    NOT NULL DEFAULT 'personnalisee' COMMENT 'securite | personnalisee',
  `priorite`   VARCHAR(255)    NOT NULL DEFAULT 'moyenne'       COMMENT 'basse | moyenne | haute | critique',
  `titre`      VARCHAR(255)    NOT NULL,
  `message`    TEXT            NULL DEFAULT NULL,
  `statut`     VARCHAR(255)    NOT NULL DEFAULT 'active'        COMMENT 'active | resolue | ignoree',
  `ip_address` VARCHAR(45)     NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `alertes_created_by_foreign` (`created_by`),
  CONSTRAINT `alertes_created_by_foreign`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `alerte_user`  (pivot BelongsToMany)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `alerte_user`;
CREATE TABLE IF NOT EXISTS `alerte_user` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alerte_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `lu`         TINYINT(1)      NOT NULL DEFAULT 0,
  `lu_at`      TIMESTAMP       NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alerte_user_alerte_id_user_id_unique` (`alerte_id`, `user_id`),
  INDEX `alerte_user_alerte_id_foreign` (`alerte_id`),
  INDEX `alerte_user_user_id_foreign`   (`user_id`),
  CONSTRAINT `alerte_user_alerte_id_foreign`
    FOREIGN KEY (`alerte_id`) REFERENCES `alertes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alerte_user_user_id_foreign`
    FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
