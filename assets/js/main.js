// main.js - Script principal pour la Taverne Kanorelim

/**
 * Fonctions communes à toutes les pages
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeScrollAnimations();
    handleReservationForm();
    initializeTestimonials();
});

/**
 * Initialisation de la navigation responsive
 */
function initializeNavigation() {
    const navToggle = document.getElementById('nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function() {
            navLinks.classList.toggle('show');
        });

        // Fermer le menu quand on clique sur un lien
        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    navLinks.classList.remove('show');
                }
            });
        });

        // Fermer le menu quand on clique en dehors
        document.addEventListener('click', function(event) {
            const isClickInside = navToggle.contains(event.target) || navLinks.contains(event.target);
            if (!isClickInside && navLinks.classList.contains('show')) {
                navLinks.classList.remove('show');
            }
        });
    }

    // Mise en évidence du lien de navigation actif
    const currentLocation = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-links a');
    
    navItems.forEach(item => {
        if (item.getAttribute('href') === currentLocation) {
            item.classList.add('active');
        } else if (currentLocation === '/' && item.getAttribute('href') === 'index.php') {
            item.classList.add('active');
        }
    });
}

/**
 * Animations au défilement
 */
function initializeScrollAnimations() {
    const elementsToAnimate = document.querySelectorAll('.about-content, .specialties-grid, .event-list, .reservation-form-container');
    
    function revealOnScroll() {
        elementsToAnimate.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('revealed');
            }
        });
    }

    // Exécuter une fois au chargement
    revealOnScroll();
    
    // Puis à chaque défilement
    window.addEventListener('scroll', revealOnScroll);
}

/**
 * Gestion du formulaire de réservation
 */
function handleReservationForm() {
    const reservationForm = document.getElementById('reservation-form');
    
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation du formulaire
            if (!validateReservationForm(this)) {
                return;
            }
            
            // Collecte des données pour l'envoi
            const formData = new FormData(this);
            
            // Simulation d'envoi (à remplacer par un vrai envoi AJAX)
            simulateFormSubmission(formData)
                .then(response => {
                    // Affichage d'un message de succès
                    showSuccessMessage('Votre réservation a été enregistrée ! Nous vous contacterons bientôt pour confirmation.');
                    // Réinitialisation du formulaire
                    this.reset();
                })
                .catch(error => {
                    // Affichage d'un message d'erreur
                    showErrorMessage('Une erreur est survenue. Veuillez réessayer ou nous contacter par téléphone.');
                });
        });
    }
}

/**
 * Validation du formulaire de réservation
 */
function validateReservationForm(form) {
    // Validation du nom
    const nameInput = form.querySelector('#name');
    if (!nameInput.value.trim()) {
        showErrorMessage('Veuillez entrer votre nom.');
        nameInput.focus();
        return false;
    }
    
    // Validation de l'email
    const emailInput = form.querySelector('#email');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(emailInput.value)) {
        showErrorMessage('Veuillez entrer une adresse email valide.');
        emailInput.focus();
        return false;
    }
    
    // Validation de la date
    const dateInput = form.querySelector('#date');
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (!dateInput.value || selectedDate < today) {
        showErrorMessage('Veuillez choisir une date valide (aujourd\'hui ou une date future).');
        dateInput.focus();
        return false;
    }
    
    // Validation du nombre de convives
    const guestsInput = form.querySelector('#guests');
    if (guestsInput.value < 1 || guestsInput.value > 20) {
        showErrorMessage('Le nombre de convives doit être entre 1 et 20.');
        guestsInput.focus();
        return false;
    }
    
    return true;
}

/**
 * Simulation d'envoi du formulaire (à remplacer par un vrai appel AJAX)
 */
function simulateFormSubmission(formData) {
    return new Promise((resolve, reject) => {
        // Simulation d'un délai réseau
        setTimeout(() => {
            // 90% de chance de succès pour la démo
            if (Math.random() > 0.1) {
                resolve({ success: true });
            } else {
                reject(new Error('Simulation d\'erreur réseau'));
            }
        }, 1000);
    });
}

/**
 * Affichage d'un message de succès
 */
function showSuccessMessage(message) {
    showMessage(message, 'success');
}

/**
 * Affichage d'un message d'erreur
 */
function showErrorMessage(message) {
    showMessage(message, 'error');
}

/**
 * Affichage d'un message temporaire
 */
function showMessage(message, type) {
    // Suppression des messages précédents
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Création du nouveau message
    const messageElement = document.createElement('div');
    messageElement.className = `message message-${type}`;
    messageElement.textContent = message;
    
    // Insertion du message dans le DOM
    const reservationForm = document.getElementById('reservation-form');
    if (reservationForm) {
        reservationForm.parentNode.insertBefore(messageElement, reservationForm);
    } else {
        document.body.appendChild(messageElement);
    }
    
    // Suppression automatique après 5 secondes
    setTimeout(() => {
        messageElement.classList.add('fade-out');
        setTimeout(() => {
            messageElement.remove();
        }, 500);
    }, 5000);
}

/**
 * Initialisation du slider de témoignages
 */
function initializeTestimonials() {
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('prev-testimonial');
    const nextBtn = document.getElementById('next-testimonial');
    
    if (!slides.length || !dots.length) return;
    
    let currentSlide = 0;
    let intervalId = null;

    function showSlide(n) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // Ajouter les event listeners pour les contrôles du slider
    if (nextBtn && prevBtn) {
        nextBtn.addEventListener('click', function() {
            clearInterval(intervalId); // Arrêter le défilement automatique
            nextSlide();
            startAutoSlide(); // Redémarrer le défilement automatique
        });
        
        prevBtn.addEventListener('click', function() {
            clearInterval(intervalId); // Arrêter le défilement automatique
            prevSlide();
            startAutoSlide(); // Redémarrer le défilement automatique
        });
    }
    
    // Ajouter les event listeners pour les points de navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            clearInterval(intervalId); // Arrêter le défilement automatique
            showSlide(index);
            startAutoSlide(); // Redémarrer le défilement automatique
        });
    });

    // Fonction pour démarrer le défilement automatique
    function startAutoSlide() {
        clearInterval(intervalId); // Nettoyer l'intervalle précédent
        intervalId = setInterval(nextSlide, 5000); // Nouveau défilement toutes les 5 secondes
    }

    // Démarrer le défilement automatique au chargement
    startAutoSlide();

    // Arrêter le défilement lorsque l'utilisateur survole le slider
    const sliderContainer = document.getElementById('testimonial-slider');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', function() {
            clearInterval(intervalId);
        });
        
        sliderContainer.addEventListener('mouseleave', function() {
            startAutoSlide();
        });
    }
}

/**
 * Animation pour le compteur de statistiques 
 * (à utiliser sur la page À propos)
 */
function initializeStatCounters() {
    const counters = document.querySelectorAll('.stat-counter');
    
    if (!counters.length) return;
    
    let countersStarted = false;
    
    function startCounters() {
        if (countersStarted) return;
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 secondes
            const step = Math.ceil(target / (duration / 20)); // Mise à jour toutes les 20ms
            
            let current = 0;
            const updateCounter = setInterval(() => {
                current += step;
                if (current > target) {
                    current = target;
                    clearInterval(updateCounter);
                }
                counter.textContent = current.toLocaleString();
            }, 20);
        });
        
        countersStarted = true;
    }
    
    // Démarrer les compteurs quand ils sont visibles lors du défilement
    function checkCounters() {
        const triggerPosition = window.innerHeight * 0.8;
        
        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            const sectionTop = statsSection.getBoundingClientRect().top;
            
            if (sectionTop < triggerPosition) {
                startCounters();
                window.removeEventListener('scroll', checkCounters);
            }
        }
    }
    
    // Vérifier une fois au chargement
    checkCounters();
    
    // Puis à chaque défilement
    window.addEventListener('scroll', checkCounters);
}

/**
 * Initialisation de la galerie d'images 
 * (pour la page Galerie)
 */
function initializeGallery() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxClose = document.getElementById('lightbox-close');
    const lightboxPrev = document.getElementById('lightbox-prev');
    const lightboxNext = document.getElementById('lightbox-next');
    const lightboxCaption = document.getElementById('lightbox-caption');
    
    if (!galleryItems.length || !lightbox) return;
    
    let currentIndex = 0;
    
    // Ouvrir la lightbox au clic sur une image
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function() {
            const imgSrc = this.querySelector('img').getAttribute('src');
            const caption = this.querySelector('.gallery-caption').textContent;
            
            lightboxImage.setAttribute('src', imgSrc);
            lightboxCaption.textContent = caption;
            lightbox.classList.add('active');
            currentIndex = index;
            
            // Désactiver le défilement de la page
            document.body.style.overflow = 'hidden';
        });
    });
    
    // Fermer la lightbox
    if (lightboxClose) {
        lightboxClose.addEventListener('click', function() {
            lightbox.classList.remove('active');
            // Réactiver le défilement
            document.body.style.overflow = '';
        });
    }
    
    // Cliquer en dehors de l'image ferme également la lightbox
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    // Image précédente
    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', function() {
            currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
            updateLightboxContent();
        });
    }
    
    // Image suivante
    if (lightboxNext) {
        lightboxNext.addEventListener('click', function() {
            currentIndex = (currentIndex + 1) % galleryItems.length;
            updateLightboxContent();
        });
    }
    
    // Mise à jour du contenu de la lightbox
    function updateLightboxContent() {
        const item = galleryItems[currentIndex];
        const imgSrc = item.querySelector('img').getAttribute('src');
        const caption = item.querySelector('.gallery-caption').textContent;
        
        // Animation de transition
        lightboxImage.style.opacity = 0;
        
        setTimeout(() => {
            lightboxImage.setAttribute('src', imgSrc);
            lightboxCaption.textContent = caption;
            lightboxImage.style.opacity = 1;
        }, 300);
    }
    
    // Navigation au clavier
    document.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('active')) return;
        
        if (e.key === 'Escape') {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        } else if (e.key === 'ArrowLeft') {
            currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
            updateLightboxContent();
        } else if (e.key === 'ArrowRight') {
            currentIndex = (currentIndex + 1) % galleryItems.length;
            updateLightboxContent();
        }
    });
}

/**
 * Initialisation de la carte pour la page Contact
 * Utilise Leaflet pour afficher une carte interactive
 */
function initializeMap() {
    const mapContainer = document.getElementById('contact-map');
    
    if (!mapContainer) return;
    
    // Vérifier si Leaflet est chargé
    if (typeof L === 'undefined') {
        console.error('Leaflet n\'est pas chargé. Assurez-vous d\'inclure les fichiers Leaflet.');
        return;
    }
    
    // Coordonnées de la taverne (à remplacer par les vraies coordonnées)
    const lat = 48.9807; // Exemple: coordonnées de Pontoise
    const lng = 2.0887;
    
    // Initialisation de la carte
    const map = L.map(mapContainer).setView([lat, lng], 15);
    
    // Ajouter la couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Ajouter un marqueur à l'emplacement de la taverne
    const taverneIcon = L.icon({
        iconUrl: 'assets/images/marker.png', // Chemin vers l'icône personnalisée
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });
    
    const marker = L.marker([lat, lng], { icon: taverneIcon }).addTo(map);
    
    // Ajouter une popup au marqueur
    marker.bindPopup('<strong>Taverne Kanorelim</strong><br>12 Rue des Templiers<br>Cité Médiévale').openPopup();
    
    // Ajuster la taille de la carte lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        map.invalidateSize();
    });
}