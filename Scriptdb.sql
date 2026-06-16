-- =============================================================================
--  SGP-RCPB — Script DDL complet
--  Généré le 2026-06-10 — synchronisé avec migrate:fresh (schéma réel)
--  Compatible MySQL 8.0+ / MySQL Workbench (import EER)
--
--  Dépendances circulaires résolues via ALTER TABLE en fin de script :
--    entites ↔ agents ↔ directions / delegation_techniques / caisses / agences
--                     / guichets / services
-- =============================================================================

SET @OLD_UNIQUE_CHECKS      = @@UNIQUE_CHECKS,      UNIQUE_CHECKS      = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE,
    SQL_MODE = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------------------------------
-- Schema
-- -----------------------------------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `sgp_rcpb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sgp_rcpb`;


-- =============================================================================
-- SECTION 1 — INFRASTRUCTURE LARAVEL
-- =============================================================================

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` INT          NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)     NOT NULL,
  `payload`      LONGTEXT         NOT NULL,
  `attempts`     TINYINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED     NULL DEFAULT NULL,
  `available_at` INT UNSIGNED     NOT NULL,
  `created_at`   INT UNSIGNED     NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`, `reserved_at`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
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

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
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

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP    NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id`            VARCHAR(255)    NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address`    VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`    TEXT            NULL DEFAULT NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index`       (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 2 — SECURITE & CONFIGURATION
-- =============================================================================

DROP TABLE IF EXISTS `login_failures`;
CREATE TABLE `login_failures` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(255)    NULL DEFAULT NULL,
  `ip_address`   VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`   TEXT            NULL DEFAULT NULL,
  `attempted_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at`   TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `login_failures_email_index`        (`email`),
  KEY `login_failures_ip_address_index`   (`ip_address`),
  KEY `login_failures_attempted_at_index` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`        VARCHAR(255)    NOT NULL,
  `value`      TEXT            NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `custom_roles`;
CREATE TABLE `custom_roles` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`       VARCHAR(100)    NOT NULL COMMENT 'Valeur stockee dans users.role',
  `label`      VARCHAR(150)    NOT NULL COMMENT 'Libelle affiche dans l''interface',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `postes`;
CREATE TABLE `postes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fonction`   VARCHAR(100)    NOT NULL COMMENT 'Role de l''agent (ex: Agent, Conseiller DG)',
  `libelle`    VARCHAR(150)    NOT NULL COMMENT 'Intitule du poste affiche (ex: Caissier, Charge de credit)',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `postes_fonction_libelle_unique` (`fonction`, `libelle`),
  KEY `postes_fonction_index` (`fonction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 3 — STRUCTURE ORGANISATIONNELLE
--   Ordre : annees / semestres → entites → directions / delegation_techniques
--           → villes → caisses → agences → guichets → services → agents → users
--   Les FKs vers `agents` sont ajoutees via ALTER TABLE en fin de script
--   pour resoudre la dependance circulaire.
-- =============================================================================

DROP TABLE IF EXISTS `annees`;
CREATE TABLE `annees` (
  `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `annee`      SMALLINT UNSIGNED NOT NULL,
  `statut`     ENUM('ouvert','cloture') NOT NULL DEFAULT 'ouvert',
  `created_at` TIMESTAMP        NULL DEFAULT NULL,
  `updated_at` TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `annees_annee_unique` (`annee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `semestres`;
CREATE TABLE `semestres` (
  `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `annee_id`   BIGINT UNSIGNED  NOT NULL,
  `numero`     TINYINT UNSIGNED NOT NULL,
  `statut`     ENUM('ouvert','cloture') NOT NULL DEFAULT 'cloture',
  `created_at` TIMESTAMP        NULL DEFAULT NULL,
  `updated_at` TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `semestres_annee_id_numero_unique` (`annee_id`, `numero`),
  CONSTRAINT `semestres_annee_id_foreign`
    FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ entites — FKs vers agents ajoutees plus bas ]---------------------------
DROP TABLE IF EXISTS `entites`;
CREATE TABLE `entites` (
  `id`                      BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `singleton`               TINYINT UNSIGNED NOT NULL DEFAULT 1
                              COMMENT 'Valeur toujours 1. Contrainte UNIQUE garantit une seule ligne.',
  `nom`                     VARCHAR(255)     NOT NULL,
  `sigle`                   VARCHAR(30)      NULL DEFAULT NULL
                              COMMENT 'Sigle/acronyme affiche dans les evaluations, ex: FCPB',
  `ville`                   VARCHAR(255)     NOT NULL,
  `region`                  VARCHAR(255)     NULL DEFAULT NULL,
  `secretariat_telephone`   VARCHAR(30)      NULL DEFAULT NULL,
  `dg_agent_id`             BIGINT UNSIGNED  NULL DEFAULT NULL,
  `dga_agent_id`            BIGINT UNSIGNED  NULL DEFAULT NULL,
  `dga_secretaire_agent_id` BIGINT UNSIGNED  NULL DEFAULT NULL,
  `pca_agent_id`            BIGINT UNSIGNED  NULL DEFAULT NULL,
  `assistante_agent_id`     BIGINT UNSIGNED  NULL DEFAULT NULL,
  `created_at`              TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entites_singleton_unique`              (`singleton`),
  UNIQUE KEY `entites_nom_unique`                    (`nom`),
  KEY `entites_dg_agent_id_foreign`                  (`dg_agent_id`),
  KEY `entites_dga_agent_id_foreign`                 (`dga_agent_id`),
  KEY `entites_dga_secretaire_agent_id_foreign`      (`dga_secretaire_agent_id`),
  KEY `entites_pca_agent_id_foreign`                 (`pca_agent_id`),
  KEY `entites_assistante_agent_id_foreign`          (`assistante_agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ directions — FK vers agents ajoutee plus bas ]--------------------------
DROP TABLE IF EXISTS `directions`;
CREATE TABLE `directions` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                 VARCHAR(255)    NOT NULL,
  `entite_id`           BIGINT UNSIGNED NULL DEFAULT NULL,
  `directeur_agent_id`  BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`          TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `directions_entite_id_foreign`           (`entite_id`),
  KEY `directions_directeur_agent_id_foreign`  (`directeur_agent_id`),
  KEY `directions_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `directions_entite_id_foreign`
    FOREIGN KEY (`entite_id`) REFERENCES `entites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ delegation_techniques — FK vers agents ajoutee plus bas ]---------------
DROP TABLE IF EXISTS `delegation_techniques`;
CREATE TABLE `delegation_techniques` (
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
  KEY `delegation_techniques_entite_id_foreign`           (`entite_id`),
  KEY `delegation_techniques_directeur_agent_id_foreign`  (`directeur_agent_id`),
  KEY `delegation_techniques_secretaire_agent_id_foreign` (`secretaire_agent_id`),
  CONSTRAINT `delegation_techniques_entite_id_foreign`
    FOREIGN KEY (`entite_id`) REFERENCES `entites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `villes`;
CREATE TABLE `villes` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id` BIGINT UNSIGNED NOT NULL,
  `nom`                     VARCHAR(255)    NOT NULL,
  `created_at`              TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villes_dt_nom_unique` (`delegation_technique_id`, `nom`),
  KEY `villes_delegation_technique_id_foreign` (`delegation_technique_id`),
  CONSTRAINT `villes_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ caisses — FK vers agents ajoutee plus bas ]-----------------------------
DROP TABLE IF EXISTS `caisses`;
CREATE TABLE `caisses` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delegation_technique_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ville_id`                BIGINT UNSIGNED NULL DEFAULT NULL,
  `nom`                     VARCHAR(255)    NOT NULL,
  `annee_ouverture`         VARCHAR(4)      NOT NULL,
  `quartier`                VARCHAR(255)    NULL DEFAULT NULL,
  `secretariat_telephone`   VARCHAR(30)     NULL DEFAULT NULL,
  `directeur_agent_id`      BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`              TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `caisses_nom_unique` (`nom`),
  KEY `caisses_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `caisses_ville_id_foreign`                (`ville_id`),
  KEY `caisses_directeur_agent_id_foreign`      (`directeur_agent_id`),
  KEY `caisses_secretaire_agent_id_foreign`     (`secretaire_agent_id`),
  CONSTRAINT `caisses_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `caisses_ville_id_foreign`
    FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ agences — FK vers agents ajoutee plus bas ]-----------------------------
DROP TABLE IF EXISTS `agences`;
CREATE TABLE `agences` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                     VARCHAR(255)    NOT NULL,
  `delegation_technique_id` BIGINT UNSIGNED NOT NULL,
  `caisse_id`               BIGINT UNSIGNED NOT NULL COMMENT 'Caisse parente',
  `chef_agent_id`           BIGINT UNSIGNED NULL DEFAULT NULL,
  `secretaire_agent_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
  `telephone_accueil`       VARCHAR(30)     NULL DEFAULT NULL,
  `created_at`              TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agences_delegation_nom_unique` (`delegation_technique_id`, `nom`),
  KEY `agences_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `agences_caisse_id_foreign`               (`caisse_id`),
  KEY `agences_chef_agent_id_foreign`           (`chef_agent_id`),
  KEY `agences_secretaire_agent_id_foreign`     (`secretaire_agent_id`),
  CONSTRAINT `agences_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agences_caisse_id_foreign`
    FOREIGN KEY (`caisse_id`) REFERENCES `caisses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ guichets — FK vers agents ajoutee plus bas ]----------------------------
DROP TABLE IF EXISTS `guichets`;
CREATE TABLE `guichets` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`               VARCHAR(255)    NOT NULL,
  `agence_id`         BIGINT UNSIGNED NOT NULL,
  `chef_agent_id`     BIGINT UNSIGNED NULL DEFAULT NULL,
  `telephone_accueil` VARCHAR(30)     NULL DEFAULT NULL,
  `created_at`        TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guichets_agence_id_foreign`     (`agence_id`),
  KEY `guichets_chef_agent_id_foreign` (`chef_agent_id`),
  CONSTRAINT `guichets_agence_id_foreign`
    FOREIGN KEY (`agence_id`) REFERENCES `agences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---[ services — FK vers agents ajoutee plus bas ]----------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                     VARCHAR(255)    NOT NULL,
  `direction_id`            BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Service d''une Direction centrale (exclusif)',
  `delegation_technique_id` BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Service d''une DT (exclusif)',
  `caisse_id`               BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Service d''une Caisse (exclusif)',
  `chef_agent_id`           BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`              TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_direction_id_foreign`            (`direction_id`),
  KEY `services_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `services_caisse_id_foreign`               (`caisse_id`),
  KEY `services_chef_agent_id_foreign`           (`chef_agent_id`),
  CONSTRAINT `services_direction_id_foreign`
    FOREIGN KEY (`direction_id`)            REFERENCES `directions`            (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_delegation_technique_id_foreign`
    FOREIGN KEY (`delegation_technique_id`) REFERENCES `delegation_techniques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_caisse_id_foreign`
    FOREIGN KEY (`caisse_id`)               REFERENCES `caisses`               (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `agents`;
CREATE TABLE `agents` (
  `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entite_id`               BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'DG, DGA, PCA, Assistante, Conseillers rattaches a la faitiere',
  `direction_id`            BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''une Direction fonctionnelle',
  `delegation_technique_id` BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''une DT',
  `caisse_id`               BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''une Caisse',
  `agence_id`               BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''une Agence',
  `guichet_id`              BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''un Guichet',
  `service_id`              BIGINT UNSIGNED NULL DEFAULT NULL
                              COMMENT 'Agent d''un Service',
  `nom`                     VARCHAR(100)    NOT NULL,
  `prenom`                  VARCHAR(100)    NOT NULL,
  `sexe`                    VARCHAR(20)     NULL DEFAULT NULL,
  `email`                   VARCHAR(191)    NOT NULL
                              COMMENT 'Email professionnel — peut differer de users.email',
  `numero_telephone`        VARCHAR(30)     NULL DEFAULT NULL,
  `photo_path`              VARCHAR(255)    NULL DEFAULT NULL,
  `matricule`               VARCHAR(50)     NOT NULL COMMENT 'Matricule unique de l''agent',
  `role`                    VARCHAR(100)    NOT NULL
                              COMMENT 'Categorie de role : Agent, Conseiller DG, Chef de Service…',
  `fonction`                VARCHAR(100)    NULL DEFAULT NULL,
  `poste`                   VARCHAR(150)    NULL DEFAULT NULL
                              COMMENT 'Poste specifique : Caissier prestataire, Charge de securite…',
  `date_debut_fonction`     DATE            NULL DEFAULT NULL,
  `created_at`              TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agents_email_unique`     (`email`),
  UNIQUE KEY `agents_matricule_unique` (`matricule`),
  KEY `agents_entite_id_foreign`               (`entite_id`),
  KEY `agents_direction_id_foreign`            (`direction_id`),
  KEY `agents_delegation_technique_id_foreign` (`delegation_technique_id`),
  KEY `agents_caisse_id_foreign`               (`caisse_id`),
  KEY `agents_agence_id_foreign`               (`agence_id`),
  KEY `agents_guichet_id_foreign`              (`guichet_id`),
  KEY `agents_service_id_foreign`              (`service_id`),
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

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agent_id`             BIGINT UNSIGNED NULL DEFAULT NULL
                           COMMENT 'Lien vers la fiche Agent de cet utilisateur',
  `name`                 VARCHAR(191)    NOT NULL
                           COMMENT 'Nom complet affiche (sync depuis agent.nom + agent.prenom)',
  `email`                VARCHAR(191)    NOT NULL,
  `password`             VARCHAR(255)    NOT NULL,
  `email_verified_at`    TIMESTAMP       NULL DEFAULT NULL,
  `remember_token`       VARCHAR(100)    NULL DEFAULT NULL,
  `must_change_password` TINYINT(1)      NOT NULL DEFAULT 1
                           COMMENT 'Force le changement de mot de passe a la premiere connexion',
  `password_plain`       VARCHAR(255)    NULL DEFAULT NULL
                           COMMENT 'Mot de passe en clair defini par l''admin (efface apres changement)',
  `role`                 VARCHAR(50)     NOT NULL DEFAULT 'Agent'
                           COMMENT 'Role systeme : DG | DGA | Directeur_Caisse | Chef_Service | Agent …',
  `theme_preference`     VARCHAR(50)     NOT NULL DEFAULT 'reference',
  `is_active`            TINYINT(1)      NOT NULL DEFAULT 1
                           COMMENT 'Compte active par l''admin. Desactive = connexion refusee.',
  `blocked_until`        DATETIME        NULL DEFAULT NULL
                           COMMENT 'Compte suspendu jusqu''a cette date (anti-brute force)',
  `manager_id`           BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`           TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`           TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique`    (`email`),
  UNIQUE KEY `users_agent_id_unique` (`agent_id`),
  KEY `users_manager_id_foreign`     (`manager_id`),
  CONSTRAINT `users_agent_id_foreign`
    FOREIGN KEY (`agent_id`)   REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_manager_id_foreign`
    FOREIGN KEY (`manager_id`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `activites`;
CREATE TABLE `activites` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `action`      VARCHAR(255)    NOT NULL COMMENT 'ex: CHANGEMENT_ROLE, VALIDATION_EVALUATION',
  `description` TEXT            NOT NULL,
  `ip_address`  VARCHAR(45)     NULL DEFAULT NULL,
  `user_agent`  VARCHAR(255)    NULL DEFAULT NULL,
  `created_at`  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activites_user_id_foreign` (`user_id`),
  CONSTRAINT `activites_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_name`      VARCHAR(255)    NULL DEFAULT NULL,
  `auditable_type` VARCHAR(255)    NOT NULL,
  `auditable_id`   BIGINT UNSIGNED NOT NULL,
  `action`         VARCHAR(255)    NOT NULL,
  `old_values`     LONGTEXT        NULL DEFAULT NULL COMMENT 'JSON',
  `new_values`     LONGTEXT        NULL DEFAULT NULL COMMENT 'JSON',
  `description`    VARCHAR(255)    NULL DEFAULT NULL,
  `ip_address`     VARCHAR(45)     NULL DEFAULT NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`, `auditable_id`),
  KEY `audit_logs_user_id_index`    (`user_id`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `formations`;
CREATE TABLE `formations` (
  `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `agent_id`         BIGINT UNSIGNED  NOT NULL,
  `theme`            VARCHAR(255)     NOT NULL COMMENT 'Theme de la formation',
  `domaine`          VARCHAR(60)      NOT NULL COMMENT 'Domaine : management, informatique, finance…',
  `date_debut`       DATE             NOT NULL,
  `date_fin`         DATE             NOT NULL,
  `duree_heures`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `type`             VARCHAR(20)      NOT NULL DEFAULT 'interne' COMMENT 'interne ou externe',
  `attestation_path` VARCHAR(500)     NULL DEFAULT NULL,
  `statut`           VARCHAR(20)      NOT NULL DEFAULT 'validee'
                       COMMENT 'en_attente | validee | refusee',
  `motif_refus`      TEXT             NULL DEFAULT NULL,
  `formateur_id`     BIGINT UNSIGNED  NULL DEFAULT NULL,
  `created_by`       BIGINT UNSIGNED  NOT NULL,
  `created_at`       TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`       TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formations_agent_id_foreign`     (`agent_id`),
  KEY `formations_formateur_id_foreign` (`formateur_id`),
  KEY `formations_created_by_foreign`   (`created_by`),
  CONSTRAINT `formations_agent_id_foreign`
    FOREIGN KEY (`agent_id`)     REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `formations_formateur_id_foreign`
    FOREIGN KEY (`formateur_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `formations_created_by_foreign`
    FOREIGN KEY (`created_by`)   REFERENCES `users`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 4 — RBAC (Spatie Laravel-Permission v6)
-- =============================================================================

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL COMMENT 'ex: evaluations.valider',
  `guard_name` VARCHAR(255)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL COMMENT 'ex: PCA, DG, DGA, Directeur',
  `guard_name` VARCHAR(255)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type`    VARCHAR(255)    NOT NULL,
  `model_id`      BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id`    BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255)    NOT NULL,
  `model_id`   BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id`       BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign`
    FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 5 — OBJECTIFS & FICHES
-- =============================================================================

DROP TABLE IF EXISTS `objectifs`;
CREATE TABLE `objectifs` (
  `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `assignable_type`       VARCHAR(255)     NOT NULL
                            COMMENT 'Type polymorphique (ex: App\\Models\\Direction)',
  `assignable_id`         BIGINT UNSIGNED  NOT NULL,
  `annee_id`              BIGINT UNSIGNED  NOT NULL,
  `date`                  DATE             NOT NULL,
  `date_echeance`         DATE             NOT NULL,
  `titre`                 VARCHAR(255)     NOT NULL,
  `commentaire`           TEXT             NULL DEFAULT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`            TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP        NULL DEFAULT NULL,
  `deleted_at`            TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `objectifs_assignable_type_assignable_id_index` (`assignable_type`, `assignable_id`),
  KEY `objectifs_annee_id_foreign`                    (`annee_id`),
  CONSTRAINT `objectifs_annee_id_foreign`
    FOREIGN KEY (`annee_id`) REFERENCES `annees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fiche_objectifs`;
CREATE TABLE `fiche_objectifs` (
  `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `titre`                 VARCHAR(255)     NOT NULL,
  `annee_id`              BIGINT UNSIGNED  NOT NULL,
  `assignable_id`         BIGINT UNSIGNED  NOT NULL
                            COMMENT 'Cible polymorphique (User, Direction…)',
  `assignable_type`       VARCHAR(255)     NOT NULL,
  `date`                  DATE             NOT NULL,
  `date_echeance`         DATE             NOT NULL,
  `date_validation`       DATE             NULL DEFAULT NULL,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `statut`                ENUM('brouillon','en_attente','acceptee','refusee','conte') NOT NULL DEFAULT 'brouillon',
  `motif_refus`           TEXT             NULL DEFAULT NULL,
  `created_by`            BIGINT UNSIGNED  NULL DEFAULT NULL
                            COMMENT 'User qui a cree/assigne la fiche',
  `created_at`            TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP        NULL DEFAULT NULL,
  `deleted_at`            TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiche_objectifs_unique_per_year` (`annee_id`, `assignable_type`, `assignable_id`),
  KEY `fiche_objectifs_assignable_index`       (`assignable_type`, `assignable_id`),
  KEY `fiche_objectifs_created_by_foreign`     (`created_by`),
  CONSTRAINT `fiche_objectifs_annee_id_foreign`
    FOREIGN KEY (`annee_id`)   REFERENCES `annees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fiche_objectifs_created_by_foreign`
    FOREIGN KEY (`created_by`) REFERENCES `users`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `lignes_fiche_objectif`;
CREATE TABLE `lignes_fiche_objectif` (
  `id`                    BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `fiche_objectif_id`     BIGINT UNSIGNED  NOT NULL,
  `description`           TEXT             NOT NULL,
  `note_obtenue`          DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
  `avancement_percentage` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `statut`                VARCHAR(255)     NOT NULL DEFAULT 'normal',
  `motif`                 TEXT             NULL DEFAULT NULL,
  `created_at`            TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lignes_fiche_objectif_fiche_objectif_id_foreign` (`fiche_objectif_id`),
  CONSTRAINT `lignes_fiche_objectif_fiche_objectif_id_foreign`
    FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 6 — EVALUATIONS
-- =============================================================================

DROP TABLE IF EXISTS `subjective_criteria_templates`;
CREATE TABLE `subjective_criteria_templates` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ordre`       INT UNSIGNED    NOT NULL DEFAULT 0,
  `titre`       VARCHAR(255)    NOT NULL,
  `description` TEXT            NULL DEFAULT NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `subjective_subcriteria_templates`;
CREATE TABLE `subjective_subcriteria_templates` (
  `id`                              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjective_criteria_template_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                           INT UNSIGNED    NOT NULL DEFAULT 0,
  `libelle`                         VARCHAR(255)    NOT NULL,
  `created_at`                      TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                      TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subcriterion_template_fk` (`subjective_criteria_template_id`),
  CONSTRAINT `subcriterion_template_fk`
    FOREIGN KEY (`subjective_criteria_template_id`)
    REFERENCES `subjective_criteria_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE `evaluations` (
  `id`                        BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `evaluateur_id`             BIGINT UNSIGNED  NOT NULL,
  `evaluable_type`            VARCHAR(255)     NOT NULL
                                COMMENT 'Type polymorphique (ex: App\\Models\\User)',
  `evaluable_id`              BIGINT UNSIGNED  NOT NULL,
  `evaluable_role`            VARCHAR(255)     NOT NULL DEFAULT 'agent',
  `fiche_objectif_id`         BIGINT UNSIGNED  NULL DEFAULT NULL,
  `annee_id`                  BIGINT UNSIGNED  NULL DEFAULT NULL,
  `semestre_id`               BIGINT UNSIGNED  NULL DEFAULT NULL,
  `date_debut`                DATE             NOT NULL,
  `date_fin`                  DATE             NOT NULL,
  `moyenne_subjectifs`        DECIMAL(8,2)     NULL DEFAULT NULL,
  `moyenne_objectifs`         DECIMAL(8,2)     NULL DEFAULT NULL,
  `note_criteres_subjectifs`  DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `note_criteres_objectifs`   DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `note_objectifs`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `note_manuelle`             TINYINT UNSIGNED NULL DEFAULT NULL,
  `note_finale`               DECIMAL(8,2)     NOT NULL DEFAULT 0.00,
  `commentaire`               TEXT             NULL DEFAULT NULL,
  `points_a_ameliorer`        TEXT             NULL DEFAULT NULL,
  `strategies_amelioration`   TEXT             NULL DEFAULT NULL,
  `commentaires_evalue`       TEXT             NULL DEFAULT NULL,
  `statut`                    ENUM('brouillon','soumis','valide','refuse','reclamation','a_reviser')
                                NOT NULL DEFAULT 'brouillon',
  `motif_refus`               TEXT             NULL DEFAULT NULL,
  `reclamation`               TEXT             NULL DEFAULT NULL,
  `statut_reclamation`        VARCHAR(20)      NULL DEFAULT NULL,
  `signature_evalue_nom`      VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_evalue`     DATE             NULL DEFAULT NULL,
  `signature_evaluateur_nom`  VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_evaluateur` DATE             NULL DEFAULT NULL,
  `signature_directeur_nom`   VARCHAR(255)     NULL DEFAULT NULL,
  `date_signature_directeur`  DATE             NULL DEFAULT NULL,
  `created_at`                TIMESTAMP        NULL DEFAULT NULL,
  `updated_at`                TIMESTAMP        NULL DEFAULT NULL,
  `deleted_at`                TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluations_evaluateur_id_foreign`             (`evaluateur_id`),
  KEY `evaluations_evaluable_type_evaluable_id_index` (`evaluable_type`, `evaluable_id`),
  KEY `evaluations_fiche_objectif_id_foreign`         (`fiche_objectif_id`),
  KEY `evaluations_annee_id_foreign`                  (`annee_id`),
  KEY `evaluations_semestre_id_foreign`               (`semestre_id`),
  CONSTRAINT `evaluations_evaluateur_id_foreign`
    FOREIGN KEY (`evaluateur_id`)     REFERENCES `users`          (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_fiche_objectif_id_foreign`
    FOREIGN KEY (`fiche_objectif_id`) REFERENCES `fiche_objectifs`(`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_annee_id_foreign`
    FOREIGN KEY (`annee_id`)          REFERENCES `annees`         (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluations_semestre_id_foreign`
    FOREIGN KEY (`semestre_id`)       REFERENCES `semestres`      (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluation_identifications`;
CREATE TABLE `evaluation_identifications` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`       BIGINT UNSIGNED NOT NULL,
  `nom_prenom`          VARCHAR(255)    NULL DEFAULT NULL,
  `semestre`            VARCHAR(20)     NULL DEFAULT NULL,
  `date_evaluation`     DATE            NULL DEFAULT NULL,
  `matricule`           VARCHAR(255)    NULL DEFAULT NULL,
  `grade`               VARCHAR(255)    NULL DEFAULT NULL,
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
  `date_prise_fonction` DATE            NULL DEFAULT NULL,
  `date_naissance`      DATE            NULL DEFAULT NULL,
  `formations`          LONGTEXT        NULL DEFAULT NULL COMMENT 'JSON',
  `experiences`         LONGTEXT        NULL DEFAULT NULL COMMENT 'JSON',
  `created_at`          TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evaluation_identifications_evaluation_id_unique` (`evaluation_id`),
  CONSTRAINT `evaluation_identifications_evaluation_id_foreign`
    FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluation_criteres`;
CREATE TABLE `evaluation_criteres` (
  `id`                                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_id`                     BIGINT UNSIGNED NOT NULL,
  `type`                              VARCHAR(20)     NOT NULL COMMENT 'objectif ou subjectif',
  `ordre`                             INT UNSIGNED    NOT NULL DEFAULT 0,
  `titre`                             VARCHAR(255)    NOT NULL,
  `description`                       TEXT            NULL DEFAULT NULL,
  `note_globale`                      DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
  `observation`                       TEXT            NULL DEFAULT NULL,
  `source_fiche_objectif_id`          BIGINT UNSIGNED NULL DEFAULT NULL,
  `source_fiche_objectif_objectif_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `source_template_id`                BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at`                        TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`                        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_criteres_evaluation_id_foreign`                     (`evaluation_id`),
  KEY `evaluation_criteres_source_fiche_objectif_id_foreign`          (`source_fiche_objectif_id`),
  KEY `evaluation_criteres_source_fiche_objectif_objectif_id_foreign` (`source_fiche_objectif_objectif_id`),
  KEY `evaluation_criteres_source_template_id_foreign`                (`source_template_id`),
  CONSTRAINT `evaluation_criteres_evaluation_id_foreign`
    FOREIGN KEY (`evaluation_id`)                     REFERENCES `evaluations`                   (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_id_foreign`
    FOREIGN KEY (`source_fiche_objectif_id`)          REFERENCES `fiche_objectifs`               (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_fiche_objectif_objectif_id_foreign`
    FOREIGN KEY (`source_fiche_objectif_objectif_id`) REFERENCES `lignes_fiche_objectif`         (`id`) ON DELETE SET NULL,
  CONSTRAINT `evaluation_criteres_source_template_id_foreign`
    FOREIGN KEY (`source_template_id`)                REFERENCES `subjective_criteria_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `evaluation_sous_criteres`;
CREATE TABLE `evaluation_sous_criteres` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `evaluation_critere_id` BIGINT UNSIGNED NOT NULL,
  `ordre`                 INT UNSIGNED    NOT NULL DEFAULT 0,
  `libelle`               VARCHAR(255)    NOT NULL,
  `note`                  DECIMAL(8,2)    NOT NULL DEFAULT 0.00,
  `observation`           TEXT            NULL DEFAULT NULL,
  `created_at`            TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`            TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `evaluation_sous_criteres_evaluation_critere_id_foreign` (`evaluation_critere_id`),
  CONSTRAINT `evaluation_sous_criteres_evaluation_critere_id_foreign`
    FOREIGN KEY (`evaluation_critere_id`) REFERENCES `evaluation_criteres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 7 — ALERTES
-- =============================================================================

DROP TABLE IF EXISTS `alertes`;
CREATE TABLE `alertes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`       VARCHAR(255)    NOT NULL DEFAULT 'personnalisee'
                 COMMENT 'securite | personnalisee',
  `priorite`   VARCHAR(255)    NOT NULL DEFAULT 'moyenne'
                 COMMENT 'basse | moyenne | haute | critique',
  `titre`      VARCHAR(255)    NOT NULL,
  `message`    TEXT            NULL DEFAULT NULL,
  `statut`     VARCHAR(255)    NOT NULL DEFAULT 'active'
                 COMMENT 'active | resolue | ignoree',
  `lien`       VARCHAR(2048)   NULL DEFAULT NULL,
  `ip_address` VARCHAR(45)     NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alertes_created_by_foreign` (`created_by`),
  CONSTRAINT `alertes_created_by_foreign`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `alerte_user`;
CREATE TABLE `alerte_user` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alerte_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `lu`         TINYINT(1)      NOT NULL DEFAULT 0,
  `lu_at`      TIMESTAMP       NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NULL DEFAULT NULL,
  `updated_at` TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alerte_user_alerte_id_user_id_unique` (`alerte_id`, `user_id`),
  KEY `alerte_user_alerte_id_foreign` (`alerte_id`),
  KEY `alerte_user_user_id_foreign`   (`user_id`),
  CONSTRAINT `alerte_user_alerte_id_foreign`
    FOREIGN KEY (`alerte_id`) REFERENCES `alertes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alerte_user_user_id_foreign`
    FOREIGN KEY (`user_id`)   REFERENCES `users`   (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 8 — ALTER TABLE : resolution des dependances circulaires
--   agents <-> entites / directions / delegation_techniques / caisses
--             / agences / guichets / services
-- =============================================================================

ALTER TABLE `entites`
  ADD CONSTRAINT `entites_dg_agent_id_foreign`
    FOREIGN KEY (`dg_agent_id`)             REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_dga_agent_id_foreign`
    FOREIGN KEY (`dga_agent_id`)            REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_dga_secretaire_agent_id_foreign`
    FOREIGN KEY (`dga_secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_pca_agent_id_foreign`
    FOREIGN KEY (`pca_agent_id`)            REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entites_assistante_agent_id_foreign`
    FOREIGN KEY (`assistante_agent_id`)     REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `directions`
  ADD CONSTRAINT `directions_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `directions_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `delegation_techniques`
  ADD CONSTRAINT `delegation_techniques_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delegation_techniques_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `caisses`
  ADD CONSTRAINT `caisses_directeur_agent_id_foreign`
    FOREIGN KEY (`directeur_agent_id`)  REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `caisses_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `agences`
  ADD CONSTRAINT `agences_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`)       REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agences_secretaire_agent_id_foreign`
    FOREIGN KEY (`secretaire_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `guichets`
  ADD CONSTRAINT `guichets_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

ALTER TABLE `services`
  ADD CONSTRAINT `services_chef_agent_id_foreign`
    FOREIGN KEY (`chef_agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;


-- =============================================================================
-- Fin du script
-- =============================================================================
SET SQL_MODE            = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS  = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS       = @OLD_UNIQUE_CHECKS;
