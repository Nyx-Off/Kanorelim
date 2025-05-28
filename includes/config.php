<?php
// includes/config.php - Configuration unifiée du site
// Définition des constantes
define('SITE_TITLE', 'Taverne Kanorelim');
define('SITE_DESCRIPTION', 'Une expérience médiévale authentique');
define('ADMIN_EMAIL', 'contact@kanorelim.fr');

// Configuration de la base de données
if (!defined('DB_HOST')) {
    define('DB_HOST', 'zy16r.myd.infomaniak.com');
    define('DB_NAME', 'zy16r_kanorelim');
    define('DB_USER', 'zy16r_system');
    define('DB_PASSWORD', 'SamyBensalem@2024');
}

// Fonction de connexion à la base de données (une seule déclaration)
if (!function_exists('connectDB')) {
    function connectDB() {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASSWORD,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
            return $pdo;
        } catch (PDOException $e) {
            // En production, ne pas afficher le message d'erreur
            die('Erreur de connexion à la base de données');
        }
    }
}

// Fonction pour nettoyer les entrées (une seule déclaration)
if (!function_exists('cleanInput')) {
    function cleanInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Skip admin-specific configurations if we're in admin context
if (defined('SITE_ADMIN')) {
    // Si nous sommes dans l'admin, on s'arrête ici pour éviter les doublons
    return;
}

// Configuration des chemins (seulement pour le site principal)
define('BASE_URL', '/'); // Ajuster selon votre configuration serveur
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');

// Configuration des horaires d'ouverture (par défaut, peut être surchargée par la BDD)
$horaires_defaut = [
    'Lundi - Jeudi' => '11h - 23h',
    'Vendredi - Samedi' => '11h - 01h',
    'Dimanche' => '12h - 22h'
];

// Configuration des réseaux sociaux (par défaut, peut être surchargée par la BDD)
$reseaux_sociaux_defaut = [
    'facebook' => 'https://facebook.com/tavernekanorelim',
    'instagram' => 'https://instagram.com/tavernekanorelim',
    'twitter' => 'https://twitter.com/tavernekanorelim'
];

// Charger les configurations depuis la base de données
try {
    $pdo = connectDB();
    
    // Charger les paramètres du site
    $settings_query = $pdo->query("
        SELECT setting_key, setting_value 
        FROM site_settings 
        WHERE is_active = 1
    ");
    
    $settings = [];
    if ($settings_query) {
        while ($row = $settings_query->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    // Utiliser les paramètres de la BDD ou les valeurs par défaut
    $horaires = isset($settings['horaires']) ? json_decode($settings['horaires'], true) : $horaires_defaut;
    $reseaux_sociaux = isset($settings['reseaux_sociaux']) ? json_decode($settings['reseaux_sociaux'], true) : $reseaux_sociaux_defaut;
    
} catch (Exception $e) {
    // En cas d'erreur, utiliser les valeurs par défaut
    $horaires = $horaires_defaut;
    $reseaux_sociaux = $reseaux_sociaux_defaut;
}

// Fonction pour charger le menu depuis la base de données
function getMenuFromDatabase() {
    try {
        $pdo = connectDB();
        $stmt = $pdo->query("
            SELECT category, name, description, price, is_vegetarian 
            FROM menus 
            WHERE is_active = 1 
            ORDER BY category, sort_order, name
        ");
        
        $menu_items = $stmt->fetchAll();
        $categories_menu = [];
        
        foreach ($menu_items as $item) {
            $menu_item = [
                'nom' => $item['name'],
                'description' => $item['description'],
                'prix' => number_format($item['price'], 0) . '€'
            ];
            
            if ($item['is_vegetarian']) {
                $menu_item['vegetarien'] = true;
            }
            
            $categories_menu[$item['category']][] = $menu_item;
        }
        
        return $categories_menu;
    } catch (Exception $e) {
        // En cas d'erreur, retourner un menu par défaut
        return getDefaultMenu();
    }
}

// Menu par défaut (fallback)
function getDefaultMenu() {
    return [
        'Boissons' => [
            [
                'nom' => 'Hydromel Royal',
                'description' => 'Notre hydromel artisanal vieilli en fût de chêne, préparé selon les recettes secrètes des moines brasseurs.',
                'prix' => '7€'
            ],
            [
                'nom' => 'Cervoise Ambrée',
                'description' => 'Bière artisanale aux notes de miel et de houblon, brassée selon les méthodes ancestrales.',
                'prix' => '6€'
            ]
        ],
        'Plats Principaux' => [
            [
                'nom' => 'Cochon Rôti',
                'description' => 'Cuit lentement sur flamme vive et arrosé d\'épices orientales, notre cochon rôti est le festin des rois.',
                'prix' => '18€'
            ]
        ]
    ];
}

// Charger le menu depuis la base de données
$categories_menu = getMenuFromDatabase();

// Fonction pour charger les événements depuis la base de données
function getEventsFromDatabase() {
    try {
        $pdo = connectDB();
        $stmt = $pdo->query("
            SELECT title, description, date, time, price, image 
            FROM events 
            WHERE is_active = 1 
            ORDER BY date ASC
        ");
        
        $events = $stmt->fetchAll();
        $evenements = [];
        
        foreach ($events as $event) {
            $evenements[] = [
                'date' => $event['date'],
                'titre' => $event['title'],
                'description' => $event['description'],
                'heure' => $event['time'] ? date('H\hi', strtotime($event['time'])) : '20h00',
                'prix' => $event['price'] ?: 'Gratuit',
                'image' => $event['image'] ?: '/api/placeholder/800/500'
            ];
        }
        
        return $evenements;
    } catch (Exception $e) {
        // En cas d'erreur, retourner des événements par défaut
        return getDefaultEvents();
    }
}

// Événements par défaut (fallback)
function getDefaultEvents() {
    return [
        [
            'date' => date('Y-m-d', strtotime('+7 days')),
            'titre' => 'Festin du Printemps',
            'description' => 'Grand banquet avec musique de troubadours et spectacle de jongleurs.',
            'heure' => '19h00',
            'prix' => '35€',
            'image' => '/api/placeholder/800/500'
        ]
    ];
}

// Charger les événements depuis la base de données
$evenements = getEventsFromDatabase();

// Fonction pour charger les images de la galerie depuis la base de données
function getGalleryFromDatabase() {
    try {
        $pdo = connectDB();
        $stmt = $pdo->query("
            SELECT id, title, description, image_path, category 
            FROM gallery 
            ORDER BY sort_order, title
        ");
        
        $gallery_items = $stmt->fetchAll();
        $galerie_images = [];
        
        foreach ($gallery_items as $item) {
            $galerie_images[] = [
                'id' => $item['id'],
                'src' => $item['image_path'],
                'caption' => $item['title'],
                'category' => $item['category'] ?: 'taverne'
            ];
        }
        
        return $galerie_images;
    } catch (Exception $e) {
        // En cas d'erreur, retourner une galerie par défaut
        return getDefaultGallery();
    }
}

// Galerie par défaut (fallback)
function getDefaultGallery() {
    return [
        [
            'id' => 1,
            'src' => '/api/placeholder/800/600',
            'caption' => 'Façade de la Taverne Kanorelim',
            'category' => 'taverne'
        ]
    ];
}

// Fonction pour formater les dates
function formatDate($date, $format = 'j F Y') {
    $date_obj = new DateTime($date);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern($format);
    return $formatter->format($date_obj);
}

// Fonction pour sécuriser les entrées utilisateur
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message, $from = ADMIN_EMAIL) {
    $headers = "From: " . SITE_TITLE . " <" . $from . ">\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>