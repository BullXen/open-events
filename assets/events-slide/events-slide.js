/**
 * Slide Eventi Consigliati — carousel hero, nessuna libreria esterna.
 *
 * Deve funzionare in 3 contesti diversi: widget Elementor (frontend "vero"),
 * shortcode [open_events_slide] e inserimento automatico in homepage — solo
 * il primo passa dal sistema di handler di Elementor (frontend/element_ready),
 * gli altri due sono HTML semplice dentro the_content. Per questo l'init gira
 * sempre al DOMContentLoaded (copre tutti e 3 i casi) ed è comunque richiamato
 * anche dall'hook Elementor per il caso "aggiunto/modificato live nell'editor"
 * senza reload di pagina; un guard per-elemento evita la doppia inizializzazione.
 */
(function ($) {
    'use strict';

    function initSlider($wrapper) {
        if ($wrapper.data('oeSlideInit')) {
            return;
        }
        $wrapper.data('oeSlideInit', true);

        let settings = {};
        try {
            settings = JSON.parse($wrapper.attr('data-settings') || '{}');
        } catch (e) {
            settings = {};
        }

        const $track = $wrapper.find('.oe-slide-track');
        const $slides = $track.find('.oe-slide-item');
        const $dots = $wrapper.find('.oe-slide-dot');
        const total = $slides.length;
        if (total <= 1) {
            return;
        }

        let index = 0;
        let timer = null;

        function goTo(i) {
            index = settings.loop ? ((i % total) + total) % total : Math.max(0, Math.min(total - 1, i));
            $track.css('transform', 'translateX(-' + (index * 100) + '%)');
            $dots.removeClass('is-active').eq(index).addClass('is-active');
        }

        function next() { goTo(index + 1); }
        function prev() { goTo(index - 1); }

        function startAutoplay() {
            if (!settings.autoplay) {
                return;
            }
            stopAutoplay();
            timer = setInterval(next, settings.autoplayDelay || 5000);
        }

        function stopAutoplay() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        $wrapper.find('.oe-slide-next').on('click', () => { next(); startAutoplay(); });
        $wrapper.find('.oe-slide-prev').on('click', () => { prev(); startAutoplay(); });
        $dots.on('click', function () { goTo($dots.index(this)); startAutoplay(); });

        $wrapper.on('mouseenter focusin', stopAutoplay);
        $wrapper.on('mouseleave focusout', startAutoplay);

        // Swipe touch (mobile).
        let touchStartX = null;
        $track.on('touchstart', (e) => { touchStartX = e.originalEvent.touches[0].clientX; stopAutoplay(); });
        $track.on('touchend', (e) => {
            if (touchStartX === null) {
                return;
            }
            const delta = e.originalEvent.changedTouches[0].clientX - touchStartX;
            if (Math.abs(delta) > 40) {
                delta < 0 ? next() : prev();
            }
            touchStartX = null;
            startAutoplay();
        });

        goTo(0);
        startAutoplay();
    }

    function initAllSliders() {
        $('.oe-slide-wrapper').each(function () {
            initSlider($(this));
        });
    }

    $(document).ready(initAllSliders);

    if (window.elementorFrontend) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/open_events_slide.default', ($element) => {
            initSlider($element.find('.oe-slide-wrapper').first());
        });
    }

})(jQuery);
