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
                timeFields: '.em-time-field-group'
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
            $timeFields: this.$element.find(this.getSettings('selectors').timeFields)
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
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(EventsManagerHandler, { $element });
    };
    elementorFrontend.hooks.addAction('frontend/element_ready/open_events_manager.default', addHandler);
});
