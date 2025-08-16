Voci API RESTful
Questo repository contiene il codice sorgente per la API RESTful di Voci, un progetto sviluppato per il master con l'obiettivo di creare una libreria digitale di risorse multimediali prodotte da e per le donne. L'API consente la gestione di tipologie di media, autrici e contenuti, con funzionalità complete di creazione, lettura, aggiornamento, eliminazione (CRUD) e filtraggio avanzato.

🚀 Vision del Progetto Voci
Voci è un media brand nato con l'obiettivo di riequilibrare la narrazione mediatica, spesso dominata da prospettive che non lasciano spazio alle donne e al loro contributo nella società. Questo progetto contribuisce a questa missione fornendo una piattaforma backend per organizzare e diffondere contenuti.

📋 Requisiti
Per eseguire questa API localmente, è necessario avere installato e configurato il seguente ambiente:

PHP 8.x o superiore

MySQL 5.7+ o MariaDB

Server Web Apache con il modulo mod_rewrite abilitato (consigliato l'uso di XAMPP, WAMP o MAMP)

Git (per clonare il repository)

Compositore (non strettamente necessario per questo progetto, ma una buona pratica per futuri sviluppi PHP)

🛠️ Installazione e Setup
Segui questi passaggi per configurare ed eseguire l'API sul tuo ambiente locale:

Clona il Repository:
Apri il terminale o il Git Bash e naviga nella directory htdocs del tuo ambiente XAMPP/WAMP/MAMP (es. /Applications/XAMPP/xamppfiles/htdocs/ su macOS o C:\xampp\htdocs\ su Windows).

git clone https://github.com/TUO_NOME_UTENTE/voci-api-master.git voci-api
cd voci-api

Nota: Sostituisci TUO_NOME_UTENTE con il tuo username GitHub.

Configura le Variabili d'Ambiente:
Crea un file chiamato .env nella radice della cartella voci-api. All'interno, inserisci le credenziali del tuo database MySQL. Se stai usando XAMPP/WAMP/MAMP con le configurazioni predefinite, di solito sono:

DB_HOST=localhost
DB_NAME=voci_db
DB_USER=root
DB_PASS=

(Se hai una password per root, inseriscila in DB_PASS).

Crea il Database e le Tabelle (Esegui Migrazioni):
Apri phpMyAdmin (solitamente accessibile tramite http://localhost/phpmyadmin/) o un altro client MySQL.
Importa ed esegui il file migrations.sql presente nella radice del progetto. Questo creerà il database voci_db e tutte le tabelle necessarie con alcuni dati di esempio.

-- Contenuto di migrations.sql
-- (già fornito nel file nel repository)

Abilita mod_rewrite su Apache (se non già abilitato):
Assicurati che il modulo rewrite_module sia abilitato nel file di configurazione httpd.conf di Apache. Decommenta la riga:

LoadModule rewrite_module modules/mod_rewrite.so

E riavvia Apache.

Verifica il File .htaccess:
Assicurati che il file .htaccess sia presente nella directory public/ del tuo progetto e contenga il seguente codice per reindirizzare tutte le richieste a index.php:

# public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L,QSA]

Riavvia Apache:
Dopo qualsiasi modifica alla configurazione di Apache o ai file .htaccess, è essenziale riavviare il server web.

🌐 Endpoint API
L'API espone i seguenti endpoint per interagire con le risorse:

1. Tipologie di Media (/media-types)
GET /media-types

Descrizione: Recupera tutte le tipologie di media.

Esempio Risposta:

[
    {"id": 1, "name": "Video", "created_at": "...", "updated_at": "..."},
    {"id": 2, "name": "Articolo", "created_at": "...", "updated_at": "..."}
]

GET /media-types/{id}

Descrizione: Recupera una singola tipologia di media per ID.

Esempio Risposta:

{"id": 1, "name": "Video", "created_at": "...", "updated_at": "..."}

POST /media-types

Descrizione: Crea una nuova tipologia di media.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{"name": "Podcast"}

Esempio Risposta:

{"message": "Media type created successfully", "id": 4}

PUT /media-types/{id}

Descrizione: Aggiorna una tipologia di media esistente.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{"name": "Video Interattivo"}

Esempio Risposta:

{"message": "Media type updated successfully"}

DELETE /media-types/{id}

Descrizione: Elimina una tipologia di media.

Esempio Risposta: (204 No Content)

2. Autrici (/authors)
GET /authors

Descrizione: Recupera tutte le autrici.

GET /authors/{id}

Descrizione: Recupera una singola autrice per ID.

POST /authors

Descrizione: Crea una nuova autrice.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{"name": "Nome", "surname": "Cognome"}

PUT /authors/{id}

Descrizione: Aggiorna un'autrice esistente.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{"name": "Nuovo Nome", "surname": "Nuovo Cognome"}

DELETE /authors/{id}

Descrizione: Elimina un'autrice.

3. Contenuti Multimediali (/contents)
GET /contents

Descrizione: Recupera tutti i contenuti multimediali. Include le autrici e il nome della tipologia di media associata.

Filtri Opzionali:

GET /contents?author_id={id}: Filtra per ID dell'autrice.

GET /contents?content_name={string}: Filtra per nome (ricerca parziale).

Esempio Risposta:

[
    {
        "id": 1,
        "name": "Intervista sulla Parità di Genere",
        "description": "Approfondimento...",
        "media_type_name": "Video",
        "created_at": "...",
        "updated_at": "...",
        "authors": "Maria Rossi, Laura Bianchi"
    }
]

GET /contents/{id}

Descrizione: Recupera un singolo contenuto per ID, includendo le autrici associate e il nome della tipologia di media.

Esempio Risposta:

{
    "id": 1,
    "name": "Intervista sulla Parità di Genere",
    "description": "Approfondimento...",
    "media_type_name": "Video",
    "created_at": "...",
    "updated_at": "...",
    "authors": "Maria Rossi, Laura Bianchi",
    "author_ids": [1, 2]
}

POST /contents

Descrizione: Crea un nuovo contenuto multimediale e lo associa alle autrici specificate.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{
    "name": "Il Futuro della Tecnologia al Femminile",
    "description": "Un'analisi approfondita sulle prospettive di carriera.",
    "media_type_id": 2,  // ID di una media_type esistente (es. Articolo)
    "author_ids": [1, 3] // Array di ID di autrici esistenti
}

Esempio Risposta:

{"message": "Content created successfully", "id": 4}

PUT /contents/{id}

Descrizione: Aggiorna un contenuto esistente e le sue associazioni con le autrici.

Headers: Content-Type: application/json

Corpo Richiesta (JSON):

{
    "name": "Il Futuro della Tecnologia: Nuove Prospettive",
    "description": "Descrizione aggiornata del contenuto.",
    "media_type_id": 2,
    "author_ids": [3] // Aggiorna le autrici associate
}

Esempio Risposta:

{"message": "Content updated successfully"}

DELETE /contents/{id}

Descrizione: Elimina un contenuto multimediale e le sue associazioni con le autrici.

🧪 Test
Si consiglia di utilizzare uno strumento come Postman (o Insomnia, cURL, ecc.) per testare gli endpoint della API. Assicurarsi che il server Apache e MySQL siano in esecuzione prima di effettuare le richieste.

🤝 Contributi
Per qualsiasi suggerimento, segnalazione di bug o miglioramento, si prega di aprire una "issue" o una "pull request" su questo repository.

Grazie!

Erika Rossi
