<?php
// functions.php - Fonctions utilitaires
// Inclure la configuration si ce n'est pas déjà fait
if (!defined('INCLUDED_CONFIG')) {
    require_once 'config.php';
    define('INCLUDED_CONFIG', true);
}

/**
 * Traitement du formulaire de contact
 */
function handleContactForm() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
        // Récupération des données
        $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $subject = isset($_POST['subject']) ? sanitize($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
        
        // Validation des données
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Le nom est requis';
        }
        
        if (empty($email)) {
            $errors[] = 'L\'email est requis';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide';
        }
        
        if (empty($subject)) {
            $errors[] = 'Le sujet est requis';
        }
        
        if (empty($message)) {
            $errors[] = 'Le message est requis';
        }
        
        // Si pas d'erreurs, enregistrer dans la base de données et envoyer l'email
        if (empty($errors)) {
            try {
                $pdo = connectDB();
                
                // Enregistrer le message dans la base de données
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages 
                    (name, email, subject, message, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $email, $subject, $message]);
                
                // Envoyer l'email
                $email_subject = "Contact Taverne Kanorelim: " . $subject;
                $email_body = "
                    <html>
                    <head>
                        <title>Nouveau message de contact</title>
                    </head>
                    <body>
                        <h2>Nouveau message de contact</h2>
                        <p><strong>Nom:</strong> {$name}</p>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Sujet:</strong> {$subject}</p>
                        <p><strong>Message:</strong></p>
                        <p>{$message}</p>
                    </body>
                    </html>
                ";
                
                sendEmail(ADMIN_EMAIL, $email_subject, $email_body, $email);
                
                return [
                    'success' => true,
                    'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dès que possible.'
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Veuillez corriger les erreurs suivantes: ' . implode(', ', $errors)
            ];
        }
    }
    
    return null;
}

/**
 * Traitement du formulaire de réservation
 */
function handleReservationForm() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_submit'])) {
        // Récupération des données
        $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
        $date = isset($_POST['date']) ? sanitize($_POST['date']) : '';
        $time = isset($_POST['time']) ? sanitize($_POST['time']) : '';
        $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
        $occasion = isset($_POST['occasion']) ? sanitize($_POST['occasion']) : '';
        $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
        
        // Validation des données
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Le nom est requis';
        }
        
        if (empty($email)) {
            $errors[] = 'L\'email est requis';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide';
        }
        
        if (empty($date)) {
            $errors[] = 'La date est requise';
        } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
            $errors[] = 'La date doit être dans le futur';
        }
        
        if (empty($time)) {
            $errors[] = 'L\'heure est requise';
        }
        
        if ($guests <= 0 || $guests > 20) {
            $errors[] = 'Le nombre de convives doit être entre 1 et 20';
        }
        
        // Si pas d'erreurs, enregistrer dans la base de données et envoyer les emails
        if (empty($errors)) {
            try {
                $pdo = connectDB();
                
                // Enregistrer la réservation dans la base de données
                $stmt = $pdo->prepare("
                    INSERT INTO reservations 
                    (name, email, phone, date, time, guests, occasion, message, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$name, $email, $phone, $date, $time, $guests, $occasion, $message]);
                
                // Envoyer l'email à l'admin
                $email_subject = "Nouvelle réservation - Taverne Kanorelim";
                $email_body = "
                    <html>
                    <head>
                        <title>Nouvelle réservation</title>
                    </head>
                    <body>
                        <h2>Nouvelle réservation</h2>
                        <p><strong>Nom:</strong> {$name}</p>
                        <p><strong>Email:</strong> {$email}</p>
                        <p><strong>Téléphone:</strong> {$phone}</p>
                        <p><strong>Date:</strong> {$date}</p>
                        <p><strong>Heure:</strong> {$time}</p>
                        <p><strong>Nombre de convives:</strong> {$guests}</p>
                        <p><strong>Occasion:</strong> {$occasion}</p>
                        <p><strong>Message spécial:</strong></p>
                        <p>{$message}</p>
                    </body>
                    </html>
                ";
                
                sendEmail(ADMIN_EMAIL, $email_subject, $email_body, $email);
                
                // Envoyer une confirmation au client
                $confirm_subject = "Confirmation de votre réservation - Taverne Kanorelim";
                $confirm_body = "
                    <html>
                    <head>
                        <title>Confirmation de votre réservation</title>
                    </head>
                    <body>
                        <h2>Confirmation de votre réservation</h2>
                        <p>Cher(e) {$name},</p>
                        <p>Nous avons bien reçu votre demande de réservation pour le {$date} à {$time} pour {$guests} personne(s).</p>
                        <p>Un membre de notre équipe vous contactera rapidement pour confirmer votre réservation.</p>
                        <p>Voici un récapitulatif de votre demande :</p>
                        <ul>
                            <li><strong>Date:</strong> {$date}</li>
                            <li><strong>Heure:</strong> {$time}</li>
                            <li><strong>Nombre de convives:</strong> {$guests}</li>
                            <li><strong>Occasion:</strong> {$occasion}</li>
                        </ul>
                        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                        <p>Au plaisir de vous accueillir prochainement!</p>
                        <p>L'équipe de la Taverne Kanorelim</p>
                    </body>
                    </html>
                ";
                
                sendEmail($email, $confirm_subject, $confirm_body);
                
                return [
                    'success' => true,
                    'message' => 'Votre réservation a été enregistrée avec succès. Nous vous contacterons rapidement pour confirmation.'
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l\'enregistrement de la réservation. Veuillez réessayer plus tard.'
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Veuillez corriger les erreurs suivantes: ' . implode(', ', $errors)
            ];
        }
    }
    
    return null;
}

/**
 * Fonction pour obtenir les événements à venir depuis la base de données
 */
function getUpcomingEvents($limit = 3) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("
            SELECT title, description, date, time, price, image 
            FROM events 
            WHERE is_active = 1 AND date >= CURRENT_DATE()
            ORDER BY date ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        $events = $stmt->fetchAll();
        $upcoming = [];
        
        foreach ($events as $event) {
            $upcoming[] = [
                'date' => $event['date'],
                'titre' => $event['title'],
                'description' => $event['description'],
                'heure' => $event['time'] ? date('H\hi', strtotime($event['time'])) : '20h00',
                'prix' => $event['price'] ?: 'Gratuit',
                'image' => $event['image'] ?: '/api/placeholder/800/500'
            ];
        }
        
        return $upcoming;
    } catch (Exception $e) {
        // En cas d'erreur, retourner un tableau vide
        return [];
    }
}

/**
 * Fonction pour générer le HTML d'un menu depuis la base de données
 */
function generateMenuHTML($categories = null) {
    // Si aucune catégorie n'est fournie, charger depuis la base de données
    if ($categories === null) {
        $categories = getMenuFromDatabase();
    }
    
    $html = '';
    
    foreach ($categories as $categorie => $items) {
        $html .= '<div class="menu-category">';
        $html .= '<h2>' . htmlspecialchars($categorie) . '</h2>';
        $html .= '<div class="medieval-divider"></div>';
        $html .= '<div class="menu-items">';
        
        foreach ($items as $item) {
            $vegetarianClass = isset($item['vegetarien']) && $item['vegetarien'] ? ' vegetarian' : '';
            
            $html .= '<div class="menu-item' . $vegetarianClass . '">';
            $html .= '<div class="menu-item-header">';
            $html .= '<h3>' . htmlspecialchars($item['nom']) . '</h3>';
            $html .= '<span class="menu-item-price">' . htmlspecialchars($item['prix']) . '</span>';
            $html .= '</div>';
            $html .= '<p class="menu-item-description">' . htmlspecialchars($item['description']) . '</p>';
            
            if (isset($item['vegetarien']) && $item['vegetarien']) {
                $html .= '<span class="vegetarian-label"><i class="fas fa-leaf"></i> Végétarien</span>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
    }
    
    return $html;
}

/**
 * Fonction pour obtenir les catégories de la galerie depuis la base de données
 */
function getGalleryCategories() {
    try {
        $pdo = connectDB();
        
        // Vérifier si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'gallery'");
        if ($stmt->rowCount() == 0) {
            // Table n'existe pas, retourner les catégories par défaut
            return [
                'all' => 'Toutes les images',
                'taverne' => 'La Taverne',
                'nourriture' => 'Mets & Boissons',
                'evenements' => 'Événements'
            ];
        }
        
        $stmt = $pdo->query("SELECT DISTINCT category FROM gallery WHERE category != '' AND category IS NOT NULL ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Ajouter "all" au début
        $all_categories = ['all' => 'Toutes les images'];
        foreach ($categories as $category) {
            $all_categories[$category] = ucfirst($category);
        }
        
        return $all_categories;
    } catch (Exception $e) {
        // En cas d'erreur, retourner les catégories par défaut
        return [
            'all' => 'Toutes les images',
            'taverne' => 'La Taverne',
            'nourriture' => 'Mets & Boissons',
            'evenements' => 'Événements'
        ];
    }
}

/**
 * Fonction pour obtenir les images de la galerie filtrées par catégorie
 */
function getGalleryImages($category = 'all') {
    try {
        $pdo = connectDB();
        
        // Vérifier si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'gallery'");
        if ($stmt->rowCount() == 0) {
            // Table n'existe pas, retourner un tableau vide
            return [];
        }
        
        if ($category === 'all') {
            $stmt = $pdo->query("
                SELECT id, title, description, image_path, category 
                FROM gallery 
                ORDER BY sort_order ASC, title ASC
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT id, title, description, image_path, category 
                FROM gallery 
                WHERE category = ? 
                ORDER BY sort_order ASC, title ASC
            ");
            $stmt->execute([$category]);
        }
        
        $images = $stmt->fetchAll();
        $gallery_images = [];
        
        foreach ($images as $image) {
            // Debug: Afficher le chemin de l'image
            error_log("Image path from DB: " . $image['image_path']);
            
            $gallery_images[] = [
                'id' => $image['id'],
                'src' => $image['image_path'],
                'caption' => $image['title'],
                'category' => $image['category'] ?: 'taverne'
            ];
        }
        
        // Debug: Afficher le nombre d'images trouvées
        error_log("Found " . count($gallery_images) . " images for category: " . $category);
        
        return $gallery_images;
    } catch (Exception $e) {
        // Debug: Afficher l'erreur
        error_log("Error in getGalleryImages: " . $e->getMessage());
        // En cas d'erreur, retourner un tableau vide
        return [];
    }
}

/**
 * Fonction pour vérifier si une table existe dans la base de données
 */
function tableExists($tableName) {
    try {
        $pdo = connectDB();
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Fonction pour obtenir les paramètres du site depuis la base de données
 */
function getSiteSetting($key, $default = null) {
    try {
        if (!tableExists('site_settings')) {
            return $default;
        }
        
        $pdo = connectDB();
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? AND is_active = 1");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Fonction pour obtenir les statistiques du site
 */
function getSiteStats() {
    try {
        $pdo = connectDB();
        $stats = [];
        
        // Nombre total de réservations
        if (tableExists('reservations')) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
            $stats['total_reservations'] = $stmt->fetchColumn();
        }
        
        // Nombre d'événements à venir
        if (tableExists('events')) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE date >= CURDATE() AND is_active = 1");
            $stats['upcoming_events'] = $stmt->fetchColumn();
        }
        
        // Nombre d'images dans la galerie
        if (tableExists('gallery')) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM gallery");
            $stats['gallery_images'] = $stmt->fetchColumn();
        }
        
        return $stats;
    } catch (Exception $e) {
        return [
            'total_reservations' => 0,
            'upcoming_events' => 0,
            'gallery_images' => 0
        ];
    }
}
?>