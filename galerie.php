<?php
// galerie.php - Page de la galerie
// Inclure les fichiers nécessaires
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Obtenir les catégories de la galerie depuis la base de données
$categories = getGalleryCategories();

// Filtrer les images par catégorie si un filtre est appliqué
$categorie_active = isset($_GET['categorie']) && array_key_exists($_GET['categorie'], $categories) ? $_GET['categorie'] : 'all';
$images_filtrees = getGalleryImages($categorie_active);

// Inclure l'en-tête
include 'includes/header.php';
?>

<main>
    <!-- Bannière de la page -->
    <div class="page-banner">
        <div class="banner-content">
            <h1>Galerie</h1>
            <p>Voyagez à travers le temps en images</p>
        </div>
    </div>

    <!-- Section Galerie -->
    <section class="gallery-section">
        <div class="container">
            <div class="gallery-intro">
                <div class="section-title">
                    <h2>Notre Univers Médiéval</h2>
                    <div class="medieval-divider"></div>
                </div>
                <p class="intro-text">Découvrez l'ambiance unique de la Taverne Kanorelim à travers notre collection d'images. De l'architecture rustique à nos plats traditionnels, en passant par nos événements festifs, chaque cliché vous plonge dans l'atmosphère médiévale que nous avons recréée.</p>
            </div>

            <!-- Filtres de la galerie -->
            <div class="gallery-filters">
                <ul class="filter-list">
                    <?php foreach ($categories as $key => $label): ?>
                        <li>
                            <a href="?categorie=<?php echo $key; ?>" class="filter-link <?php echo ($categorie_active === $key) ? 'active' : ''; ?>">
                                <?php echo $label; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Grille de la galerie -->
            <div class="gallery-grid">
                <?php if (!empty($images_filtrees)): ?>
                    <?php foreach ($images_filtrees as $image): ?>
                        <div class="gallery-item">
                            <div class="gallery-image">
                                <img src="<?php echo htmlspecialchars($image['src']); ?>" alt="<?php echo htmlspecialchars($image['caption']); ?>">
                                <div class="gallery-overlay">
                                    <span><i class="fas fa-search-plus"></i></span>
                                </div>
                            </div>
                            <div class="gallery-caption"><?php echo htmlspecialchars($image['caption']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-images">
                        <p>Aucune image dans cette catégorie.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Lightbox pour afficher les images en grand -->
    <div id="lightbox" class="lightbox">
        <div class="lightbox-content">
            <button id="lightbox-close" class="lightbox-close"><i class="fas fa-times"></i></button>
            <button id="lightbox-prev" class="lightbox-nav lightbox-prev"><i class="fas fa-chevron-left"></i></button>
            <img id="lightbox-image" src="" alt="">
            <button id="lightbox-next" class="lightbox-nav lightbox-next"><i class="fas fa-chevron-right"></i></button>
            <div id="lightbox-caption" class="lightbox-caption"></div>
        </div>
    </div>

    <!-- Section visite virtuelle -->
    <section class="virtual-tour">
        <div class="container">
            <div class="section-title">
                <h2>Visite Virtuelle</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="virtual-tour-content">
                <div class="virtual-tour-text">
                    <p>Explorez la Taverne Kanorelim comme si vous y étiez ! Notre visite virtuelle vous permet de découvrir tous les recoins de notre établissement, de la grande salle avec sa cheminée monumentale aux salles privées pour vos banquets.</p>
                    <p>Naviguez à travers les différentes pièces, admirez les détails d'architecture authentique et plongez dans l'atmosphère médiévale qui fait la renommée de notre taverne.</p>
                    <a href="#" class="cta-button">Lancer la visite virtuelle</a>
                </div>
                <div class="virtual-tour-preview">
                    <img src="/api/placeholder/600/400" alt="Aperçu de la visite virtuelle">
                    <div class="play-button">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section historique en images -->
    <section class="history-timeline">
        <div class="container">
            <div class="section-title">
                <h2>Notre Histoire en Images</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date">1254</div>
                    <div class="timeline-content">
                        <div class="timeline-image">
                            <img src="/api/placeholder/400/300" alt="Fondation de la taverne">
                        </div>
                        <h3>Fondation</h3>
                        <p>Fondation de la taverne par le Seigneur Eodred de Kanorelim, comme lieu de repos pour les voyageurs et commerçants.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">1415</div>
                    <div class="timeline-content">
                        <div class="timeline-image">
                            <img src="/api/placeholder/400/300" alt="Reconstruction après l'incendie">
                        </div>
                        <h3>Reconstruction</h3>
                        <p>Après le grand incendie, la taverne est reconstruite et agrandie, avec l'ajout de la grande cheminée centrale.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">1789</div>
                    <div class="timeline-content">
                        <div class="timeline-image">
                            <img src="/api/placeholder/400/300" alt="Période révolutionnaire">
                        </div>
                        <h3>Période Révolutionnaire</h3>
                        <p>La taverne devient un lieu de rencontre pour les révolutionnaires, caché des regards indiscrets.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-date">2023</div>
                    <div class="timeline-content">
                        <div class="timeline-image">
                            <img src="/api/placeholder/400/300" alt="Rénovation complète">
                        </div>
                        <h3>Renaissance</h3>
                        <p>Rénovation complète dans le respect de l'histoire, pour faire revivre l'atmosphère médiévale authentique.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section partage d'images -->
    <section class="share-photos">
        <div class="container">
            <div class="section-title">
                <h2>Partagez Vos Photos</h2>
                <div class="medieval-divider"></div>
            </div>
            <div class="share-photos-content">
                <div class="share-info">
                    <p>Vous avez visité la Taverne Kanorelim et immortalisé ce moment ? Partagez vos photos avec nous et participez à enrichir notre galerie !</p>
                    <p>Utilisez le hashtag <strong>#TaverneKanorelim</strong> sur Instagram ou envoyez-nous directement vos clichés par email.</p>
                    <div class="social-share">
                        <a href="#" class="social-button"><i class="fab fa-instagram"></i> Instagram</a>
                        <a href="mailto:photos@kanorelim.fr" class="social-button"><i class="fas fa-envelope"></i> Email</a>
                    </div>
                </div>
                <div class="share-form">
                    <h3>Envoi Direct</h3>
                    <form id="photo-upload-form">
                        <div class="form-group">
                            <label for="name">Votre nom</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Votre email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="photo">Sélectionner des photos</label>
                            <input type="file" id="photo" name="photo" accept="image/*" multiple>
                        </div>
                        <div class="form-group">
                            <label for="comment">Commentaire</label>
                            <textarea id="comment" name="comment" rows="3"></textarea>
                        </div>
                        <div class="form-submit">
                            <button type="submit" class="cta-button">Envoyer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
/* Styles spécifiques à la page galerie */
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

.gallery-section {
    padding: 40px 0 80px;
}

.gallery-intro {
    max-width: 800px;
    margin: 0 auto 40px;
    text-align: center;
}

.intro-text {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 20px;
}

.gallery-filters {
    margin-bottom: 40px;
    text-align: center;
}

.filter-list {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 15px;
    list-style: none;
}

.filter-link {
    display: inline-block;
    padding: 8px 20px;
    background-color: #f0e6d2;
    color: var(--color-primary);
    border-radius: 30px;
    font-family: var(--font-medieval);
    font-weight: 600;
    transition: var(--transition-medium);
}

.filter-link:hover,
.filter-link.active {
    background-color: var(--color-primary);
    color: var(--color-light);
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.gallery-item {
    position: relative;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: var(--shadow-soft);
    background-color: #fff;
    transition: var(--transition-medium);
    cursor: pointer;
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hard);
}

.gallery-image {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-medium);
}

.gallery-item:hover .gallery-image img {
    transform: scale(1.1);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(44, 27, 14, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition-medium);
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

.gallery-overlay span {
    color: var(--color-light);
    font-size: 2rem;
}

.gallery-caption {
    padding: 15px;
    background-color: #fff;
    color: var(--color-primary);
    font-family: var(--font-medieval);
    font-size: 1.1rem;
    text-align: center;
}

.no-images {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px 20px;
    background-color: #f9f6f0;
    border-radius: 5px;
}

.no-images p {
    font-size: 1.2rem;
    color: #666;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    padding: 30px;
}

.lightbox.active {
    display: flex;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 2100;
    transition: var(--transition-fast);
}

.lightbox-close:hover {
    color: var(--color-secondary);
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #fff;
    font-size: 2rem;
    cursor: pointer;
    z-index: 2100;
    transition: var(--transition-fast);
    padding: 20px;
}

.lightbox-nav:hover {
    color: var(--color-secondary);
}

.lightbox-prev {
    left: -80px;
}

.lightbox-next {
    right: -80px;
}

#lightbox-image {
    display: block;
    max-width: 100%;
    max-height: 80vh;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.5);
}

.lightbox-caption {
    text-align: center;
    color: #fff;
    margin-top: 20px;
    font-family: var(--font-medieval);
    font-size: 1.2rem;
}

/* Section visite virtuelle */
.virtual-tour {
    padding: 80px 0;
    background-color: #2C1B0E;
    color: var(--color-light);
}

.virtual-tour .section-title h2 {
    color: var(--color-gold);
}

.virtual-tour-content {
    display: flex;
    align-items: center;
    gap: 50px;
    max-width: 1100px;
    margin: 0 auto;
}

.virtual-tour-text {
    flex: 1;
}

.virtual-tour-text p {
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.8;
}

.virtual-tour-preview {
    flex: 1;
    position: relative;
    border: 8px solid var(--color-secondary);
    box-shadow: var(--shadow-hard);
    cursor: pointer;
}

.virtual-tour-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 80px;
    height: 80px;
    background-color: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-medium);
}

.play-button i {
    color: #fff;
    font-size: 2rem;
    margin-left: 5px;
}

.virtual-tour-preview:hover .play-button {
    background-color: var(--color-accent);
}

/* Section historique en images */
.history-timeline {
    padding: 80px 0;
    background-color: #f9f6f0;
}

.timeline {
    max-width: 1000px;
    margin: 40px auto 0;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    background-color: var(--color-primary);
}

.timeline-item {
    position: relative;
    margin-bottom: 60px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-date {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    padding: 10px 0;
    background-color: var(--color-primary);
    color: var(--color-light);
    text-align: center;
    font-family: var(--font-medieval);
    font-size: 1.5rem;
    font-weight: 700;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    z-index: 1;
}

.timeline-content {
    width: 45%;
    padding: 30px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
    margin-top: 40px;
}

.timeline-item:nth-child(odd) .timeline-content {
    margin-left: 0;
    margin-right: auto;
}

.timeline-item:nth-child(even) .timeline-content {
    margin-left: auto;
    margin-right: 0;
}

.timeline-image {
    margin-bottom: 20px;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    border-radius: 5px;
}

.timeline-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: var(--transition-medium);
}

.timeline-content:hover .timeline-image img {
    transform: scale(1.05);
}

.timeline-content h3 {
    color: var(--color-primary);
    font-size: 1.6rem;
    margin-bottom: 10px;
}

.timeline-content p {
    color: #555;
    line-height: 1.7;
}

/* Section partage de photos */
.share-photos {
    padding: 80px 0;
    background-color: #efe7d5;
}

.share-photos-content {
    display: flex;
    gap: 50px;
    max-width: 1100px;
    margin: 0 auto;
}

.share-info {
    flex: 1;
}

.share-info p {
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.7;
    color: #555;
}

.social-share {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.social-button {
    display: inline-flex;
    align-items: center;
    padding: 10px 20px;
    background-color: var(--color-primary);
    color: var(--color-light);
    border-radius: 5px;
    text-decoration: none;
    transition: var(--transition-medium);
}

.social-button:hover {
    background-color: var(--color-accent);
    color: var(--color-light);
}

.social-button i {
    margin-right: 8px;
}

.share-form {
    flex: 1;
    background-color: #fff;
    padding: 30px;
    border-radius: 5px;
    box-shadow: var(--shadow-soft);
}

.share-form h3 {
    color: var(--color-primary);
    margin-bottom: 20px;
}

.share-form .form-group {
    margin-bottom: 20px;
}

.share-form label {
    display: block;
    margin-bottom: 8px;
    color: var(--color-primary);
    font-weight: 600;
}

.share-form input,
.share-form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-family: var(--font-body);
    font-size: 1rem;
    transition: var(--transition-medium);
}

.share-form input:focus,
.share-form textarea:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.1);
}

@media screen and (max-width: 992px) {
    .virtual-tour-content,
    .share-photos-content {
        flex-direction: column;
    }
    
    .timeline::before {
        left: 30px;
    }
    
    .timeline-date {
        left: 30px;
        transform: none;
    }
    
    .timeline-content {
        width: calc(100% - 100px);
        margin-left: 100px;
        margin-right: 0;
    }
}

@media screen and (max-width: 768px) {
    .banner-content h1 {
        font-size: 2.5rem;
    }
    
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
    
    .lightbox-nav {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .lightbox-prev {
        left: 20px;
    }
    
    .lightbox-next {
        right: 20px;
    }
}

@media screen and (max-width: 480px) {
    .banner-content h1 {
        font-size: 2rem;
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .social-share {
        flex-direction: column;
    }
}
</style>

<script>
    // Script pour la galerie et la lightbox
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation de la galerie
        initializeGallery();
    });
</script>

<?php
// Inclure le pied de page
include 'includes/footer.php';
?>