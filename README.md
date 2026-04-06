# MV Shoots Backend

Laravel backend and admin panel for the MV Shoots photoshoot booking platform.

## Stack

- Laravel 12
- Inertia.js + Vue 3 admin panel
- MySQL
- Sanctum
- Vite

## Features

- Admin authentication
- Dashboard and reporting
- Categories, plans, reels, bookings, payments, notifications, settings
- Mobile app APIs for user, partner, and owner apps
- Dynamic app config, Firebase config, SMTP settings, and app dialogs

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```
