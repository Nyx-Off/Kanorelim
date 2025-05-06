
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
        
        // Si pas d'erreurs, envoyer l'email
        if (empty($errors)) {
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
            
            if (sendEmail(ADMIN_EMAIL, $email_subject, $email_body, $email)) {
                return [
                    'success' => true,
                    'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dès que possible.'
                ];
            } else {
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
        
        // Si pas d'erreurs, envoyer l'email de réservation
        if (empty($errors)) {
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
                    <p><strong>Date:</strong> {$date}</p>
                    <p><strong>Heure:</strong> {$time}</p>
                    <p><strong>Nombre de convives:</strong> {$guests}</p>
                    <p><strong>Occasion:</strong> {$occasion}</p>
                    <p><strong>Message spécial:</strong></p>
                    <p>{$message}</p>
                </body>
                </html>
            ";
            
            if (sendEmail(ADMIN_EMAIL, $email_subject, $email_body, $email)) {
                // Envoyer aussi une confirmation au client
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
            } else {
                return [
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l\'envoi de la réservation. Veuillez réessayer plus tard.'
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
 * Fonction pour obtenir les événements à venir
 */
function getUpcomingEvents($limit = 3) {
    global $evenements;
    
    // Trier les événements par date
    usort($evenements, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    // Filtrer pour ne garder que les événements à venir
    $upcoming = array_filter($evenements, function($event) {
        return strtotime($event['date']) >= strtotime(date('Y-m-d'));
    });
    
    // Limiter le nombre d'événements
    return array_slice($upcoming, 0, $limit);
}

/**
 * Fonction pour générer le HTML d'un menu
 */
function generateMenuHTML($categories) {
    $html = '';
    
    foreach ($categories as $categorie => $items) {
        $html .= '<div class="menu-category">';
        $html .= '<h2>' . $categorie . '</h2>';
        $html .= '<div class="medieval-divider"></div>';
        $html .= '<div class="menu-items">';
        
        foreach ($items as $item) {
            $vegetarianClass = isset($item['vegetarien']) && $item['vegetarien'] ? ' vegetarian' : '';
            
            $html .= '<div class="menu-item' . $vegetarianClass . '">';
            $html .= '<div class="menu-item-header">';
            $html .= '<h3>' . $item['nom'] . '</h3>';
            $html .= '<span class="menu-item-price">' . $item['prix'] . '</span>';
            $html .= '</div>';
            $html .= '<p class="menu-item-description">' . $item['description'] . '</p>';
            
            if (isset($item['vegetarien']) && $item['vegetarien']) {
                $html .= '<span class="vegetarian-label"><i class="fas fa-leaf"></i> Végétarien</span>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
    }
    
    return $html;
}

?>