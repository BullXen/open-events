/**
 * Ricerca Eventi — aggiornamento live della griglia via AJAX.
 * Ogni cambio di filtro (testo con debounce, comune, categoria, data, chip
 * rapidi) rilancia la query e sostituisce i risultati senza ricaricare.
 */
class OpenEventsSearchHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                root: '.oes-search',
                text: '.oes-input-text',
                comune: '.oes-input-comune',
                category: '.oes-input-category',
                date: '.oes-input-date',
                chip: '.oes-chip',
                reset: '.oes-reset',
                results: '.oes-results'
            }
        };
    }

    getDefaultElements() {
        const s = this.getSettings('selectors');
        return {
            $text: this.$element.find(s.text),
            $comune: this.$element.find(s.comune),
            $category: this.$element.find(s.category),
            $date: this.$element.find(s.date),
            $chips: this.$element.find(s.chip),
            $reset: this.$element.find(s.reset),
            $results: this.$element.find(s.results)
        };
    }

    bindEvents() {
        this.dateMode = 'upcoming';
        this.debounceTimer = null;
        this.xhr = null;

        this.elements.$text.on('input', () => this.debouncedSearch());
        this.elements.$comune.on('change', () => this.runSearch());
        this.elements.$category.on('change', () => this.runSearch());

        // Scegliere una data specifica passa in modalità 'day' e disattiva i chip.
        this.elements.$date.on('change', () => {
            if (this.elements.$date.val()) {
                this.dateMode = 'day';
                this.elements.$chips.removeClass('is-active');
            } else {
                this.dateMode = 'upcoming';
                this.setActiveChip('upcoming');
            }
            this.runSearch();
        });

        this.elements.$chips.on('click', (e) => {
            const mode = jQuery(e.currentTarget).data('dateMode');
            this.dateMode = mode;
            this.elements.$date.val('');
            this.setActiveChip(mode);
            this.runSearch();
        });

        this.elements.$reset.on('click', () => this.resetFilters());
    }

    setActiveChip(mode) {
        this.elements.$chips.each(function () {
            const $chip = jQuery(this);
            $chip.toggleClass('is-active', $chip.data('dateMode') === mode);
        });
    }

    debouncedSearch() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.runSearch(), 300);
    }

    hasActiveFilters() {
        return (this.elements.$text.val() || '').trim() !== '' ||
            (this.elements.$comune.val() || '') !== '' ||
            String(this.elements.$category.val() || '0') !== '0' ||
            this.dateMode !== 'upcoming';
    }

    resetFilters() {
        this.elements.$text.val('');
        this.elements.$comune.val('');
        this.elements.$category.val('0');
        this.elements.$date.val('');
        this.dateMode = 'upcoming';
        this.setActiveChip('upcoming');
        this.runSearch();
    }

    runSearch() {
        if (typeof openEventsSearch === 'undefined') {
            return;
        }

        this.elements.$reset.prop('hidden', !this.hasActiveFilters());
        this.elements.$results.addClass('is-loading');

        // Annulla una richiesta ancora in volo: conta solo l'ultimo stato dei filtri.
        if (this.xhr) {
            this.xhr.abort();
        }

        const data = {
            action: 'open_events_search',
            nonce: openEventsSearch.nonce,
            text: (this.elements.$text.val() || '').trim(),
            comune: this.elements.$comune.val() || '',
            category: this.elements.$category.val() || '0',
            date_mode: this.dateMode,
            date: this.elements.$date.val() || ''
        };

        this.xhr = jQuery.post(openEventsSearch.ajaxUrl, data)
            .done((response) => {
                if (response && response.success && response.data) {
                    this.elements.$results.html(response.data.html);
                }
            })
            .always((_, status) => {
                if (status !== 'abort') {
                    this.elements.$results.removeClass('is-loading');
                    this.xhr = null;
                }
            });
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(OpenEventsSearchHandler, { $element });
    };
    elementorFrontend.hooks.addAction('frontend/element_ready/open_events_search.default', addHandler);
});
