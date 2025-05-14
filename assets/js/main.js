// main.js - Script principal pour la Taverne Kanorelim

/**
 * Fonctions communes à toutes les pages
 */
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeScrollAnimations();
    
    // Initialiser les fonctionnalités spécifiques selon la page
    const currentPage = location.pathname.split('/').pop() || 'index.php';
    
    if (currentPage === 'index.php' || currentPage === '') {
        handleReservationForm();
        initializeTestimonials();
    } else if (currentPage === 'galerie.php') {
        initializeGallery();
    } else if (currentPage === 'contact.php') {
        initializeMap();
    }
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
        // Le formulaire est géré par PHP, pas besoin d'ajouter de code JavaScript
        // Si vous souhaitez ajouter une validation côté client:
        
        reservationForm.addEventListener('submit', function(e) {
            if (!validateReservationForm(this)) {
                e.preventDefault();
            }
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
 * Affichage d'un message d'erreur
 */
function showErrorMessage(message) {
    // Créer un élément de message si nécessaire
    let messageElement = document.querySelector('.message');
    
    if (!messageElement) {
        messageElement = document.createElement('div');
        messageElement.className = 'message message-error';
        
        const reservationForm = document.getElementById('reservation-form');
        if (reservationForm) {
            reservationForm.parentNode.insertBefore(messageElement, reservationForm);
        }
    }
    
    // Définir le message et le type
    messageElement.textContent = message;
    messageElement.className = 'message message-error';
    
    // Faire défiler jusqu'au message
    messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
            clearInterval(intervalId);
            nextSlide();
            startAutoSlide();
        });
        
        prevBtn.addEventListener('click', function() {
            clearInterval(intervalId);
            prevSlide();
            startAutoSlide();
        });
    }
    
    // Ajouter les event listeners pour les points de navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            clearInterval(intervalId);
            showSlide(index);
            startAutoSlide();
        });
    });

    // Fonction pour démarrer le défilement automatique
    function startAutoSlide() {
        clearInterval(intervalId);
        intervalId = setInterval(nextSlide, 5000);
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
 * Initialisation de la galerie d'images 
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
 */
function initializeMap() {
    const mapContainer = document.getElementById('contact-map');
    
    if (!mapContainer) return;
    
    // Vérifier si Leaflet est chargé
    if (typeof L === 'undefined') {
        console.error('Leaflet n\'est pas chargé.');
        return;
    }
    
    // Coordonnées de la taverne (Pontoise, France)
    const lat = 49.0508;
    const lng = 2.1008;
    
    // Initialisation de la carte
    const map = L.map(mapContainer).setView([lat, lng], 15);
    
    // Ajouter la couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Ajouter un marqueur à l'emplacement de la taverne
    const marker = L.marker([lat, lng]).addTo(map);
    
    // Ajouter une popup au marqueur
    marker.bindPopup('<strong>Taverne Kanorelim</strong><br>12 Rue des Templiers<br>Cité Médiévale, Pontoise').openPopup();
    
    // Ajuster la taille de la carte lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        map.invalidateSize();
    });
}