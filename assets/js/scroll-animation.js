/**
 * Warung Tiga Saudara - Scroll Reveal Animation
 * Lightweight Vanilla JS IntersectionObserver for smooth scroll animations
 */
document.addEventListener('DOMContentLoaded', () => {
    // Configure observer options
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -50px 0px',
        threshold: 0.15
    };

    // Callback when elements intersect viewport
    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                // Unobserve after element is revealed for better performance
                observer.unobserve(entry.target);
            }
        });
    };

    // Initialize IntersectionObserver
    const scrollObserver = new IntersectionObserver(revealCallback, observerOptions);

    // Select all elements marked with .scroll-reveal or .fade-in
    const targetElements = document.querySelectorAll('.scroll-reveal, .fade-in');
    
    targetElements.forEach(el => {
        // Ensure scroll-reveal class is present for base styles
        if (!el.classList.contains('scroll-reveal')) {
            el.classList.add('scroll-reveal');
        }
        scrollObserver.observe(el);
    });
});
