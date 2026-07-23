=== Open Events ===
Contributors: BullXen
Tags: events, elementor, the-events-calendar, front-end submission
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.7
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
