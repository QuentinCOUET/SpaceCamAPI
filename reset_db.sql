-- Script de réinitialisation de la BD SpaceCamAPI
-- Exécutez ce script sur votre BD MySQL distante
--
-- Connectez-vous d'abord à la BD smartcam, puis copiez-collez tout ce contenu

-- 1. Supprimer la table des migrations
DROP TABLE IF EXISTS doctrine_migration_versions;

-- 2. Supprimer les anciennes tables
DROP TABLE IF EXISTS photo;
DROP TABLE IF EXISTS cam;
DROP TABLE IF EXISTS users;

-- 3. Créer la table users
CREATE TABLE users (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB;

-- 4. Créer la table cam
CREATE TABLE cam (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    ip_cam VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    owner_id INT DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_FA35EB687E3C61F9 (owner_id),
    CONSTRAINT FK_FA35EB687E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB;

-- 5. Créer la table photo
CREATE TABLE photo (
    id INT AUTO_INCREMENT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    cam_id INT DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_14B78418CBBCB156 (cam_id),
    CONSTRAINT FK_14B78418CBBCB156 FOREIGN KEY (cam_id) REFERENCES cam (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB;

-- 6. Créer la table des migrations (obligatoire pour Doctrine)
CREATE TABLE doctrine_migration_versions (
    version VARCHAR(191) NOT NULL,
    executed_at DATETIME DEFAULT NULL,
    execution_time INT DEFAULT NULL,
    PRIMARY KEY (version)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB;

-- 7. Marquer la migration comme exécutée (évite les erreurs Doctrine)
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time)
VALUES ('DoctrineMigrations\\Version20260401120000', NOW(), 0);

-- Vérification
SELECT 'Tables créées avec succès !' AS Status;
SHOW TABLES;

