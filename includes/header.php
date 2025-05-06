<?php
// header.php - En-tête du site
// Inclure la configuration
if (!defined('INCLUDED_CONFIG')) {
    require_once 'config.php';
    define('INCLUDED_CONFIG', true);
}

// Fonction pour déterminer si le lien de navigation est actif
function isActiveLink($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_TITLE ?> - <?= SITE_DESCRIPTION ?></title>
    <meta name="description" content="La Taverne Kanorelim vous offre une expérience médiévale authentique avec des mets traditionnels, des boissons d'époque et une ambiance unique.">
    <link rel="stylesheet" href="<?= CSS_URL ?>style.css">
    <link rel="stylesheet" href="<?= CSS_URL ?>responsive.css">
    <script src="<?= JS_URL ?>main.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (basename($_SERVER['PHP_SELF']) == 'contact.php'): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <?php endif; ?>
</head>
<body>
    <div class="parchment-background">
        <header>
            <div class="header-container">
                <div class="logo">
                    <h1>Kanorelim</h1>
                    <p class="tagline">Taverne Médiévale</p>
                </div>
                <nav id="main-nav">
                    <button id="nav-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="nav-links">
                        <li><a href="index.php" class="<?= isActiveLink('index.php') ?>">Accueil</a></li>
                        <li><a href="menu.php" class="<?= isActiveLink('menu.php') ?>">Menu</a></li>
                        <li><a href="evenements.php" class="<?= isActiveLink('evenements.php') ?>">Événements</a></li>
                        <li><a href="galerie.php" class="<?= isActiveLink('galerie.php') ?>">Galerie</a></li>
                        <li><a href="contact.php" class="<?= isActiveLink('contact.php') ?>">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </header>

<?php