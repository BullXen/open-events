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
                dropzone: '.em-dropzone'
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
            $dropzone: this.$element.find(this.getSettings('selectors').dropzone)
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
            if (this.elements.$recurringSwitch.is(':checked')) {
                this.elements.$recurringDetails.removeClass('em-hidden');
            } else {
                this.elements.$recurringDetails.addClass('em-hidden');
            }
        };

        this.elements.$allDaySwitch.on('change', toggleTimeFields);
        this.elements.$recurringSwitch.on('change', toggleRecurringFields);

        toggleTimeFields(); // Run on load
        toggleRecurringFields(); // Run on load

        this.initCategoryPicker();
        this.initImageDropzones();
        this.scrollActiveSidebarLinkIntoView();
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
