<?php
/**
 * Script de réinitialisation de la BD SpaceCamAPI
 * Exécutez ce fichier une seule fois via: http://localhost:8000/reset.php
 *
 * ⚠️ À SUPPRIMER après exécution pour des raisons de sécurité !
 */

// Configuration
$host = '172.16.124.5';
$user = 'smartcam_admin';
$password = '2{W#v"W+?!M-NgK';
$database = 'smartcam';

echo "🔄 Réinitialisation de la BD SmartCam...\n\n";

// Connexion à MySQL
try {
    $mysqli = new mysqli($host, $user, $password, $database);

    if ($mysqli->connect_error) {
        die("❌ Erreur de connexion: " . $mysqli->connect_error);
    }

    echo "✅ Connecté à la BD\n\n";

    // SQL à exécuter
    $sql = "
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
    
    -- 6. Créer la table des migrations
    CREATE TABLE doctrine_migration_versions (
        version VARCHAR(191) NOT NULL,
        executed_at DATETIME DEFAULT NULL,
        execution_time INT DEFAULT NULL,
        PRIMARY KEY (version)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB;
    
    -- 7. Marquer la migration comme exécutée
    INSERT INTO doctrine_migration_versions (version, executed_at, execution_time)
    VALUES ('DoctrineMigrations\\\\Version20260401120000', NOW(), 0);
    ";

    // Diviser le SQL en requêtes individuelles
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    $count = 0;
    foreach ($queries as $query) {
        if (!empty($query) && !str_starts_with(trim($query), '--')) {
            if ($mysqli->query($query)) {
                $count++;
                echo "✅ Requête " . $count . " exécutée\n";
            } else {
                echo "⚠️  Erreur requête: " . $mysqli->error . "\n";
            }
        }
    }

    echo "\n✨ BD réinitialisée avec succès !\n";
    echo "📊 Tables créées: users, cam, photo, doctrine_migration_versions\n";
    echo "🔒 Migrations marquées comme exécutées\n";

    $mysqli->close();

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}

echo "\n⚠️  N'OUBLIEZ PAS DE SUPPRIMER CE FICHIER APRÈS EXÉCUTION !\n";
?>

