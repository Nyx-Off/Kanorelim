
<?php
// footer.php - Pied de page du site
// Inclure la configuration si ce n'est pas déjà fait
if (!defined('INCLUDED_CONFIG')) {
    require_once 'config.php';
    define('INCLUDED_CONFIG', true);
}
?>
        <footer>
            <div class="footer-container">
                <div class="footer-column">
                    <div class="footer-logo">Kanorelim</div>
                    <p>Une expérience médiévale authentique au cœur de la cité.</p>
                    <div class="social-links">
                        <?php foreach ($reseaux_sociaux as $reseau => $url): ?>
                        <a href="<?= $url ?>" class="social-icon" target="_blank"><i class="fab fa-<?= $reseau ?>"></i></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Navigation</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="evenements.php">Événements</a></li>
                        <li><a href="galerie.php">Galerie</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Horaires</h3>
                    <ul class="hours-list">
                        <?php foreach ($horaires as $jour => $heures): ?>
                        <li><span><?= $jour ?>:</span> <?= $heures ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul class="contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> 12 Rue des Templiers, Cité Médiévale</li>
                        <li><i class="fas fa-phone"></i> +33 (0)1 23 45 67 89</li>
                        <li><i class="fas fa-envelope"></i> contact@kanorelim.fr</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Taverne Kanorelim. Tous droits réservés.</p>
            </div>
        </footer>
    </div>
</body>
</html>

<?php