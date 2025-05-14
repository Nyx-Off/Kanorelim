<?php
// admin/logout.php - Déconnexion de l'administration
session_start();

// Journaliser la déconnexion si l'utilisateur était connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    require_once 'config.php';
    require_once 'includes/functions.php';
    
    // Journaliser la déconnexion
    logAction('logout', 'session');
}

// Détruire la session
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Rediriger vers la page de connexion
header('Location: login.php');
exit;
?>