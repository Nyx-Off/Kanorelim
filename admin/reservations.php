<?php
// admin/reservations.php - Gestion des réservations
session_start();
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier la session admin
checkAdminSession();

// Vérifier les permissions
if (!hasPermission('view_reservations')) {
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
$status_filter = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'confirmed', 'cancelled']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? cleanInput($_GET['date']) : '';
$search_term = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

// Changer le statut d'une réservation
if (isset($_GET['action']) && $_GET['action'] === 'change_status' && isset($_GET['id']) && isset($_GET['status'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_reservations')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            $status = cleanInput($_GET['status']);
            
            // Vérifier que le statut est valide
            if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
                throw new Exception('Statut non valide.');
            }
            
            // Mettre à jour le statut
            $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // Journaliser l'action
            logAction('update', 'reservation', $id);
            
            $message = 'Le statut de la réservation a été mis à jour avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Supprimer une réservation
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_reservations')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            
            // Supprimer la réservation
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$id]);
            
            // Journaliser l'action
            logAction('delete', 'reservation', $id);
            
            $message = 'La réservation a été supprimée avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Ajouter une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_reservations')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            
            // Récupération et nettoyage des données
            $name = cleanInput($_POST['name']);
            $email = cleanInput($_POST['email']);
            $phone = cleanInput($_POST['phone']);
            $date = cleanInput($_POST['date']);
            $time = cleanInput($_POST['time']);
            $guests = intval($_POST['guests']);
            $occasion = cleanInput($_POST['occasion']);
            $message_text = cleanInput($_POST['message']);
            $status = cleanInput($_POST['status']);
            
            // Validation des données
            if (empty($name) || empty($email) || empty($date) || empty($time) || $guests <= 0) {
                throw new Exception('Tous les champs obligatoires doivent être remplis.');
            }
            
            // Vérifier le format de l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('L\'adresse email n\'est pas valide.');
            }
            
            // Vérifier que la date est dans le futur
            if (strtotime($date) < strtotime(date('Y-m-d'))) {
                throw new Exception('La date de réservation doit être dans le futur.');
            }
            
            // Vérifier que le statut est valide
            if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
                throw new Exception('Statut non valide.');
            }
            
            // Ajouter la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations 
                (name, email, phone, date, time, guests, occasion, message, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $date, $time, $guests, $occasion, $message_text, $status]);
            
            // Journaliser l'action
            logAction('create', 'reservation', $pdo->lastInsertId());
            
            $message = 'La réservation a été ajoutée avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Récupération des réservations
function getReservations($offset, $limit, $status = '', $date = '', $search = '') {
    $pdo = connectDB();
    $params = [];
    
    $query = "SELECT * FROM reservations WHERE 1=1";
    
    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    if (!empty($date)) {
        $query .= " AND date = ?";
        $params[] = $date;
    }
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $search_pattern = '%' . $search . '%';
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
    }
    
    $query .= " ORDER BY date DESC, time DESC";
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Récupération du nombre total de réservations (pour la pagination)
function getTotalReservationsCount($status = '', $date = '', $search = '') {
    $pdo = connectDB();
    $params = [];
    
    $query = "SELECT COUNT(*) FROM reservations WHERE 1=1";
    
    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    if (!empty($date)) {
        $query .= " AND date = ?";
        $params[] = $date;
    }
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $search_pattern = '%' . $search . '%';
        $params[] = $search_pattern;
        $params[] = $search_pattern;
        $params[] = $search_pattern;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Récupération d'une réservation spécifique
function getReservation($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$total_items = getTotalReservationsCount($status_filter, $date_filter, $search_term);
$total_pages = ceil($total_items / $items_per_page);
$reservations = getReservations($offset, $items_per_page, $status_filter, $date_filter, $search_term);

// Détails d'une réservation (si demandé)
$viewMode = false;
$viewReservation = null;

if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
    $viewMode = true;
    $viewReservation = getReservation(intval($_GET['id']));
    
    if (!$viewReservation) {
        $viewMode = false;
        $message = 'La réservation demandée n\'existe pas.';
        $message_type = 'danger';
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
    <h2>Gestion des Réservations</h2>
    <p>Consultez et gérez les réservations de tables de la Taverne Kanorelim</p>
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
                    <label for="status-filter">Statut:</label>
                    <select id="status-filter" name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date-filter">Date:</label>
                    <input type="date" id="date-filter" name="date" class="form-control" value="<?php echo $date_filter; ?>" onchange="this.form.submit()">
                </div>
                
                <div class="form-group">
                    <label for="search-filter">Recherche:</label>
                    <div class="search-box">
                        <input type="text" id="search-filter" name="search" placeholder="Nom, email, téléphone..." class="form-control" value="<?php echo $search_term; ?>">
                        <button type="submit" class="search-button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (hasPermission('edit_reservations')): ?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addReservationModal">
                <i class="fas fa-plus"></i> Ajouter une réservation
            </button>
        <?php endif; ?>
    </div>
    
    <?php if ($viewMode): ?>
        <!-- Détails d'une réservation -->
        <div class="card">
            <div class="card-header">
                <h3>Détails de la réservation #<?php echo $viewReservation['id']; ?></h3>
                <a href="reservations.php" class="card-link">Retour à la liste</a>
            </div>
            <div class="card-body">
                <div class="reservation-details">
                    <div class="reservation-header">
                        <div class="reservation-date">
                            <span class="date"><?php echo formatDate($viewReservation['date']); ?></span>
                            <span class="time"><?php echo $viewReservation['time']; ?></span>
                        </div>
                        <div class="reservation-status">
                            <span class="status-badge status-<?php echo $viewReservation['status']; ?>">
                                <?php 
                                $status_labels = [
                                    'pending' => 'En attente',
                                    'confirmed' => 'Confirmée',
                                    'cancelled' => 'Annulée'
                                ];
                                echo $status_labels[$viewReservation['status']] ?? $viewReservation['status'];
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="reservation-content">
                        <div class="reservation-section">
                            <h4>Informations du client</h4>
                            <dl class="details-list">
                                <dt>Nom:</dt>
                                <dd><?php echo htmlspecialchars($viewReservation['name']); ?></dd>
                                
                                <dt>Email:</dt>
                                <dd>
                                    <a href="mailto:<?php echo htmlspecialchars($viewReservation['email']); ?>">
                                        <?php echo htmlspecialchars($viewReservation['email']); ?>
                                    </a>
                                </dd>
                                
                                <dt>Téléphone:</dt>
                                <dd>
                                    <?php if (!empty($viewReservation['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($viewReservation['phone']); ?>">
                                            <?php echo htmlspecialchars($viewReservation['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <em>Non renseigné</em>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                        
                        <div class="reservation-section">
                            <h4>Détails de la réservation</h4>
                            <dl class="details-list">
                                <dt>Nombre de convives:</dt>
                                <dd><?php echo $viewReservation['guests']; ?> personnes</dd>
                                
                                <dt>Occasion:</dt>
                                <dd>
                                    <?php if (!empty($viewReservation['occasion'])): ?>
                                        <?php echo htmlspecialchars($viewReservation['occasion']); ?>
                                    <?php else: ?>
                                        <em>Non précisée</em>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt>Message:</dt>
                                <dd>
                                    <?php if (!empty($viewReservation['message'])): ?>
                                        <div class="message-box">
                                            <?php echo nl2br(htmlspecialchars($viewReservation['message'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <em>Pas de message</em>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt>Date de création:</dt>
                                <dd><?php echo date('d/m/Y H:i', strtotime($viewReservation['created_at'])); ?></dd>
                            </dl>
                        </div>
                        
                        <?php if (hasPermission('edit_reservations')): ?>
                            <div class="reservation-actions">
                                <h4>Actions</h4>
                                <div class="action-buttons">
                                    <a href="?action=change_status&id=<?php echo $viewReservation['id']; ?>&status=confirmed" class="btn btn-success">
                                        <i class="fas fa-check"></i> Confirmer
                                    </a>
                                    <a href="?action=change_status&id=<?php echo $viewReservation['id']; ?>&status=cancelled" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                    <a href="?action=delete&id=<?php echo $viewReservation['id']; ?>" class="btn btn-danger" data-confirm="Êtes-vous sûr de vouloir supprimer cette réservation ?">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                                
                                <div class="contact-client">
                                    <a href="mailto:<?php echo htmlspecialchars($viewReservation['email']); ?>" class="btn btn-primary">
                                        <i class="fas fa-envelope"></i> Contacter par email
                                    </a>
                                    <?php if (!empty($viewReservation['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($viewReservation['phone']); ?>" class="btn btn-info">
                                            <i class="fas fa-phone"></i> Appeler
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Liste des réservations -->
        <div class="card">
            <div class="card-header">
                <h3>Liste des réservations</h3>
                <span class="result-count"><?php echo $total_items; ?> résultat<?php echo $total_items > 1 ? 's' : ''; ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($reservations)): ?>
                    <table class="data-table" id="reservations-table">
                        <thead>
                            <tr>
                                <th data-sort>ID</th>
                                <th data-sort>Date</th>
                                <th data-sort>Heure</th>
                                <th data-sort>Nom</th>
                                <th>Email</th>
                                <th>Convives</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo $reservation['id']; ?></td>
                                    <td data-sort="<?php echo $reservation['date']; ?>">
                                        <?php echo formatDate($reservation['date']); ?>
                                        <?php if (strtotime($reservation['date']) >= strtotime(date('Y-m-d'))): ?>
                                            <span class="badge badge-info">À venir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $reservation['time']; ?></td>
                                    <td><?php echo htmlspecialchars($reservation['name']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['email']); ?></td>
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
                                    <td class="actions">
                                        <a href="?action=view&id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if (hasPermission('edit_reservations')): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="status-dropdown-<?php echo $reservation['id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="status-dropdown-<?php echo $reservation['id']; ?>">
                                                    <a class="dropdown-item" href="?action=change_status&id=<?php echo $reservation['id']; ?>&status=pending">
                                                        <i class="fas fa-clock"></i> Marquer en attente
                                                    </a>
                                                    <a class="dropdown-item" href="?action=change_status&id=<?php echo $reservation['id']; ?>&status=confirmed">
                                                        <i class="fas fa-check"></i> Confirmer
                                                    </a>
                                                    <a class="dropdown-item" href="?action=change_status&id=<?php echo $reservation['id']; ?>&status=cancelled">
                                                        <i class="fas fa-times"></i> Annuler
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="?action=delete&id=<?php echo $reservation['id']; ?>" data-confirm="Êtes-vous sûr de vouloir supprimer cette réservation ?">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_pages > 1): ?>
                        <!-- Pagination -->
                        <?php echo generatePagination($current_page, $total_pages, buildPaginationUrl()); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-data">Aucune réservation trouvée.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (hasPermission('edit_reservations')): ?>
    <!-- Modal pour ajouter une réservation -->
    <div class="modal" id="addReservationModal" tabindex="-1" role="dialog" aria-labelledby="addReservationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addReservationModalLabel">Ajouter une réservation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nom *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="date">Date *</label>
                                    <input type="date" id="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="time">Heure *</label>
                                    <input type="time" id="time" name="time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="guests">Nombre de convives *</label>
                                    <input type="number" id="guests" name="guests" class="form-control" required min="1" max="20" value="2">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="occasion">Occasion</label>
                                    <select id="occasion" name="occasion" class="form-control">
                                        <option value="">Repas régulier</option>
                                        <option value="birthday">Anniversaire</option>
                                        <option value="special">Occasion spéciale</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Statut *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="pending">En attente</option>
                                <option value="confirmed">Confirmée</option>
                                <option value="cancelled">Annulée</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Initialisation du tableau
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    });
    
    // Fermer les dropdowns au clic en dehors
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
            const menus = document.querySelectorAll('.dropdown-menu.show');
            menus.forEach(menu => menu.classList.remove('show'));
        }
    });
    
    // Initialisation du tri des tableaux
    initSortableTable('reservations-table');
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>