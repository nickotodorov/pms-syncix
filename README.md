# PMS Booking Sync Service

A Laravel-based console command and service for **synchronizing bookings, guests, rooms, and room types** from a Property Management System (PMS) API into a local database.

Built for efficiency, data consistency, and minimal API usage — powered by bulk upserts, sync hashes, and a clean, throttled sync loop.

---

## Features

- 🔄 Sync bookings from the PMS API
- 🛏 Upserts guests, rooms, and room types
- ⚡ Skips unchanged data using `sync_hash`
- 🧠 Throttled API calls (2 requests/second)
- 📉 Real-time progress bar for CLI users
- 📦 Bulk inserts via Laravel `upsert`
- 💥 Graceful error handling + retry visibility

---

## PMS API Reference

This tool uses the [Donatix PMS API](https://api.pms.donatix.info/), particularly:

- `GET /bookings?since={date}` – fetch booking IDs
- `GET /bookings/{id}` – retrieve full booking data
- `GET /guests/{id}` – get guest details
- `GET /rooms/{id}` – fetch room info
- `GET /room-types/{id}` – fetch room type info

See the full [Donatix API docs](https://api.pms.donatix.info/) for more.

---

## Installation & Setup

```
git clone https://github.com/your-org/pms-booking-sync.git
cd pms-booking-sync
composer install
setup PosstgreSQL connection to database
php artisan migrate
```
## Usage

```
php artisan pms:sync-bookings
or by passing parameter like this
php artisan pms:sync-bookings --since="2024-01-01"
```