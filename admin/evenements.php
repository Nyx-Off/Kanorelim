<?php
// admin/evenements.php - Gestion des événements
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier les permissions
if (!hasPermission('view_events')) {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Traitement de l'ajout ou de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_events')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            
            // Récupération et nettoyage des données
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $title = cleanInput($_POST['title']);
            $description = cleanInput($_POST['description']);
            $date = cleanInput($_POST['date']);
            $time = cleanInput($_POST['time']);
            $price = cleanInput($_POST['price']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Image
            $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : '';
            
            // Validation des données
            if (empty($title) || empty($date)) {
                throw new Exception('Tous les champs obligatoires doivent être remplis.');
            }
            
            // Traitement de l'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/events/';
                $filename = 'event_' . time();
                
                $image_info = uploadImage($_FILES['image'], $upload_dir, $filename);
                
                if ($image_info) {
                    $image_path = $image_info['url'];
                } else {
                    throw new Exception('Erreur lors du téléchargement de l\'image.');
                }
            }
            
            // Traitement de l'action
            if ($_POST['action'] === 'add') {
                // Ajout d'un nouvel événement
                $stmt = $pdo->prepare("
                    INSERT INTO events 
                    (title, description, date, time, price, image, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $description, $date, $time, $price, $image_path, $is_active]);
                
                // Journaliser l'action
                logAction('create', 'event', $pdo->lastInsertId());
                
                $message = 'L\'événement a été ajouté avec succès.';
                $message_type = 'success';
            } else if ($_POST['action'] === 'edit' && $id > 0) {
                // Modification d'un événement existant
                $stmt = $pdo->prepare("
                    UPDATE events 
                    SET title = ?, description = ?, date = ?, time = ?, 
                        price = ?, image = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $description, $date, $time, $price, $image_path, $is_active, $id]);
                
                // Journaliser l'action
                logAction('update', 'event', $id);
                
                $message = 'L\'événement a été modifié avec succès.';
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_events')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            
            // Récupérer l'image pour la supprimer
            $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
            
            // Supprimer l'événement
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);
            
            // Supprimer l'image si elle existe
            if (!empty($image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $image);
            }
            
            // Journaliser l'action
            logAction('delete', 'event', $id);
            
            $message = 'L\'événement a été supprimé avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Récupération des événements
function getEvents($future_only = false) {
    $pdo = connectDB();
    $query = "SELECT * FROM events";
    
    if ($future_only) {
        $query .= " WHERE date >= CURRENT_DATE()";
    }
    
    $query .= " ORDER BY date DESC";
    
    $stmt = $pdo->query($query);
    return $stmt->fetchAll();
}

// Récupération d'un événement spécifique
function getEvent($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$futureOnly = isset($_GET['future']) && $_GET['future'] === '1';
$events = getEvents($futureOnly);

// Mode édition
$editMode = false;
$editEvent = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMode = true;
    $editEvent = getEvent(intval($_GET['id']));
    
    if (!$editEvent) {
        $editMode = false;
        $message = 'L\'événement demandé n\'existe pas.';
        $message_type = 'danger';
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Gestion des Événements</h2>
    <p>Gérez les événements de la Taverne Kanorelim</p>
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
            <div class="toggle-filter">
                <input type="checkbox" id="future-toggle" <?php echo $futureOnly ? 'checked' : ''; ?> onchange="window.location.href='?future=' + (this.checked ? '1' : '0')">
                <label for="future-toggle">Afficher uniquement les événements à venir</label>
            </div>
            
            <div class="search-box">
                <input type="text" id="event-search" placeholder="Rechercher..." class="form-control">
                <i class="fas fa-search"></i>
            </div>
        </div>
        
        <?php if (hasPermission('edit_events')): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un événement
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Formulaire d'ajout/édition -->
    <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || $editMode): ?>
        <div class="card">
            <div class="card-header">
                <h3><?php echo $editMode ? 'Modifier un événement' : 'Ajouter un événement'; ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo $editEvent['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $editEvent['image']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="title">Titre *</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editEvent['title']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="price">Prix</label>
                                <input type="text" id="price" name="price" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editEvent['price']) : ''; ?>" placeholder="Ex: 15€, Gratuit, etc.">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="date">Date *</label>
                                <input type="date" id="date" name="date" class="form-control" value="<?php echo $editMode ? $editEvent['date'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="time">Heure</label>
                                <input type="time" id="time" name="time" class="form-control" value="<?php echo $editMode ? $editEvent['time'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control rich-editor" rows="5"><?php echo $editMode ? htmlspecialchars($editEvent['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" id="image" name="image" class="form-control image-upload" data-preview="#image-preview">
                        <small class="form-text text-muted">Formats acceptés : JPG, PNG, GIF. Taille maximale : 2 Mo.</small>
                        
                        <?php if ($editMode && !empty($editEvent['image'])): ?>
                            <div class="current-image">
                                <p>Image actuelle :</p>
                                <img src="<?php echo $editEvent['image']; ?>" alt="Image actuelle" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        
                        <div id="image-preview" class="image-preview-container"></div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="is_active" name="is_active" <?php echo ($editMode && $editEvent['is_active']) || !$editMode ? 'checked' : ''; ?>>
                            <label for="is_active">Actif</label>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="evenements.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Liste des événements -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des événements</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($events)): ?>
                <table class="data-table" id="events-table">
                    <thead>
                        <tr>
                            <th data-sort>Titre</th>
                            <th data-sort>Date</th>
                            <th>Heure</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td data-sort="<?php echo $event['date']; ?>">
                                    <?php echo formatDate($event['date']); ?>
                                    <?php if (strtotime($event['date']) >= strtotime(date('Y-m-d'))): ?>
                                        <span class="badge badge-info">À venir</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Passé</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $event['time']; ?></td>
                                <td><?php echo htmlspecialchars($event['price']); ?></td>
                                <td>
                                    <?php if ($event['is_active']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if (hasPermission('edit_events')): ?>
                                        <a href="?action=edit&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" data-confirm="Êtes-vous sûr de vouloir supprimer cet événement ?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=view&id=<?php echo $event['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Aucun événement trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Initialisation du filtrage du tableau
document.addEventListener('DOMContentLoaded', function() {
    filterTable('event-search', 'events-table');
    initSortableTable('events-table');
    initImagePreviews();
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>