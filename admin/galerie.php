<?php
// admin/galerie.php - Gestion de la galerie
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier les permissions
if (!hasPermission('view_gallery')) {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Traitement de l'ajout ou de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_gallery')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            
            // Récupération et nettoyage des données
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $title = cleanInput($_POST['title']);
            $description = cleanInput($_POST['description']);
            $category = cleanInput($_POST['category']);
            $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
            
            // Image
            $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : '';
            
            // Validation des données
            if (empty($title)) {
                throw new Exception('Le titre est obligatoire.');
            }
            
            // Traitement de l'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/gallery/';
                $filename = 'gallery_' . time();
                
                $image_info = uploadImage($_FILES['image'], $upload_dir, $filename);
                
                if ($image_info) {
                    $image_path = $image_info['url'];
                } else {
                    throw new Exception('Erreur lors du téléchargement de l\'image.');
                }
            } elseif ($_POST['action'] === 'add' && empty($image_path)) {
                throw new Exception('Vous devez sélectionner une image.');
            }
            
            // Traitement de l'action
            if ($_POST['action'] === 'add') {
                // Ajout d'une nouvelle image
                $stmt = $pdo->prepare("
                    INSERT INTO gallery 
                    (title, description, image_path, category, sort_order)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $description, $image_path, $category, $sort_order]);
                
                // Journaliser l'action
                logAction('create', 'gallery', $pdo->lastInsertId());
                
                $message = 'L\'image a été ajoutée avec succès.';
                $message_type = 'success';
            } else if ($_POST['action'] === 'edit' && $id > 0) {
                // Modification d'une image existante
                $stmt = $pdo->prepare("
                    UPDATE gallery 
                    SET title = ?, description = ?, image_path = ?, category = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $description, $image_path, $category, $sort_order, $id]);
                
                // Journaliser l'action
                logAction('update', 'gallery', $id);
                
                $message = 'L\'image a été modifiée avec succès.';
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
    if (!hasPermission('edit_gallery')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            
            // Récupérer l'image pour la supprimer
            $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
            
            // Supprimer l'image de la galerie
            $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->execute([$id]);
            
            // Supprimer l'image si elle existe
            if (!empty($image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $image)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $image);
            }
            
            // Journaliser l'action
            logAction('delete', 'gallery', $id);
            
            $message = 'L\'image a été supprimée avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Récupération des catégories de la galerie
function getGalleryCategories() {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE category != '' ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupération des images de la galerie
function getGalleryImages($category = null) {
    $pdo = connectDB();
    $query = "SELECT * FROM gallery";
    $params = [];
    
    if ($category) {
        $query .= " WHERE category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY sort_order, title";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Récupération d'une image spécifique
function getGalleryImage($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$categories = getGalleryCategories();
$activeCategory = isset($_GET['category']) ? cleanInput($_GET['category']) : null;
$galleryImages = getGalleryImages($activeCategory);

// Mode édition
$editMode = false;
$editImage = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMode = true;
    $editImage = getGalleryImage(intval($_GET['id']));
    
    if (!$editImage) {
        $editMode = false;
        $message = 'L\'image demandée n\'existe pas.';
        $message_type = 'danger';
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Gestion de la Galerie</h2>
    <p>Gérez les images de la galerie de la Taverne Kanorelim</p>
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
                    <label for="category-filter">Catégorie:</label>
                    <select id="category-filter" name="category" class="form-control" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $activeCategory === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <?php if (hasPermission('edit_gallery')): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une image
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Formulaire d'ajout/édition -->
    <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || $editMode): ?>
        <div class="card">
            <div class="card-header">
                <h3><?php echo $editMode ? 'Modifier une image' : 'Ajouter une image'; ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo $editImage['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo $editImage['image_path']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="title">Titre *</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editImage['title']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="category">Catégorie</label>
                                <input type="text" id="category" name="category" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editImage['category']) : ''; ?>" list="categories">
                                <datalist id="categories">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo $editMode ? htmlspecialchars($editImage['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image <?php echo $editMode ? '' : '*'; ?></label>
                        <input type="file" id="image" name="image" class="form-control image-upload" data-preview="#image-preview" <?php echo $editMode ? '' : 'required'; ?>>
                        <small class="form-text text-muted">Formats acceptés : JPG, PNG, GIF. Taille maximale : 2 Mo.</small>
                        
                        <?php if ($editMode && !empty($editImage['image_path'])): ?>
                            <div class="current-image">
                                <p>Image actuelle :</p>
                                <img src="<?php echo $editImage['image_path']; ?>" alt="Image actuelle" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        
                        <div id="image-preview" class="image-preview-container"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort_order">Ordre d'affichage</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo $editMode ? $editImage['sort_order'] : 0; ?>">
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="galerie.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Galerie d'images -->
    <div class="card">
        <div class="card-header">
            <h3>Images de la galerie</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($galleryImages)): ?>
                <div class="gallery-grid">
                    <?php foreach ($galleryImages as $image): ?>
                        <div class="gallery-item">
                            <div class="gallery-image">
                                <img src="<?php echo $image['image_path']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                                <div class="gallery-overlay">
                                    <div class="gallery-actions">
                                        <?php if (hasPermission('edit_gallery')): ?>
                                            <a href="?action=edit&id=<?php echo $image['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $image['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" data-confirm="Êtes-vous sûr de vouloir supprimer cette image ?">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=view&id=<?php echo $image['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4 class="gallery-title"><?php echo htmlspecialchars($image['title']); ?></h4>
                                <?php if (!empty($image['category'])): ?>
                                    <div class="gallery-category">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($image['category']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">Aucune image trouvée.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la galerie */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.gallery-item {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.gallery-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(44, 27, 14, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-actions {
    display: flex;
    gap: 10px;
}

.gallery-info {
    padding: 15px;
}

.gallery-title {
    margin: 0 0 10px;
    font-size: 1.1rem;
    color: #8B4513;
    font-family: 'Cinzel', serif;
}

.gallery-category {
    margin-top: 5px;
}

.current-image {
    margin: 15px 0;
    padding: 10px;
    background-color: #f5f5f5;
    border-radius: 5px;
}

.current-image p {
    margin-bottom: 10px;
    font-weight: 600;
}

.image-preview-container {
    margin-top: 15px;
}

.image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des confirmations de suppression
    initDeleteConfirmations();
    
    // Initialisation des prévisualisations d'images
    initImagePreviews();
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>