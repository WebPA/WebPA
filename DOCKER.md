# WebPA Docker Deployment

Quick-start guide for running WebPA with Docker.

## Prerequisites
- Docker Desktop (or Docker Engine + Docker Compose v2)
- ~1GB free disk space

## Quick Start

```bash
# 1. Build and start all services
docker compose up -d --build

# 2. Check status
docker compose ps

# 3. View logs (optional)
docker compose logs -f php-fpm
```

## Access
- **WebPA App:** http://localhost:8080
- **MySQL:** localhost:3306

## Default Credentials
- **Username:** admin
- **Password:** admin

## Database
- Host: `localhost` (port 3306 mapped)
- Name: `webpa`
- User: `webpa`
- Password: `webpa_password`

## Common Commands

```bash
# Stop all services
docker compose down

# Stop and remove volumes (wipes DB!)
docker compose down -v

# Rebuild after Dockerfile changes
docker compose up -d --build

# Run composer commands inside PHP container
docker compose exec php-fpm composer install

# Run PHP scripts
docker compose exec php-fpm php -l src/index.php

# View database
docker compose exec mysql mysql -u webpa -p webpa
```

## Environment Variables
Edit `.env` (or `docker-compose.yml` environment section) to customize:
- `DB_HOST` — MySQL hostname (default: `mysql` inside compose, `localhost` from host)
- `DB_PORT` — MySQL port (default: `3306`)
- `DB_USER` / `DB_PASS` / `DB_NAME` — Database credentials
- `DB_PREFIX` — Table prefix (default: `pa2_`)
- `SMTP_HOST` / `SMTP_PORT` — Email server settings
- `HELP_EMAIL` / `NO_REPLY_EMAIL` — Notification email addresses

## Troubleshooting

### "Permission denied" errors
```bash
docker compose down
sudo chown -R $(whoami) vendor/
docker compose up -d
```

### Database not initializing
Check MySQL logs:
```bash
docker compose logs mysql
```

### PHP syntax errors
```bash
docker compose exec php-fpm php -l src/index.php
```

### Composer install fails
```bash
docker compose exec php-fpm composer install
```
