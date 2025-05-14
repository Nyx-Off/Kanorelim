// admin.js - Script principal pour l'administration Kanorelim

/**
 * Fonctions communes à toutes les pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Toggle de la barre latérale
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
    
    // Toggle du menu utilisateur
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Fermer le menu au clic en dehors
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Initialisation des confirmations de suppression
    initDeleteConfirmations();
    
    // Initialisation des previews d'images
    initImagePreviews();
    
    // Initialisation des éditeurs de texte enrichi
    initRichTextEditors();
    
    // Initialisation des messages d'alerte auto-fermants
    initAlertDismiss();
});

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

/**
 * Active la modification en ligne d'un champ
 * 
 * @param {string} displayId ID de l'élément d'affichage
 * @param {string} editId ID de l'élément d'édition
 * @param {string} saveId ID du bouton de sauvegarde
 * @param {string} cancelId ID du bouton d'annulation
 */
function enableInlineEdit(displayId, editId, saveId, cancelId) {
    const displayElement = document.getElementById(displayId);
    const editElement = document.getElementById(editId);
    const saveButton = document.getElementById(saveId);
    const cancelButton = document.getElementById(cancelId);
    
    if (displayElement && editElement && saveButton && cancelButton) {
        const originalValue = editElement.value;
        
        // Afficher le champ d'édition et masquer l'affichage
        displayElement.style.display = 'none';
        editElement.style.display = 'block';
        saveButton.style.display = 'inline-block';
        cancelButton.style.display = 'inline-block';
        
        // Focus sur le champ d'édition
        editElement.focus();
        
        // Gérer l'annulation
        cancelButton.addEventListener('click', function() {
            editElement.value = originalValue;
            editElement.style.display = 'none';
            displayElement.style.display = 'block';
            saveButton.style.display = 'none';
            cancelButton.style.display = 'none';
        });
    }
}

/**
 * Charge un contenu via AJAX
 * 
 * @param {string} url L'URL à charger
 * @param {string} targetId L'ID de l'élément cible
 * @param {Object} params Paramètres à envoyer (facultatif)
 * @param {string} method Méthode HTTP (GET ou POST)
 */
function loadContent(url, targetId, params = null, method = 'GET') {
    const target = document.getElementById(targetId);
    
    if (!target) {
        console.error('Élément cible non trouvé:', targetId);
        return;
    }
    
    // Afficher un indicateur de chargement
    target.innerHTML = '<div class="loading">Chargement...</div>';
    
    // Préparer les paramètres
    let urlWithParams = url;
    let body = null;
    
    if (params) {
        if (method === 'GET') {
            const queryString = new URLSearchParams(params).toString();
            urlWithParams = url + (url.includes('?') ? '&' : '?') + queryString;
        } else {
            body = JSON.stringify(params);
        }
    }
    
    // Effectuer la requête
    fetch(urlWithParams, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: method === 'POST' ? body : null
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.text();
    })
    .then(html => {
        target.innerHTML = html;
    })
    .catch(error => {
        console.error('Erreur:', error);
        target.innerHTML = '<div class="error">Une erreur est survenue lors du chargement.</div>';
    });
}

/**
 * Envoie un formulaire via AJAX
 * 
 * @param {string} formId L'ID du formulaire
 * @param {Function} successCallback Fonction à appeler en cas de succès
 * @param {Function} errorCallback Fonction à appeler en cas d'erreur
 */
function submitFormAjax(formId, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    
    if (!form) {
        console.error('Formulaire non trouvé:', formId);
        return;
    }
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Récupérer les données du formulaire
        const formData = new FormData(form);
        
        // Effectuer la requête
        fetch(form.action, {
            method: form.method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof successCallback === 'function') {
                    successCallback(data);
                }
            } else {
                if (typeof errorCallback === 'function') {
                    errorCallback(data);
                }
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            if (typeof errorCallback === 'function') {
                errorCallback({ success: false, message: 'Une erreur est survenue lors de l\'envoi du formulaire.' });
            }
        });
    });
}