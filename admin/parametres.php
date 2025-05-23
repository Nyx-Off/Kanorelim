<?php
// admin/parametres.php - Gestion des paramètres
session_start();
define('ADMIN_INCLUDED', true);
require_once 'config.php';
require_once 'includes/functions.php';

// Vérifier la session admin
checkAdminSession();

// Vérifier les permissions (seuls les administrateurs peuvent accéder aux paramètres)
if ($_SESSION['admin_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Traitement des actions
$message = '';
$message_type = '';

// Traitement du formulaire de paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = connectDB();
        
        if ($_POST['action'] === 'general') {
            // Paramètres généraux
            $site_name = cleanInput($_POST['site_name']);
            $site_description = cleanInput($_POST['site_description']);
            $admin_email = cleanInput($_POST['admin_email']);
            $phone = cleanInput($_POST['phone']);
            $address = cleanInput($_POST['address']);
            
            // Ici, vous pourriez sauvegarder ces paramètres dans une table de configuration
            // Pour l'instant, on simule la sauvegarde
            
            $message = 'Les paramètres généraux ont été mis à jour avec succès.';
            $message_type = 'success';
            
            // Journaliser l'action
            logAction('update', 'settings', null);
        }
        
        if ($_POST['action'] === 'hours') {
            // Horaires d'ouverture
            // Traitement des horaires...
            
            $message = 'Les horaires ont été mis à jour avec succès.';
            $message_type = 'success';
        }
        
        if ($_POST['action'] === 'social') {
            // Réseaux sociaux
            $facebook = cleanInput($_POST['facebook']);
            $instagram = cleanInput($_POST['instagram']);
            $twitter = cleanInput($_POST['twitter']);
            
            $message = 'Les liens des réseaux sociaux ont été mis à jour avec succès.';
            $message_type = 'success';
        }
        
    } catch (Exception $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<div class="admin-page-header">
    <h2>Paramètres</h2>
    <p>Configurez les paramètres généraux de votre site</p>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?>" data-dismiss="5000">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="admin-content-wrapper">
    <!-- Paramètres généraux -->
    <div class="card">
        <div class="card-header">
            <h3>Paramètres généraux</h3>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="action" value="general">
                
                <div class="form-group">
                    <label for="site_name">Nom du site</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="Taverne Kanorelim" required>
                </div>
                
                <div class="form-group">
                    <label for="site_description">Description du site</label>
                    <textarea id="site_description" name="site_description" class="form-control" rows="3">Une expérience médiévale authentique</textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="admin_email">Email administrateur</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" value="contact@kanorelim.fr" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" class="form-control" value="+33 (0)1 23 45 67 89">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Adresse complète</label>
                    <textarea id="address" name="address" class="form-control" rows="3">12 Rue des Templiers
Cité Médiévale
95300 Pontoise
France</textarea>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Horaires d'ouverture -->
    <div class="card">
        <div class="card-header">
            <h3>Horaires d'ouverture</h3>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="action" value="hours">
                
                <div class="hours-list">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label>Lundi - Jeudi</label>
                                <input type="text" name="hours_weekdays" class="form-control" value="11h - 23h">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label>Vendredi - Samedi</label>
                                <input type="text" name="hours_weekend" class="form-control" value="11h - 01h">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dimanche</label>
                        <input type="text" name="hours_sunday" class="form-control" value="12h - 22h">
                    </div>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Réseaux sociaux -->
    <div class="card">
        <div class="card-header">
            <h3>Réseaux sociaux</h3>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <input type="hidden" name="action" value="social">
                
                <div class="form-group">
                    <label for="facebook">
                        <i class="fab fa-facebook"></i> Facebook
                    </label>
                    <input type="url" id="facebook" name="facebook" class="form-control" value="https://facebook.com/tavernekanorelim">
                </div>
                
                <div class="form-group">
                    <label for="instagram">
                        <i class="fab fa-instagram"></i> Instagram
                    </label>
                    <input type="url" id="instagram" name="instagram" class="form-control" value="https://instagram.com/tavernekanorelim">
                </div>
                
                <div class="form-group">
                    <label for="twitter">
                        <i class="fab fa-twitter"></i> Twitter
                    </label>
                    <input type="url" id="twitter" name="twitter" class="form-control" value="https://twitter.com/tavernekanorelim">
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Paramètres avancés -->
    <div class="card">
        <div class="card-header">
            <h3>Paramètres avancés</h3>
        </div>
        <div class="card-body">
            <div class="settings-actions">
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Maintenance du site</h4>
                        <p>Activer le mode maintenance pour effectuer des mises à jour</p>
                    </div>
                    <div class="setting-action">
                        <label class="switch">
                            <input type="checkbox" id="maintenance_mode">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Sauvegardes</h4>
                        <p>Dernière sauvegarde : <?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                    <div class="setting-action">
                        <button class="btn btn-secondary">
                            <i class="fas fa-download"></i> Sauvegarder maintenant
                        </button>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Réinitialisation</h4>
                        <p>Réinitialiser tous les paramètres par défaut</p>
                    </div>
                    <div class="setting-action">
                        <button class="btn btn-danger" data-confirm="Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques aux paramètres */
.hours-list {
    margin-bottom: 20px;
}

.settings-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: #f9f6f0;
    border-radius: 5px;
}

.setting-info h4 {
    margin: 0 0 5px 0;
    color: var(--color-primary);
}

.setting-info p {
    margin: 0;
    color: #666;
}

/* Switch button */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #8B4513;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

@media (max-width: 768px) {
    .setting-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>