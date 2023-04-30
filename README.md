# MVO Website

## Testing with Docker

* Run `docker compose -f docker-compose.yml -f docker-compose.dev.yml up --build`
* The website will be available on localhost on port 8080: [http://localhost:8080](http://localhost:8080)

You may want to import some sample data into the database to get started. For this simply execute `/app/bin/create-sample-data.php` inside the app container: `docker compose exec app /app/bin/create-sample-data.php`

This will create the user `admin` with password `admin` and some other example users as well as some example dates.