// --------------------------------------------------------------------------
// EmDatePicker — trigger + calendario popup, stesso stile/comportamento del
// datepicker della barra di ricerca (OesDatePicker in events-search.js), ma
// a selezione singola: qui serve solo scegliere un giorno per volta.
// --------------------------------------------------------------------------
let emDatePickerUid = 0;

class EmDatePicker {
    constructor($trigger, $input) {
        this.$trigger = $trigger;
        this.$input = $input;
        this.uid = ++emDatePickerUid;

        this.selected = $input.val() ? new Date($input.val() + 'T00:00:00') : null;
        const base = this.selected || new Date();
        this.viewYear = base.getFullYear();
        this.viewMonth = base.getMonth();
        this.$dp = null;

        this._outsideHandler = (e) => {
            if (this.$dp && !jQuery(e.target).closest(this.$trigger).length && !jQuery(e.target).closest(this.$dp).length) {
                this.close();
            }
        };

        this.$trigger.on('click', (e) => { e.stopPropagation(); this.toggle(); });
        this._updateLabel();
    }

    toggle() { this.$dp ? this.close() : this.open(); }

    open() {
        if (this.$dp) return;
        this.$dp = jQuery(this._buildHtml());
        this.$trigger.closest('.em-date-field').append(this.$dp);
        this.$trigger.addClass('is-open').attr('aria-expanded', 'true');
        this._renderDays();
        this._bindEvents();
        jQuery(document).on('click.em-dp-' + this.uid, this._outsideHandler);
    }

    close() {
        if (!this.$dp) return;
        this.$dp.remove();
        this.$dp = null;
        this.$trigger.removeClass('is-open').attr('aria-expanded', 'false');
        jQuery(document).off('click.em-dp-' + this.uid);
    }

    _fmt(d) {
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }

    _updateLabel() {
        if (!this.selected) {
            this.$trigger.removeClass('has-value');
            this.$trigger.find('.em-date-label').text('Scegli una data');
            return;
        }
        const mm = ['gen', 'feb', 'mar', 'apr', 'mag', 'giu', 'lug', 'ago', 'set', 'ott', 'nov', 'dic'];
        this.$trigger.addClass('has-value');
        this.$trigger.find('.em-date-label').text(`${this.selected.getDate()} ${mm[this.selected.getMonth()]} ${this.selected.getFullYear()}`);
    }

    _buildHtml() {
        const days = ['Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa', 'Do'];
        const wd = days.map((d) => `<span class="em-dp-weekday">${d}</span>`).join('');
        return `
<div class="em-dp">
    <div class="em-dp-header">
        <span class="em-dp-month-label"></span>
        <div class="em-dp-nav">
            <button type="button" class="em-dp-prev" aria-label="Mese precedente">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <button type="button" class="em-dp-next" aria-label="Mese successivo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
    </div>
    <div class="em-dp-weekdays">${wd}</div>
    <div class="em-dp-days"></div>
</div>`;
    }

    _renderDays() {
        if (!this.$dp) return;

        const months = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
        this.$dp.find('.em-dp-month-label').text(`${months[this.viewMonth]} ${this.viewYear}`);

        const today = new Date(); today.setHours(0, 0, 0, 0);
        const firstDay = new Date(this.viewYear, this.viewMonth, 1);
        let startDow = firstDay.getDay();
        startDow = startDow === 0 ? 6 : startDow - 1;
        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        const selectedTs = this.selected ? this.selected.getTime() : null;

        let html = '';
        for (let i = 0; i < startDow; i++) html += '<span class="em-dp-day is-empty"></span>';

        for (let day = 1; day <= daysInMonth; day++) {
            const d = new Date(this.viewYear, this.viewMonth, day);
            d.setHours(0, 0, 0, 0);
            const ts = d.getTime();
            const cl = ['em-dp-day'];
            if (ts === today.getTime()) cl.push('is-today');
            if (ts === selectedTs) cl.push('is-selected');
            html += `<button type="button" class="${cl.join(' ')}" data-ts="${ts}">${day}</button>`;
        }

        this.$dp.find('.em-dp-days').html(html);
    }

    _bindEvents() {
        this.$dp.on('click', (e) => e.stopPropagation());

        this.$dp.on('click', '.em-dp-prev', (e) => {
            e.stopPropagation();
            if (--this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; }
            this._renderDays();
        });

        this.$dp.on('click', '.em-dp-next', (e) => {
            e.stopPropagation();
            if (++this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; }
            this._renderDays();
        });

        this.$dp.on('click', '.em-dp-day:not(.is-empty)', (e) => {
            e.stopPropagation();
            const ts = parseInt(jQuery(e.currentTarget).data('ts'), 10);
            this.selected = new Date(ts);
            this.$input.val(this._fmt(this.selected)).trigger('change');
            this._updateLabel();
            this.close();
        });
    }
}

class EventsManagerHandler extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        return {
            selectors: {
                form: '.em-modern-form',
                venueSelect: '.em-venue-select',
                organizerSelect: '.em-organizer-select',
                venueCreator: '#em-inline-venue-creator',
                organizerCreator: '#em-inline-organizer-creator',
                allDaySwitch: '.em-all-day-switch',
                recurringSwitch: '.em-recurring-switch',
                recurringDetails: '#em-recurring-details',
                timeFields: '.em-time-field-group',
                categorySelect: '.em-category-select',
                dropzone: '.em-dropzone',
                endDateGroup: '#em_end_date_group',
                startDate: '#em_event_start_date',
                patternBtn: '.em-pattern-btn',
                patternInput: '#em_series_pattern',
                weeklyDaysWrapper: '#em_weekly_days_wrapper',
                seriesUntilWrapper: '#em_series_until_wrapper',
                dayChip: '.em-day-chip',
                seriesUntil: '#em_series_until',
                previewList: '#em_preview_list',
                seriesDatesInput: '#em_series_dates',
                addCustomDate: '#em_add_custom_date',
                addCustomDateBtn: '#em_add_custom_date_btn'
            }
        };
    }
    getDefaultElements() {
        return {
            $form: this.$element.find(this.getSettings('selectors').form),
            $venueSelect: this.$element.find(this.getSettings('selectors').venueSelect),
            $organizerSelect: this.$element.find(this.getSettings('selectors').organizerSelect),
            $venueCreator: this.$element.find(this.getSettings('selectors').venueCreator),
            $organizerCreator: this.$element.find(this.getSettings('selectors').organizerCreator),
            $allDaySwitch: this.$element.find(this.getSettings('selectors').allDaySwitch),
            $recurringSwitch: this.$element.find(this.getSettings('selectors').recurringSwitch),
            $recurringDetails: this.$element.find(this.getSettings('selectors').recurringDetails),
            $timeFields: this.$element.find(this.getSettings('selectors').timeFields),
            $categorySelect: this.$element.find(this.getSettings('selectors').categorySelect),
            $dropzone: this.$element.find(this.getSettings('selectors').dropzone),
            $endDateGroup: this.$element.find(this.getSettings('selectors').endDateGroup),
            $startDate: this.$element.find(this.getSettings('selectors').startDate),
            $patternBtns: this.$element.find(this.getSettings('selectors').patternBtn),
            $patternInput: this.$element.find(this.getSettings('selectors').patternInput),
            $weeklyDaysWrapper: this.$element.find(this.getSettings('selectors').weeklyDaysWrapper),
            $seriesUntilWrapper: this.$element.find(this.getSettings('selectors').seriesUntilWrapper),
            $dayChips: this.$element.find(this.getSettings('selectors').dayChip),
            $seriesUntil: this.$element.find(this.getSettings('selectors').seriesUntil),
            $previewList: this.$element.find(this.getSettings('selectors').previewList),
            $seriesDatesInput: this.$element.find(this.getSettings('selectors').seriesDatesInput),
            $addCustomDate: this.$element.find(this.getSettings('selectors').addCustomDate),
            $addCustomDateBtn: this.$element.find(this.getSettings('selectors').addCustomDateBtn)
        };
    }
    bindEvents() {
        this.elements.$venueSelect.on('change', (e) => {
            if (jQuery(e.currentTarget).val() === '__create_new__') {
                this.elements.$venueCreator.removeClass('em-hidden');
                this.elements.$venueCreator.find('input[name="new_venue_title"]').attr('required', 'required');
            } else {
                this.elements.$venueCreator.addClass('em-hidden');
                this.elements.$venueCreator.find('input[name="new_venue_title"]').removeAttr('required');
            }
        });

        this.elements.$organizerSelect.on('change', (e) => {
            if (jQuery(e.currentTarget).val() === '__create_new__') {
                this.elements.$organizerCreator.removeClass('em-hidden');
                this.elements.$organizerCreator.find('input[name="new_organizer_title"]').attr('required', 'required');
            } else {
                this.elements.$organizerCreator.addClass('em-hidden');
                this.elements.$organizerCreator.find('input[name="new_organizer_title"]').removeAttr('required');
            }
        });

        const toggleTimeFields = () => {
            if (this.elements.$allDaySwitch.is(':checked')) {
                this.elements.$timeFields.addClass('em-hidden');
            } else {
                this.elements.$timeFields.removeClass('em-hidden');
            }
        };

        const toggleRecurringFields = () => {
            const on = this.elements.$recurringSwitch.is(':checked');
            this.elements.$recurringDetails.toggleClass('em-hidden', !on);
            // Con più date, ogni occorrenza finisce lo stesso giorno (solo l'ora
            // fine conta): il campo "Data Fine" non serve e, se nascosto senza
            // togliere required, bloccherebbe il submit.
            this.elements.$endDateGroup.toggleClass('em-hidden', on);
            this.elements.$endDateGroup.find('input').prop('required', !on);
            if (on) {
                this.recalcSeriesPreview();
            }
        };

        this.elements.$allDaySwitch.on('change', toggleTimeFields);
        this.elements.$recurringSwitch.on('change', toggleRecurringFields);

        toggleTimeFields(); // Run on load
        toggleRecurringFields(); // Run on load

        this.seriesDatesCache = [];
        this.initSeriesPreview();
        this.initDatePickers();

        this.initCategoryPicker();
        this.initImageDropzones();
        this.scrollActiveSidebarLinkIntoView();
    }

    // --------------------------------------------------------------------
    // Campi data: stesso trigger + calendario popup della barra di ricerca
    // --------------------------------------------------------------------
    initDatePickers() {
        this.$element.find('.em-date-trigger').each((_, el) => {
            const $trigger = jQuery(el);
            const $input = this.$element.find('#' + $trigger.data('for'));
            if ($input.length) {
                new EmDatePicker($trigger, $input);
            }
        });
    }

    // --------------------------------------------------------------------
    // Anteprima live delle date generate per un evento con più date
    // --------------------------------------------------------------------
    initSeriesPreview() {
        this.elements.$patternBtns.on('click', (e) => {
            this.elements.$patternBtns.removeClass('active');
            const $btn = jQuery(e.currentTarget);
            $btn.addClass('active');
            const pattern = $btn.data('pattern');
            this.elements.$patternInput.val(pattern);
            this.elements.$weeklyDaysWrapper.toggleClass('em-hidden', pattern !== 'weekly');
            // "Date libere": nessun range da ripetere, solo il campo aggiungi data.
            this.elements.$seriesUntilWrapper.toggleClass('em-hidden', pattern === 'custom');
            this.recalcSeriesPreview();
        });

        this.elements.$dayChips.on('click', (e) => {
            jQuery(e.currentTarget).toggleClass('active');
            this.recalcSeriesPreview();
        });

        this.elements.$startDate.on('change', () => this.recalcSeriesPreview());
        this.elements.$seriesUntil.on('change', () => this.recalcSeriesPreview());

        this.elements.$addCustomDateBtn.on('click', () => {
            const iso = this.elements.$addCustomDate.val();
            if (!iso) return;
            if (!this.seriesDatesCache.find((d) => d.date === iso)) {
                this.seriesDatesCache.push({ date: iso, excluded: false, custom: true });
                this.seriesDatesCache.sort((a, b) => a.date.localeCompare(b.date));
                this.renderSeriesPreview();
            }
            this.elements.$addCustomDate.val('');
        });

        this.elements.$previewList.on('click', '.em-preview-x, .em-preview-restore', (e) => {
            const idx = jQuery(e.currentTarget).data('idx');
            this.seriesDatesCache[idx].excluded = jQuery(e.currentTarget).hasClass('em-preview-x');
            this.renderSeriesPreview();
        });
    }

    recalcSeriesPreview() {
        const pattern = this.elements.$patternInput.val();
        const start = this.elements.$startDate.val();
        const until = this.elements.$seriesUntil.val();
        const days = this.elements.$dayChips.filter('.active').map((_, el) => jQuery(el).data('day')).get();

        const generated = [];
        if (pattern !== 'custom' && start && until && start <= until) {
            let cur = new Date(start + 'T00:00:00');
            const end = new Date(until + 'T00:00:00');
            const oneDay = 86400000;
            while (cur <= end) {
                // Niente toISOString(): converte in UTC e in fusi avanti su UTC
                // (es. Italia) sposta la data indietro di un giorno.
                const iso = cur.getFullYear() + '-'
                    + String(cur.getMonth() + 1).padStart(2, '0') + '-'
                    + String(cur.getDate()).padStart(2, '0');
                const dow = cur.getDay() === 0 ? 7 : cur.getDay(); // 1=Lun ... 7=Dom
                const include = pattern === 'daily' || (pattern === 'weekly' && days.includes(dow));
                if (include) {
                    const existing = this.seriesDatesCache.find((d) => d.date === iso);
                    generated.push(existing ? { ...existing } : { date: iso, excluded: false, custom: false });
                }
                cur = new Date(cur.getTime() + oneDay);
            }
        }

        // Le date aggiunte a mano restano anche se fuori dal range/pattern corrente.
        this.seriesDatesCache.filter((d) => d.custom).forEach((d) => {
            if (!generated.find((g) => g.date === d.date)) generated.push({ ...d });
        });

        generated.sort((a, b) => a.date.localeCompare(b.date));
        this.seriesDatesCache = generated;
        this.renderSeriesPreview();
    }

    renderSeriesPreview() {
        this.elements.$seriesDatesInput.val(JSON.stringify(this.seriesDatesCache));

        if (!this.seriesDatesCache.length) {
            this.elements.$previewList.html('<p class="em-field-help">Nessuna data generata.</p>');
            return;
        }

        const html = this.seriesDatesCache.map((d, idx) => {
            const label = new Date(d.date + 'T00:00:00').toLocaleDateString('it-IT', {
                weekday: 'long', day: 'numeric', month: 'short'
            });
            const badges = (d.custom ? '<span class="em-badge-extra">extra</span>' : '')
                + (d.excluded ? '<span class="em-badge-exc">eccezione</span>' : '');
            const btn = d.excluded
                ? `<button type="button" class="em-preview-restore" data-idx="${idx}">Ripristina</button>`
                : `<button type="button" class="em-preview-x" data-idx="${idx}">&times;</button>`;
            return `<div class="em-preview-item${d.excluded ? ' excluded' : ''}">`
                + `<span>${label}${badges}</span>${btn}</div>`;
        }).join('');

        this.elements.$previewList.html(html);
    }

    scrollActiveSidebarLinkIntoView() {
        const sidebarEl = this.$element.find('.em-portal-sidebar')[0];
        const activeEl = this.$element.find('.em-portal-sidebar-link.is-active')[0];

        if (!sidebarEl || !activeEl) {
            return;
        }

        const targetScroll = activeEl.offsetLeft - (sidebarEl.clientWidth / 2) + (activeEl.clientWidth / 2);
        sidebarEl.scrollLeft = Math.max(0, targetScroll);
    }

    initImageDropzones() {
        this.elements.$dropzone.each(function () {
            const $zone = jQuery(this);
            if ($zone.data('emDropzoneInit')) {
                return;
            }
            $zone.data('emDropzoneInit', true);

            const $input = $zone.find('.em-dropzone-input');
            const $preview = $zone.find('.em-dropzone-preview');
            const $previewImg = $preview.find('img');
            const $empty = $zone.find('.em-dropzone-empty');

            const showPreview = (src) => {
                $previewImg.attr('src', src);
                $preview.show();
                $empty.hide();
            };

            const showEmpty = () => {
                $previewImg.attr('src', '');
                $preview.hide();
                $empty.show();
            };

            $input.on('change', function () {
                const file = this.files && this.files[0];
                if (!file) {
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => showPreview(e.target.result);
                reader.readAsDataURL(file);
            });

            $zone.on('dragenter dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $zone.addClass('is-dragover');
            });

            $zone.on('dragleave drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $zone.removeClass('is-dragover');
            });

            $zone.on('click', '.em-dropzone-remove', (e) => {
                e.preventDefault();
                e.stopPropagation();
                $input.val('');
                showEmpty();
            });
        });
    }

    initCategoryPicker() {
        const $form = this.elements.$form;

        this.elements.$categorySelect.each(function () {
            const $select = jQuery(this);
            if ($select.data('emPickerInit')) {
                return;
            }
            $select.data('emPickerInit', true);

            const $wrap = jQuery('<div class="em-category-picker"></div>');
            const $tags = jQuery('<div class="em-category-tags"></div>');
            const $search = jQuery('<input type="text" class="em-category-search" autocomplete="off">')
                .attr('placeholder', $select.find('option').length ? 'Cerca categoria...' : '');
            const $dropdown = jQuery('<div class="em-category-dropdown em-hidden"></div>');
            const $error = jQuery('<small class="em-field-error em-hidden"></small>').text('Seleziona almeno una categoria.');

            $tags.append($search);
            $wrap.append($tags).append($dropdown);
            $select.addClass('em-hidden').before($wrap).after($error);

            const buildDropdown = () => {
                $dropdown.empty();
                $select.find('option').each(function () {
                    const $opt = jQuery(this);
                    if ($opt.prop('selected')) {
                        return;
                    }
                    jQuery('<div class="em-category-option"></div>')
                        .attr('data-value', $opt.val())
                        .text($opt.text().trim())
                        .appendTo($dropdown);
                });
                if (!$dropdown.children().length) {
                    jQuery('<div class="em-category-empty"></div>').text('Nessuna categoria trovata.').appendTo($dropdown);
                }
            };

            const renderTags = () => {
                $tags.find('.em-category-pill').remove();
                $select.find('option:selected').each(function () {
                    const $opt = jQuery(this);
                    const $pill = jQuery('<span class="em-category-pill"></span>').text($opt.text().trim());
                    jQuery('<button type="button" class="em-category-pill-remove" aria-label="Rimuovi categoria">&times;</button>')
                        .attr('data-value', $opt.val())
                        .appendTo($pill);
                    $pill.insertBefore($search);
                });
            };

            const filterDropdown = (term) => {
                const needle = term.trim().toLowerCase();
                $dropdown.find('.em-category-option').each(function () {
                    const match = jQuery(this).text().toLowerCase().indexOf(needle) !== -1;
                    jQuery(this).toggleClass('em-hidden', needle !== '' && !match);
                });
            };

            const closeDropdown = () => $dropdown.addClass('em-hidden');
            const openDropdown = () => $dropdown.removeClass('em-hidden');

            $wrap.on('click', '.em-category-option:not(.em-hidden)', function () {
                $select.find('option[value="' + jQuery(this).attr('data-value') + '"]').prop('selected', true);
                $select.trigger('change');
                renderTags();
                buildDropdown();
                $search.val('').trigger('focus');
                $error.addClass('em-hidden');
            });

            $wrap.on('click', '.em-category-pill-remove', function (e) {
                e.stopPropagation();
                $select.find('option[value="' + jQuery(this).attr('data-value') + '"]').prop('selected', false);
                $select.trigger('change');
                renderTags();
                buildDropdown();
            });

            $search.on('focus', openDropdown);
            $search.on('input', function () {
                filterDropdown(jQuery(this).val());
                openDropdown();
            });
            $tags.on('click', () => $search.trigger('focus'));

            jQuery(document).on('click', (e) => {
                if (!jQuery(e.target).closest($wrap).length) {
                    closeDropdown();
                }
            });

            renderTags();
            buildDropdown();
        });

        if ($form.length) {
            $form.on('submit', (e) => {
                this.elements.$categorySelect.each(function () {
                    const $select = jQuery(this);
                    const $error = $select.next('.em-field-error');
                    if ($select.prop('required') && $select.find('option:selected').length === 0) {
                        e.preventDefault();
                        $error.removeClass('em-hidden');
                        $select.prev('.em-category-picker').find('.em-category-search').trigger('focus');
                    } else {
                        $error.addClass('em-hidden');
                    }
                });
            });
        }
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(EventsManagerHandler, { $element });
    };
    elementorFrontend.hooks.addAction('frontend/element_ready/open_events_manager.default', addHandler);
});
