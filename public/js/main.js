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
    //  User menu dropdown
    // ----------------------------------------------------------
    const userMenu    = document.getElementById('user-menu');
    const userTrigger = document.getElementById('user-menu-trigger');
    if (userMenu && userTrigger) {
        userTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = userMenu.classList.toggle('open');
            userTrigger.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
                userTrigger.setAttribute('aria-expanded', false);
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                userMenu.classList.remove('open');
                userTrigger.setAttribute('aria-expanded', false);
            }
        });
    }

    // ----------------------------------------------------------
    //  Flash banner dismiss
    // ----------------------------------------------------------
    document.querySelectorAll('.flash-banner__close').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.flash-banner').style.display = 'none';
        });
    });

    // ----------------------------------------------------------
    //  Back to top button – appears at 60% scroll depth
    // ----------------------------------------------------------
    const backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
            backToTop.classList.toggle('visible', scrolled >= 0.6);
        }, { passive: true });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ----------------------------------------------------------
    //  Custom confirm modal
    // ----------------------------------------------------------
    const cModal   = document.getElementById('c-modal');
    const cMessage = document.getElementById('c-modal-message');
    const cOk      = document.getElementById('c-modal-ok');
    const cCancel  = document.getElementById('c-modal-cancel');

    if (cModal && cMessage && cOk && cCancel) {
        let pendingForm = null;

        const openModal = (message, form) => {
            pendingForm = form;
            cMessage.textContent = message;
            cModal.hidden = false;
            document.body.style.overflow = 'hidden';
            // Small delay so the animation is visible before focus shift
            setTimeout(() => cOk.focus(), 50);
        };

        const closeModal = () => {
            cModal.hidden = true;
            document.body.style.overflow = '';
            pendingForm = null;
        };

        // Convert every [data-confirm] button to type="button" so the
        // form never auto-submits, then bind a direct click handler.
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.type = 'button';
            btn.addEventListener('click', () => {
                openModal(btn.dataset.confirm, btn.closest('form'));
            });
        });

        cOk.addEventListener('click', () => {
            const form = pendingForm;
            closeModal();
            if (form) form.submit();
        });

        cCancel.addEventListener('click', closeModal);

        cModal.querySelector('.c-modal__backdrop').addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !cModal.hidden) closeModal();
        });
    }

    // ----------------------------------------------------------
    //  Get Cash — platform dropdown portal
    //  Rows are pre-built as <template> elements by Blade.
    //  JS clones the template content into the fixed-position portal,
    //  sidestepping overflow:hidden on .game-card entirely.
    // ----------------------------------------------------------
    const cashDropdown  = document.getElementById('cash-dropdown');
    const cashDropBody  = document.getElementById('cash-dropdown-body');
    const cashDropTitle = document.getElementById('cash-dropdown-title');
    const cashDropClose = document.getElementById('cash-dropdown-close');
    const cashBackdrop  = document.getElementById('cash-dropdown-backdrop');

    if (cashDropdown && cashBackdrop) {

        const closeCashDropdown = () => {
            cashDropdown.hidden          = true;
            cashBackdrop.hidden          = true;
            document.body.style.overflow = '';
        };

        const openCashDropdown = (btn) => {
            const tplId = btn.dataset.tpl;
            const tpl   = tplId ? document.getElementById(tplId) : null;

            // Title comes from the template's data-title attribute
            cashDropTitle.textContent = tpl ? (tpl.dataset.title || '') : '';

            // Clone the pre-built Blade rows into the portal body
            cashDropBody.innerHTML = '';
            if (tpl && tpl.content.querySelector('.cash-dropdown__item')) {
                cashDropBody.appendChild(tpl.content.cloneNode(true));
            } else {
                const empty = document.createElement('p');
                empty.className   = 'cash-dropdown__empty';
                empty.textContent = 'No platforms available.';
                cashDropBody.appendChild(empty);
            }

            // Position using fixed coords from the button's viewport rect
            const rect = btn.getBoundingClientRect();
            const dropW = 270;
            const viewW = window.innerWidth;
            const viewH = window.innerHeight;

            let top  = rect.bottom + 8;
            let left = rect.left;

            if (left + dropW > viewW - 8) left = viewW - dropW - 8;
            if (left < 8) left = 8;

            // Rough height estimate for flip-upward check
            const estH = Math.max(cashDropdown.offsetHeight, 80 + cashDropBody.children.length * 56);
            if (top + estH > viewH - 8) top = Math.max(8, rect.top - estH - 8);

            cashDropdown.style.top  = top  + 'px';
            cashDropdown.style.left = left + 'px';

            cashDropdown.hidden          = false;
            cashBackdrop.hidden          = false;
            document.body.style.overflow = 'hidden';
        };

        // Event delegation — works for any .js-cash-btn on the page
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-cash-btn');
            if (btn) { e.stopPropagation(); openCashDropdown(btn); }
        });

        cashDropClose?.addEventListener('click', closeCashDropdown);
        cashBackdrop.addEventListener('click', closeCashDropdown);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !cashDropdown.hidden) closeCashDropdown();
        });
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
