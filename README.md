<div align="center">

# 🔗 Shortlink-Pro

**Self-hosted URL shortener for internal teams — built with Laravel & Filament**

[![Laravel](https://img.shields.io/badge/Laravel-12-orange.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-PHP-7C3AED.svg)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://www.docker.com)

[Features](#features) • [Tech Stack](#tech-stack) • [Quick Start](#quick-start) • [Docker](#docker-setup) • [Roadmap](#roadmap)

</div>

---

## About

**Shortlink-Pro** is a secure, self-hosted URL shortener designed for internal teams and organizations. Unlike public services like Bitly or TinyURL, this app gives your organization **full control** over who creates, manages, and accesses shortened URLs.

Every link has a clear owner, optional expiration, password protection, and full click analytics — all managed through a clean admin dashboard powered by Filament.

## Features

- **Short URL Management** — Create short links with auto-generated or custom slugs
- **Password Protection** — Secure sensitive links with hashed passwords
- **Link Expiration** — Set auto-expiring links with start/end dates
- **Click Analytics** — Track clicks, status codes, referrers, and user agents
- **Role-Based Access** — User (manage own links) vs Admin (full control)
- **Audit Log** — Every action is logged for compliance and security
- **Admin Panel** — Manage users, links, settings, and branding via Filament
- **Custom Branding** — Admin can change login page logo and app name
- **SSO/LDAP Ready** — Switch auth mode via environment variable (dev uses local login)
- **Docker Support** — Deploy in seconds with Docker Compose

## Tech Stack

| Layer       | Technology              |
|-------------|-------------------------|
| Backend     | Laravel 12              |
| Admin Panel | Filament PHP            |
| Database    | SQLite (default) / MySQL 8 |
| Cache/Queue | Redis (optional)        |
| Auth        | Local / LDAP / SSO      |
| Frontend    | Blade, Vite, Tailwind   |
| Deploy      | Docker, Nginx, PHP-FPM  |

## Quick Start

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (default) or MySQL 8

### Installation

```bash
# Clone the repo
git clone https://github.com/Angga-Permana-Git/Shortlink-Pro.git
cd Shortlink-Pro

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

### Default Credentials (after seeding)

| Role  | Email                | Password  |
|-------|----------------------|-----------|
| Admin | admin@company.local  | password  |
| User  | user@company.local   | password  |

> **Important:** Change these credentials immediately in production!

### Access

- **App:** [http://localhost:8000](http://localhost:8000)
- **Admin Panel:** [http://localhost:8000/admin](http://localhost:8000/admin)
- **Login:** [http://localhost:8000/login](http://localhost:8000/login)

## Docker Setup

### Which Mode to Choose?

| Mode | Use Case |
|------|----------|
| **Mode 1 (All-in-One)** | Development, testing, single-server deployment |
| **Mode 2 (Separated)** | Production, when you need separate DB server for scaling |

### Mode 1 — All-in-One (app + db)

```bash
docker compose up -d --build
# App available at http://localhost:8080
```

### Mode 2 — Separated (app & db on different containers)

```bash
docker compose -f docker-compose.db.yaml up -d
docker compose -f docker-compose.app.yaml up -d --build
```

### Important Config

| Variable          | Description                                    |
|-------------------|------------------------------------------------|
| `APP_URL`         | Your app URL (default: `http://localhost:8080`) |
| `DB_PASSWORD`     | Database password (change for production!)      |
| `APP_KEY`         | Must be same across app & queue                |
| `KEYCLOAK_*`      | Fill only if using SSO; leave empty for local login |
| `APP_SEED=true`   | Auto-seed demo data on first run               |

## Project Structure

```
app/
├── Actions/ShortUrl/       # Business logic for URL operations
├── Filament/
│   ├── Admin/              # Admin panel resources
│   └── User/               # User dashboard resources
├── Http/Controllers/       # Public redirect endpoint
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies
├── Services/
│   ├── Auth/               # Auth provider logic
│   ├── ShortUrl/           # URL management services
│   ├── Analytics/          # Click tracking
│   └── Branding/           # Branding settings
└── Support/Enums/          # Enumerations
```

## Roadmap

- [x] Short URL CRUD with ownership
- [x] Password-protected links
- [x] Link expiration
- [x] Click analytics
- [x] Admin panel with Filament
- [x] Audit logging
- [x] Docker deployment
- [ ] QR code generation
- [ ] Bulk import/export
- [ ] Custom domains
- [ ] API key authentication
- [ ] Advanced analytics dashboard

## Troubleshooting

### Port 8080 already in use
Change the port in `docker-compose.yaml`:
```yaml
ports:
  - "8081:80"  # Change 8081 to your preferred port
```

### Database connection refused (Docker)
Make sure the DB container is running first:
```bash
docker compose -f docker-compose.db.yaml up -d
# Wait 10-15 seconds for MySQL to initialize
docker compose -f docker-compose.app.yaml up -d --build
```

### Migration errors
Clear cache and re-run:
```bash
php artisan migrate:fresh --seed
```

### SSO/Login not working
- Ensure `KEYCLOAK_BASE_URL` and `KEYCLOAK_REALM` are correctly filled
- Check `KEYCLOAK_SETUP.md` for detailed Keycloak configuration
- If not using SSO, leave those fields empty — login button will be hidden automatically

## Security

- All passwords are hashed (never stored in plain text)
- Visitor IPs are stored as hashes
- Rate limiting on login and password forms
- Authorization enforced at backend level
- HTTPS required in production
- Audit log captures all sensitive actions

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

**Angga Permana** — [GitHub](https://github.com/Angga-Permana-Git)

## License

This project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
