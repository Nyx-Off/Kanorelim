<?php
// admin/includes/header.php - En-tête de l'administration
// Vérification de la session
session_start();
if (!defined('ADMIN_INCLUDED')) {
    require_once '../config.php';
}
checkAdminSession();

// Récupérer les informations de l'utilisateur connecté
function getUserInfo($user_id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

$userInfo = getUserInfo($_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Taverne Kanorelim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <script src="assets/js/admin.js" defer></script>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Barre latérale -->
        <?php include 'sidebar.php'; ?>
        
        <!-- Contenu principal -->
        <div class="admin-content">
            <!-- En-tête -->
            <header class="admin-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="admin-title">Administration Kanorelim</h1>
                </div>
                <div class="header-right">
                    <div class="admin-user">
                        <span class="user-name"><?php echo htmlspecialchars($userInfo['username']); ?></span>
                        <button class="user-dropdown-toggle">
                            <i class="fas fa-user-circle"></i>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="profile.php">
                                <i class="fas fa-user"></i> Mon profil
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Contenu de la page -->
            <main class="admin-main">
                <!-- Le contenu spécifique de chaque page sera inséré ici -->