# TAYA Project Setup

This project is a Laravel application. After cloning it, make sure the following tools are installed before it can run locally.

## Prerequisites

Install these on your machine first:

- PHP 8.3 or newer
- Composer
- Node.js 20+ and npm
- Git
- SQLite support for PHP (`pdo_sqlite` or `sqlite3`)

If you are on Windows, a local stack such as XAMPP, Laragon, or WSL with PHP installed is usually the easiest option.

## Quick setup after cloning

From the project root, run:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

If you are using PowerShell on Windows, use:

```powershell
copy .env.example .env
```

## Database setup

This app is configured to use SQLite by default. Make sure the database file exists:

```bash
mkdir -p database
touch database/database.sqlite
```

On PowerShell:

```powershell
New-Item -ItemType File -Path database/database.sqlite -Force | Out-Null
```

Then run:

```bash
php artisan migrate
```

## Frontend dependencies

Install the Node dependencies and build the assets:

```bash
npm install
npm run build
```

## Run the application

Start the Laravel app:

```bash
php artisan serve
```

In another terminal, start the Vite frontend:

```bash
npm run dev
```

You can also start both with the Composer script:

```bash
composer run dev
```

## Optional checks

```bash
php artisan test
```

If you want to seed sample data, run:

```bash
php artisan db:seed
```
