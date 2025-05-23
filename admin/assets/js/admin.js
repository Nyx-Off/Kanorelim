// admin.js - Script principal pour l'administration Kanorelim

/**
 * Fonctions communes à toutes les pages
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeUserDropdown();
    initDeleteConfirmations();
    initImagePreviews();
    initRichTextEditors();
    initAlertDismiss();
});

/**
 * Initialisation de la navigation (sidebar)
 */
function initializeNavigation() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const adminWrapper = document.querySelector('.admin-wrapper');
    const sidebarClose = document.getElementById('sidebar-close');
    
    if (sidebarToggle && adminWrapper) {
        sidebarToggle.addEventListener('click', function() {
            adminWrapper.classList.toggle('sidebar-collapsed');
        });
    }
    
    if (sidebarClose && adminWrapper) {
        sidebarClose.addEventListener('click', function() {
            adminWrapper.classList.add('sidebar-collapsed');
        });
    }
}

/**
 * Initialisation du dropdown utilisateur
 */
function initializeUserDropdown() {
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        // Toggle au clic sur le bouton
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Fermer le menu au clic en dehors
        document.addEventListener('click', function(e) {
            if (!userDropdownToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
        
        // Fermer le menu au clic sur un lien
        const dropdownLinks = userDropdownMenu.querySelectorAll('a');
        dropdownLinks.forEach(link => {
            link.addEventListener('click', function() {
                userDropdownMenu.classList.remove('show');
            });
        });
    }
}

/**
 * Initialisation des confirmations de suppression
 */
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialisation des previews d'images
 */
function initImagePreviews() {
    const imageInputs = document.querySelectorAll('.image-upload');
    
    imageInputs.forEach(input => {
        const previewContainer = document.querySelector(input.getAttribute('data-preview'));
        
        if (previewContainer) {
            input.addEventListener('change', function() {
                previewContainer.innerHTML = '';
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        img.style.maxWidth = '200px';
                        img.style.marginTop = '10px';
                        img.style.borderRadius = '5px';
                        img.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
}

/**
 * Initialisation des éditeurs de texte enrichi
 */
function initRichTextEditors() {
    const textareas = document.querySelectorAll('.rich-editor');
    
    if (textareas.length > 0 && typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '.rich-editor',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }'
        });
    }
}

/**
 * Initialisation des messages d'alerte auto-fermants
 */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert[data-dismiss]');
    
    alerts.forEach(alert => {
        const timeout = parseInt(alert.getAttribute('data-dismiss')) || 5000;
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, timeout);
    });
}

/**
 * Filtrage des tableaux
 * 
 * @param {string} inputId L'ID de l'input de recherche
 * @param {string} tableId L'ID du tableau à filtrer
 */
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

/**
 * Tri des tableaux
 * 
 * @param {HTMLTableElement} table Le tableau à trier
 * @param {number} column L'index de la colonne à trier
 * @param {boolean} asc Ordre ascendant ou descendant
 */
function sortTable(table, column, asc = true) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Trier les lignes
    const sortedRows = rows.sort((a, b) => {
        const aCol = a.querySelectorAll('td')[column].textContent.trim();
        const bCol = b.querySelectorAll('td')[column].textContent.trim();
        
        return asc ? aCol.localeCompare(bCol) : bCol.localeCompare(aCol);
    });
    
    // Supprimer les lignes existantes
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }
    
    // Ajouter les lignes triées
    tbody.append(...sortedRows);
    
    // Mettre à jour les classes des en-têtes
    table.querySelectorAll('th').forEach((th, i) => {
        th.classList.remove('sort-asc', 'sort-desc');
        if (i === column) {
            th.classList.add(asc ? 'sort-asc' : 'sort-desc');
        }
    });
}

/**
 * Initialisation du tri des tableaux
 * 
 * @param {string} tableId L'ID du tableau
 */
function initSortableTable(tableId) {
    const table = document.getElementById(tableId);
    
    if (table) {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const column = Array.from(this.parentNode.children).indexOf(this);
                const currentIsAsc = this.classList.contains('sort-asc');
                
                sortTable(table, column, !currentIsAsc);
            });
        });
    }
}

/**
 * Sélection/Désélection de toutes les cases à cocher
 * 
 * @param {string} toggleId L'ID de la case à cocher principale
 * @param {string} checkboxClass La classe des cases à cocher à contrôler
 */
function initCheckboxToggle(toggleId, checkboxClass) {
    const toggle = document.getElementById(toggleId);
    const checkboxes = document.querySelectorAll('.' + checkboxClass);
    
    if (toggle && checkboxes.length > 0) {
        toggle.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                
                toggle.checked = allChecked;
                toggle.indeterminate = someChecked && !allChecked;
            });
        });
    }
}

/**
 * Fonction pour prévisualiser une image avant upload
 * 
 * @param {HTMLInputElement} input L'input de type file
 * @param {string} previewId L'ID de l'élément pour la prévisualisation
 */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Formatage de date pour l'affichage
 * 
 * @param {string} dateString La date à formater (format ISO)
 * @param {boolean} includeTime Inclure l'heure
 * @return {string} La date formatée
 */
function formatDate(dateString, includeTime = false) {
    const date = new Date(dateString);
    
    if (isNaN(date.getTime())) {
        return dateString;
    }
    
    const options = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return date.toLocaleDateString('fr-FR', options);
}