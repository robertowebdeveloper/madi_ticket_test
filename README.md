# Madisoft Ticket test

```
Autore: Roberto Parrella
Data: 14/06/2019
```
## Requisiti
- Composer
- Npm
- Php 7.2+
- Mysql 5.7+

## Installazione

Dopo aver clonato il branch master:
- installare tutte le dipendenze lanciando "composer install"
- editare (o creare) il file ".env", ed inserire i parametri di accesso al proprio database (DATABASE_URL=mysql://utente:password@127.0.0.1:3001/madisoft_ticket
)
- dopo aver verificato che il server Mysql sia attivo, creare un database tramite il comando "bin/console doctrine:database:create"
- creare le tabelle tramite il comando "bin/console doctrine:schema:update --force"
- spostarsi nella cartella public: "cd public"
- installare le dipendenze di frontend tramite: "npm ci"
- tornare alla radice del progetto: "cd .."
- lanciare il web server: "bin/console server:run"

```
Grazie
```