# Laravel Dockerized Stack

This repository ships a ready-to-run development environment for a Laravel application using Docker Compose.

Services
---------

| Service | Image | Port | Notes |
|---------|---------------------|------|-------|
| **app** | Custom `php:8.3-fpm` (see `Dockerfile`) | 9000 (internal) | Runs PHP-FPM + Composer |
| **nginx** | `nginx:1.25-alpine` | **80** | Serves traffic and proxies PHP to `app` |
| **mysql** | `mysql:8.0` | **3306** | Persistent database stored in `mysql-data` volume |

Prerequisites
-------------

* [Docker Desktop](https://www.docker.com/products/docker-desktop/) or Docker Engine ≥ **v24**
* Docker Compose (bundled with Docker Desktop)

Quick Start
-----------

1. Clone the repository and move into it:

   ```bash
   git clone <your-fork-url> project && cd project
   ```

2. Build and start the containers (first run takes a few minutes):

   ```bash
   docker compose up -d --build
   ```

3. (First-time setup) Install PHP dependencies & generate the Laravel app key:

   ```bash
   # Enter the app container
   docker compose exec app bash

   # Inside the container
   composer install
   cp .env.example .env  # if not present
   php artisan key:generate
   php artisan migrate   # optional – creates DB tables
   exit
   ```

4. Visit `http://localhost` in your browser. You should see the Laravel welcome page.

Common Tasks
------------

* **Stop containers**
  ```bash
  docker compose down
  ```
* **View logs for all services**
  ```bash
  docker compose logs -f
  ```
* **Tinker with Laravel**
  ```bash
  docker compose exec app php artisan tinker
  ```

Environment Variables
---------------------

The `mysql` credentials are defined in `docker-compose.yml` and should match your Laravel `.env` file:

```
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

If you need to change them, update **both** the compose file and your `.env`.

Volumes & Persistence
---------------------

* `mysql-data` – stores MySQL data on your host; removing the volume resets the database:
  ```bash
  docker compose down -v  # dangerous – deletes DB
  ```

Rebuilding After Changes
------------------------

If you modify the `Dockerfile` or add new PHP extensions, rebuild the `app` image:

```bash
docker compose build app
```

Troubleshooting
---------------

* **Port already in use** – change the host port mapping (e.g., `80:80` → `8080:80`) in `docker-compose.yml`.
* **White screen / 500 error** – inspect logs via `docker compose logs nginx` and `docker compose logs app`.

---

Happy coding! 🚀