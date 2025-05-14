<?php
// admin/index.php - Tableau de bord administratif
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Inclure l'en-tête
include 'includes/header.php';

// Obtenir les statistiques
function getStats() {
    $pdo = connectDB();
    $stats = [];
    
    // Nombre total de réservations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $stats['total_reservations'] = $stmt->fetch()['count'];
    
    // Réservations à venir
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations WHERE date >= CURDATE() AND status = 'confirmed'");
    $stats['upcoming_reservations'] = $stmt->fetch()['count'];
    
    // Messages non lus
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $stats['unread_messages'] = $stmt->fetch()['count'];
    
    // Événements à venir
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE date >= CURDATE() AND is_active = 1");
    $stats['upcoming_events'] = $stmt->fetch()['count'];
    
    // Nombre d'éléments de menu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menus WHERE is_active = 1");
    $stats['menu_items'] = $stmt->fetch()['count'];
    
    // Nombre d'images dans la galerie
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM gallery");
    $stats['gallery_images'] = $stmt->fetch()['count'];
    
    return $stats;
}

// Obtenir les dernières réservations
function getRecentReservations($limit = 5) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM reservations ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Obtenir les derniers messages
function getRecentMessages($limit = 5) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Obtenir les prochains événements
function getUpcomingEvents($limit = 5) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Récupérer les statistiques
$stats = getStats();
$recent_reservations = getRecentReservations();
$recent_messages = getRecentMessages();
$upcoming_events = getUpcomingEvents();

// Récupérer l'activité récente
$pdo = connectDB();
$stmt = $pdo->query("
    SELECT al.*, u.username 
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");
$activity_logs = $stmt->fetchAll();
?>

<div class="admin-page-header">
    <h2>Tableau de bord</h2>
    <p>Bienvenue dans l'administration de la Taverne Kanorelim</p>
</div>

<!-- Statistiques -->
<section class="dashboard-stats">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-book"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total_reservations']; ?></div>
                <div class="stat-label">Réservations totales</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['upcoming_reservations']; ?></div>
                <div class="stat-label">Réservations à venir</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['unread_messages']; ?></div>
                <div class="stat-label">Messages non lus</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['upcoming_events']; ?></div>
                <div class="stat-label">Événements à venir</div>
            </div>
        </div>
    </div>
</section>

<!-- Contenu du tableau de bord -->
<div class="dashboard-content">
    <div class="dashboard-column">
        <!-- Dernières réservations -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Dernières réservations</h3>
                <a href="reservations.php" class="card-link">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_reservations)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Date</th>
                                <th>Convives</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                                    <td><?php echo formatDate($reservation['date']); ?></td>
                                    <td><?php echo $reservation['guests']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $reservation['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'En attente',
                                                'confirmed' => 'Confirmée',
                                                'cancelled' => 'Annulée'
                                            ];
                                            echo $status_labels[$reservation['status']] ?? $reservation['status'];
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Aucune réservation récente.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Prochains événements -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Prochains événements</h3>
                <a href="evenements.php" class="card-link">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_events)): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Heure</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo formatDate($event['date']); ?></td>
                                    <td><?php echo $event['time']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Aucun événement à venir.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="dashboard-column">
        <!-- Derniers messages -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Derniers messages</h3>
                <a href="messages.php" class="card-link">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_messages)): ?>
                    <div class="message-list">
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                                <div class="message-header">
                                    <div class="message-sender"><?php echo htmlspecialchars($message['name']); ?></div>
                                    <div class="message-date"><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></div>
                                </div>
                                <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                <div class="message-preview"><?php echo truncateText(htmlspecialchars($message['message']), 100); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">Aucun message récent.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Activité récente -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Activité récente</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($activity_logs)): ?>
                    <div class="activity-list">
                        <?php foreach ($activity_logs as $log): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    $icon = 'fa-circle-info';
                                    switch ($log['action']) {
                                        case 'create': $icon = 'fa-plus'; break;
                                        case 'update': $icon = 'fa-pencil'; break;
                                        case 'delete': $icon = 'fa-trash'; break;
                                        case 'login': $icon = 'fa-sign-in-alt'; break;
                                        case 'logout': $icon = 'fa-sign-out-alt'; break;
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-text">
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong> 
                                        a <?php 
                                        $actions = [
                                            'create' => 'créé',
                                            'update' => 'modifié',
                                            'delete' => 'supprimé',
                                            'login' => 'connecté',
                                            'logout' => 'déconnecté'
                                        ];
                                        echo $actions[$log['action']] ?? $log['action']; 
                                        ?>
                                        <?php if ($log['entity'] && $log['action'] !== 'login' && $log['action'] !== 'logout'): ?>
                                            <?php echo $log['entity']; ?>
                                            <?php if ($log['entity_id']): ?>
                                                #<?php echo $log['entity_id']; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-time"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">Aucune activité récente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>