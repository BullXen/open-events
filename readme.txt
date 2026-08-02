=== Open Events ===
Contributors: BullXen
Tags: events, elementor, the-events-calendar, front-end submission
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.15
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
