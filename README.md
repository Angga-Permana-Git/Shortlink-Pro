<h1 align="center">🔗 Enterprise URL Shortener</h1>

<p align="center">
  <strong>Self-hosted URL shortener for internal teams — built with Laravel &amp; Filament</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#quick-start">Quick Start</a> •
  <a href="#docker-setup">Docker</a> •
  <a href="#roadmap">Roadmap</a> •
  <a href="#license">License</a>
</p>

---

## About

**Enterprise URL Shortener** is an internal tool for companies that need a secure, self-hosted link shortener with role-based access control. Unlike public services like Bitly or TinyURL, this app gives your organization full control over who creates, manages, and accesses shortened URLs.

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
| Backend     | Laravel 11              |
| Admin Panel | Filament PHP            |
| Database    | MySQL 8                 |
| Cache/Queue | Redis (optional)        |
| Auth        | Local / LDAP / SSO      |
| Frontend    | Blade, Vite, Tailwind   |
| Deploy      | Docker, Nginx, PHP-FPM  |

## Quick Start

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8

### Installation

```bash
# Clone the repo
git clone https://github.com/your-username/enterprise-url-shortener.git
cd enterprise-url-shortener

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

Visit **http://localhost:8000** and login with the seeded admin account.

## Docker Setup

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

| Variable          | Description                        |
|-------------------|------------------------------------|
| `APP_URL`         | Your app URL (default: `http://localhost:8080`) |
| `DB_PASSWORD`     | Database password (change for production!) |
| `APP_KEY`         | Must be same across app & queue    |
| `KEYCLOAK_*`      | Fill only if using SSO; leave empty for local login |
| `APP_SEED=true`   | Auto-seed demo data on first run   |

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

## Security

- All passwords are hashed (never stored in plain text)
- Visitor IPs are stored as hashes
- Rate limiting on login and password forms
- Authorization enforced at backend level
- HTTPS required in production
- Audit log captures all sensitive actions

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).
