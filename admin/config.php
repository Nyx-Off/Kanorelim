<?php
// admin/config.php - Configuration spécifique à l'administration
define('ADMIN_INCLUDED', true);
require_once '../includes/config.php';

// Configuration de la connexion à la base de données
define('DB_HOST', 'zy16r.myd.infomaniak.com');
define('DB_NAME', 'zy16r_kanorelim');
define('DB_USER', 'zy16r_system');
define('DB_PASSWORD', 'SamyBensalem@2024'); // Remplacer par votre mot de passe réel

// Fonction de connexion à la base de données
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

// Vérification de la session admin
function checkAdminSession() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// Nettoyage des données
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}