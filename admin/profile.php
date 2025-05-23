<?php
// admin/profile.php - Profil de l'utilisateur
session_start();
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier la session admin
checkAdminSession();

// Traitement des actions
$message = '';
$message_type = '';

// Traitement du formulaire de profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = connectDB();
        $user_id = $_SESSION['admin_id'];
        
        if ($_POST['action'] === 'update_profile') {
            // Mise à jour du profil
            $username = cleanInput($_POST['username']);
            $email = cleanInput($_POST['email']);
            
            // Validation
            if (empty($username) || empty($email)) {
                throw new Exception('Tous les champs sont obligatoires.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('L\'adresse email n\'est pas valide.');
            }
            
            // Vérifier l'unicité du nom d'utilisateur et de l'email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Le nom d\'utilisateur ou l\'email est déjà utilisé.');
            }
            
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $user_id]);
            
            // Mettre à jour la session
            $_SESSION['admin_username'] = $username;
            
            // Journaliser l'action
            logAction('update', 'profile', $user_id);
            
            $message = 'Votre profil a été mis à jour avec succès.';
            $message_type = 'success';
        }
        
        if ($_POST['action'] === 'change_password') {
            // Changement de mot de passe
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validation
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception('Tous les champs sont obligatoires.');
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception('Les nouveaux mots de passe ne correspondent pas.');
            }
            
            if (strlen($new_password) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères.');
            }
            
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception('Le mot de passe actuel est incorrect.');
            }
            
            // Mettre à jour le mot de passe
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$password_hash, $user_id]);
            
            // Journaliser l'action
            logAction('update', 'password', $user_id);
            
            $message = 'Votre mot de passe a été modifié avec succès.';
            $message_type = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Récupérer les informations de l'utilisateur
$pdo = connectDB();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

// Récupérer l'activité récente de l'utilisateur
$stmt = $pdo->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['admin_id']]);
$activities = $stmt->fetchAll();

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Mon Profil</h2>
    <p>Gérez vos informations personnelles et votre mot de passe</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" data-dismiss="5000">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-content-wrapper">
    <div class="profile-grid">
        <!-- Informations du profil -->
        <div class="profile-section">
            <div class="card">
                <div class="card-header">
                    <h3>Informations personnelles</h3>
                </div>
                <div class="card-body">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-details">
                            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="user-role">
                                <?php 
                                $role_labels = [
                                    'admin' => 'Administrateur',
                                    'editeur' => 'Éditeur',
                                    'moderateur' => 'Modérateur'
                                ];
                                echo $role_labels[$user['role']] ?? $user['role'];
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <form method="post" action="" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="username">Nom d'utilisateur</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Changement de mot de passe -->
            <div class="card">
                <div class="card-header">
                    <h3>Changer le mot de passe</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <small class="form-text text-muted">Minimum 8 caractères</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-submit">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Activité et statistiques -->
        <div class="profile-sidebar">
            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h3>Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-label">Membre depuis</div>
                            <div class="stat-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Dernière connexion</div>
                            <div class="stat-value">
                                <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais'; ?>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Actions effectuées</div>
                            <div class="stat-value"><?php echo count($activities); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activité récente -->
            <div class="card">
                <div class="card-header">
                    <h3>Activité récente</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($activities)): ?>
                        <div class="activity-list">
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php
                                        $icon = 'fa-circle-info';
                                        switch ($activity['action']) {
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
                                            <?php 
                                            $actions = [
                                                'create' => 'Création',
                                                'update' => 'Modification',
                                                'delete' => 'Suppression',
                                                'login' => 'Connexion',
                                                'logout' => 'Déconnexion'
                                            ];
                                            echo $actions[$activity['action']] ?? $activity['action']; 
                                            ?>
                                            <?php if ($activity['entity']): ?>
                                                - <?php echo $activity['entity']; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-time"><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></div>
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
</div>

<style>
/* Styles spécifiques au profil */
.profile-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.profile-avatar {
    font-size: 5rem;
    color: #8B4513;
}

.profile-details h4 {
    margin: 0 0 5px 0;
    color: #8B4513;
    font-size: 1.5rem;
}

.profile-details p {
    margin: 0 0 10px 0;
    color: #666;
}

.user-role {
    display: inline-block;
    padding: 5px 10px;
    background-color: #8B4513;
    color: white;
    border-radius: 3px;
    font-size: 0.9rem;
}

.profile-form {
    margin-top: 20px;
}

.profile-stats {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.stat-value {
    font-weight: 600;
    color: #8B4513;
}

@media (max-width: 992px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .profile-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>