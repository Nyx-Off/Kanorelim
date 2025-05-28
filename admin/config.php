<?php
// admin/config.php - Configuration spécifique à l'administration

// Check if we're already in admin context to prevent redefinition
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Include the main site configuration if needed
// But prevent the main site from including admin config
define('SITE_ADMIN', true);
require_once '../includes/config.php';

// Vérification de la session admin
function checkAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}
?>