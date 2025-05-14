<?php
// admin/menu.php - Gestion du menu
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier les permissions
if (!hasPermission('view_menu')) {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Traitement de l'ajout ou de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérifier les permissions d'édition
    if (!hasPermission('edit_menu')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            
            // Récupération et nettoyage des données
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $category = cleanInput($_POST['category']);
            $name = cleanInput($_POST['name']);
            $description = cleanInput($_POST['description']);
            $price = str_replace(',', '.', cleanInput($_POST['price']));
            $is_vegetarian = isset($_POST['is_vegetarian']) ? 1 : 0;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
            
            // Validation des données
            if (empty($category) || empty($name) || empty($price)) {
                throw new Exception('Tous les champs obligatoires doivent être remplis.');
            }
            
            if (!is_numeric($price)) {
                throw new Exception('Le prix doit être un nombre.');
            }
            
            // Traitement de l'action
            if ($_POST['action'] === 'add') {
                // Ajout d'un nouvel élément
                $stmt = $pdo->prepare("
                    INSERT INTO menus 
                    (category, name, description, price, is_vegetarian, is_active, sort_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$category, $name, $description, $price, $is_vegetarian, $is_active, $sort_order]);
                
                // Journaliser l'action
                logAction('create', 'menu', $pdo->lastInsertId());
                
                $message = 'L\'élément a été ajouté avec succès.';
                $message_type = 'success';
            } else if ($_POST['action'] === 'edit' && $id > 0) {
                // Modification d'un élément existant
                $stmt = $pdo->prepare("
                    UPDATE menus 
                    SET category = ?, name = ?, description = ?, price = ?, 
                        is_vegetarian = ?, is_active = ?, sort_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$category, $name, $description, $price, $is_vegetarian, $is_active, $sort_order, $id]);
                
                // Journaliser l'action
                logAction('update', 'menu', $id);
                
                $message = 'L\'élément a été modifié avec succès.';
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
    if (!hasPermission('edit_menu')) {
        $message = 'Vous n\'avez pas les droits pour effectuer cette action.';
        $message_type = 'danger';
    } else {
        try {
            $pdo = connectDB();
            $id = intval($_GET['id']);
            
            // Supprimer l'élément
            $stmt = $pdo->prepare("DELETE FROM menus WHERE id = ?");
            $stmt->execute([$id]);
            
            // Journaliser l'action
            logAction('delete', 'menu', $id);
            
            $message = 'L\'élément a été supprimé avec succès.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Récupération des catégories distinctes
function getCategories() {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT DISTINCT category FROM menus ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupération des éléments du menu
function getMenuItems($category = null) {
    $pdo = connectDB();
    $query = "SELECT * FROM menus";
    $params = [];
    
    if ($category) {
        $query .= " WHERE category = ?";
        $params[] = $category;
    }
    
    $query .= " ORDER BY category, sort_order, name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Récupération d'un élément spécifique
function getMenuItem($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$categories = getCategories();
$activeCategory = isset($_GET['category']) ? cleanInput($_GET['category']) : null;
$menuItems = getMenuItems($activeCategory);

// Mode édition
$editMode = false;
$editItem = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMode = true;
    $editItem = getMenuItem(intval($_GET['id']));
    
    if (!$editItem) {
        $editMode = false;
        $message = 'L\'élément demandé n\'existe pas.';
        $message_type = 'danger';
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Gestion du Menu</h2>
    <p>Gérez les éléments du menu de la Taverne Kanorelim</p>
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
            
            <div class="search-box">
                <input type="text" id="menu-search" placeholder="Rechercher..." class="form-control">
                <i class="fas fa-search"></i>
            </div>
        </div>
        
        <?php if (hasPermission('edit_menu')): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un élément
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Formulaire d'ajout/édition -->
    <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || $editMode): ?>
        <div class="card">
            <div class="card-header">
                <h3><?php echo $editMode ? 'Modifier un élément' : 'Ajouter un élément'; ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="category">Catégorie *</label>
                                <input type="text" id="category" name="category" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editItem['category']) : ''; ?>" required list="categories">
                                <datalist id="categories">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name">Nom *</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editItem['name']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo $editMode ? htmlspecialchars($editItem['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="price">Prix (€) *</label>
                                <input type="text" id="price" name="price" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editItem['price']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="sort_order">Ordre d'affichage</label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo $editMode ? intval($editItem['sort_order']) : '0'; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="is_vegetarian" name="is_vegetarian" <?php echo $editMode && $editItem['is_vegetarian'] ? 'checked' : ''; ?>>
                            <label for="is_vegetarian">Plat végétarien</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" id="is_active" name="is_active" <?php echo ($editMode && $editItem['is_active']) || !$editMode ? 'checked' : ''; ?>>
                            <label for="is_active">Actif</label>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="menu.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Liste des éléments de menu -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des éléments du menu</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($menuItems)): ?>
                <table class="data-table" id="menu-table">
                    <thead>
                        <tr>
                            <th data-sort>Catégorie</th>
                            <th data-sort>Nom</th>
                            <th>Description</th>
                            <th data-sort>Prix</th>
                            <th>Végétarien</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <?php
                                    $description = htmlspecialchars($item['description']);
                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                    ?>
                                </td>
                                <td><?php echo number_format($item['price'], 2, ',', ' ') . ' €'; ?></td>
                                <td>
                                    <?php if ($item['is_vegetarian']): ?>
                                        <span class="badge badge-success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($item['is_active']): ?>
                                        <span class="badge badge-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if (hasPermission('edit_menu')): ?>
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" data-confirm="Êtes-vous sûr de vouloir supprimer cet élément ?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=view&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Aucun élément de menu trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Initialisation du filtrage du tableau
document.addEventListener('DOMContentLoaded', function() {
    filterTable('menu-search', 'menu-table');
    initSortableTable('menu-table');
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>