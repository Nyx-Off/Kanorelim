<?php
// admin/includes/footer.php - Pied de page de l'administration
if (!defined('ADMIN_INCLUDED')) {
    require_once '../config.php';
}
?>

            </main>
            <!-- Pied de page -->
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> Taverne Kanorelim - Administration</p>
            </footer>
        </div>
    </div>
    
    <script>
        // Toggle de la barre latérale
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
        });
        
        // Fermeture de la barre latérale (mobile)
        document.getElementById('sidebar-close').addEventListener('click', function() {
            document.querySelector('.admin-wrapper').classList.add('sidebar-collapsed');
        });
        
        // Toggle du menu utilisateur
        const userDropdown = document.querySelector('.user-dropdown-toggle');
        if (userDropdown) {
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                document.querySelector('.user-dropdown-menu').classList.toggle('show');
            });
        }
        
        // Fermeture du menu utilisateur au clic en dehors
        document.addEventListener('click', function() {
            const dropdownMenu = document.querySelector('.user-dropdown-menu');
            if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>