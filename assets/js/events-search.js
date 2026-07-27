/**
 * Ricerca Eventi — barra di ricerca live con datepicker custom a selezione range.
 */

// --------------------------------------------------------------------------
// OesDatePicker — dropdown calendario con selezione singola o a intervallo
// --------------------------------------------------------------------------
class OesDatePicker {
    constructor($trigger, $label, onChange) {
        this.$trigger = $trigger;
        this.$label   = $label;
        this.onChange = onChange;

        this.dateFrom = null;
        this.dateTo   = null;
        this.pickStep = 0;    // 0 = nessuna, 1 = from scelto, attendo to
        this.hover    = null;

        const now = new Date();
        this.viewYear  = now.getFullYear();
        this.viewMonth = now.getMonth();

        this.$dp = null;

        this._outsideHandler = (e) => {
            if (
                this.$dp &&
                !jQuery(e.target).closest(this.$trigger).length &&
                !jQuery(e.target).closest(this.$dp).length
            ) {
                this.close();
            }
        };

        this.$trigger.on('click', (e) => { e.stopPropagation(); this.toggle(); });
    }

    // ---- API pubblica ----

    getFrom() { return this.dateFrom ? this._fmt(this.dateFrom) : ''; }
    getTo()   { const d = this.dateTo || this.dateFrom; return d ? this._fmt(d) : ''; }
    hasValue() { return !!this.dateFrom; }

    clearSilent() {
        this.dateFrom = null;
        this.dateTo   = null;
        this.pickStep = 0;
        this.hover    = null;
        this.$trigger.removeClass('has-value');
        this.$label.text(this.$label.data('placeholder') || 'Qualsiasi data');
        if (this.$dp) this._renderDays();
    }

    open() {
        if (this.$dp) return;
        this.$dp = jQuery(this._buildHtml());
        this.$trigger.closest('.oes-field').append(this.$dp);
        this.$trigger.addClass('is-open').attr('aria-expanded', 'true');
        this._renderDays();
        this._bindEvents();
        jQuery(document).on('click.oes-dp', this._outsideHandler);
    }

    close() {
        if (!this.$dp) return;
        this.$dp.remove();
        this.$dp = null;
        this.$trigger.removeClass('is-open').attr('aria-expanded', 'false');
        jQuery(document).off('click.oes-dp', this._outsideHandler);
        // Selezione parziale (solo from scelto): cancella silenziosamente
        // senza scatenare una ricerca con dati incompleti.
        if (this.pickStep === 1) {
            this.dateFrom = null;
            this.dateTo   = null;
            this.pickStep = 0;
            this.hover    = null;
            this.$trigger.removeClass('has-value');
            this.$label.text(this.$label.data('placeholder') || 'Qualsiasi data');
        }
    }

    toggle() { this.$dp ? this.close() : this.open(); }

    // ---- Internals ----

    _fmt(d) {
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    _fmtDisplay(d, withYear) {
        const mm = ['gen','feb','mar','apr','mag','giu','lug','ago','set','ott','nov','dic'];
        return withYear ? `${d.getDate()} ${mm[d.getMonth()]} ${d.getFullYear()}` : `${d.getDate()} ${mm[d.getMonth()]}`;
    }

    _updateLabel() {
        if (!this.dateFrom) {
            this.$trigger.removeClass('has-value');
            this.$label.text(this.$label.data('placeholder') || 'Qualsiasi data');
            return;
        }
        this.$trigger.addClass('has-value');
        const to = this.dateTo || this.dateFrom;
        const sameYear = this.dateFrom.getFullYear() === to.getFullYear();
        if (!this.dateTo || this._fmt(this.dateFrom) === this._fmt(this.dateTo)) {
            this.$label.text(this._fmtDisplay(this.dateFrom, true));
        } else {
            this.$label.text(`${this._fmtDisplay(this.dateFrom, !sameYear)} – ${this._fmtDisplay(to, true)}`);
        }
    }

    _buildHtml() {
        const days = ['Lu','Ma','Me','Gi','Ve','Sa','Do'];
        const wd   = days.map(d => `<span class="oes-dp-weekday">${d}</span>`).join('');
        return `
<div class="oes-dp">
    <div class="oes-dp-header">
        <span class="oes-dp-month-label"></span>
        <div class="oes-dp-nav">
            <button type="button" class="oes-dp-prev" aria-label="Mese precedente">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" class="oes-dp-next" aria-label="Mese successivo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
    <div class="oes-dp-weekdays">${wd}</div>
    <div class="oes-dp-days"></div>
</div>`;
    }

    _renderDays() {
        if (!this.$dp) return;

        const months = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
        this.$dp.find('.oes-dp-month-label').text(`${months[this.viewMonth]} ${this.viewYear}`);

        const today = new Date(); today.setHours(0,0,0,0);
        const firstDay = new Date(this.viewYear, this.viewMonth, 1);
        let startDow = firstDay.getDay(); // 0=dom
        startDow = startDow === 0 ? 6 : startDow - 1; // lun=0…dom=6
        const daysInMonth = new Date(this.viewYear, this.viewMonth+1, 0).getDate();

        const fromTs   = this.dateFrom ? this.dateFrom.getTime() : null;
        const toD      = this.dateTo || this.dateFrom;
        const toTs     = toD ? toD.getTime() : null;
        const hoverTs  = this.hover ? this.hover.getTime() : null;

        let html = '';
        for (let i = 0; i < startDow; i++) html += '<span class="oes-dp-day is-empty"></span>';

        for (let day = 1; day <= daysInMonth; day++) {
            const d = new Date(this.viewYear, this.viewMonth, day);
            d.setHours(0,0,0,0);
            const ts = d.getTime();
            const cl = ['oes-dp-day'];

            if (ts === today.getTime()) cl.push('is-today');

            if (fromTs !== null) {
                const isStart  = ts === fromTs;
                const isEnd    = toTs !== null && ts === toTs;
                const isRange  = toTs !== null && fromTs !== toTs && ts > fromTs && ts < toTs;
                const isSingle = this.pickStep === 0 && (!this.dateTo || fromTs === toTs);

                if (isStart && isSingle && !isRange) cl.push('is-single');
                else if (isStart) { cl.push('is-start'); if (isRange || (toTs !== null && fromTs !== toTs)) cl.push('is-in-range'); }
                else if (isEnd)   { cl.push('is-end', 'is-in-range'); }
                else if (isRange) cl.push('is-in-range');

                // Anteprima hover (solo mentre step=1)
                if (this.pickStep === 1 && hoverTs !== null) {
                    const lo = Math.min(fromTs, hoverTs);
                    const hi = Math.max(fromTs, hoverTs);
                    if (ts > lo && ts < hi && !cl.includes('is-in-range')) cl.push('is-preview');
                    if (ts === hoverTs && ts !== fromTs) cl.push('is-preview');
                }
            }

            html += `<button type="button" class="${cl.join(' ')}" data-ts="${ts}">${day}</button>`;
        }

        this.$dp.find('.oes-dp-days').html(html);
    }

    _bindEvents() {
        this.$dp.on('click', (e) => e.stopPropagation());

        this.$dp.on('click', '.oes-dp-prev', (e) => {
            e.stopPropagation();
            if (--this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; }
            this._renderDays();
        });

        this.$dp.on('click', '.oes-dp-next', (e) => {
            e.stopPropagation();
            if (++this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; }
            this._renderDays();
        });

        this.$dp.on('click', '.oes-dp-day:not(.is-empty)', (e) => {
            e.stopPropagation();
            const ts = parseInt(jQuery(e.currentTarget).data('ts'), 10);
            const clicked = new Date(ts);

            if (this.pickStep === 0) {
                this.dateFrom = clicked;
                this.dateTo   = null;
                this.pickStep = 1;
                this.hover    = null;
                this._renderDays();
            } else {
                let from = this.dateFrom, to = clicked;
                if (to < from) [from, to] = [to, from];
                this.dateFrom = from;
                this.dateTo   = to;
                this.pickStep = 0;
                this.hover    = null;
                this._updateLabel();
                this.close();       // chiudi subito
                this.onChange();    // lancia la ricerca
            }
        });

        this.$dp.on('mouseover', '.oes-dp-day:not(.is-empty)', (e) => {
            if (this.pickStep !== 1) return;
            const ts = parseInt(jQuery(e.currentTarget).data('ts'), 10);
            // Ri-renderizza solo se il giorno cambia: evita il loop
            // "DOM replaced under cursor → new mouseover → _renderDays() → loop"
            if (this.hover && this.hover.getTime() === ts) return;
            this.hover = new Date(ts);
            this._renderDays();
        });

        this.$dp.on('mouseleave', '.oes-dp-days', () => {
            if (this.pickStep !== 1 || !this.hover) return;
            this.hover = null;
            this._renderDays();
        });

    }
}

// --------------------------------------------------------------------------
// OpenEventsSearchHandler — handler Elementor per il widget di ricerca
// --------------------------------------------------------------------------
class OpenEventsSearchHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                text:         '.oes-input-text',
                comune:       '.oes-input-comune',
                category:     '.oes-input-category',
                dateTrigger:  '.oes-date-trigger',
                dateLabel:    '.oes-date-label',
                chip:         '.oes-chip',
                reset:        '.oes-reset',
                results:      '.oes-results'
            }
        };
    }

    getDefaultElements() {
        const s = this.getSettings('selectors');
        return {
            $text:        this.$element.find(s.text),
            $comune:      this.$element.find(s.comune),
            $category:    this.$element.find(s.category),
            $dateTrigger: this.$element.find(s.dateTrigger),
            $dateLabel:   this.$element.find(s.dateLabel),
            $chips:       this.$element.find(s.chip),
            $reset:       this.$element.find(s.reset),
            $results:     this.$element.find(s.results)
        };
    }

    bindEvents() {
        this.dateMode      = 'upcoming';
        this.debounceTimer = null;
        this.xhr           = null;
        this.dp            = null;
        this.maxEvents     = parseInt(this.elements.$results.data('maxEvents') || '0', 10);

        this.elements.$text.on('input', () => this.debouncedSearch());
        this.elements.$comune.on('change', () => this.runSearch());
        this.elements.$category.on('change', () => this.runSearch());

        this.initDatePicker();

        this.elements.$chips.on('click', (e) => {
            const mode = jQuery(e.currentTarget).data('dateMode');
            this.dateMode = mode;
            if (this.dp) this.dp.clearSilent();
            this.setActiveChip(mode);
            this.runSearch();
        });

        this.elements.$reset.on('click', () => this.resetFilters());
    }

    initDatePicker() {
        if (!this.elements.$dateTrigger.length) return;
        this.elements.$dateLabel.data('placeholder', 'Qualsiasi data');
        this.dp = new OesDatePicker(
            this.elements.$dateTrigger,
            this.elements.$dateLabel,
            () => {
                if (this.dp.hasValue()) {
                    this.dateMode = 'range';
                    this.elements.$chips.removeClass('is-active');
                } else {
                    this.dateMode = 'upcoming';
                    this.setActiveChip('upcoming');
                }
                this.runSearch();
            }
        );
    }

    setActiveChip(mode) {
        this.elements.$chips.each(function () {
            const $c = jQuery(this);
            $c.toggleClass('is-active', $c.data('dateMode') === mode);
        });
    }

    debouncedSearch() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => this.runSearch(), 300);
    }

    hasActiveFilters() {
        return (this.elements.$text.val() || '').trim() !== '' ||
            (this.elements.$comune.length && (this.elements.$comune.val() || '') !== '') ||
            (this.elements.$category.length && String(this.elements.$category.val() || '0') !== '0') ||
            this.dateMode !== 'upcoming';
    }

    resetFilters() {
        this.elements.$text.val('');
        if (this.elements.$comune.length) this.elements.$comune.val('');
        if (this.elements.$category.length) this.elements.$category.val('0');
        if (this.dp) this.dp.clearSilent();
        this.dateMode = 'upcoming';
        this.setActiveChip('upcoming');
        this.runSearch();
    }

    runSearch() {
        if (typeof openEventsSearch === 'undefined') return;

        this.elements.$reset.prop('hidden', !this.hasActiveFilters());
        this.elements.$results.addClass('is-loading');

        if (this.xhr) this.xhr.abort();

        const data = {
            action:     'open_events_search',
            nonce:      openEventsSearch.nonce,
            text:       (this.elements.$text.val() || '').trim(),
            comune:     this.elements.$comune.length ? (this.elements.$comune.val() || '') : '',
            category:   this.elements.$category.length ? (this.elements.$category.val() || '0') : '0',
            date_mode:  this.dateMode,
            max_events: this.maxEvents,
        };

        if (this.dateMode === 'range' && this.dp) {
            data.date_from = this.dp.getFrom();
            data.date_to   = this.dp.getTo();
        }

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
