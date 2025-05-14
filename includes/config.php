<?php
// includes/config.php - Configuration du site
// Définition des constantes
define('SITE_TITLE', 'Taverne Kanorelim');
define('SITE_DESCRIPTION', 'Une expérience médiévale authentique');
define('ADMIN_EMAIL', 'contact@kanorelim.fr');

// Skip admin-specific configurations if we're in admin context
if (defined('SITE_ADMIN')) {
    // Admin already has these defined, skip the rest
    return;
}

// Configuration de la base de données (à activer si besoin)
/*
define('DB_HOST', 'localhost');
define('DB_USER', 'votre_utilisateur');
define('DB_PASSWORD', 'votre_mot_de_passe');
define('DB_NAME', 'kanorelim_db');
*/

// Configuration des chemins
define('BASE_URL', '/'); // Ajuster selon votre configuration serveur
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMAGES_URL', ASSETS_URL . 'images/');

// Configuration des horaires
$horaires = [
    'Lundi - Jeudi' => '11h - 23h',
    'Vendredi - Samedi' => '11h - 01h',
    'Dimanche' => '12h - 22h'
];

// Configuration des réseaux sociaux
$reseaux_sociaux = [
    'facebook' => 'https://facebook.com/tavernekanorelim',
    'instagram' => 'https://instagram.com/tavernekanorelim',
    'twitter' => 'https://twitter.com/tavernekanorelim'
];

// Configuration du menu
$categories_menu = [
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
        ],
        [
            'nom' => 'Hypocras Épicé',
            'description' => 'Vin rouge infusé aux épices (cannelle, gingembre, cardamome) et adouci au miel.',
            'prix' => '8€'
        ],
        [
            'nom' => 'Cidre Fermier',
            'description' => 'Cidre traditionnel aux pommes sauvages, légèrement pétillant.',
            'prix' => '5€'
        ],
        [
            'nom' => 'Tisane des Druides',
            'description' => 'Mélange d\'herbes et de fleurs cueillies dans nos jardins selon les phases de la lune.',
            'prix' => '4€'
        ]
    ],
    'Entrées' => [
        [
            'nom' => 'Potage du Jour',
            'description' => 'Soupe épaisse de légumes de saison, servie dans une miche de pain creusée.',
            'prix' => '8€'
        ],
        [
            'nom' => 'Terrine de Gibier',
            'description' => 'Terrine de sanglier et de cerf aux herbes de la forêt, servie avec pain de campagne.',
            'prix' => '10€'
        ],
        [
            'nom' => 'Plateau du Monastère',
            'description' => 'Assortiment de fromages fermiers affinés, noix, miel et pain aux graines.',
            'prix' => '12€'
        ]
    ],
    'Plats Principaux' => [
        [
            'nom' => 'Cochon Rôti',
            'description' => 'Cuit lentement sur flamme vive et arrosé d\'épices orientales, notre cochon rôti est le festin des rois. Servi avec pommes de terre.',
            'prix' => '18€'
        ],
        [
            'nom' => 'Poulet aux Herbes',
            'description' => 'Volaille fermière rôtie aux herbes de Provence et à l\'ail, servie avec légumes racines.',
            'prix' => '16€'
        ],
        [
            'nom' => 'Civet de Cerf',
            'description' => 'Ragoût de cerf longuement mijoté au vin rouge et aux champignons des bois.',
            'prix' => '20€'
        ],
        [
            'nom' => 'Tourte Rustique',
            'description' => 'Tourte garnie de légumes, champignons et fromage, dans une croûte dorée.',
            'prix' => '14€',
            'vegetarien' => true
        ],
        [
            'nom' => 'Écuelles du Pêcheur',
            'description' => 'Poisson du jour et fruits de mer mijotés dans une sauce crémeuse aux herbes.',
            'prix' => '19€'
        ]
    ],
    'Desserts' => [
        [
            'nom' => 'Tarte aux Pommes',
            'description' => 'Tarte rustique aux pommes caramélisées et à la cannelle, servie tiède.',
            'prix' => '7€'
        ],
        [
            'nom' => 'Crème à la Lavande',
            'description' => 'Crème onctueuse parfumée à la lavande et au miel, garnie de noix caramélisées.',
            'prix' => '8€'
        ],
        [
            'nom' => 'Pain Perdu aux Fruits Rouges',
            'description' => 'Pain brioché trempé dans un mélange d\'œufs et de lait, doré au feu, servi avec fruits rouges et miel.',
            'prix' => '9€'
        ]
    ]
];

// Configuration des événements
$evenements = [
    [
        'date' => '2025-05-15',
        'titre' => 'Festin du Printemps',
        'description' => 'Grand banquet avec musique de troubadours et spectacle de jongleurs. Un festin digne des plus grandes cours médiévales, avec une multitude de plats servis tout au long de la soirée.',
        'heure' => '19h00',
        'prix' => '35€',
        'image' => '/api/placeholder/800/500'
    ],
    [
        'date' => '2025-05-21',
        'titre' => 'Tournoi de Dés Anciens',
        'description' => 'Participez à notre tournoi de jeux médiévaux avec prix à la clé. Découvrez des jeux authentiques du Moyen Âge comme les Mérelles, les Dés, les Tables (ancêtre du backgammon) et bien d\'autres.',
        'heure' => '20h00',
        'prix' => 'Gratuit (consommation obligatoire)',
        'image' => '/api/placeholder/800/500'
    ],
    [
        'date' => '2025-05-28',
        'titre' => 'Soirée Contes et Légendes',
        'description' => 'Une soirée enchantée avec notre conteur royal et dégustation d\'hypocras. Plongez dans l\'univers des légendes arthuriennes et des récits de chevalerie.',
        'heure' => '20h30',
        'prix' => '15€ (avec une coupe d\'hypocras incluse)',
        'image' => '/api/placeholder/800/500'
    ],
    [
        'date' => '2025-06-10',
        'titre' => 'Banquet Médiéval',
        'description' => 'Un grand festin à l\'ancienne avec des recettes authentiques du Moyen Âge. Venez découvrir les saveurs d\'antan et festoyer comme au temps des seigneurs.',
        'heure' => '19h00',
        'prix' => '40€',
        'image' => '/api/placeholder/800/500'
    ],
    [
        'date' => '2025-06-17',
        'titre' => 'Initiation au Combat d\'Épée',
        'description' => 'Démonstration et initiation aux techniques de combat médiéval par la Compagnie des Lames Anciennes. Apprenez les bases du maniement de l\'épée et découvrez l\'art du combat médiéval.',
        'heure' => '16h00',
        'prix' => '20€',
        'image' => '/api/placeholder/800/500'
    ],
    [
        'date' => '2025-06-24',
        'titre' => 'Atelier de Calligraphie',
        'description' => 'Apprenez l\'art de la calligraphie médiévale et repartez avec votre création. Un maître calligraphe vous guidera dans la réalisation d\'une œuvre personnalisée.',
        'heure' => '15h00',
        'prix' => '25€ (matériel fourni)',
        'image' => '/api/placeholder/800/500'
    ]
];

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