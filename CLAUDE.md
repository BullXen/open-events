# Stile di lavoro

- **ponytail**: applica sempre lo stile ponytail (soluzione più semplice e minimale che funziona, YAGNI, standard library prima di dipendenze custom) per ogni task di codice — scrittura, refactoring, fix, review. Non serve che l'utente lo richieda ogni volta.
- **caveman**: applica sempre la modalità caveman (comunicazione ultra-compressa, meno token) nelle risposte testuali. Non serve che l'utente lo richieda ogni volta.

# Strumenti

- **headroom**: il proxy locale (`headroom proxy`, porta 8787) deve essere sempre attivo. All'inizio di ogni sessione verifica con `curl http://127.0.0.1:8787/livez` (o equivalente); se non risponde, avvialo in background con `headroom proxy` prima di procedere col resto del lavoro.

# Sincronizzazione file

- **Cartella di lavoro principale**: questa repo (`D:\DEV\ILOVELAKEISEO\open-events`, o il worktree corrente). Tutte le modifiche vanno fatte qui per prime — è la cartella tracciata da git, usata per commit/push/PR.
- **Cartella di test locale (sito reale)**: `C:\Users\michel\Local Sites\ilovelakeiseo\app\public\wp-content\plugins\open-events-main`. È il plugin realmente caricato da WordPress su `ilovelakeiseo.local` — NON è tracciata da git e NON riceve automaticamente le modifiche fatte nella cartella principale.
- Dopo ogni modifica a un file del plugin (PHP/CSS/JS), copiare il file aggiornato anche in `open-events-main`, così il sito locale riflette sempre l'ultimo stato. Prima di sovrascrivere un file lì, verificare che non contenga modifiche proprie non presenti nella cartella principale (diff veloce) — se le contiene, non sovrascrivere alla cieca ma applicare le stesse modifiche puntualmente.
