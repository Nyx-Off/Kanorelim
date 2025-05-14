<?php
// contact.php - Page de contact
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Traitement du formulaire si soumis
$contact_result = handleContactForm();

// Inclure l'en-tête
include 'includes/header.php';
?>

<main>
    <!-- Bannière de la page -->
    <div class="page-banner">
        <div class="banner-content">
            <h1>Contact</h1>
            <p>Entrons en communication par messager royal</p>
        </div>
    </div>

    <!-- Section Contact -->
    <section class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Nous Contacter</h2>
                <div class="medieval-divider"></div>
            </div>

            <div class="contact-content">
                <div class="contact-info">
                    <div class="info-block">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-text">
                            <h3>Adresse</h3>
                            <p>12 Rue des Templiers<br>Cité Médiévale<br>95300 Pontoise<br>France</p>
                        </div>
                    </div>
                    <div class="info-block">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-text">
                            <h3>Téléphone</h3>
                            <p>+33 (0)1 23 45 67 89</p>
                            <p class="note">Disponible tous les jours de 10h à 22h</p>
                        </div>
                    </div>
                    <div class="info-block">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-text">
                            <h3>Email</h3>
                            <p><a href="mailto:contact@kanorelim.fr">contact@kanorelim.fr</a></p>
                            <p class="note">Nous répondons sous 24 heures</p>
                        </div>
                    </div>
                    <div class="info-block">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-text">
                            <h3>Horaires</h3>
                            <ul class="hours-list">
                                <?php foreach ($horaires as $jour => $heures): ?>
                                <li><span><?= $jour ?>:</span> <?= $heures ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="social-block">
                        <h3>Suivez-nous</h3>
                        <div class="social-links">
                            <?php foreach ($reseaux_sociaux as $reseau => $url): ?>
                            <a href="<?= $url ?>" class="social-icon" target="_blank"><i class="fab fa-<?= $reseau ?>"></i></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="contact-form-container">
                    <h3>Envoyez-nous un message</h3>
                    <?php if (isset($contact_result)): ?>
                        <div class="message message-<?php echo $contact_result['success'] ? 'success' : 'error'; ?>">
                            <?php echo $contact_result['message']; ?>
                        </div>
                    <?php endif; ?>
                    <form class="contact-form" method="post" action="#contact-form">
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
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="form-submit">
                            <button type="submit" name="contact_submit" class="cta-button">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Carte -->
    <section class="map-section">
        <div class="section-title">
            <h2>Nous Trouver</h2>
            <div class="medieval-divider"></div>
        </div>
        <div id="contact-map"></div>
        <div class="container">
            <div class="directions">
                <h3>Comment nous rejoindre</h3>
                <div class="directions-content">
                    <div class="direction-block">
                        <div class="direction-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="direction-text">
                            <h4>En voiture</h4>
                            <p>Depuis Paris, prenez l'A15 direction Cergy-Pontoise, sortie Pontoise Centre. Suivez les indications pour la Cité Médiévale.</p>
                            <p>Parking public à 100m de la taverne.</p>
                        </div>
                    </div>
                    <div class="direction-block">
                        <div class="direction-icon">
                            <i class="fas fa-train"></i>
                        </div>
                        <div class="direction-text">
                            <h4>En train</h4>
                            <p>Gare de Pontoise à 10 minutes à pied. Trains directs depuis Paris Gare Saint-Lazare et Paris Gare du Nord.</p>
                        </div>
                    </div>
                    <div class="direction-block">
                        <div class="direction-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <div class="direction-text">
                            <h4>En bus</h4>
                            <p>Lignes 30, 34 et 95, arrêt "Cité Médiévale".</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section FAQ -->
    <section class="faq-section">
        <div class="container">
            <div class="section-title">
                <h2>Questions Fréquentes</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="faq-content">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Faut-il réserver à l'avance ?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Pour les soirées du vendredi et samedi, ainsi que pour les événements spéciaux, nous recommandons vivement de réserver à l'avance. Pour les autres jours, ce n'est pas obligatoire mais conseillé si vous êtes un groupe de plus de 6 personnes.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Proposez-vous des options végétariennes ?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Oui, nous proposons plusieurs plats végétariens à notre carte, marqués par un symbole de feuille. Nous pouvons également adapter certains plats sur demande.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Peut-on venir avec des enfants ?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Les familles sont les bienvenues à la Taverne Kanorelim ! Nous proposons un menu spécial pour les petits chevaliers et princesses, ainsi que des chaises hautes pour les tout-petits. Lors des soirées thématiques après 20h, l'ambiance peut être plus adulte.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Peut-on privatiser la taverne pour un événement ?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Oui, il est possible de privatiser la taverne ou notre salle du donjon pour des événements privés comme des anniversaires, mariages médiévaux ou fêtes d'entreprise. Contactez-nous pour un devis personnalisé.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Proposez-vous des spectacles ou animations ?</h3>
                        <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                    </div>
                    <div class="faq-answer">
                        <p>Oui, nous organisons régulièrement des soirées thématiques avec des troubadours, jongleurs, conteurs ou combats d'épée. Consultez notre page Événements pour connaître le programme.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Styles spécifiques à la page contact */
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

.contact-section {
    padding: 40px 0 80px;
}

.contact-content {
    display: flex;
    gap: 50px;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.info-block {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
}

.info-block:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hard);
}

.info-icon {
    width: 50px;
    height: 50px;
    background-color: var(--color-primary);
    color: var(--color-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.info-text {
    flex-grow: 1;
}

.info-text h3 {
    color: var(--color-primary);
    font-size: 1.3rem;
    margin-bottom: 10px;
}

.info-text p {
    color: #555;
    margin-bottom: 5px;
}

.info-text .note {
    font-size: 0.9rem;
    color: #777;
    font-style: italic;
}

.info-text .hours-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.info-text .hours-list li {
    margin-bottom: 5px;
    display: flex;
    color: #555;
}

.info-text .hours-list li span {
    font-weight: 600;
    color: var(--color-primary);
    min-width: 140px;
}

.social-block {
    text-align: center;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.social-block h3 {
    color: var(--color-primary);
    font-size: 1.3rem;
    margin-bottom: 15px;
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.social-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    background-color: var(--color-primary);
    color: var(--color-light);
    border-radius: 50%;
    font-size: 1.2rem;
    transition: var(--transition-medium);
}

.social-icon:hover {
    background-color: var(--color-accent);
    transform: translateY(-5px);
}

.contact-form-container {
    flex: 1;
    background-color: #fff;
    padding: 30px;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.contact-form-container h3 {
    color: var(--color-primary);
    font-size: 1.5rem;
    margin-bottom: 20px;
    text-align: center;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    color: var(--color-primary);
    font-weight: 600;
}

.form-group input,
.form-group textarea {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: var(--font-body);
    font-size: 1rem;
    transition: var(--transition-medium);
}

.form-group input:focus,
.form-group textarea:focus {
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

.map-section {
    padding-bottom: 80px;
}

#contact-map {
    height: 500px;
    width: 100%;
    margin-bottom: 50px;
    box-shadow: var(--shadow-soft);
}

.directions {
    max-width: 900px;
    margin: 0 auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.directions h3 {
    color: var(--color-primary);
    font-size: 1.8rem;
    margin-bottom: 30px;
    text-align: center;
}

.directions-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.direction-block {
    flex: 1;
    min-width: 250px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.direction-icon {
    width: 40px;
    height: 40px;
    background-color: var(--color-primary);
    color: var(--color-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.direction-text h4 {
    color: var(--color-primary);
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.direction-text p {
    color: #555;
    margin-bottom: 10px;
    line-height: 1.6;
}

.faq-section {
    padding: 80px 0;
    background-color: #f9f6f0;
}

.faq-content {
    max-width: 900px;
    margin: 0 auto;
}

.faq-item {
    margin-bottom: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
}

.faq-question {
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: var(--transition-medium);
}

.faq-question:hover {
    background-color: #f5f5f5;
}

.faq-question h3 {
    margin: 0;
    font-size: 1.2rem;
    color: var(--color-primary);
}

.faq-toggle {
    width: 30px;
    height: 30px;
    background-color: var(--color-primary);
    color: var(--color-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-medium);
}

.faq-item.active .faq-toggle {
    transform: rotate(45deg);
    background-color: var(--color-accent);
}

.faq-answer {
    padding: 0 20px;
    max-height: 0;
    overflow: hidden;
    transition: var(--transition-medium);
}

.faq-item.active .faq-answer {
    padding: 0 20px 20px;
    max-height: 1000px;
}

.faq-answer p {
    color: #555;
    line-height: 1.7;
}

@media screen and (max-width: 992px) {
    .contact-content {
        flex-direction: column;
    }
    
    .directions-content {
        flex-direction: column;
    }
}

@media screen and (max-width: 768px) {
    .banner-content h1 {
        font-size: 2.5rem;
    }
    
    .form-row {
        flex-direction: column;
    }
    
    #contact-map {
        height: 350px;
    }
}

@media screen and (max-width: 480px) {
    .banner-content h1 {
        font-size: 2rem;
    }
    
    .info-block {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .direction-block {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>

<script>
    // Script pour la carte et les FAQ
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de la carte
        initializeMap();
        
        // Gestion des FAQ
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Fermer tous les autres items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle l'état de l'item actuel
                item.classList.toggle('active');
            });
        });
    });
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>