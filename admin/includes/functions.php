<?php
// admin/includes/functions.php - Fonctions utilitaires pour l'administration
if (!defined('ADMIN_INCLUDED')) {
    require_once '../config.php';
}

/**
 * Génère un message d'alerte
 *
 * @param string $message Le message à afficher
 * @param string $type Le type d'alerte (success, danger, warning, info)
 * @return string Le HTML de l'alerte
 */
function generateAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . '">' . $message . '</div>';
}

/**
 * Génère une pagination
 *
 * @param int $current_page La page actuelle
 * @param int $total_pages Le nombre total de pages
 * @param string $url L'URL de base pour les liens de pagination
 * @return string Le HTML de la pagination
 */
function generatePagination($current_page, $total_pages, $url = '?') {
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<nav class="pagination"><ul>';
    
    // Lien précédent
    if ($current_page > 1) {
        $pagination .= '<li><a href="' . $url . 'page=' . ($current_page - 1) . '" class="pagination-link prev">&laquo; Précédent</a></li>';
    } else {
        $pagination .= '<li><span class="pagination-link disabled">&laquo; Précédent</span></li>';
    }
    
    // Liens numérotés
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if ($start > 1) {
        $pagination .= '<li><a href="' . $url . 'page=1" class="pagination-link">1</a></li>';
        if ($start > 2) {
            $pagination .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $pagination .= '<li><span class="pagination-link active">' . $i . '</span></li>';
        } else {
            $pagination .= '<li><a href="' . $url . 'page=' . $i . '" class="pagination-link">' . $i . '</a></li>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $pagination .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
        $pagination .= '<li><a href="' . $url . 'page=' . $total_pages . '" class="pagination-link">' . $total_pages . '</a></li>';
    }
    
    // Lien suivant
    if ($current_page < $total_pages) {
        $pagination .= '<li><a href="' . $url . 'page=' . ($current_page + 1) . '" class="pagination-link next">Suivant &raquo;</a></li>';
    } else {
        $pagination .= '<li><span class="pagination-link disabled">Suivant &raquo;</span></li>';
    }
    
    $pagination .= '</ul></nav>';
    
    return $pagination;
}

/**
 * Télécharge et traite une image
 *
 * @param array $file Le fichier téléchargé ($_FILES['input_name'])
 * @param string $destination Le dossier de destination
 * @param string $filename Le nom de fichier souhaité (sans extension)
 * @param int $max_size La taille maximale en octets
 * @return array|bool Les informations du fichier ou false en cas d'erreur
 */
function uploadImage($file, $destination, $filename = '', $max_size = 2097152) {
    // Vérifier s'il y a des erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Vérifier le type de fichier
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Vérifier la taille
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Créer le dossier de destination s'il n'existe pas
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Générer un nom de fichier unique si non spécifié
    if (empty($filename)) {
        $filename = uniqid('img_');
    }
    
    // Obtenir l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Chemin complet du fichier
    $filepath = $destination . '/' . $filename . '.' . $extension;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'filename' => $filename . '.' . $extension,
            'filepath' => $filepath,
            'url' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $filepath)
        ];
    }
    
    return false;
}

/**
 * Formate une date pour l'affichage (version admin)
 * 
 * Cette fonction est différente de celle du site principal
 * car elle utilise IntlDateFormatter directement
 *
 * @param string $date La date au format YYYY-MM-DD
 * @param string $format Le format souhaité
 * @return string La date formatée
 */
function adminFormatDate($date, $format = 'j F Y') {
    $date_obj = new DateTime($date);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $formatter->setPattern($format);
    return $formatter->format($date_obj);
}

/**
 * Tronque un texte à une longueur donnée
 *
 * @param string $text Le texte à tronquer
 * @param int $length La longueur maximale
 * @param string $suffix Le suffixe à ajouter si tronqué
 * @return string Le texte tronqué
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Génère une chaîne aléatoire
 *
 * @param int $length La longueur de la chaîne
 * @return string La chaîne générée
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Journalise une action
 *
 * @param string $action L'action effectuée
 * @param string $entity L'entité concernée
 * @param int $entity_id L'ID de l'entité (facultatif)
 * @return bool Succès ou échec
 */
function logAction($action, $entity, $entity_id = null) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, entity, entity_id, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['admin_id'],
            $action,
            $entity,
            $entity_id,
            $_SERVER['REMOTE_ADDR']
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Vérifie si l'utilisateur a la permission requise
 *
 * @param string $permission La permission à vérifier
 * @return bool A la permission ou non
 */
function hasPermission($permission) {
    // Si l'utilisateur est admin, il a toutes les permissions
    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
        return true;
    }
    
    // Liste des permissions par rôle
    $permissions = [
        'editeur' => [
            'view_menu', 'edit_menu',
            'view_events', 'edit_events',
            'view_gallery', 'edit_gallery',
            'view_reservations', 'edit_reservations',
            'view_messages', 'reply_messages'
        ],
        'moderateur' => [
            'view_menu',
            'view_events',
            'view_gallery',
            'view_reservations', 'edit_reservations',
            'view_messages', 'reply_messages'
        ]
    ];
    
    // Vérifier si le rôle de l'utilisateur a la permission demandée
    return isset($_SESSION['admin_role']) && 
           isset($permissions[$_SESSION['admin_role']]) && 
           in_array($permission, $permissions[$_SESSION['admin_role']]);
}