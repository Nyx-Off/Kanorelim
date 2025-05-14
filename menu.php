<?php
// menu.php - Page du menu
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Inclure l'en-tête
include 'includes/header.php';
?>

<main>
    <!-- Bannière de la page -->
    <div class="page-banner">
        <div class="banner-content">
            <h1>Notre Menu</h1>
            <p>Des saveurs d'antan pour un festin médiéval</p>
        </div>
    </div>

    <section class="menu-section">
        <div class="container">
            <div class="menu-intro">
                <div class="section-title">
                    <h2>Savourez l'Authenticité</h2>
                    <div class="medieval-divider"></div>
                </div>
                <p class="intro-text">Nos mets et boissons sont préparés selon des recettes ancestrales, avec des ingrédients locaux et de saison. Plongez dans une expérience culinaire authentique qui vous transportera à l'époque des grands banquets médiévaux.</p>
                <div class="legend">
                    <span class="vegetarian-label"><i class="fas fa-leaf"></i> Plat végétarien</span>
                </div>
            </div>

            <div class="menu-container">
                <!-- Menu généré dynamiquement -->
                <?php echo generateMenuHTML($categories_menu); ?>
            </div>

            <div class="menu-notes">
                <h3>Notes sur notre cuisine</h3>
                <p>Tous nos plats sont préparés sur place avec des ingrédients frais et locaux.</p>
                <p>Notre pain est cuit quotidiennement dans notre four à bois traditionnel.</p>
                <p>Nous pouvons adapter certains plats pour les régimes spéciaux, n'hésitez pas à nous consulter.</p>
            </div>

            <div class="center-button">
                <a href="#reservation" class="cta-button">Réserver une table</a>
            </div>
        </div>
    </section>

    <!-- Section spéciale du Chef -->
    <section class="chef-special">
        <div class="container">
            <div class="section-title">
                <h2>Le Festin du Seigneur</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="special-content">
                <div class="special-image">
                    <img src="/api/placeholder/600/400" alt="Festin du Seigneur">
                </div>
                <div class="special-text">
                    <h3>Notre menu dégustation</h3>
                    <p>Pour les grandes occasions, découvrez notre menu dégustation qui vous fera voyager à travers les saveurs du Moyen Âge.</p>
                    <p>Ce festin comprend six services accompagnés de nos meilleures boissons, servis dans une ambiance digne des plus grands banquets seigneuriaux.</p>
                    <p>Idéal pour les célébrations en groupe, ce menu est disponible sur réservation uniquement (minimum 4 personnes).</p>
                    <div class="special-price">
                        <span>60€ par personne</span>
                    </div>
                    <a href="contact.php" class="text-button">Nous contacter pour plus d'informations <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Témoignages sur la nourriture -->
    <section class="food-testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Ce qu'en disent nos convives</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>"Le civet de cerf était succulent, tendre à souhait et parfumé aux herbes forestières. Un vrai délice qui m'a transporté au temps des chevaliers !"</p>
                    <div class="testimonial-author">
                        <span class="name">Geoffroy de Charny</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>"L'hydromel est divinement préparé, avec des notes de miel subtilement équilibrées. La meilleure boisson médiévale que j'ai goûtée !"</p>
                    <div class="testimonial-author">
                        <span class="name">Blanche de Castille</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="quote"><i class="fas fa-quote-left"></i></div>
                    <p>"La tourte rustique est généreuse et savoureuse, un régal même pour les non-végétariens. Les légumes et champignons sont parfaitement assaisonnés."</p>
                    <div class="testimonial-author">
                        <span class="name">Hildegarde de Bingen</span>
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
        </div>
    </section>

    <!-- Section de banquet privé -->
    <section class="private-dining">
        <div class="container">
            <div class="section-title">
                <h2>Banquets Privés</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="private-dining-content">
                <div class="private-dining-text">
                    <p>La Taverne Kanorelim peut accueillir vos événements privés : anniversaires, mariages médiévaux, fêtes d'entreprise ou toute autre célébration spéciale.</p>
                    <p>Notre salle du donjon peut accueillir jusqu'à 50 convives pour un banquet médiéval inoubliable.</p>
                    <p>Nous proposons des menus personnalisés, des animations d'époque (musique, jongleurs, conteurs) et une décoration thématique.</p>
                    <a href="contact.php" class="cta-button">Demander un devis</a>
                </div>
                <div class="private-dining-image">
                    <img src="/api/placeholder/500/350" alt="Salle de banquet privé">
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Styles spécifiques à la page menu */
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

.menu-section {
    padding: 40px 0 80px;
}

.menu-intro {
    max-width: 800px;
    margin: 0 auto 50px;
    text-align: center;
}

.intro-text {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 20px;
}

.legend {
    margin-top: 20px;
    font-size: 0.9rem;
}

.menu-container {
    max-width: 1000px;
    margin: 0 auto;
}

.menu-category {
    margin-bottom: 60px;
}

.menu-category h2 {
    color: var(--color-primary);
    text-align: center;
    font-size: 2.2rem;
}

.menu-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.menu-item {
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
    position: relative;
}

.menu-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hard);
}

.menu-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.menu-item h3 {
    font-size: 1.5rem;
    color: var(--color-primary);
    margin: 0;
}

.menu-item-price {
    font-family: var(--font-medieval);
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--color-accent);
}

.menu-item-description {
    color: #666;
    margin-bottom: 5px;
}

.vegetarian-label {
    display: inline-block;
    color: #4a8d47;
    font-size: 0.9rem;
    margin-top: 10px;
}

.vegetarian-label i {
    margin-right: 5px;
}

.menu-item.vegetarian {
    border-left: 3px solid #4a8d47;
}

.menu-notes {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background-color: #f9f6f0;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.menu-notes h3 {
    color: var(--color-primary);
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.menu-notes p {
    color: #666;
    margin-bottom: 10px;
}

.chef-special {
    padding: 80px 0;
    background-color: #2C1B0E;
    color: var(--color-light);
}

.special-content {
    display: flex;
    align-items: center;
    gap: 40px;
    max-width: 1100px;
    margin: 0 auto;
}

.special-image {
    flex: 1;
    border: 8px solid var(--color-secondary);
    box-shadow: var(--shadow-hard);
}

.special-image img {
    width: 100%;
    height: auto;
    display: block;
}

.special-text {
    flex: 1;
}

.special-text h3 {
    color: var(--color-gold);
    font-size: 2rem;
    margin-bottom: 20px;
}

.special-text p {
    margin-bottom: 15px;
    font-size: 1.1rem;
    line-height: 1.8;
}

.special-price {
    margin: 30px 0;
    font-family: var(--font-medieval);
    font-size: 1.8rem;
    color: var(--color-gold);
}

.chef-special .text-button {
    color: var(--color-secondary);
}

.chef-special .text-button:hover {
    color: var(--color-gold);
}

.food-testimonials {
    padding: 80px 0;
    background-color: #f9f6f0;
}

.testimonial-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.testimonial-card {
    background-color: #fff;
    padding: 30px;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-medium);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hard);
}

.testimonial-card .quote {
    color: var(--color-primary);
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.testimonial-card p {
    font-style: italic;
    color: #555;
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.7;
}

.private-dining {
    padding: 80px 0;
    background-color: #efe7d5;
}

.private-dining-content {
    display: flex;
    align-items: center;
    gap: 40px;
    max-width: 1100px;
    margin: 0 auto;
}

.private-dining-text {
    flex: 1;
}

.private-dining-text p {
    margin-bottom: 15px;
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
}

.private-dining-text .cta-button {
    margin-top: 20px;
}

.private-dining-image {
    flex: 1;
    box-shadow: var(--shadow-hard);
    border: 8px solid #fff;
}

.private-dining-image img {
    width: 100%;
    height: auto;
    display: block;
}

@media screen and (max-width: 992px) {
    .menu-items {
        grid-template-columns: 1fr;
    }
    
    .special-content,
    .private-dining-content {
        flex-direction: column;
    }
    
    .special-image,
    .special-text,
    .private-dining-text,
    .private-dining-image {
        flex: none;
        width: 100%;
    }
    
    .special-image {
        margin-bottom: 30px;
    }
    
    .private-dining-text {
        margin-bottom: 30px;
    }
}

@media screen and (max-width: 768px) {
    .banner-content h1 {
        font-size: 2.5rem;
    }
    
    .banner-content p {
        font-size: 1.2rem;
    }
    
    .testimonial-grid {
        grid-template-columns: 1fr;
    }
}

@media screen and (max-width: 480px) {
    .banner-content h1 {
        font-size: 2rem;
    }
    
    .menu-item-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .menu-item-price {
        margin-top: 5px;
    }
}
</style>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>