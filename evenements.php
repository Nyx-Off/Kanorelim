<?php
// evenements.php - Page des événements
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Charger les événements depuis la base de données
$evenements = getEventsFromDatabase();

// Trier les événements par date
usort($evenements, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// Filtrer pour séparer les événements à venir et passés
$evenements_a_venir = array_filter($evenements, function($event) {
    return strtotime($event['date']) >= strtotime(date('Y-m-d'));
});

$evenements_passes = array_filter($evenements, function($event) {
    return strtotime($event['date']) < strtotime(date('Y-m-d'));
});

// Limiter les événements passés aux 3 derniers
$evenements_passes = array_slice($evenements_passes, -3);

// Traitement du formulaire de réservation d'événement
$reservation_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_reservation_submit'])) {
    // Récupération des données
    $event_name = isset($_POST['event']) ? sanitize($_POST['event']) : '';
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    // Validation des données
    $errors = [];
    
    if (empty($event_name)) {
        $errors[] = 'Veuillez sélectionner un événement';
    }
    
    if (empty($name)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (empty($email)) {
        $errors[] = 'L\'email est requis';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide';
    }
    
    if ($guests <= 0 || $guests > 20) {
        $errors[] = 'Le nombre de personnes doit être entre 1 et 20';
    }
    
    // Si pas d'erreurs, enregistrer la réservation
    if (empty($errors)) {
        try {
            $pdo = connectDB();
            
            // Enregistrer la réservation d'événement
            $stmt = $pdo->prepare("
                INSERT INTO event_reservations 
                (event_name, name, email, phone, guests, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$event_name, $name, $email, $phone, $guests, $message]);
            
            // Envoyer l'email à l'admin
            $email_subject = "Nouvelle réservation d'événement - Taverne Kanorelim";
            $email_body = "
                <html>
                <head>
                    <title>Nouvelle réservation d'événement</title>
                </head>
                <body>
                    <h2>Nouvelle réservation d'événement</h2>
                    <p><strong>Événement:</strong> {$event_name}</p>
                    <p><strong>Nom:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Téléphone:</strong> {$phone}</p>
                    <p><strong>Nombre de personnes:</strong> {$guests}</p>
                    <p><strong>Message:</strong></p>
                    <p>{$message}</p>
                </body>
                </html>
            ";
            
            sendEmail(ADMIN_EMAIL, $email_subject, $email_body, $email);
            
            // Envoyer une confirmation au client
            $confirm_subject = "Confirmation de votre réservation d'événement - Taverne Kanorelim";
            $confirm_body = "
                <html>
                <head>
                    <title>Confirmation de votre réservation d'événement</title>
                </head>
                <body>
                    <h2>Confirmation de votre réservation d'événement</h2>
                    <p>Cher(e) {$name},</p>
                    <p>Nous avons bien reçu votre demande de réservation pour l'événement \"{$event_name}\" pour {$guests} personne(s).</p>
                    <p>Un membre de notre équipe vous contactera rapidement pour confirmer votre réservation et vous donner tous les détails pratiques.</p>
                    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                    <p>Au plaisir de vous accueillir prochainement!</p>
                    <p>L'équipe de la Taverne Kanorelim</p>
                </body>
                </html>
            ";
            
            sendEmail($email, $confirm_subject, $confirm_body);
            
            $reservation_result = [
                'success' => true,
                'message' => 'Votre réservation pour l\'événement a été enregistrée avec succès. Nous vous contacterons rapidement pour confirmation.'
            ];
        } catch (Exception $e) {
            $reservation_result = [
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement de la réservation. Veuillez réessayer plus tard.'
            ];
        }
    } else {
        $reservation_result = [
            'success' => false,
            'message' => 'Veuillez corriger les erreurs suivantes: ' . implode(', ', $errors)
        ];
    }
}

// Inclure l'en-tête
include 'includes/header.php';
?>

<main>
    <!-- Bannière de la page -->
    <div class="page-banner">
        <div class="banner-content">
            <h1>Nos Événements</h1>
            <p>Festivités médiévales et animations d'époque</p>
        </div>
    </div>

    <!-- Introduction -->
    <section class="events-intro">
        <div class="container">
            <div class="intro-content">
                <div class="intro-image">
                    <img src="/api/placeholder/500/350" alt="Animation médiévale à la Taverne Kanorelim">
                </div>
                <div class="intro-text">
                    <div class="section-title">
                        <h2>Vivez l'Expérience Médiévale</h2>
                        <div class="medieval-divider"></div>
                    </div>
                    <p>La Taverne Kanorelim n'est pas seulement un lieu où l'on se restaure, c'est un véritable voyage dans le temps. Tout au long de l'année, nous organisons des événements qui vous plongeront dans l'atmosphère festive du Moyen Âge.</p>
                    <p>De nos banquets somptueux à nos soirées thématiques, en passant par nos ateliers d'artisanat médiéval, chaque événement est soigneusement conçu pour vous offrir une expérience authentique et immersive.</p>
                    <p>Consultez notre calendrier ci-dessous et rejoignez-nous pour festoyer comme au temps des chevaliers !</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Événements à venir -->
    <section class="upcoming-events">
        <div class="container">
            <div class="section-title">
                <h2>Événements à Venir</h2>
                <div class="medieval-divider"></div>
            </div>
            
            <?php if (!empty($evenements_a_venir)): ?>
                <div class="events-grid">
                    <?php foreach ($evenements_a_venir as $event): ?>
                        <div class="event-card-large">
                            <div class="event-image">
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['titre']); ?>">
                                <div class="event-date-badge">
                                    <div class="day"><?php echo date('d', strtotime($event['date'])); ?></div>
                                    <div class="month"><?php echo date('M', strtotime($event['date'])); ?></div>
                                </div>
                            </div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['titre']); ?></h3>
                                <div class="event-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['heure']); ?></span>
                                    <span><i class="fas fa-coins"></i> <?php echo htmlspecialchars($event['prix']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <a href="#reservation" class="cta-button">Réserver</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-events">
                    <p>Aucun événement à venir pour le moment. Consultez régulièrement cette page pour découvrir nos prochains événements.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section inscription newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2>Restez Informés</h2>
                    <p>Inscrivez-vous à notre parchemin des nouvelles pour être les premiers à connaître nos événements et offres spéciales.</p>
                </div>
                <form class="newsletter-form">
                    <div class="form-row">
                        <input type="email" placeholder="Votre adresse de messagerie" required>
                        <button type="submit" class="cta-button">S'inscrire</button>
                    </div>
                    <div class="form-consent">
                        <input type="checkbox" id="consent" required>
                        <label for="consent">J'accepte de recevoir les nouvelles de la Taverne Kanorelim</label>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Événements passés -->
    <?php if (!empty($evenements_passes)): ?>
    <section class="past-events">
        <div class="container">
            <div class="section-title">
                <h2>Événements Passés</h2>
                <div class="medieval-divider"></div>
            </div>
            
            <div class="past-events-grid">
                <?php foreach ($evenements_passes as $event): ?>
                    <div class="past-event-card">
                        <div class="past-event-image">
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['titre']); ?>">
                            <div class="event-overlay">
                                <span>Événement passé</span>
                            </div>
                        </div>
                        <div class="past-event-details">
                            <h3><?php echo htmlspecialchars($event['titre']); ?></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($event['date'])); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars(substr($event['description'], 0, 100) . '...'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section de services personnalisés -->
    <section class="custom-events">
        <div class="container">
            <div class="section-title">
                <h2>Événements Sur Mesure</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="custom-events-content">
                <div class="custom-events-text">
                    <p>Vous souhaitez organiser un événement privé à thème médiéval ? La Taverne Kanorelim vous propose ses services pour créer une expérience personnalisée et inoubliable.</p>
                    <p>Que ce soit pour un anniversaire, un mariage, une fête d'entreprise ou toute autre célébration, nous mettons à votre disposition :</p>
                    <ul class="custom-events-list">
                        <li><i class="fas fa-utensils"></i> <span>Un service de restauration médiévale avec menus personnalisés</span></li>
                        <li><i class="fas fa-music"></i> <span>Des animations d'époque (musique, jongleurs, conteurs, combats)</span></li>
                        <li><i class="fas fa-theater-masks"></i> <span>Des costumes et accessoires pour vos invités</span></li>
                        <li><i class="fas fa-scroll"></i> <span>Des invitations et décorations sur mesure</span></li>
                    </ul>
                    <a href="contact.php" class="cta-button">Demander un devis</a>
                </div>
                <div class="custom-events-images">
                    <div class="image-grid">
                        <img src="/api/placeholder/300/200" alt="Banquet privé">
                        <img src="/api/placeholder/300/200" alt="Musiciens médiévaux">
                        <img src="/api/placeholder/300/200" alt="Décorations médiévales">
                        <img src="/api/placeholder/300/200" alt="Costumes médiévaux">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section de réservation -->
    <section id="reservation" class="event-reservation">
        <div class="container">
            <div class="section-title">
                <h2>Réserver pour un Événement</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="reservation-content">
                <div class="reservation-text">
                    <p>Pour participer à l'un de nos événements, nous vous recommandons de réserver à l'avance car les places sont limitées.</p>
                    <p>Vous pouvez effectuer votre réservation en remplissant le formulaire ci-contre, en nous appelant au +33 (0)1 23 45 67 89 ou en nous rendant visite directement à la taverne.</p>
                    <p>Un acompte de 30% sera demandé pour confirmer votre réservation.</p>
                </div>
                <div class="reservation-form-container">
                    <?php if (isset($reservation_result)): ?>
                        <div class="message message-<?php echo $reservation_result['success'] ? 'success' : 'error'; ?>">
                            <?php echo $reservation_result['message']; ?>
                        </div>
                    <?php endif; ?>
                    <form class="reservation-form" id="event-reservation-form" method="post" action="#reservation">
                        <div class="form-group">
                            <label for="event-select">Choisir un événement</label>
                            <select id="event-select" name="event" required>
                                <option value="">-- Sélectionner un événement --</option>
                                <?php foreach ($evenements_a_venir as $event): ?>
                                    <option value="<?php echo htmlspecialchars($event['titre']); ?>">
                                        <?php echo htmlspecialchars($event['titre']); ?> (<?php echo date('d/m/Y', strtotime($event['date'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Votre nom</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Votre email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Téléphone</label>
                                <input type="tel" id="phone" name="phone" required>
                            </div>
                            <div class="form-group">
                                <label for="guests">Nombre de personnes</label>
                                <input type="number" id="guests" name="guests" min="1" max="20" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="message">Message spécial</label>
                            <textarea id="message" name="message" rows="3"></textarea>
                        </div>
                        <div class="form-submit">
                            <button type="submit" name="event_reservation_submit" class="cta-button">Réserver</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Styles spécifiques à la page événements */
.page-banner {
    height: 40vh;
    min-height: 300px;
    background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/api/placeholder/1600/600');
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: var(--color-light);
    margin-bottom: 60px;
}

.banner-content h1 {
    font-size: 3.5rem;
    margin-bottom: 10px;
    text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.8);
}

.banner-content p {
    font-size: 1.5rem;
    font-family: var(--font-script);
    max-width: 700px;
    margin: 0 auto;
}

.events-intro {
    padding: 40px 0 80px;
}

.intro-content {
    display: flex;
    align-items: center;
    gap: 50px;
    max-width: 1100px;
    margin: 0 auto;
}

.intro-image {
    flex: 1;
    box-shadow: var(--shadow-hard);
    border: 8px solid #fff;
    position: relative;
}

.intro-image img {
    width: 100%;
    height: auto;
    display: block;
}

.intro-text {
    flex: 1;
}

.intro-text p {
    margin-bottom: 15px;
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
}

.upcoming-events {
    padding: 80px 0;
    background-color: #f9f6f0;
}

.events-grid {
    display: flex;
    flex-direction: column;
    gap: 40px;
    max-width: 1100px;
    margin: 0 auto;
}

.event-card-large {
    display: flex;
    background-color: #fff;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
}

.event-card-large:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hard);
}

.event-image {
    width: 40%;
    position: relative;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-medium);
}

.event-card-large:hover .event-image img {
    transform: scale(1.1);
}

.event-date-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background-color: var(--color-primary);
    color: #fff;
    padding: 10px;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
}

.event-date-badge .day {
    font-family: var(--font-medieval);
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
}

.event-date-badge .month {
    font-family: var(--font-medieval);
    font-size: 1rem;
    margin-top: 5px;
}

.event-details {
    flex: 1;
    padding: 30px;
}

.event-details h3 {
    font-size: 1.8rem;
    color: var(--color-primary);
    margin-bottom: 15px;
}

.event-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    color: #666;
}

.event-meta span {
    display: flex;
    align-items: center;
}

.event-meta i {
    margin-right: 8px;
    color: var(--color-accent);
}

.event-details p {
    color: #555;
    margin-bottom: 25px;
    line-height: 1.7;
}

.no-events {
    text-align: center;
    padding: 50px 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.no-events p {
    font-size: 1.2rem;
    color: #666;
}

.newsletter {
    padding: 80px 0;
    background-color: var(--color-dark);
    color: var(--color-light);
}

.newsletter-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.newsletter-text h2 {
    color: var(--color-gold);
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.newsletter-text p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.newsletter-form {
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-form .form-row {
    display: flex;
    margin-bottom: 15px;
}

.newsletter-form input[type="email"] {
    flex: 1;
    padding: 15px;
    border: none;
    border-radius: 3px 0 0 3px;
    font-size: 1rem;
}

.newsletter-form .cta-button {
    border-radius: 0 3px 3px 0;
}

.form-consent {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 0.9rem;
    color: #ccc;
}

.past-events {
    padding: 80px 0;
    background-color: #efe7d5;
}

.past-events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1100px;
    margin: 0 auto;
}

.past-event-card {
    background-color: #fff;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
}

.past-event-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hard);
}

.past-event-image {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.past-event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-medium);
    filter: grayscale(30%);
}

.event-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-overlay span {
    background-color: rgba(0, 0, 0, 0.7);
    color: #fff;
    padding: 5px 15px;
    border-radius: 3px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.past-event-details {
    padding: 20px;
}

.past-event-details h3 {
    font-size: 1.3rem;
    color: var(--color-primary);
    margin-bottom: 10px;
}

.past-event-details .event-meta {
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.past-event-details p {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
}

.custom-events {
    padding: 80px 0;
    background-color: #f9f6f0;
}

.custom-events-content {
    display: flex;
    align-items: center;
    gap: 50px;
    max-width: 1100px;
    margin: 0 auto;
}

.custom-events-text {
    flex: 1;
}

.custom-events-text p {
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
}

.custom-events-list {
    list-style: none;
    margin-bottom: 30px;
}

.custom-events-list li {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
}

.custom-events-list i {
    color: var(--color-primary);
    font-size: 1.2rem;
    margin-right: 15px;
    width: 25px;
}

.custom-events-images {
    flex: 1;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.image-grid img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
}

.image-grid img:hover {
    transform: scale(1.05);
    box-shadow: var(--shadow-hard);
}

.event-reservation {
    padding: 80px 0;
    background-color: #2C1B0E;
    color: var(--color-light);
}

.event-reservation .section-title h2 {
    color: var(--color-gold);
}

.reservation-content {
    display: flex;
    gap: 50px;
    max-width: 1100px;
    margin: 0 auto;
}

.reservation-text {
    flex: 1;
    padding-top: 20px;
}

.reservation-text p {
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.7;
}

.reservation-form-container {
    flex: 1;
    background-color: #fff;
    padding: 30px;
    border-radius: 5px;
    box-shadow: var(--shadow-hard);
}

.event-reservation .form-group {
    margin-bottom: 20px;
}

.event-reservation label {
    color: var(--color-primary);
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
}

.event-reservation input,
.event-reservation select,
.event-reservation textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: var(--font-body);
    font-size: 1rem;
    color: #333;
}

.event-reservation input:focus,
.event-reservation select:focus,
.event-reservation textarea:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.1);
}

.message {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

.message-success {
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
}

.message-error {
    background-color: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
}

@media screen and (max-width: 992px) {
    .intro-content,
    .custom-events-content,
    .reservation-content {
        flex-direction: column;
    }
    
    .event-card-large {
        flex-direction: column;
    }
    
    .event-image {
        width: 100%;
        height: 250px;
    }
    
    .image-grid {
        margin-top: 30px;
    }
}

@media screen and (max-width: 768px) {
    .banner-content h1 {
        font-size: 2.5rem;
    }
    
    .image-grid {
        grid-template-columns: 1fr;
    }
    
    .event-meta {
        flex-direction: column;
        gap: 5px;
    }
    
    .newsletter-form .form-row {
        flex-direction: column;
    }
    
    .newsletter-form input[type="email"] {
        border-radius: 3px;
        margin-bottom: 10px;
    }
    
    .newsletter-form .cta-button {
        border-radius: 3px;
    }
}

@media screen and (max-width: 480px) {
    .banner-content h1 {
        font-size: 2rem;
    }
    
    .past-events-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>