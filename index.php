<?php
// index.php - Page d'accueil
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Récupérer les événements à venir
$upcoming_events = getUpcomingEvents(3);

// Traitement du formulaire de réservation si soumis
$reservation_result = handleReservationForm();

// Inclure l'en-tête
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <div class="hero-content">
            <h2>Bienvenue en l'an de grâce</h2>
            <div class="hero-title">Taverne Kanorelim</div>
            <p>Une expérience culinaire et festive médiévale authentique</p>
            <a href="#reservation" class="cta-button">Réserver une table</a>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Notre Histoire</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <img src="/api/placeholder/500/350" alt="Intérieur de la taverne Kanorelim">
                </div>
                <div class="about-text">
                    <p>Fondée en l'an 1254 par le Seigneur Eodred de Kanorelim, notre taverne est l'une des plus anciennes du royaume.</p>
                    <p>Située au cœur du vieux quartier, nos murs de pierre ancestraux ont été témoins d'innombrables festins, complots et célébrations à travers les âges.</p>
                    <p>Aujourd'hui, nous perpétuons la tradition en vous offrant une expérience médiévale authentique, des mets préparés selon les recettes d'antan et une ambiance digne des grands banquets seigneuriaux.</p>
                    <a href="#" class="text-button">En savoir plus <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="specialties">
        <div class="container">
            <div class="section-title">
                <h2>Nos Spécialités</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="specialties-grid">
                <div class="specialty-card">
                    <div class="specialty-image">
                        <img src="/api/placeholder/300/200" alt="Hydromel Royal">
                    </div>
                    <h3>Hydromel Royal</h3>
                    <p>Notre hydromel artisanal vieilli en fût de chêne, préparé selon les recettes secrètes des moines brasseurs.</p>
                </div>
                <div class="specialty-card">
                    <div class="specialty-image">
                        <img src="/api/placeholder/300/200" alt="Cochon rôti">
                    </div>
                    <h3>Cochon Rôti</h3>
                    <p>Cuit lentement sur flamme vive et arrosé d'épices orientales, notre cochon rôti est le festin des rois.</p>
                </div>
                <div class="specialty-card">
                    <div class="specialty-image">
                        <img src="/api/placeholder/300/200" alt="Pain de campagne">
                    </div>
                    <h3>Pain de Campagne</h3>
                    <p>Cuit au feu de bois dans notre four séculaire, accompagné de fromages fermiers et de miel sauvage.</p>
                </div>
            </div>
            <div class="center-button">
                <a href="menu.php" class="cta-button">Voir le menu complet</a>
            </div>
        </div>
    </section>

    <section class="events">
        <div class="container">
            <div class="section-title">
                <h2>Événements à venir</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="event-list">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <h3><?php echo $event['titre']; ?></h3>
                                <p><?php echo substr($event['description'], 0, 120) . '...'; ?></p>
                                <a href="evenements.php" class="text-button">Plus d'infos <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events">Aucun événement à venir pour le moment. Consultez régulièrement cette page pour découvrir nos prochains événements.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="reservation" class="reservation">
        <div class="container">
            <div class="section-title">
                <h2>Réservez Votre Table</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="reservation-form-container">
                <?php if (isset($reservation_result)): ?>
                    <div class="message message-<?php echo $reservation_result['success'] ? 'success' : 'error'; ?>">
                        <?php echo $reservation_result['message']; ?>
                    </div>
                <?php endif; ?>
                <form class="reservation-form" id="reservation-form" method="post" action="#reservation">
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
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="time">Heure</label>
                            <input type="time" id="time" name="time" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guests">Nombre de convives</label>
                            <input type="number" id="guests" name="guests" min="1" max="20" required>
                        </div>
                        <div class="form-group">
                            <label for="occasion">Occasion</label>
                            <select id="occasion" name="occasion">
                                <option value="regular">Repas régulier</option>
                                <option value="birthday">Anniversaire</option>
                                <option value="special">Occasion spéciale</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message">Message spécial</label>
                        <textarea id="message" name="message" rows="3"></textarea>
                    </div>
                    <div class="form-submit">
                        <button type="submit" name="reservation_submit" class="cta-button">Réserver</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Paroles de Convives</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="testimonial-slider" id="testimonial-slider">
                <div class="testimonial-slide active">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>La meilleure expérience médiévale que j'ai vécue ! L'hydromel est divin et l'ambiance vous transporte vraiment dans une autre époque.</p>
                    <div class="testimonial-author">
                        <span class="name">Guillaume de Montfort</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>Les mets sont savoureux et généreux, le service est impeccable. On se croirait réellement au Moyen Âge mais avec le confort moderne !</p>
                    <div class="testimonial-author">
                        <span class="name">Aliénor d'Aquitaine</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slide">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>J'ai célébré mon anniversaire ici et ce fut mémorable. Les musiciens, la nourriture, l'ambiance... tout était parfait !</p>
                    <div class="testimonial-author">
                        <span class="name">Roland de Roncevaux</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-controls">
                <button class="prev-testimonial" id="prev-testimonial"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots">
                    <span class="dot active" data-slide="0"></span>
                    <span class="dot" data-slide="1"></span>
                    <span class="dot" data-slide="2"></span>
                </div>
                <button class="next-testimonial" id="next-testimonial"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>
</main>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>