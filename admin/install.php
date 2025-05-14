<?php
// admin/install.php - Script d'installation de la base de données
// IMPORTANT: Supprimer ce fichier après utilisation!

// Inclure la configuration
require_once 'config.php';

try {
    // Connexion à la base de données
    $pdo = connectDB();
    
    // Tableau des requêtes SQL pour créer les tables
    $tables = [
        // Table des utilisateurs
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(100) NOT NULL,
            `role` enum('admin','editeur','moderateur') NOT NULL DEFAULT 'moderateur',
            `last_login` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des événements
        "CREATE TABLE IF NOT EXISTS `events` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(100) NOT NULL,
            `description` text,
            `date` date NOT NULL,
            `time` time DEFAULT NULL,
            `price` varchar(50) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table de la galerie
        "CREATE TABLE IF NOT EXISTS `gallery` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(100) NOT NULL,
            `description` text,
            `image_path` varchar(255) NOT NULL,
            `category` varchar(50) DEFAULT NULL,
            `sort_order` int(11) DEFAULT '0',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table du menu
        "CREATE TABLE IF NOT EXISTS `menus` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `category` varchar(50) NOT NULL,
            `name` varchar(100) NOT NULL,
            `description` text,
            `price` decimal(10,2) NOT NULL,
            `is_vegetarian` tinyint(1) DEFAULT '0',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `sort_order` int(11) DEFAULT '0',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des réservations
        "CREATE TABLE IF NOT EXISTS `reservations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `date` date NOT NULL,
            `time` time NOT NULL,
            `guests` int(11) NOT NULL,
            `occasion` varchar(50) DEFAULT NULL,
            `message` text,
            `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des messages de contact
        "CREATE TABLE IF NOT EXISTS `contact_messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `subject` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `is_read` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des logs d'activité
        "CREATE TABLE IF NOT EXISTS `activity_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `action` varchar(50) NOT NULL,
            `entity` varchar(50) NOT NULL,
            `entity_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    // Créer les tables
    $success = true;
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p class='success'>Table créée avec succès.</p>";
        } catch (PDOException $e) {
            echo "<p class='error'>Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
            $success = false;
        }
    }
    
    if ($success) {
        echo "<p class='success'><strong>Installation réussie!</strong> Toutes les tables ont été créées.</p>";
        echo "<p>Vous pouvez maintenant <a href='create_admin.php'>créer un compte administrateur</a>.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation de la base de données</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #8B4513;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
        }
        a {
            color: #8B4513;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Installation de la base de données Kanorelim</h1>
    <p>IMPORTANT: Supprimez ce fichier immédiatement après utilisation!</p>
</body>
</html>