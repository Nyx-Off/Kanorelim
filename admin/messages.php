<?php
// admin/messages.php - Gestion des messages de contact
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier les permissions
if (!hasPermission('view_messages')) {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Pagination
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

// Filtres
$read_filter = isset($_GET['read']) ? (int)$_GET['read'] : -1; // -1 = tous, 0 = non lus, 1 = lus
$search_term = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Marquer comme lu/non lu
if (isset($_GET['action']) && ($_GET['action'] === 'mark_read' || $_GET['action'] === 'mark_unread') && isset($_GET['id'])) {
    // Vérifier les permissions
    if (!hasPermission('reply_messages')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            $is_read = ($_GET['action'] === 'mark_read') ? 1 : 0;
            
            // Mettre à jour le statut
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = ? WHERE id = ?");
            $stmt->execute([$is_read, $id]);
            
            // Journaliser l'action
            logAction('update', 'message', $id);
            
            $message = 'Le message a été marqué comme ' . ($is_read ? 'lu' : 'non lu') . '.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Supprimer un message
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Vérifier les permissions
    if (!hasPermission('reply_messages')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            
            // Supprimer le message
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$id]);
            
            // Journaliser l'action
            logAction('delete', 'message', $id);
            
            $message = 'Le message a été supprimé avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Marquer tous les messages comme lus
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    // Vérifier les permissions
    if (!hasPermission('reply_messages')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            
            // Mettre à jour tous les messages
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
            $stmt->execute();
            
            // Journaliser l'action
            logAction('update', 'messages');
            
            $message = 'Tous les messages ont été marqués comme lus.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Récupération des messages
function getMessages($offset, $limit, $read_status = -1, $search = '') {
    $pdo = connectDB();
    $params = [];
    
    $query = "SELECT * FROM contact_messages WHERE 1=1";
    
    if ($read_status !== -1) {
        $query .= " AND is_read = ?";
        $params[] = $read_status;
    }
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $search_pattern = '%' . $search . '%';
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
    }
    
    $query .= " ORDER BY created_at DESC";
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Récupération du nombre total de messages (pour la pagination)
function getTotalMessagesCount($read_status = -1, $search = '') {
    $pdo = connectDB();
    $params = [];
    
    $query = "SELECT COUNT(*) FROM contact_messages WHERE 1=1";
    
    if ($read_status !== -1) {
        $query .= " AND is_read = ?";
        $params[] = $read_status;
    }
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $search_pattern = '%' . $search . '%';
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Récupération d'un message spécifique
function getMessage($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$total_items = getTotalMessagesCount($read_filter, $search_term);
$total_pages = ceil($total_items / $items_per_page);
$messages = getMessages($offset, $items_per_page, $read_filter, $search_term);

// Détails d'un message (si demandé)
$viewMode = false;
$viewMessage = null;

if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $viewMode = true;
    $viewMessage = getMessage(intval($_GET['id']));
    
    if (!$viewMessage) {
        $viewMode = false;
        $message = 'Le message demandé n\'existe pas.';
        $message_type = 'danger';
    } else {
        // Marquer comme lu si ce n'est pas déjà le cas
        if (!$viewMessage['is_read'] && hasPermission('reply_messages')) {
            $pdo = connectDB();
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$viewMessage['id']]);
            $viewMessage['is_read'] = 1;
        }
    }
}

// Construction de l'URL pour la pagination
function buildPaginationUrl() {
    $params = $_GET;
    unset($params['page']);
    $query_string = http_build_query($params);
    return '?' . ($query_string ? $query_string . '&' : '');
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Gestion des Messages</h2>
    <p>Consultez et gérez les messages de contact envoyés via le site</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" data-dismiss="5000">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-content-wrapper">
    <!-- Filtres et actions -->
    <div class="admin-actions">
        <div class="filters">
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label for="read-filter">Statut:</label>
                    <select id="read-filter" name="read" class="form-control" onchange="this.form.submit()">
                        <option value="-1" <?php echo $read_filter === -1 ? 'selected' : ''; ?>>Tous les messages</option>
                        <option value="0" <?php echo $read_filter === 0 ? 'selected' : ''; ?>>Non lus</option>
                        <option value="1" <?php echo $read_filter === 1 ? 'selected' : ''; ?>>Lus</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search-filter">Recherche:</label>
                    <div class="search-box">
                        <input type="text" id="search-filter" name="search" placeholder="Nom, email, sujet..." class="form-control" value="<?php echo $search_term; ?>">
                        <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (hasPermission('reply_messages')): ?>
            <div class="bulk-actions">
                <a href="?action=mark_all_read" class="btn btn-secondary" id="mark-all-read" data-confirm="Êtes-vous sûr de vouloir marquer tous les messages comme lus ?">
                    <i class="fas fa-check-double"></i> Tout marquer comme lu
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($viewMode): ?>
        <!-- Détails d'un message -->
        <div class="card">
            <div class="card-header">
                <h3>Message de <?php echo htmlspecialchars($viewMessage['name']); ?></h3>
                <a href="messages.php" class="card-link">Retour à la liste</a>
            </div>
            <div class="card-body">
                <div class="message-details">
                    <div class="message-header">
                        <div class="message-info">
                            <div class="message-subject">
                                <h4><?php echo htmlspecialchars($viewMessage['subject']); ?></h4>
                            </div>
                            <div class="message-meta">
                                <span class="message-date">
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($viewMessage['created_at'])); ?>
                                </span>
                                <span class="message-status">
                                    <?php if ($viewMessage['is_read']): ?>
                                        <span class="status-badge status-read">Lu</span>
                                    <?php else: ?>
                                        <span class="status-badge status-unread">Non lu</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-sender">
                        <div class="sender-info">
                            <div class="sender-name">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($viewMessage['name']); ?>
                            </div>
                            <div class="sender-email">
                                <i class="fas fa-envelope"></i> 
                                <a href="mailto:<?php echo htmlspecialchars($viewMessage['email']); ?>">
                                    <?php echo htmlspecialchars($viewMessage['email']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="message-content">
                        <div class="message-text">
                            <?php echo nl2br(htmlspecialchars($viewMessage['message'])); ?>
                        </div>
                    </div>
                    
                    <?php if (hasPermission('reply_messages')): ?>
                        <div class="message-actions">
                            <div class="action-buttons">
                                <a href="mailto:<?php echo htmlspecialchars($viewMessage['email']); ?>?subject=Re: <?php echo htmlspecialchars($viewMessage['subject']); ?>" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Répondre
                                </a>
                                <?php if ($viewMessage['is_read']): ?>
                                    <a href="?action=mark_unread&id=<?php echo $viewMessage['id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-envelope"></i> Marquer comme non lu
                                    </a>
                                <?php else: ?>
                                    <a href="?action=mark_read&id=<?php echo $viewMessage['id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-envelope-open"></i> Marquer comme lu
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $viewMessage['id']; ?>" class="btn btn-danger" data-confirm="Êtes-vous sûr de vouloir supprimer ce message ?">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste des messages -->
        <div class="card">
            <div class="card-header">
                <h3>Liste des messages</h3>
                <span class="result-count"><?php echo $total_items; ?> message<?php echo $total_items > 1 ? 's' : ''; ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($messages)): ?>
                    <div class="message-list">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item <?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                                <div class="message-item-content">
                                    <div class="message-item-header">
                                        <div class="message-item-sender">
                                            <span class="sender-name"><?php echo htmlspecialchars($msg['name']); ?></span>
                                            <span class="sender-email">&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;</span>
                                        </div>
                                        <div class="message-item-date">
                                            <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </div>
                                    <div class="message-item-subject">
                                        <a href="?action=view&id=<?php echo $msg['id']; ?>">
                                            <?php echo htmlspecialchars($msg['subject']); ?>
                                        </a>
                                    </div>
                                    <div class="message-item-preview">
                                        <?php echo htmlspecialchars(substr($msg['message'], 0, 150)) . (strlen($msg['message']) > 150 ? '...' : ''); ?>
                                    </div>
                                </div>
                                <div class="message-item-actions">
                                    <?php if (hasPermission('reply_messages')): ?>
                                        <div class="action-buttons">
                                            <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($msg['is_read']): ?>
                                                <a href="?action=mark_unread&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-secondary" title="Marquer comme non lu">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=mark_read&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-secondary" title="Marquer comme lu">
                                                    <i class="fas fa-envelope-open"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" data-confirm="Êtes-vous sûr de vouloir supprimer ce message ?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <!-- Pagination -->
                        <?php echo generatePagination($current_page, $total_pages, buildPaginationUrl()); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-data">Aucun message trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des confirmations de suppression
    initDeleteConfirmations();
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>