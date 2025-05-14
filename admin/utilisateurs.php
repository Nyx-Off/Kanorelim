<?php
// admin/utilisateurs.php - Gestion des utilisateurs
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier les permissions (seuls les administrateurs peuvent gérer les utilisateurs)
if ($_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Traitement de l'ajout ou de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = connectDB();
        
        // Récupération et nettoyage des données
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $username = cleanInput($_POST['username']);
        $email = cleanInput($_POST['email']);
        $role = cleanInput($_POST['role']);
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validation des données
        if (empty($username) || empty($email) || empty($role)) {
            throw new Exception('Tous les champs obligatoires doivent être remplis.');
        }
        
        // Vérifier que le rôle est valide
        if (!in_array($role, ['admin', 'editeur', 'moderateur'])) {
            throw new Exception('Rôle non valide.');
        }
        
        // Vérifier le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('L\'adresse email n\'est pas valide.');
        }
        
        // Vérifier l'unicité du nom d'utilisateur et de l'email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $id]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Le nom d\'utilisateur ou l\'email est déjà utilisé.');
        }
        
        // Traitement de l'action
        if ($_POST['action'] === 'add') {
            // Vérifier que les mots de passe correspondent
            if (empty($password)) {
                throw new Exception('Le mot de passe est obligatoire pour un nouvel utilisateur.');
            }
            
            if ($password !== $confirm_password) {
                throw new Exception('Les mots de passe ne correspondent pas.');
            }
            
            // Hachage du mot de passe
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Ajout d'un nouvel utilisateur
            $stmt = $pdo->prepare("
                INSERT INTO users 
                (username, password, email, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $password_hash, $email, $role]);
            
            // Journaliser l'action
            logAction('create', 'user', $pdo->lastInsertId());
            
            $message = 'L\'utilisateur a été ajouté avec succès.';
            $message_type = 'success';
        } else if ($_POST['action'] === 'edit' && $id > 0) {
            // Si l'utilisateur modifie son propre compte, vérifier qu'il ne se retire pas les droits admin
            if ($id === $_SESSION['admin_id'] && $role !== 'admin') {
                throw new Exception('Vous ne pouvez pas modifier votre propre rôle d\'administrateur.');
            }
            
            // Modification d'un utilisateur existant
            if (!empty($password)) {
                // Vérifier que les mots de passe correspondent si un nouveau mot de passe est fourni
                if ($password !== $confirm_password) {
                    throw new Exception('Les mots de passe ne correspondent pas.');
                }
                
                // Hachage du nouveau mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Mise à jour avec le nouveau mot de passe
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, password = ?, email = ?, role = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $password_hash, $email, $role, $id]);
            } else {
                // Mise à jour sans changer le mot de passe
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, role = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $role, $id]);
            }
            
            // Journaliser l'action
            logAction('update', 'user', $id);
            
            $message = 'L\'utilisateur a été modifié avec succès.';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $pdo = connectDB();
        $id = intval($_GET['id']);
        
        // Vérifier que l'utilisateur ne tente pas de se supprimer lui-même
        if ($id === $_SESSION['admin_id']) {
            throw new Exception('Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Journaliser l'action
        logAction('delete', 'user', $id);
        
        $message = 'L\'utilisateur a été supprimé avec succès.';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Récupération des utilisateurs
function getUsers() {
    $pdo = connectDB();
    $stmt = $pdo->query("SELECT * FROM users ORDER BY username");
    return $stmt->fetchAll();
}

// Récupération d'un utilisateur spécifique
function getUser($id) {
    $pdo = connectDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Chargement des données
$users = getUsers();

// Mode édition
$editMode = false;
$editUser = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editMode = true;
    $editUser = getUser(intval($_GET['id']));
    
    if (!$editUser) {
        $editMode = false;
        $message = 'L\'utilisateur demandé n\'existe pas.';
        $message_type = 'danger';
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Gestion des Utilisateurs</h2>
    <p>Gérez les utilisateurs ayant accès à l'administration de la Taverne Kanorelim</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" data-dismiss="5000">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-content-wrapper">
    <!-- Actions -->
    <div class="admin-actions">
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Ajouter un utilisateur
        </a>
    </div>
    
    <!-- Formulaire d'ajout/édition -->
    <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || $editMode): ?>
        <div class="card">
            <div class="card-header">
                <h3><?php echo $editMode ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'; ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="<?php echo $editMode ? 'edit' : 'add'; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur *</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editUser['username']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo $editMode ? htmlspecialchars($editUser['email']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="admin" <?php echo $editMode && $editUser['role'] === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                            <option value="editeur" <?php echo $editMode && $editUser['role'] === 'editeur' ? 'selected' : ''; ?>>Éditeur</option>
                            <option value="moderateur" <?php echo $editMode && $editUser['role'] === 'moderateur' ? 'selected' : ''; ?>>Modérateur</option>
                        </select>
                        <small class="form-text text-muted">
                            Administrateur : Tous les droits.<br>
                            Éditeur : Peut ajouter/modifier du contenu.<br>
                            Modérateur : Peut gérer les réservations et les messages.
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <?php echo $editMode ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *'; ?>
                        </label>
                        <input type="password" id="password" name="password" class="form-control" <?php echo $editMode ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" <?php echo $editMode ? '' : 'required'; ?>>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="utilisateurs.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Liste des utilisateurs -->
    <div class="card">
        <div class="card-header">
            <h3>Liste des utilisateurs</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <table class="data-table" id="users-table">
                    <thead>
                        <tr>
                            <th data-sort>ID</th>
                            <th data-sort>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Dernière connexion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo $user['id'] === $_SESSION['admin_id'] ? 'current-user' : ''; ?>">
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                    <?php if ($user['id'] === $_SESSION['admin_id']): ?>
                                        <span class="badge badge-info">Vous</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                    $role_labels = [
                                        'admin' => '<span class="badge badge-primary">Administrateur</span>',
                                        'editeur' => '<span class="badge badge-success">Éditeur</span>',
                                        'moderateur' => '<span class="badge badge-secondary">Modérateur</span>'
                                    ];
                                    echo $role_labels[$user['role']] ?? $user['role'];
                                    ?>
                                </td>
                                <td>
                                    <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?>
                                </td>
                                <td class="actions">
                                    <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" title="Supprimer" data-confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">Aucun utilisateur trouvé.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques à la gestion des utilisateurs */
.current-user {
    background-color: #f8f9fa;
}

#password-strength {
    margin-top: 5px;
    height: 5px;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.strength-weak {
    width: 33%;
    background-color: #dc3545;
}

.strength-medium {
    width: 66%;
    background-color: #ffc107;
}

.strength-strong {
    width: 100%;
    background-color: #28a745;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des confirmations de suppression
    initDeleteConfirmations();
    
    // Initialisation du tri des tableaux
    initSortableTable('users-table');
    
    // Vérification de la force du mot de passe
    const passwordInput = document.getElementById('password');
    
    if (passwordInput) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.id = 'password-strength';
        passwordInput.parentNode.appendChild(strengthIndicator);
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Réinitialiser l'indicateur si le champ est vide
            if (password.length === 0) {
                strengthIndicator.className = '';
                strengthIndicator.style.width = '0';
                return;
            }
            
            // Calculer la force du mot de passe
            let strength = 0;
            
            // Longueur minimale
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Complexité
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                strength += 1;
            }
            
            if (/[0-9]/.test(password)) {
                strength += 1;
            }
            
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
            }
            
            // Mettre à jour l'indicateur
            if (strength < 2) {
                strengthIndicator.className = 'strength-weak';
            } else if (strength < 4) {
                strengthIndicator.className = 'strength-medium';
            } else {
                strengthIndicator.className = 'strength-strong';
            }
        });
    }
    
    // Vérification de la correspondance des mots de passe
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value === passwordInput.value) {
                this.setCustomValidity('');
            } else {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value !== '') {
                if (this.value === confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('');
                } else {
                    confirmPasswordInput.setCustomValidity('Les mots de passe ne correspondent pas');
                }
            }
        });
    }
});
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>