// Widget "Community Auth" — toggle Accedi/Registrati senza reload pagina.
// Nessuna dipendenza da elementor-frontend/jQuery: solo due pannelli e due
// pulsanti, non serve altro.
(function () {
    function initCommunityAuth(root) {
        var tabs = root.querySelectorAll('.oe-community-tab');
        var panels = root.querySelectorAll('.oe-community-panel');

        function activate(tabName) {
            tabs.forEach(function (btn) {
                btn.classList.toggle('is-active', btn.dataset.tab === tabName);
            });
            panels.forEach(function (panel) {
                panel.classList.toggle('is-active', panel.dataset.panel === tabName);
            });
        }

        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activate(btn.dataset.tab);
            });
        });

        var initialTab = root.dataset.initialTab || 'accedi';
        var hasInitialPanel = Array.prototype.some.call(panels, function (panel) {
            return panel.dataset.panel === initialTab;
        });
        activate(hasInitialPanel ? initialTab : 'accedi');
    }

    document.querySelectorAll('.oe-community').forEach(initCommunityAuth);

    // reCAPTCHA v3: il token va generato in modo asincrono subito prima
    // dell'invio (ha vita breve), quindi intercettiamo il submit, chiediamo
    // il token a Google, lo mettiamo nel campo nascosto e solo allora
    // inviamo davvero il form.
    function initRecaptchaForm(form) {
        var siteKey = form.dataset.recaptchaSiteKey;
        var tokenField = form.querySelector('.oe-recaptcha-token');
        if (!siteKey || !tokenField) {
            return;
        }

        form.addEventListener('submit', function (e) {
            if (tokenField.value) {
                return; // Token già ottenuto (secondo tentativo di submit).
            }
            e.preventDefault();

            if (typeof grecaptcha === 'undefined') {
                form.submit(); // Script Google non caricato: lascia decidere al server.
                return;
            }

            grecaptcha.ready(function () {
                grecaptcha.execute(siteKey, { action: 'register' }).then(function (token) {
                    tokenField.value = token;
                    form.submit();
                });
            });
        });
    }

    document.querySelectorAll('form[data-recaptcha-site-key]').forEach(initRecaptchaForm);

    // Utente già loggato che riapre la pagina di login: conto alla rovescia
    // e redirect automatico alla dashboard (l'utente può comunque cliccare
    // "Vai subito" o "Esci" prima che scada).
    function initRedirectNotice(el) {
        var url = el.dataset.redirectUrl;
        var seconds = parseInt(el.dataset.redirectSeconds, 10) || 5;
        var counter = el.querySelector('.oe-community-countdown');

        var timer = setInterval(function () {
            seconds--;
            if (counter) {
                counter.textContent = seconds;
            }
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = url;
            }
        }, 1000);
    }

    document.querySelectorAll('.oe-community-alert[data-redirect-url]').forEach(initRedirectNotice);
})();
