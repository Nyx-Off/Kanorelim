<?php
// admin/install_additional_tables.php - Script pour créer les tables supplémentaires
// IMPORTANT: Supprimer ce fichier après utilisation!

// Inclure la configuration
require_once 'config.php';

try {
    // Connexion à la base de données
    $pdo = connectDB();
    
    // Tableau des requêtes SQL pour créer les nouvelles tables
    $tables = [
        // Table des réservations d'événements
        "CREATE TABLE IF NOT EXISTS `event_reservations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `event_name` varchar(255) NOT NULL,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `guests` int(11) NOT NULL,
            `message` text,
            `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des paramètres du site
        "CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `setting_type` varchar(50) DEFAULT 'text',
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Table des newsletters
        "CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `name` varchar(100) DEFAULT NULL,
            `status` enum('active','inactive','unsubscribed') NOT NULL DEFAULT 'active',
            `subscribed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `unsubscribed_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
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
    
    // Insérer quelques paramètres par défaut
    if ($success) {
        $default_settings = [
            ['site_name', 'Taverne Kanorelim', 'text'],
            ['site_description', 'Une expérience médiévale authentique', 'text'],
            ['admin_email', 'contact@kanorelim.fr', 'email'],
            ['phone', '+33 (0)1 23 45 67 89', 'text'],
            ['address', "12 Rue des Templiers\nCité Médiévale\n95300 Pontoise\nFrance", 'textarea'],
            ['horaires', '{"Lundi - Jeudi":"11h - 23h","Vendredi - Samedi":"11h - 01h","Dimanche":"12h - 22h"}', 'json'],
            ['reseaux_sociaux', '{"facebook":"https://facebook.com/tavernekanorelim","instagram":"https://instagram.com/tavernekanorelim","twitter":"https://twitter.com/tavernekanorelim"}', 'json']
        ];
        
        foreach ($default_settings as $setting) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)");
                $stmt->execute($setting);
            } catch (PDOException $e) {
                echo "<p class='warning'>Paramètre " . $setting[0] . " non ajouté: " . $e->getMessage() . "</p>";
            }
        }
        
        // Vérifier si des données de démonstration existent déjà
        $stmt = $pdo->query("SELECT COUNT(*) FROM events");
        $event_count = $stmt->fetchColumn();
        
        if ($event_count == 0) {
            // Ajouter quelques événements de démonstration
            $demo_events = [
                [
                    'Festin du Printemps',
                    'Grand banquet avec musique de troubadours et spectacle de jongleurs. Un festin digne des plus grandes cours médiévales.',
                    date('Y-m-d', strtotime('+7 days')),
                    '19:00:00',
                    '35€',
                    '/api/placeholder/800/500',
                    1
                ],
                [
                    'Tournoi de Dés Anciens',
                    'Participez à notre tournoi de jeux médiévaux avec prix à la clé. Découvrez des jeux authentiques du Moyen Âge.',
                    date('Y-m-d', strtotime('+14 days')),
                    '20:00:00',
                    'Gratuit',
                    '/api/placeholder/800/500',
                    1
                ],
                [
                    'Soirée Contes et Légendes',
                    'Une soirée enchantée avec notre conteur royal et dégustation d\'hypocras.',
                    date('Y-m-d', strtotime('+21 days')),
                    '20:30:00',
                    '15€',
                    '/api/placeholder/800/500',
                    1
                ]
            ];
            
            foreach ($demo_events as $event) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO events (title, description, date, time, price, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute($event);
                } catch (PDOException $e) {
                    echo "<p class='warning'>Événement de démonstration non ajouté: " . $e->getMessage() . "</p>";
                }
            }
            echo "<p class='success'>Événements de démonstration ajoutés.</p>";
        }
        
        // Vérifier si des éléments de menu existent déjà
        $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
        $menu_count = $stmt->fetchColumn();
        
        if ($menu_count == 0) {
            // Ajouter quelques éléments de menu de démonstration
            $demo_menu = [
                ['Boissons', 'Hydromel Royal', 'Notre hydromel artisanal vieilli en fût de chêne, préparé selon les recettes secrètes des moines brasseurs.', 7.00, 0, 1, 1],
                ['Boissons', 'Cervoise Ambrée', 'Bière artisanale aux notes de miel et de houblon, brassée selon les méthodes ancestrales.', 6.00, 0, 1, 2],
                ['Entrées', 'Potage du Jour', 'Soupe épaisse de légumes de saison, servie dans une miche de pain creusée.', 8.00, 1, 1, 1],
                ['Plats Principaux', 'Cochon Rôti', 'Cuit lentement sur flamme vive et arrosé d\'épices orientales, notre cochon rôti est le festin des rois.', 18.00, 0, 1, 1],
                ['Plats Principaux', 'Tourte Rustique', 'Tourte garnie de légumes, champignons et fromage, dans une croûte dorée.', 14.00, 1, 1, 2],
                ['Desserts', 'Tarte aux Pommes', 'Tarte rustique aux pommes caramélisées et à la cannelle, servie tiède.', 7.00, 1, 1, 1]
            ];
            
            foreach ($demo_menu as $item) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO menus (category, name, description, price, is_vegetarian, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute($item);
                } catch (PDOException $e) {
                    echo "<p class='warning'>Élément de menu de démonstration non ajouté: " . $e->getMessage() . "</p>";
                }
            }
            echo "<p class='success'>Éléments de menu de démonstration ajoutés.</p>";
        }
        
        // Vérifier si des images de galerie existent déjà
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery");
        $gallery_count = $stmt->fetchColumn();
        
        if ($gallery_count == 0) {
            // Ajouter quelques images de galerie de démonstration
            $demo_gallery = [
                ['Façade de la Taverne Kanorelim', 'Vue extérieure de notre taverne médiévale', '/api/placeholder/800/600', 'taverne', 1],
                ['Salle principale avec son feu de cheminée', 'L\'ambiance chaleureuse de notre salle principale', '/api/placeholder/800/600', 'taverne', 2],
                ['Plateau de fromages et charcuteries', 'Sélection de nos fromages fermiers', '/api/placeholder/800/600', 'nourriture', 1],
                ['Cochon rôti, notre spécialité', 'Notre plat signature cuit à la perfection', '/api/placeholder/800/600', 'nourriture', 2],
                ['Troubadours lors de notre soirée musicale', 'Animation musicale lors d\'un événement', '/api/placeholder/800/600', 'evenements', 1],
                ['Grand banquet du Printemps', 'Nos convives lors du festin printanier', '/api/placeholder/800/600', 'evenements', 2]
            ];
            
            foreach ($demo_gallery as $image) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category, sort_order) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute($image);
                } catch (PDOException $e) {
                    echo "<p class='warning'>Image de galerie de démonstration non ajoutée: " . $e->getMessage() . "</p>";
                }
            }
            echo "<p class='success'>Images de galerie de démonstration ajoutées.</p>";
        }
        
        echo "<p class='success'><strong>Installation complète réussie!</strong></p>";
        echo "<p><strong>Prochaines étapes :</strong></p>";
        echo "<ul>";
        echo "<li>✅ Tables de base créées</li>";
        echo "<li>✅ Tables supplémentaires créées</li>";
        echo "<li>✅ Paramètres par défaut configurés</li>";
        echo "<li>✅ Données de démonstration ajoutées</li>";
        echo "<li>🔄 <a href='create_admin.php'>Créer un compte administrateur</a></li>";
        echo "<li>🔄 Supprimer ce fichier après utilisation</li>";
        echo "</ul>";
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
    <title>Installation des tables supplémentaires</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8B4513;
            text-align: center;
            margin-bottom: 30px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        a {
            color: #8B4513;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        ul {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            border-left: 4px solid #8B4513;
        }
        li {
            margin-bottom: 8px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installation des Tables Supplémentaires - Kanorelim</h1>
        <div class="alert-box">
            <strong>⚠️ IMPORTANT:</strong> Supprimez ce fichier immédiatement après utilisation pour des raisons de sécurité!
        </div>
    </div>
</body>
</html>