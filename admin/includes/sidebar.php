<?php
// admin/includes/sidebar.php - Barre latérale de navigation
if (!defined('ADMIN_INCLUDED')) {
    require_once '../config.php';
}

// Fonction pour déterminer si le lien est actif
function isActiveSidebarLink($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page) ? 'active' : '';
}
?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <h2>Kanorelim</h2>
            <p>Taverne Médiévale</p>
        </div>
        <button id="sidebar-close" class="sidebar-close">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php" class="<?php echo isActiveSidebarLink('index.php'); ?>">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </li>
            <li>
                <a href="menu.php" class="<?php echo isActiveSidebarLink('menu.php'); ?>">
                    <i class="fas fa-utensils"></i> Menu
                </a>
            </li>
            <li>
                <a href="evenements.php" class="<?php echo isActiveSidebarLink('evenements.php'); ?>">
                    <i class="fas fa-calendar-alt"></i> Événements
                </a>
            </li>
            <li>
                <a href="galerie.php" class="<?php echo isActiveSidebarLink('galerie.php'); ?>">
                    <i class="fas fa-images"></i> Galerie
                </a>
            </li>
            <li>
                <a href="reservations.php" class="<?php echo isActiveSidebarLink('reservations.php'); ?>">
                    <i class="fas fa-book"></i> Réservations
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?php echo isActiveSidebarLink('messages.php'); ?>">
                    <i class="fas fa-envelope"></i> Messages
                    <?php
                    // Afficher le nombre de messages non lus
                    $pdo = connectDB();
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
                    $count = $stmt->fetch()['count'];
                    if ($count > 0) {
                        echo '<span class="badge">' . $count . '</span>';
                    }
                    ?>
                </a>
            </li>
            <?php if ($_SESSION['admin_role'] === 'admin'): ?>
            <li>
                <a href="utilisateurs.php" class="<?php echo isActiveSidebarLink('utilisateurs.php'); ?>">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
            </li>
            <li>
                <a href="parametres.php" class="<?php echo isActiveSidebarLink('parametres.php'); ?>">
                    <i class="fas fa-cog"></i> Paramètres
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> Voir le site
        </a>
    </div>
</aside>