// ============================================================
//  GameVault – Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // ----------------------------------------------------------
    //  Sticky header scroll effect
    // ----------------------------------------------------------
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });
    }

    // ----------------------------------------------------------
    //  Mobile nav toggle
    // ----------------------------------------------------------
    const navToggle = document.getElementById('nav-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    if (navToggle && mobileNav) {
        navToggle.addEventListener('click', () => {
            const open = mobileNav.classList.toggle('open');
            navToggle.textContent = open ? '✕' : '☰';
            document.body.style.overflow = open ? 'hidden' : '';
        });
    }

    // ----------------------------------------------------------
    //  Intersection Observer – fade-up animations
    // ----------------------------------------------------------
    const fadeEls = document.querySelectorAll('.fade-up');
    if (fadeEls.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e, i) => {
                if (e.isIntersecting) {
                    setTimeout(() => e.target.classList.add('visible'), i * 60);
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });
        fadeEls.forEach(el => io.observe(el));
    }

    // ----------------------------------------------------------
    //  Screenshot lightbox
    // ----------------------------------------------------------
    const lightbox   = document.getElementById('lightbox');
    const lbImg      = document.getElementById('lb-img');
    const lbClose    = document.getElementById('lb-close');
    const screenshots = document.querySelectorAll('.screenshot');

    if (lightbox && lbImg) {
        screenshots.forEach(s => {
            s.addEventListener('click', () => {
                const src = s.dataset.full || s.querySelector('img')?.src;
                if (!src) return;
                lbImg.src = src;
                lightbox.classList.add('open');
                document.body.style.overflow = 'hidden';
            });
        });

        const closeLightbox = () => {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { lbImg.src = ''; }, 300);
        };

        lbClose?.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });
    }

    // ----------------------------------------------------------
    //  Auto-submit search on clear
    // ----------------------------------------------------------
    const searchInput = document.querySelector('.search-bar input[type="search"]');
    if (searchInput) {
        searchInput.addEventListener('search', () => {
            if (!searchInput.value.trim()) {
                searchInput.closest('form')?.submit();
            }
        });
    }

    // ----------------------------------------------------------
    //  Lazy load polyfill fallback
    // ----------------------------------------------------------
    if ('loading' in HTMLImageElement.prototype === false) {
        const imgs = document.querySelectorAll('img[loading="lazy"]');
        const lazyIo = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const img = e.target;
                    img.src = img.dataset.src || img.src;
                    lazyIo.unobserve(img);
                }
            });
        });
        imgs.forEach(img => lazyIo.observe(img));
    }

    // ----------------------------------------------------------
    //  Platform pill active scroll indication
    // ----------------------------------------------------------
    const strip = document.querySelector('.platform-strip');
    if (strip) {
        // Subtle drag/swipe scroll on desktop
        let isDown = false, startX, scrollLeft;
        strip.addEventListener('mousedown', e => {
            isDown = true;
            strip.style.cursor = 'grabbing';
            startX = e.pageX - strip.offsetLeft;
            scrollLeft = strip.scrollLeft;
        });
        strip.addEventListener('mouseleave', () => { isDown = false; strip.style.cursor = ''; });
        strip.addEventListener('mouseup', () => { isDown = false; strip.style.cursor = ''; });
        strip.addEventListener('mousemove', e => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - strip.offsetLeft;
            strip.scrollLeft = scrollLeft - (x - startX) * 1.5;
        });
    }
});
