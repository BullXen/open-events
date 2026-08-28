# Open Events — Documentazione tecnica

> Plugin WordPress standalone (v1.9.0). Aggiunge widget Elementor che trasformano una pagina in un **portale front-end self-service** per gestire eventi/luoghi/organizzatori di **The Events Calendar (TEC)**, più un modulo di autenticazione utenti (Community) e uno di promozione a pagamento via Stripe (Eventi Consigliati).
>
> Documento generato da analisi del codice sorgente (branch `claude/open-events-plugin-docs-184823`). Riferimenti `file:riga` verificati sui file letti; per il dettaglio riga-per-riga più fine vedi il codice sorgente stesso, che resta la fonte di verità.

---

## 1. Panoramica

| | |
|---|---|
| Nome | Open Events |
| Autore | BullXen |
| Versione | 1.9.0 |
| Text domain | `open-events` |
| Namespace PHP | `OpenEvents` |
| Requires WP | 5.8+ |
| Requires PHP | 7.4+ |
| File entry point | [open-events.php](../open-events.php) |

**Cosa fa in una frase**: dà agli utenti loggati un portale front-end (fuori da wp-admin) per creare/modificare eventi, luoghi e organizzatori di The Events Calendar, con moderazione admin, login/registrazione custom (incl. Google/Facebook), una barra di ricerca eventi alternativa a quella nativa TEC, e un modulo di promozione a pagamento (Stripe) per mettere in evidenza un evento.

**Dove gira**: qualunque pagina del sito su cui viene trascinato uno dei 3 widget Elementor del plugin. I moduli Community, Featured Events e il core (`admin-settings.php`) sono però caricati **sempre**, su ogni pagina del sito (non solo dove c'è il widget) — vedi [open-events.php:21-57](../open-events.php#L21-L57): servono ai filtri `login_url`/`authenticate`, agli handler `admin-post.php` e al webhook REST di Stripe, che devono funzionare indipendentemente da dove Elementor carica i widget.

---

## 2. Dipendenze

| Plugin | Obbligatorio? | Come viene verificato |
|---|---|---|
| **Elementor** | Sì, hard-block | `did_action('elementor/loaded')` in `open_events_is_elementor_active()` ([open-events.php:240-242](../open-events.php#L240)); se assente, i widget non vengono registrati e appare un admin notice ([open-events.php:244-248](../open-events.php#L244)) |
| **The Events Calendar (TEC)** | Sì, ma **nessun controllo esplicito** — il plugin assume che i CPT `tribe_events`/`tribe_venue`/`tribe_organizer` esistano già. L'unico check condizionale è su una classe interna di TEC 6+ (`\TEC\Events\Custom_Tables\V1\Updates\Events`, [admin-settings.php:266](../includes/core/admin-settings.php#L266)) per capire se usare le custom tables (TEC 6+) o il vecchio meccanismo a meta (`tribe_events_update_meta`, TEC 5.x) | — |
| **Events Calendar Pro** | Opzionale | Se attivo, il plugin aggancia hook aggiuntivi per le sue viste (photo, summary, map, week) — vedi §8 |
| **Stripe** | Opzionale (solo se si usa "Eventi Consigliati") | Nessun SDK/libreria: tutte le chiamate sono `wp_remote_post`/`wp_remote_get` verso `api.stripe.com` |

Nessun `register_activation_hook`/`register_deactivation_hook`/`register_uninstall_hook`: le opzioni restano salvate anche a plugin disattivato, nessuna pulizia automatica al disinstall.

---

## 3. Struttura del plugin

```
open-events.php                    entry point: bootstrap, widget/asset registration, boost eventi featured/consigliati sul calendario pubblico TEC

includes/
├── core/
│   └── admin-settings.php         impostazioni globali + pagina "Open Events" + helper di sync con TEC (custom tables)
├── events-manager/                IL PORTALE: widget "Front-end Events Manager"
│   ├── class-widget-events-manager.php   (1469 righe) — classe Elementor + tutta la logica CRUD/routing del portale
│   └── templates/
│       ├── hub.php                dashboard home
│       ├── event-form.php         form Inserisci/Modifica Evento|Luogo|Organizzatore
│       ├── items-list.php         elenco "I Miei/Tutti gli Eventi|Luoghi|Organizzatori"
│       ├── users.php              sezione Utenti (solo admin)
│       ├── stats.php              sezione Statistiche (solo admin)
│       ├── consigliati.php        sezione Consigliati/pagamenti (solo admin)
│       └── profile.php            modifica profilo utente corrente
├── events-search/                 widget pubblico "Ricerca Eventi" (barra di ricerca custom + AJAX)
│   ├── events-search.php          query, filtri, rendering card, handler AJAX
│   └── class-widget-events-search.php    classe Elementor
├── events-slide/                  widget "Slide Eventi Consigliati" (carousel hero)
│   ├── events-slide.php           query (riusa quella di events-search), rendering slide, shortcode, auto-home
│   └── class-widget-events-slide.php    classe Elementor
├── community/                     login/registrazione/OAuth/email
│   ├── community-settings.php     pagina impostazioni "Open Events > Community"
│   ├── community-auth.php         login/registrazione classici + filtri login_url/authenticate ecc.
│   ├── community-oauth.php        OAuth "fatto in casa" Google + Facebook
│   ├── community-emails.php       invio email transazionali (ascolta gli hook custom del plugin)
│   ├── class-widget-community-auth.php   widget Elementor "Community Auth"
│   └── templates/auth-form.php    form Accedi/Registrati
└── featured-events/                "Eventi Consigliati" — promozione a pagamento via Stripe
    ├── featured-events-settings.php   pagina impostazioni "Open Events > Eventi Consigliati"
    ├── featured-events-slots.php      logica slot settimanali + finestre di visibilità badge
    ├── featured-events-stripe.php     Checkout Session Stripe (creazione/avvio/ricevute)
    └── featured-events-webhook.php    endpoint REST che riceve la conferma di pagamento da Stripe

assets/                           CSS/JS dei widget (community, events-manager, events-search, events-slide)
```

Ogni cartella ha un `index.php` vuoto (silence is golden, pattern standard WP per impedire il directory listing).

---

## 4. Architettura dati

**Nessun Custom Post Type proprio, nessuna tabella custom via `dbDelta`.** Il plugin si appoggia interamente sui CPT che The Events Calendar già registra:

- `tribe_events` (evento)
- `tribe_venue` (luogo)
- `tribe_organizer` (organizzatore)

Il plugin **estende** questi CPT scrivendo:
1. i meta standard di TEC (`_EventStartDate`, `_VenueCity`, ecc.);
2. propri meta custom con prefisso `_illi_featured_*` (modulo Consigliati), `_oe_*` (varie), `_tribe_featured` (flag TEC nativo riusato per "in primo piano" manuale).

### 4.1 Opzioni globali (`wp_options`)

| option_name | Modulo | Contenuto |
|---|---|---|
| `open_events_default_event_status` | core | draft / pending / publish — stato assegnato ai nuovi eventi utente |
| `open_events_description_editor` | core | classic / visual |
| `open_events_available_cities` | core | array comuni (whitelist dropdown) |
| `open_events_featured_label` | core | testo badge "in primo piano" |
| `open_events_featured_limit` | core | max eventi in primo piano contemporanei |
| `open_events_published_by_display` | core | organizer / username / email |
| `open_events_venue_visibility` | core | all / own |
| `open_events_enable_all_day_event` | core | on/off campo "Evento Giornaliero" nel form |
| `open_events_enable_recurring_event` | core | on/off campo "Evento Ricorrente" nel form |
| `open_events_enable_past_dates` | core | on/off selezione date passate nei calendari |
| `open_events_community_settings` | community | **array unico** con tutte le impostazioni Community (vedi §5.4) |
| `open_events_community_avatar_default_migrated` | community | flag di migrazione one-shot |
| `open_events_featured_events_settings` | featured-events | **array unico** con chiavi Stripe, prezzo, slot/settimana, colore badge, vantaggi |

### 4.2 Meta custom introdotti dal plugin (su post `tribe_events`, salvo indicazione)

| meta_key | Scritto da | Significato |
|---|---|---|
| `_oe_series_id` | events-manager | collega i post "figli" di un evento ricorrente al post primario (ID del primo) |
| `_oe_card_views` | events-search | contatore visualizzazioni scheda evento (incrementato ad ogni render card) |
| `_illi_featured_status` | featured-events | `none` / `pending_payment` / `paid` / `expired` |
| `_illi_featured_event_dates`, `_illi_featured_first_date` | featured-events | data(e) su cui è stato acquistato lo slot settimanale |
| `_illi_featured_payment_id` | featured-events | ID Checkout Session Stripe |
| `_illi_featured_amount` | featured-events | importo pagato (centesimi) |
| `_illi_featured_paid_at` | featured-events | timestamp MySQL pagamento confermato |
| `_illi_featured_receipt_url` | featured-events | URL ricevuta Stripe (cache) |

Meta utente custom: `oe_city`, `oe_google_id`, `oe_facebook_id`, `oe_avatar_url` (community); `_oe_hub_seen_consigliati` (ultimo accesso admin a sezione Consigliati, per il badge "nuovi").

Transient: `oe_oauth_state_{token}` (community-oauth, TTL 10 minuti, anti-CSRF sul flusso OAuth).

### 4.3 Perché tante query dirette a `$wpdb` invece di `WP_Query`/`get_posts()`

Motivo ricorrente, documentato nei commenti del codice e nel changelog (v1.0.5/1.0.6/1.4.5/1.5.2): **The Events Calendar forza `post_status = 'publish'`** a livello SQL (`posts_where`/`posts_clauses`) su **ogni** query con `post_type=tribe_events`, indipendentemente da cosa viene passato a `WP_Query`. Questo rende invisibili via query normale gli eventi in bozza/pending — necessari invece per "I Miei Eventi", conteggi badge, ricerca fratelli di una serie ricorrente, statistiche, elenco Consigliati. Il plugin bypassa quindi `WP_Query` con SQL diretto in questi punti specifici (elencati nel dettaglio in §6 e nella tabella hook di TEC in §8).

---

## 5. I moduli

### 5.1 Core — `includes/core/admin-settings.php`

Pagina admin **Open Events** (menu proprio, agganciato subito sotto "Eventi" di TEC — [admin-settings.php:20-58](../includes/core/admin-settings.php#L20)), sezione "Impostazioni": tutte le opzioni della tabella §4.1. Nessuna Settings API, form con nonce manuale (`open_events_save_settings`).

Espone anche due funzioni "collante" con TEC, richiamate da events-manager dopo ogni salvataggio:

- **`open_events_force_post_status($post_id, $new_status)`** — bypassa `wp_update_post()`/`wp_trash_post()` (che su `tribe_events` a volte falliscono silenziosamente per via del mapping capability non-standard di TEC) scrivendo `post_status` direttamente via `$wpdb->update()`, poi rilancia manualmente `wp_transition_post_status()` + `save_post`/`save_post_{post_type}`.
- **`open_events_sync_event_custom_tables($post_id)`** — calcola i meta UTC/timezone/durata (`_EventTimezone`, `_EventStartDateUTC`, `_EventEndDateUTC`, `_EventDuration`) richiesti dalle **custom tables di TEC 6+** e forza la ricostruzione delle occorrenze (`tribe(...)->update($post_id)` se TEC 6+, altrimenti `do_action('tribe_events_update_meta', ...)` per TEC 5.x). Senza questo passaggio un evento salvato dal portale risulta `publish` in `wp_posts` ma **senza occorrenza generata**, quindi invisibile nel calendario pubblico (bug storico risolto in v1.0.8, vedi changelog).

### 5.2 Events Manager — `includes/events-manager/`

Il cuore del plugin: widget Elementor `open_events_manager` ("Front-end Events Manager"). Un'unica classe (`Widget_Events_Manager`) fa da **router** leggendo la query string e includendo il template giusto, con lo scope PHP condiviso (i template non sono isolati, leggono variabili locali del metodo `render()`).

**Controlli Elementor**: modalità azione (hub / dashboard / add / edit), tipo contenuto (evento/luogo/organizzatore), URL di redirect, repeater "Link personalizzati Hub", stile (larghezza max, colori, padding).

**Routing via query string** (nessun endpoint REST/AJAX per il CRUD — tutto è rendering server-side + `admin-post`-style GET/POST sulla stessa pagina):

| Query string | Sezione | Template |
|---|---|---|
| *(nessuna)* | Dashboard | `hub.php` |
| `?view=tribe_events\|tribe_venue\|tribe_organizer` | Liste "I Miei/Tutti gli X" | `items-list.php` |
| `?action=add&type=...` / `?edit_id=...` | Form inserisci/modifica | `event-form.php` |
| `?view=users` (solo admin) | Gestione utenti | `users.php` |
| `?view=stats` (solo admin) | Statistiche | `stats.php` |
| `?view=consigliati` (solo admin) | Pagamenti Eventi Consigliati | `consigliati.php` |
| `?view=profile` | Profilo utente | `profile.php` |
| `?em_action=publish\|delete&post_id=&_wpnonce=` | Azioni rapide | — (redirect) |
| `?oe_featured_checkout=start&post_id=&_wpnonce=` | Avvia pagamento Stripe | — (redirect a Stripe) |

**Sidebar** ([class-widget-events-manager.php:247-288](../includes/events-manager/class-widget-events-manager.php#L247)): sempre Dashboard/Eventi/Luoghi/Organizzatori/Profilo; Utenti/Statistiche/Consigliati solo se `current_user_can('manage_options')`.

**Permessi**:
- un utente normale vede/modifica solo i propri contenuti; un admin vede tutto (raggruppato "tuoi" / "altri").
- **una volta che un evento passa a `publish`, solo un admin può più modificarlo** (blocco introdotto in v1.7.6): il link "Modifica" scompare da "I Miei Eventi" per l'autore originale.
- ogni salvataggio di un utente **non admin** (creazione o modifica) forza lo stato al default configurato (`open_events_get_default_event_status()`, di norma `pending`) — non esiste un "salva bozza personale" indipendente.

**Sezione Utenti** (solo admin): cambio ruolo (`current_user_can('promote_users')`), eliminazione con riassegnazione automatica dei contenuti all'admin corrente (`wp_delete_user`). Protezioni hardcoded: non puoi modificare/eliminare te stesso, non puoi eliminare un altro admin, non puoi togliere il ruolo admin se è l'ultimo amministratore rimasto.

**Sezione Statistiche** (solo admin): conteggio pubblicati/online/passati per i 3 CPT, utenti iscritti, somma e classifica top-5 di `_oe_card_views`.

Dettaglio completo del flusso di salvataggio evento → §6.

### 5.3 Events Search — `includes/events-search/`

Widget pubblico `open_events_search` ("Ricerca Eventi"): barra di ricerca alternativa a quella nativa di TEC, con card griglia/lista e aggiornamento **via AJAX** (`admin-ajax.php`, azione `open_events_search`, nonce `open_events_search`), accessibile a loggati e anonimi.

Filtri: testo libero, comune (basato sui venue collegati), categoria (`tribe_events_cat`), data (Prossimi/Oggi/Questa settimana/Mese/intervallo custom). Query principale via `WP_Query` (non SQL diretto, a differenza di events-manager, perché qui si cercano solo eventi già `publish`); l'ordinamento "featured in cima" è invece fatto in **PHP con `usort()`** dopo la query, perché durante una richiesta AJAX `is_admin()` è `true` e il filtro `posts_clauses` che pinna i featured (vedi §8) non si applica in quel contesto.

Effetto collaterale non ovvio: ogni volta che una card evento viene renderizzata (sia al primo caricamento sia via AJAX) il plugin incrementa `_oe_card_views` — nessuna deduplica per sessione/IP, quindi un refresh ripetuto della pagina gonfia il contatore.

Controlli Elementor: quali filtri mostrare, colore accento, numero colonne, aspect ratio card, numero massimo eventi (cap assoluto 48), tutta la parte colori/stile card e datepicker.

### 5.3bis Events Slide — `includes/events-slide/` *(v1.10.0)*

Widget pubblico `open_events_slide` ("Slide Eventi Consigliati"): carousel hero (una slide grande per volta, immagine a piena larghezza con testo in overlay) con priorità agli eventi Consigliati/in primo piano, poi ai prossimi eventi per data. **Non ha una query propria**: riusa deliberatamente `open_events_search_query()` di `events-search.php` (stesso ordinamento boosted-first già visto in §5.3) per non duplicare la stessa logica due volte — solo il markup della singola slide è diverso da quello della card a griglia.

Tre modi d'uso, tutti supportati dallo stesso file `events-slide.php`:
1. **Widget Elementor** — controlli: numero massimo eventi (1-20), solo Consigliati/in primo piano (switcher), messaggio di fallback, autoplay/velocità/loop, altezza slide (responsive), opacità overlay, colori testo/accento, bordo arrotondato.
2. **Shortcode** `[open_events_slide max_items="6" only_featured="false"]` — utilizzabile in qualunque contenuto, anche senza Elementor.
3. **Auto-inserimento in homepage** — opzione in Open Events → Impostazioni ("Slide eventi in homepage" + numero eventi), agganciata a `the_content` con guard `is_front_page() && in_the_loop() && is_main_query()` per non scattare su loop secondari/widget.

Carousel scritto **senza librerie esterne** (niente Swiper/CDN, coerente con la scelta già fatta altrove nel plugin per Stripe/OAuth): `assets/events-slide/events-slide.js`, ~100 righe, `translateX` sul contenitore + frecce/dots/autoplay/swipe touch. Asset CSS/JS registrati sempre (`open_events_register_assets()` in open-events.php) ma **enqueued condizionalmente**: Elementor li carica da solo per il widget (via `get_style_depends()`/`get_script_depends()`), mentre shortcode e auto-home sono coperti da un controllo dedicato (`open_events_slide_maybe_enqueue_assets()`, hook `wp_enqueue_scripts` priorità 20) che verifica `has_shortcode()` sul post corrente o l'opzione auto-home.

### 5.4 Community — `includes/community/`

Sistema di login/registrazione **alternativo** a quello nativo WordPress, con OAuth "fatto in casa" (nessuna libreria/SDK):

- **Login/registrazione classici** ([community-auth.php](../includes/community/community-auth.php)): endpoint `admin-post.php?action=oe_community_register` / `oe_community_login`, entrambi con varianti `nopriv`. Registrazione protetta da 3 livelli anti-bot in sequenza (v1.8.6/v1.9.0): **honeypot** (campo nascosto `website`), **time-trap** (submit troppo rapido, <3s), **reCAPTCHA v3** (invisibile, soglia punteggio configurabile) — livelli aggiuntivi, non sostitutivi l'uno dell'altro.
- I filtri nativi WP `login_url`/`register_url`/`lostpassword_url`/`login_redirect`/`authenticate` vengono rimappati per usare le pagine Community configurate al posto di `wp-login.php`; `authenticate` a priorità 5 permette anche il login con indirizzo email oltre allo username.
- **OAuth Google/Facebook** ([community-oauth.php](../includes/community/community-oauth.php)): Authorization Code flow implementato a mano con `wp_remote_get`/`wp_remote_post`. Se l'email del provider corrisponde a un utente già esistente, l'account social viene **collegato** invece di crearne uno duplicato. Import opzionale della foto profilo (filtro `get_avatar_url`).
- **Email transazionali** ([community-emails.php](../includes/community/community-emails.php)): mittente configurabile, 3 template con placeholder (`{nome}`, `{evento_titolo}`, `{evento_link}`, ...), inviate reagendo ai 3 hook custom del plugin (§9). Log diagnostico su `wp_mail_failed` e su ogni tentativo di invio.
- **Widget "Community Auth"** ([class-widget-community-auth.php](../includes/community/class-widget-community-auth.php)): form unico Accedi/Registrati con toggle client-side, tab default configurabile, parametro URL `?tab=registrati`, immagine laterale opzionale, palette colori dedicata. Nell'editor Elementor (dove si è sempre loggati) forza comunque la visualizzazione dei form per poterli stilizzare.

Impostazioni (`open_events_community_settings`, array unico): attiva/disattiva registrazione, campi obbligatori/opzionali/nascosti (nome/cognome/comune), ruolo WP assegnato ai nuovi iscritti, pagine login/registrazione/redirect/password dimenticata, credenziali OAuth Google/Facebook, chiavi + soglia reCAPTCHA v3, import avatar social, mittente ed editor dei 3 template email.

### 5.5 Featured Events ("Eventi Consigliati") — `includes/featured-events/`

Modulo di promozione a pagamento, **integrazione Stripe diretta via REST API** (nessun SDK):

1. Nel form evento, checkbox "Rendi il mio evento Consigliato" → `open_events_featured_start_checkout_flow()` verifica lo slot settimanale disponibile (`max_slots_per_week`, default 3, contati sulla settimana solare della data evento) e crea una Checkout Session Stripe (`open_events_featured_create_checkout_session()`).
2. L'utente paga su Stripe; al ritorno, `admin-post.php?action=oe_featured_checkout_return` è **solo una pagina di transito UX** (redirect con `?oe_featured_checkout=success|cancelled`) — **non conferma il pagamento**.
3. La conferma reale arriva dal **webhook REST** (`POST /wp-json/open-events/v1/stripe-webhook`, unica REST route del plugin), che verifica manualmente la firma HMAC-SHA256 di Stripe (anti-replay 5 minuti) e, su `checkout.session.completed`, scrive `_illi_featured_status=paid` + importo + data pagamento + recupera la ricevuta. Su `checkout.session.expired` libera lo slot solo se era ancora `pending_payment` (non tocca un pagamento già confermato da un webhook precedente).
4. Sezione admin "Consigliati" ([consigliati.php](../includes/events-manager/templates/consigliati.php)): elenco pagamenti con filtro stato, conferma/revoca manuale (per pagamenti gestiti fuori piattaforma), link ricevuta.

**Due finestre di visibilità badge distinte e volutamente diverse** (dettaglio non ovvio, fissato nel changelog v1.7.5-1.7.9):
- `open_events_featured_is_active()` — **badge "Consigliato"** nella dashboard utente e nelle card di ricerca: attivo da **subito dopo il pagamento** fino a fine della settimana solare promossa.
- `open_events_featured_is_public_active()` — **priorità di ordinamento** nel calendario pubblico TEC e nella ricerca: attiva solo negli **ultimi 7 giorni prima dell'inizio evento**, indipendentemente da quando è avvenuto il pagamento (evita che un evento pagato con settimane di anticipo resti "in cima" per tutto quel tempo).

---

## 6. Flusso completo: creazione → modifica → pubblicazione di un evento

```
Utente (front-end, loggato)
  │
  ▼
event-form.php (POST, nonce em_save)
  │
  ▼
Widget_Events_Manager::render()  [class-widget-events-manager.php:1087-1432]
  │
  ├─ 1. Sanitizza titolo/contenuto
  ├─ 2. Se admin: eventuale riassegnazione "assign_to_user" (solo luogo/organizzatore)
  ├─ 3. Valida titolo obbligatorio + limite eventi "in primo piano"
  ├─ 4. wp_insert_post() / wp_update_post()
  │      → stato: default configurato (pending, di norma) se utente NON admin,
  │        qualunque valore scelto se admin
  ├─ 5. Scrive meta TEC: _EventAllDay, _EventStartDate, _EventEndDate,
  │      _EventCost, _EventURL, categoria (tribe_events_cat)
  ├─ 6. Creazione rapida Luogo/Organizzatore ("__create_new__") se richiesta
  │      inline, o collega quello scelto (_EventVenueID / _EventOrganizerID)
  ├─ 7. Rilancia manualmente do_action('save_post_tribe_events', ...)
  │      (WP l'aveva già lanciato troppo presto, PRIMA che i meta sopra
  │      fossero scritti — TEC avrebbe sincronizzato un evento "vuoto")
  ├─ 8. open_events_sync_event_custom_tables($post_id)
  │      → calcola meta UTC/timezone/durata + forza ricostruzione
  │        occorrenze nelle custom tables di TEC 6+ (o hook legacy TEC 5.x)
  ├─ 9. Upload immagine di copertina (media_handle_upload + set_post_thumbnail)
  ├─ 10. Se evento ricorrente/date multiple (_oe_series_id):
  │       clona l'evento per ogni data extra, ripetendo i passi 7-9
  │       per ciascun clone (venue/org/categoria/copertina ereditati)
  ├─ 11. do_action('oe_community_event_submitted', ...) → email admin
  └─ 12. Se "Rendi Consigliato" selezionato → avvia checkout Stripe (§5.5)

Admin (wp-admin o portale, con capability manage_options)
  │
  ▼
"Pubblica" (?em_action=publish&post_id=&_wpnonce=)
  │
  ├─ publish_post_now(): forza slug + post_date a "ora" (altrimenti WP
  │    converte 'publish' in 'future' se il post_date esistente è nel
  │    passato), fallback open_events_force_post_status() se
  │    wp_update_post() fallisce silenziosamente
  ├─ open_events_sync_event_custom_tables() di nuovo (ripara anche eventi
  │    creati prima della fix v1.0.8)
  ├─ do_action('oe_community_event_published', ...) → email all'autore
  └─ Auto-pubblica tutti i "fratelli" della stessa serie ricorrente
       (_oe_series_id) ancora in draft/pending/future
```

Note chiave:
- **Perché due `do_action` manuali** (`save_post_tribe_events` due volte, in fasi diverse): il primo (dopo il salvataggio del post principale) copre il caso singolo evento; se poi la serie ricorrente sposta la data primaria o clona nuovi post, ciascuno rilancia di nuovo l'hook TEC con i propri meta ormai completi.
- **Query dirette `$wpdb`** invece di `WP_Query` compaiono in: conteggio badge "nuovi" (hub), ricerca "fratelli" della serie da auto-pubblicare, statistiche, elenco Consigliati, elenco principale "I Miei/Tutti gli Eventi" — sempre per lo stesso motivo (§4.3).
- Un evento pubblicato **non può più essere modificato dall'autore originale**, solo da un admin (v1.7.6).

---

## 7. Integrazione con Elementor

- 4 widget registrati su `elementor/widgets/register`: `Widget_Events_Manager`, `Widget_Events_Search`, `Widget_Community_Auth`, `Widget_Events_Slide` (v1.10.0), tutti nella categoria custom **"Open Events"**.
- Ogni widget dichiara `get_script_depends()`/`get_style_depends()`; gli script di events-manager e events-search dipendono da `elementor-frontend` (si aspettano l'ambiente frontend di Elementor caricato); lo script di community non ne dipende.
- **Cache "Elementi" di Elementor bypassata per gli utenti loggati** ([open-events.php:39-41](../open-events.php#L39)): quella cache serve HTML identico a tutti i visitatori fino a 24h, il che "congelava" lo stato del portale/login per il primo utente che visitava la pagina (bug risolto in v1.5.1). I visitatori anonimi continuano a beneficiare normalmente della cache.
- Nell'editor/anteprima Elementor (dove si è sempre loggati come admin) il widget Community Auth forza comunque la visualizzazione dei form invece del solo messaggio "sei già loggato", per poterli stilizzare (v1.3.3).
- Se Elementor non è attivo, il plugin non registra nulla e mostra un admin notice — nessun errore fatale.

---

## 8. Integrazione con The Events Calendar

Nessuna dipendenza a livello di Composer/vendor: il plugin usa direttamente i CPT, i meta e (in alcuni punti) le classi interne di TEC.

**CPT e meta letti/scritti**: `tribe_events` (`_EventStartDate`, `_EventEndDate`, `_EventAllDay`, `_EventCost`, `_EventURL`, `_EventVenueID`, `_EventOrganizerID`, `_EventTimezone*`, `_EventStartDateUTC`/`_EventEndDateUTC`, `_EventDuration`, `_tribe_featured`), `tribe_venue` (`_VenueAddress`, `_VenueCity`, `_VenueCountry`, `_VenueZip`), `tribe_organizer` (`_OrganizerPhone`, `_OrganizerEmail`, `_OrganizerWebsite`), tassonomia `tribe_events_cat`.

**Compatibilità TEC 5 vs TEC 6+ (custom tables)**: TEC 6 ha spostato le occorrenze evento in tabelle SQL dedicate (`tec_events`/`tec_occurrences`), generate da un builder interno che richiede i meta UTC/timezone/durata calcolati (§5.1, §6). Il plugin rileva quale versione è attiva via `class_exists('\TEC\Events\Custom_Tables\V1\Updates\Events')` e sceglie il percorso giusto.

**Hook di TEC agganciati dal plugin**:

| Hook TEC | Dove | Scopo |
|---|---|---|
| `posts_clauses` (priorità 100) | [open-events.php:82-121](../open-events.php#L82) | Porta in cima al calendario pubblico gli eventi "in primo piano" (`_tribe_featured=1`) e "Consigliato attivo" (finestra 7gg pre-evento). Riconosce la query dal `post_type` (**non** da `tribe_is_event_query()`, che con TEC 6/Views v2 ritorna sempre `false` per le query del calendario pubblico — bug scoperto e risolto in v1.0.8) |
| `tribe_template_after_include_html:{template}` (10 template diversi, list/day/photo/summary/map/tooltip di TEC core + TEC Pro) | [open-events.php:214-234](../open-events.php#L214) | Aggiunge il testo del badge "in primo piano"/"Consigliato" accanto al titolo evento nelle viste pubbliche a card/lista (TEC di suo mostra solo un'icona con testo screen-reader) |
| `save_post_tribe_events` | rilanciato manualmente da events-manager | Forza TEC a sincronizzare le sue strutture dati **dopo** che il plugin ha scritto tutti i meta (vedi §6) |
| `tribe_events_update_meta` | fallback in `open_events_sync_event_custom_tables()` (solo TEC 5.x) | Equivalente legacy della ricostruzione occorrenze |

**Bypass della query TEC per stati non-`publish`**: vedi §4.3 — qualunque lista che deve mostrare eventi in bozza/pending/futuro non pubblicati usa SQL diretto, perché TEC intercetta e riscrive `post_status` a `publish` su ogni query `WP_Query`/`get_posts` con `post_type=tribe_events`.

---

## 9. Hook custom del plugin (punti di estensione)

Il plugin espone **3 action hook propri**, pensati per disaccoppiare il modulo email dal resto (oggi consumati solo da `community-emails.php`, ma agganciabili da codice esterno — child theme, `mu-plugins`, altri plugin):

| Hook | Parametri | Lanciato da | Significato |
|---|---|---|---|
| `oe_community_user_registered` | `$user_id` (int) | `community-auth.php:145` (registrazione classica), `community-oauth.php:90` (primo login social) | Nuovo utente creato tramite il portale (qualunque metodo) |
| `oe_community_event_submitted` | `$post_id, $event_title, $author_display_name` | `class-widget-events-manager.php:1361` | Un evento è stato salvato/inviato in revisione dal front-end |
| `oe_community_event_published` | `$post_id, $event_title, $author_id` | `class-widget-events-manager.php:524` | Un evento è passato a `publish` tramite l'azione rapida "Pubblica" |

**Esempio di estensione** (es. per notificare Slack invece di/in aggiunta alle email):

```php
add_action( 'oe_community_event_published', function( $post_id, $event_title, $author_id ) {
    // logica custom, es. webhook Slack, log su servizio esterno, ecc.
}, 10, 3 );
```

**Filtri WordPress nativi ri-mappati dal plugin** (utili per un dev che debba intervenire su login/registrazione): `login_url`, `register_url`, `lostpassword_url`, `login_redirect`, `authenticate`, `get_avatar_url`, `pre_option_elementor_element_cache_ttl`, `wp_mail_from`, `wp_mail_from_name` — tutti applicati con le priorità indicate nei rispettivi paragrafi sopra; un filtro aggiuntivo con priorità più alta di quella usata dal plugin può sempre sovrascriverne il comportamento.

Il plugin **non** espone ancora propri `apply_filters()` (nessun filtro custom trovato nel codice attuale) — un'estensione che debba alterare dati (es. i meta scritti, i template inclusi) deve oggi agganciarsi agli hook nativi WP/TEC elencati, o intervenire sui template stessi (sono `include` semplici, sovrascrivibili solo copiando il file, non via `locate_template()`).

---

## 10. Sicurezza

- **Nonce** su ogni azione che scrive: `em_save` (form evento/luogo/organizzatore), `em_delete_{id}`/`em_publish_{id}` (azioni rapide), `profile_save`, `oe_user_role`/`oe_user_delete_{id}` (gestione utenti), `oe_featured_start_{id}`/`oe_featured_confirm_{id}`/`oe_featured_revoke_{id}`, `oe_community_register`/`oe_community_login`, `open_events_save_settings`, `open_events_save_community_settings`, `open_events_save_featured_settings`.
- **Capability check** su ogni azione admin-only: `manage_options` (impostazioni, sezioni Utenti/Statistiche/Consigliati), `promote_users`/`delete_users` (gestione utenti, con le protezioni auto-admin descritte in §5.2).
- **Anti-bot registrazione** (3 livelli, additivi non sostitutivi): honeypot → time-trap (<3s) → reCAPTCHA v3 (score minimo configurabile).
- **Anti-CSRF OAuth**: transient `oe_oauth_state_{token}` (TTL 10 minuti), consumato a ogni callback.
- **Webhook Stripe**: verifica manuale della firma HMAC-SHA256 (`stripe-signature` header, tolleranza anti-replay 5 minuti, `hash_equals()` per confronto costante-nel-tempo). `permission_callback` della REST route è `__return_true` — la sicurezza è **interamente** demandata alla verifica firma nel callback, non ai permessi REST standard (scelta corretta per un webhook server-to-server senza cookie/sessione WP).
- Sanitizzazione sistematica: `sanitize_text_field`, `wp_kses_post` (contenuto), `esc_url_raw`, `absint`/`intval`, `sanitize_hex_color`.

---

## 11. Osservazioni operative (non bug, ma da tenere presente)

- **Logging webhook Stripe sempre attivo**: `featured-events-webhook.php` scrive su `error_log()` ad ogni chiamata (payload length, esito verifica firma, tipo evento) senza un flag debug — utile in diagnosi, rumoroso in produzione ad alto volume.
- **`_oe_card_views` non deduplicato**: ogni render di card (anche refresh ripetuti dello stesso visitatore) incrementa il contatore — le statistiche di "eventi più visti" vanno lette come proxy di traffico, non come utenti unici.
- **`open_events_search_get_active_cities()`** fa una query per ogni comune configurato (costo N+1) — accettabile con poche decine di comuni, da monitorare se la lista whitelist crescesse molto.
- **Nessun controllo esplicito "TEC è attivo?"**: se The Events Calendar viene disattivato, gran parte del plugin (che assume i CPT `tribe_events`/`tribe_venue`/`tribe_organizer` esistenti) fallirebbe silenziosamente invece di mostrare un avviso come fa per Elementor.

---

## 12. Riferimenti

- Changelog dettagliato riga per riga di ogni fix/feature dalla v1.0.0: [readme.txt](../readme.txt) — molto utile per capire il *perché* storico di certe scelte (query dirette, doppio hook TEC, finestre badge separate, ecc.).
- Codice sorgente: cartella [includes/](../includes) come mappato in §3.
