=== Open Events ===
Contributors: BullXen
Tags: events, elementor, the-events-calendar, front-end submission
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.10.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin per la gestione di eventi. Aggiunge a Elementor un widget che permette agli utenti loggati di gestire da front-end eventi, luoghi e organizzatori (The Events Calendar) come un portale.

== Description ==

Open Events aggiunge un widget Elementor ("Front-end Events Manager") che trasforma qualunque pagina in un portale self-service per gli utenti loggati:

* Portale Hub con sezioni "I Miei Eventi", "I Miei Luoghi", "I Miei Organizzatori" e modifica profilo.
* Form di inserimento/modifica evento (date, orari, ricorrenza, categorie, luogo, organizzatore, immagine di copertina).
* Creazione rapida di nuovi luoghi/organizzatori direttamente dal form evento.
* I nuovi eventi vengono salvati come "in attesa di revisione" (pending) cosi' un amministratore puo' approvarli prima della pubblicazione; luoghi e organizzatori restano in bozza.

== Changelog ==

= 1.10.0 =
* Nuovo: modulo "Slide Eventi Consigliati" — carousel hero con priorità agli eventi Consigliati/in primo piano, poi ai prossimi eventi. Disponibile come widget Elementor, shortcode `[open_events_slide max_items="6" only_featured="false"]`, o auto-inserimento in cima alla homepage (opzione in Open Events → Impostazioni). Nessuna libreria esterna: carousel scritto da zero, stessa query/ordinamento già usati dalla Ricerca Eventi.

= 1.9.1 =
* Fix: un errore di rete/timeout (es. errore 522 Cloudflare) durante il salvataggio di un evento poteva portare l'utente a reinviare più volte lo stesso modulo, creando eventi duplicati identici; ora un secondo invio con lo stesso token viene riconosciuto e ignorato. Il pulsante "Salva/Pubblica" si disabilita inoltre subito dopo il primo click per ridurre i doppi invii accidentali.

= 1.9.0 =
* Nuovo: Google reCAPTCHA v3 (invisibile) sulla registrazione Community, in aggiunta a honeypot e time-trap già presenti — configurabile da Open Events → Community (chiavi + soglia punteggio minimo).

= 1.8.7 =
* Fix: nella scheda evento di "Ricerca Eventi" veniva mostrata solo la prima categoria quando un evento ne aveva più di una (es. "Musica" e "Cibo"); ora compaiono tutte.

= 1.8.6 =
* Nuovo: protezione anti-bot sulla registrazione Community (honeypot + controllo tempo minimo di compilazione) — nessun servizio esterno/chiave API, blocca le registrazioni automatiche più comuni.

= 1.8.5 =
* Migliorato il badge "Consigliato": testo ora in maiuscolo ovunque compare (schede evento "Ricerca Eventi", dashboard, calendario pubblico), come già fatto per "in primo piano".

= 1.8.4 =
* Migliorata l'etichetta "in primo piano": testo ora in maiuscolo ovunque compare (dashboard, calendario pubblico, Ricerca Eventi).

= 1.8.3 =
* Nuovo: filtro Tutti/Pubblicati/Scaduti anche in "I Miei Eventi" (utente), non solo nella dashboard admin "Tutti gli Eventi". Ordinamento e paginazione restano solo lato admin.

= 1.8.2 =
* Nuovo: due pulsanti in alto a destra in "Tutti gli Eventi" per ordinare per data di pubblicazione o per data evento, crescente/decrescente (clic ripetuto sullo stesso pulsante inverte il verso).

= 1.8.1 =
* Fix: il pulsante "Rendi Consigliato"/"Completa il pagamento" in "I Miei Eventi" restava visibile anche su un evento con data ormai passata; ora non compare più per un evento scaduto.

= 1.8.0 =
* Fix: un evento Consigliato con data ormai passata restava "in primo piano" in cima alla dashboard admin "Tutti gli Eventi"; ora un evento scaduto torna in ordine normale.
* Nuovo: filtro Tutti/Pubblicati/Scaduti e paginazione (30 per pagina, con numeri di pagina e Avanti/Indietro) nella dashboard admin "Tutti gli Eventi".

= 1.7.9 =
* Fix: il badge pubblico "Consigliato" (calendario TEC ed elenco "Ricerca Eventi") aspettava gli ultimi 7 giorni prima dell'evento come il boost in cima ai risultati; ora il badge è visibile da subito dopo il pagamento (fino a fine settimana promossa), mentre solo il boost in cima all'elenco resta negli ultimi 7 giorni.

= 1.7.8 =
* Fix: il badge/priorità "Consigliato" nel calendario pubblico di TEC non veniva applicato alla vista "Ricerca Eventi" (widget Elementor con query e card proprie, separate dalle Views v2 di TEC) — ora un evento Consigliato attivo (ultimi 7 giorni prima dell'inizio) mostra il badge e va in cima ai risultati anche lì.

= 1.7.7 =
* Nuovo: link "Ricevuta" (icona) sull'evento Consigliato pagato, visibile sia in "I Miei Eventi" (utente) sia nella sezione admin "Consigliati" — apre la ricevuta Stripe in una nuova scheda. Recuperata dal webhook al momento del pagamento; per i pagamenti già confermati prima di questa modifica viene recuperata al volo la prima volta e poi salvata.

= 1.7.6 =
* Nuovo: un evento "Consigliato" pagato va in primo piano nel calendario pubblico (badge + priorità di ordinamento, stesso meccanismo del flag "in primo piano" manuale) negli ultimi 7 giorni prima dell'inizio evento, non da subito dopo il pagamento.
* Fix: un utente poteva ancora modificare un proprio evento dopo che veniva pubblicato da un admin; ora, una volta pubblicato, solo un amministratore può modificarlo (il link "Modifica" in "I Miei Eventi" viene nascosto per l'evento).

= 1.7.5 =
* Fix: il badge "Consigliato" in "I Miei Eventi" restava attivo per sempre dopo il pagamento; ora scompare automaticamente alla fine della settimana solare promossa (coerente con lo slot settimanale a pagamento).

= 1.7.4 =
* Nuovo: elenco vantaggi (opzionale, configurabile da Open Events → Eventi Consigliati) mostrato nel form evento quando l'utente seleziona "Rendi il mio evento Consigliato" — 2 colonne su mobile, 3 su desktop.

= 1.7.3 =
* Migliorata l'azione "Rendi Consigliato / Completa il pagamento" in "I Miei Eventi": ora è un pulsante con icona carta di credito ed etichetta testuale visibile, invece della sola stella con testo solo al passaggio del mouse.

= 1.7.2 =
* Fix: warning PHP "Undefined variable $show_sidebar" sull'azione rapida "Rendi Consigliato/Completa pagamento" nell'elenco eventi.
* Fix: il campo "Segreto webhook Stripe" nelle impostazioni era di tipo password, con rischio di interferenza dell'autocompletamento del browser che impediva il salvataggio; ora è un campo di testo semplice.

= 1.7.1 =
* Fix: un evento eliminato (cestinato) da "I Miei Eventi" restava comunque visibile nella sezione admin "Consigliati" — la query non escludeva gli eventi nel cestino.

= 1.7.0 =
* Nuovo: modulo "Eventi Consigliati" (Fase 2) — nuova sezione admin "Consigliati" nella Dashboard (elenco eventi pagati/in attesa/scaduti con filtro per stato, badge "nuovi" come le altre sezioni, conferma/revoca manuale per pagamenti gestiti fuori piattaforma).
* Nuovo: nell'elenco "I Miei/Tutti gli Eventi", badge "Consigliato" sugli eventi pagati e pulsante rapido per attivare o completare il pagamento su un proprio evento già pubblicato, senza dover riaprire il form.

= 1.6.0 =
* Nuovo: modulo "Eventi Consigliati" (Fase 1) — nuova voce di menu "Eventi Consigliati" in Open Events per configurare Stripe (chiavi, webhook, prezzo/valuta, pagina informativa, slot massimi per settimana, colore badge).
* Nuovo: nel form Inserisci/Modifica Evento, checkbox "Rendi il mio evento Consigliato" (per tutti gli utenti) con prezzo e link alla pagina informativa; se lo slot settimanale è pieno risulta disattivata con messaggio.
* Nuovo: alla conferma, se lo slot è disponibile, l'utente viene reindirizzato a una Stripe Checkout Session (implementazione diretta via REST API di Stripe, nessuna dipendenza esterna); un webhook dedicato (prima REST route del plugin) conferma il pagamento in modo affidabile indipendentemente dal redirect del browser.
* Nuovo: massimo 3 eventi Consigliati per settimana solare (configurabile); per un evento ricorrente conta solo la prima data della serie.
* Nota: sezione admin "Consigliati" per gestire/monitorare i pagamenti, badge nella dashboard utente e priorità nell'elenco pubblico arriveranno in aggiornamenti successivi (Fase 2/3).

= 1.5.2 =
* Fix: il badge "nuovi" nella Dashboard non compariva per "I Miei/Tutti gli Eventi" quando veniva inserito un nuovo evento (funzionava correttamente per Luoghi e Organizzatori). Stessa causa già risolta altrove nel plugin: la query di conteggio usava get_posts(), che The Events Calendar filtra forzando post_status a "publish" su ogni query tribe_events — un evento appena inserito, ancora "in attesa", non veniva mai contato. Ora usa una query diretta al database.

= 1.5.1 =
* Fix: dopo il login (classico o social) la sessione sembrava "non tenere" — ricaricando la pagina si tornava allo stato precedente. Causa reale: la cache "Elementi" di Elementor (attiva di default, fino a 24h) mette in cache l'HTML dell'intera pagina in modo identico per tutti i visitatori, senza distinguere utenti loggati/anonimi — chi visitava per primo una pagina col widget Community Auth o Front-end Events Manager ne "congelava" lo stato per chiunque altro. Ora la cache viene bypassata per i visitatori loggati (i visitatori anonimi continuano a beneficiarne normalmente).

= 1.5.0 =
* Nuovo: un evento singolo (non ricorrente) può ora essere convertito in "Evento ricorrente" anche in modifica, aggiungendo le date mancanti — prima il checkbox era disattivato per qualunque evento in modifica. Un evento già parte di una serie resta invece bloccato (si rischierebbe di rigenerare le date della serie ad ogni salvataggio).
* Fix: quando un evento singolo con un'immagine di copertina viene convertito in ricorrente, le nuove date clonate ora ereditano la stessa immagine invece di restare senza copertina.

= 1.4.5 =
* Fix: pubblicando un evento di una serie ricorrente (più date), gli altri eventi della stessa serie non venivano più pubblicati insieme come previsto. Causa: la ricerca dei "fratelli" della serie usava get_posts(), la cui query viene silenziosamente filtrata da The Events Calendar (forza post_status a "publish" a livello SQL su ogni query tribe_events); ora usa una query diretta al database, come già fatto altrove nel plugin per lo stesso problema.

= 1.4.4 =
* Nuovo: nel pannello Stile del widget "Community Auth", controlli colore per il pulsante Accedi/Registrati (sfondo/testo normale e al passaggio del mouse) e per i tab in alto (sfondo/testo attivo e al passaggio del mouse).

= 1.4.3 =
* Nuovo: log diagnostico (error_log) quando un'email Community viene inviata o fallisce, per capire se il codice ha tentato l'invio quando poi non risulta ricevuta (utile soprattutto in locale, dove spesso manca un vero server SMTP).

= 1.4.2 =
* Fix: "Importa la foto profilo" poteva risultare disattivata senza che l'admin l'avesse mai deselezionata (il checkbox è stato aggiunto al form dopo che l'opzione era già salvabile, quindi un salvataggio precedente della pagina Community l'aveva silenziosamente spenta). Corretta una tantum al prossimo caricamento di wp-admin, senza toccare eventuali disattivazioni volute in seguito.

= 1.4.1 =
* Nuovo: icona del rispettivo provider (Google, Facebook) sui pulsanti "Accedi/Registrati con..." del widget Community Auth.

= 1.4.0 =
* Nuovo: modulo Community, Fase 2/3 — login/registrazione con Google e Facebook (OAuth "fatto in casa", nessuna dipendenza esterna), configurabili da Open Events → Community con le rispettive credenziali (Client ID/Secret, App ID/Secret) e l'indirizzo di redirect da incollare nella console Google/Meta.
* Nuovo: se l'email del provider social corrisponde a un account già esistente, l'account social viene collegato a quello invece di crearne uno duplicato.
* Nuovo: opzione per importare la foto profilo da Google/Facebook al primo accesso (mostrata al posto di Gravatar).
* Nuovo: modulo Community, Fase 4 — editor delle email (mittente, email di benvenuto alla registrazione, notifica admin per nuovo evento in attesa di revisione, notifica all'autore quando il suo evento viene pubblicato) con segnaposto configurabili, tutto da Open Events → Community.

= 1.3.3 =
* Fix: nell'editor di Elementor (dove si è sempre loggati) il widget "Community Auth" mostrava solo il messaggio "hai già effettuato l'accesso", impedendo di vedere e stilizzare i form Accedi/Registrati. Ora nell'editor/anteprima Elementor i form vengono comunque mostrati, con un avviso che lo segnala.

= 1.3.2 =
* Nuovo: nel widget "Community Auth", chi è già loggato e riapre la pagina di accesso ora vede un conto alla rovescia di 5 secondi e viene reindirizzato automaticamente alla dashboard (può comunque cliccare "Vai subito" o "Esci" prima).

= 1.3.1 =
* Nuovo: il widget "Community Auth" ha ora controlli propri in Elementor oltre alle Impostazioni globali — tab predefinito (Accedi/Registrati), mostra/nascondi il tab "Registrati" su questa istanza, e un'immagine laterale opzionale (sinistra o destra).
* Nuovo: impostando un'immagine laterale, il widget diventa un box a tutta larghezza diviso in due colonne (form + immagine); senza immagine il form resta centrato come prima.

= 1.3.0 =
* Nuovo: modulo "Community" (Fase 1) — nuova voce di menu "Community" in Open Events, con pagina di impostazioni per attivare/disattivare la registrazione classica, scegliere quali campi mostrare (nome, cognome, comune) come obbligatori/opzionali/nascosti, il ruolo WordPress assegnato ai nuovi iscritti, e le pagine di login/registrazione, redirect e password dimenticata.
* Nuovo: widget Elementor "Community Auth" — form unico Accedi/Registrati con toggle via JS (senza reload pagina), supporta il parametro `?tab=registrati` per aprire direttamente la scheda registrazione da un link esterno.
* Nuovo: login/registrazione classici del sito ora passano dal plugin — `wp_login_url()`/`wp_registration_url()`/`wp_lostpassword_url()` puntano alla pagina Community configurata, il login accetta anche l'indirizzo email oltre allo username, e dopo l'accesso l'utente atterra sulla pagina configurata (di norma la dashboard) invece che su wp-admin.
* Nota: login/registrazione con Google e Facebook, editor email e conferma email via link non sono ancora attivi — arriveranno in aggiornamenti successivi (i relativi campi sono già visibili ma disattivati nella pagina Community).

= 1.2.16 =
* Fix: nel form evento, i campi "Ripeti fino al" e "Inserisci data extra" (evento ricorrente) permettevano di scegliere una data antecedente alla Data Inizio; ora, come la Data Fine, non possono precedere la Data Inizio scelta.

= 1.2.15 =
* Nuovo: pulsante "+ Nuovo Evento" anche nella Dashboard (non solo nella sezione "I Miei Eventi"), in alto a destra sopra le card.
* Fix: nell'admin gli organizzatori mostravano solo quelli dell'utente corrente invece di tutti; ora, come per i luoghi, l'admin vede sempre tutti gli organizzatori (raggruppati "I tuoi organizzatori" / "Altri organizzatori"), l'utente normale solo i propri.
* Nuovo: opzioni "Funzioni form Inserisci Evento" in Impostazioni — attiva/disattiva indipendentemente i campi "Evento Giornaliero" ed "Evento Ricorrente" nel form front-end (entrambi attivi di default).
* Nuovo: opzione "Date antecedenti" in Impostazioni (disattiva di default) — se disattivata, nei calendari del form Inserisci Evento non è possibile selezionare o navigare a giorni/mesi/anni precedenti a oggi.
* Nuovo: nel form evento, la Data Fine si pre-seleziona sulla Data Inizio scelta e non permette date antecedenti ad essa; si riallinea automaticamente se la Data Inizio viene spostata più avanti.
* Nuovo: il calendario dei campi data ha ora select dirette per mese e anno (oltre alle frecce avanti/indietro), per saltare rapidamente a un mese/anno lontano invece di scorrere un mese alla volta; la select anno mostra i prossimi 5 anni.
* Fix: pulsante "Annulla" in fondo al form, su mobile, sostituito da un'icona X compatta (box rosso) per non tagliare il testo del pulsante di invio sulla stessa riga; resta invariato su desktop.
* Fix: spaziatura eccessiva su mobile tra data/ora inizio, data/ora fine e gli switch "Evento Giornaliero"/"Evento Ricorrente" (il gap della grid si sommava al margine dei singoli campi).
* Fix: spazio vuoto in eccesso in fondo alle sezioni "2. Date e Orari" e "4. Costo e Collegamenti" del form evento.
* Fix: il popup del calendario poteva andare in overflow orizzontale su schermi stretti, spingendo/allargando l'intera pagina.

= 1.2.0 =
* Nuovo: vista Lista nel widget Ricerca Eventi (in alternativa alla griglia a card), con toggle persistente in localStorage; ogni riga mostra immagine, categoria, data/ora, titolo e comune in formato compatto.
* Nuovo: nella vista Lista, cliccando sul comune di un evento si applica automaticamente il filtro "Comune" (se attivo nel widget), senza dover aprire il menu a tendina.
* Nuovo: pulsante "Condividi" su ogni riga della vista Lista (visibile sempre su mobile, al passaggio del mouse su desktop) — usa la condivisione nativa del dispositivo se disponibile, altrimenti copia il link dell'evento negli appunti.
* Fix: durante lo scroll della pagina, le card degli eventi passavano sopra l'header del sito invece di restarci sotto (z-index eccessivo).
* Fix: nel widget Ricerca Eventi, passare il mouse sul campo data lo coloriva di rosso bordeaux (colore hover ereditato dal tema); ora resta neutro come gli altri campi.
* Miglioramento: contrasto badge categoria nella vista Lista portato a conformità WCAG AA; card della vista Lista rese navigabili da tastiera (`article` con `tabindex`, focus visibile).
* Nuovo: nella Dashboard, le sezioni "I Miei Eventi/Luoghi/Organizzatori" mostrano un badge con il numero di elementi aggiunti dall'ultima visita; sparisce non appena si apre la sezione.
* Nuovo: sezione "Statistiche" nella Dashboard, riservata agli amministratori — eventi pubblicati/online/passati, luoghi e organizzatori pubblicati, utenti iscritti, visualizzazioni totali delle schede evento e classifica dei 5 eventi più visualizzati.

= 1.1.1 =
* Fix: nel datepicker del widget Ricerca Eventi, passare il mouse sui numeri del calendario rendeva il testo bianco su sfondo bianco (hover CSS sovrascriveva il colore di sfondo accent dei giorni selezionati senza ripristinare il colore del testo). La regola hover ora esclude i giorni con classe is-start/is-end/is-single.
* Fix: nel datepicker, non era possibile selezionare la seconda data dell'intervallo. Causa: il gestore mouseover chiamava _renderDays() ad ogni evento, ricreando i bottoni sotto il cursore; il browser sparava un nuovo mouseover sui nuovi elementi, creando un loop di re-render che impediva il click di completarsi. Aggiunto un guard che ri-renderizza solo quando il giorno hoverato cambia effettivamente.

= 1.1.0 =
* Nuovo: widget Elementor "Ricerca Eventi" — una barra di ricerca personalizzata e moderna per la pagina eventi, alternativa alla barra nativa di The Events Calendar. Include: campo di ricerca testuale (opzionale), menu a tendina "Tutti i Comuni" con selezione del singolo comune, menu a tendina delle categorie evento, e filtri rapidi per data (Prossimi, Oggi, Questa settimana) più la scelta di una data singola. I risultati sono mostrati in una griglia di card disegnata dal plugin (immagine, data, comune, categoria, badge "in primo piano") e si aggiornano dal vivo via AJAX, senza ricaricare la pagina. Dalle opzioni Elementor si possono regolare colore principale e numero di colonne.

= 1.0.9 =
* Nuovo: sezione "Utenti" nel portale, riservata agli amministratori — voce nella barra laterale e scheda nella bacheca — per vedere tutti gli utenti iscritti (con email, ruolo, data di iscrizione e numero di eventi creati), cambiarne il ruolo, aprirli in wp-admin o eliminarli (i loro eventi/luoghi/organizzatori vengono riassegnati all'amministratore). Sono presenti protezioni: non è possibile modificare/eliminare il proprio account, eliminare altri amministratori o rimuovere l'ultimo amministratore.
* Nuovo: opzione "Visibilità luoghi nel form evento" nelle Impostazioni — permette di mostrare all'utente tutti i luoghi (predefinito) oppure solo quelli che ha inserito lui. Gli amministratori vedono comunque tutti i luoghi.
* Miglioramento: nel form evento, il menu "Seleziona Luogo" ora raggruppa in cima i luoghi inseriti dall'utente ("I tuoi luoghi") e sotto tutti gli altri ("Altri luoghi"), in ordine alfabetico.
* Miglioramento: i testi di esempio (placeholder) del campo nome sono ora specifici per tipo — Luogo ("Es. Comune Iseo, Campo Sportivo di, Chiesa di...") e Organizzatore ("Es. Pro Loco, Associazione, Comune...") — invece di mostrare sempre un esempio riferito agli eventi.

= 1.0.8 =
* Fix: gli eventi salvati o pubblicati dal portale front-end restavano invisibili nel calendario pubblico con The Events Calendar 6+ (custom tables). Il widget scriveva solo le date locali, ma TEC 6 per generare l'occorrenza pretende anche i meta UTC/timezone/durata: senza, non veniva creata alcuna occorrenza e l'evento non compariva mai, pur risultando "pubblicato". Ora il plugin calcola quei meta e forza TEC a ricostruire subito evento e occorrenze, sia al salvataggio sia dal pulsante "Pubblica" (che ripara anche gli eventi creati prima di questa fix).
* Fix: l'evento "in primo piano" non veniva portato in cima alle liste del calendario pubblico con The Events Calendar 6+ perché la query eventi veniva riconosciuta tramite tribe_is_event_query(), che con la nuova architettura (Views v2 / custom tables) restituisce sempre "falso" per le query del calendario; ora la query viene riconosciuta dal tipo di contenuto (post_type), così l'ordinamento in primo piano viene applicato di nuovo.
* Nuovo: l'etichetta "in primo piano" (con il testo dell'opzione "Testo In Primo Piano") viene ora mostrata anche sulle card degli eventi in evidenza nel calendario pubblico del sito, sotto il titolo, e non solo nel portale.

= 1.0.7 =
* Nuovo: gli amministratori possono ora vedere, modificare, pubblicare ed eliminare (cestino) eventi, luoghi e organizzatori di **tutti** gli utenti dal portale front-end, non solo i propri; l'autore è indicato in elenco. Modificare un elemento come admin non ne resetta più lo stato a "in attesa di revisione".
* Nuovo: toggle "Evento in Primo Piano" (solo per admin, nel form evento) — gli eventi contrassegnati restano sempre in cima all'elenco "I Miei Eventi", con etichetta ★ personalizzabile.
* Nuovo: opzioni "Testo In Primo Piano" e "Limite eventi in primo piano" nella pagina Impostazioni (Open Events), con conteggio degli eventi attualmente in primo piano e blocco del salvataggio se il limite viene superato.
* Nuovo: pulsante "Anteprima" nell'elenco eventi/luoghi/organizzatori, apre l'elemento in una nuova scheda (usa l'anteprima nativa di WordPress per bozze/in attesa di revisione).
* Nuovo: nell'elenco, per gli admin, è ora visibile anche lo username (oltre al nome visualizzato) di chi ha inserito l'evento.
* Nuovo: gli eventi "in primo piano" ora vengono portati in cima anche nel calendario pubblico del sito (non solo nella dashboard del portale), mantenendo l'ordinamento per data di The Events Calendar per tutti gli altri eventi.
* Miglioramento: le azioni "Modifica/Anteprima/Pubblica/Elimina" nell'elenco sono ora icone compatte invece di testo.
* Nuovo: opzione "Visualizzazione 'pubblicato da'" in Impostazioni — scegli se accanto al nome di chi ha inserito l'evento mostrare il Nome Organizzatore collegato, lo Username o l'Email.
* Fix: cliccando "Pubblica" su un evento inserito da un utente, in alcuni casi l'evento restava invisibile (né in anteprima né online) perché non aveva mai ricevuto uno slug/permalink essendo stato salvato inizialmente come "in attesa di revisione"; ora lo slug viene generato al momento della pubblicazione se mancante.
* Miglioramento: nell'elenco, immagine e titolo aprono la scheda di modifica; l'icona Modifica è ora l'ultima a destra, Anteprima e Pubblica precedono lo stato; se la visualizzazione "pubblicato da" è impostata su Organizzatore, viene mostrato solo il nome dell'organizzatore (cliccabile, apre la sua scheda) invece di "di autore (organizzatore)".
* Fix: il titolo del form "Nuovo Luogo" e "Nuovo Organizzatore" mostrava erroneamente "Inserisci Nuovo Evento".
* Nuovo: nel form Luogo/Organizzatore, campo admin "Assegna a" in fondo pagina — se lasciato vuoto l'elemento resta assegnato all'account admin, altrimenti si può scegliere a quale utente assegnarlo (anche in modifica, per riassegnare elementi esistenti).
* Fix: l'ordinamento "in primo piano" nel calendario pubblico non aveva alcun effetto perché la Lista Eventi di The Events Calendar non è la query principale della pagina; il filtro era limitato per errore alla sola query principale.
* Fix: pubblicare un evento tramite l'admin poteva lasciarlo comunque invisibile (404) perché WordPress converte automaticamente lo stato "pubblicato" in "programmato" se il post_date esistente non è coerente con l'istante attuale; ora viene forzato all'istante della pubblicazione.
* Miglioramento: se il link "Pubblica"/"Elimina" non è più valido (sessione scaduta) viene ora mostrato un messaggio d'errore invece di non fare nulla in silenzio.
* Fix: un evento poteva restare invisibile (né in anteprima né online) anche dopo aver confermato "Pubblica" dal portale, pur risultando "publish" in WordPress — causa: creando/modificando un evento, WordPress lancia l'hook di salvataggio di The Events Calendar PRIMA che il plugin scrivesse data/ora/luogo/organizzatore nei meta, quindi TEC sincronizzava le sue tabelle interne su un evento ancora "vuoto" e non lo considerava mai pronto (riaprirlo e ripubblicarlo da wp-admin invece funzionava perché lì i meta vengono scritti prima del salvataggio). Ora il plugin rilancia la sincronizzazione di TEC a meta completi, sia al salvataggio sia dal pulsante "Pubblica" rapido (che aggiorna anche gli eventi creati prima di questa fix).

= 1.0.6 =
* Fix: confermato bug persistente su "I Miei Eventi" nonostante 1.0.5 (priorità pre_get_posts non bastava, probabile filtro SQL diretto di un altro plugin). Query della lista ora bypassa completamente WP_Query/WordPress hooks con lettura diretta al database, nessun plugin terzo può più interferire.
* Miglioramento: sidebar del portale ottimizzata per mobile (≤600px) — diventa una barra orizzontale scorrevole compatta, blocco utente nascosto per risparmiare spazio, invece del wrap multi-riga precedente.
* Fix: nella barra mobile, cliccando una voce non centrale (es. 3a/4a) la pagina ricaricava sempre mostrando l'inizio della barra (Dashboard) invece della voce attiva; ora la voce attiva viene centrata automaticamente al caricamento.

= 1.0.5 =
* Fix: eventi utente (bozza/in attesa di revisione) confermati presenti in wp-admin ma assenti nella lista front-end "I Miei Eventi" — causa: un altro plugin (probabile The Events Calendar) sovrascrive post_status via pre_get_posts su ogni query tribe_events. Riaffermato lo status con priorità massima sulla query della lista.

= 1.0.4 =
* Fix: le icone del portale (sidebar, dashboard, dropzone immagine, pulsante salva) non venivano visualizzate per alcuni utenti perché dipendevano dal caricamento del font eicons di Elementor, non sempre incluso nella pagina. Sostituite con icone SVG incorporate direttamente nel plugin, indipendenti da Elementor.

= 1.0.3 =
* Nuovo: opzione "Descrizione Evento" in Impostazioni per scegliere tra campo classico (testo semplice) ed editor visuale di WordPress per la descrizione di eventi, luoghi e organizzatori.
* Nuovo: opzione "Città disponibili" in Impostazioni (una per riga); se configurata, gli utenti scelgono la città del luogo da un menu a tendina invece di scriverla liberamente, sia nel form evento (creazione rapida luogo) sia nel form luogo.
* Miglioramento: campo immagine (copertina evento e logo organizzatore) sostituito con un'area drag-and-drop con anteprima e pulsante per rimuovere la selezione.

= 1.0.2 =
* Nuovo: pagina "Open Events" nel menu admin (subito dopo "Eventi") con sezione Impostazioni per scegliere lo stato default assegnato ai nuovi eventi inseriti dagli utenti (Bozza / In attesa di revisione / Pubblicato).
* Nuovo: sidebar di navigazione sempre visibile nel Portale Hub (Dashboard, I Miei Eventi, I Miei Luoghi, I Miei Organizzatori, Profilo) e breadcrumb gerarchiche (es. Dashboard > I Miei Eventi > Nuovo Evento).
* Miglioramento: pulsante "Annulla" in fondo al form evento/luogo/organizzatore, e link di ritorno con etichetta corretta in base alla sezione.
* Miglioramento: rinominata "Portale Gestione Eventi" in "Dashboard" nei testi del portale.
* Miglioramento: le liste "I Miei Eventi/Luoghi/Organizzatori" ora mostrano righe con miniatura/icona e un dettaglio contestuale (data evento, indirizzo luogo, email organizzatore) al posto della tabella semplice.

= 1.0.1 =
* Fix: gli eventi in bozza/in attesa di revisione non comparivano nella lista "I Miei Eventi" dell'autore sul front-end (WP_Query esclude gli stati protetti senza `perm => readable`).
* Fix: gli eventi nuovi/modificati vengono ora salvati come "in attesa di revisione" (pending) invece che come bozza, cosi' l'etichetta di stato riflette il flusso di moderazione. Luoghi e organizzatori restano in bozza.
* Fix: caricamento forzato del font eicons come dipendenza di stile del widget, altrimenti alcune icone risultavano invisibili.
* Miglioramento: selezione categorie evento sostituita con un tag picker interattivo (ricerca live + pillole rimovibili) al posto della select nativa a scelta multipla.

= 1.0.0 =
* Rilascio iniziale: struttura plugin standalone con widget Elementor "Front-end Events Manager" (portale hub, dashboard, form per eventi/luoghi/organizzatori, modifica profilo).
