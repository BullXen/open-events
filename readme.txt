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
* Miglioramento: sidebar del portale ottimizzata per mobile (≤600px) — diventa una barra orizzontale scorrevole compatta, blocco utente nascosto per risparmiare spazio, invece del wrap multi-riga precedente.

= 1.0.6 =
* Fix: confermato bug persistente su "I Miei Eventi" nonostante 1.0.5 (priorità pre_get_posts non bastava, probabile filtro SQL diretto di un altro plugin). Query della lista ora bypassa completamente WP_Query/WordPress hooks con lettura diretta al database, nessun plugin terzo può più interferire.

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
