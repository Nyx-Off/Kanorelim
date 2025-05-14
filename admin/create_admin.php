<?php
// admin/create_admin.php - Script pour créer un compte administrateur
// IMPORTANT: Supprimer ce fichier après utilisation!

// Inclure la configuration
require_once 'config.php';

// Paramètres du compte administrateur
$admin_username = 'admin';
$admin_password = 'admin123'; // Vous pourrez le changer plus tard
$admin_email = 'admin@kanorelim.fr';
$admin_role = 'admin';

try {
    // Connexion à la base de données
    $pdo = connectDB();
    
    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$admin_username]);
    $user = $stmt->fetch();
    
    // Hachage du mot de passe
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    if ($user) {
        // Mettre à jour l'utilisateur existant
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password = ?, email = ?, role = ?
            WHERE username = ?
        ");
        $stmt->execute([$password_hash, $admin_email, $admin_role, $admin_username]);
        echo "<p>L'utilisateur admin a été mis à jour avec succès.</p>";
    } else {
        // Créer un nouvel utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (username, password, email, role) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$admin_username, $password_hash, $admin_email, $admin_role]);
        echo "<p>L'utilisateur admin a été créé avec succès.</p>";
    }
    
    echo "<p>Nom d'utilisateur: <strong>{$admin_username}</strong></p>";
    echo "<p>Mot de passe: <strong>{$admin_password}</strong></p>";
    echo "<p>IMPORTANT: Veuillez changer ce mot de passe dès votre première connexion!</p>";
    echo "<p>IMPORTANT: Supprimez ce fichier immédiatement après utilisation!</p>";
    
} catch (PDOException $e) {
    // Afficher l'erreur (en développement uniquement)
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
}
?>