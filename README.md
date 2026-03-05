# Attendance Professional System (PHP + PDO)

A professional raw PHP + PDO attendance workflow system that supports:

- CR submission of session attendance
- Lecturer review/edit workflow
- Mandatory signed sheet upload before approval
- Admin overrides with reason
- Escalation for overdue pending submissions
- Read-only Marazone SMS sync into local mirror tables
- Immutable audit logs

## Quick Start

1. Copy environment:
```bash
cp .env.example .env
```
2. Create database and run migrations:
```bash
php scripts/migrate.php
```
3. Seed demo users/data:
```bash
php scripts/seed.php
```
4. Start local server:
```bash
php -S localhost:8080 -t public
```

Required PHP extensions:

- `PDO`
- `pdo_mysql` (for MySQL/MariaDB runtime)
- `fileinfo` (for signed sheet MIME checks)

## Legacy Source

The original project was preserved under:

- `legacy/`

No legacy files were deleted; they were relocated to keep the rebuilt architecture clean.

## Default Demo Users

- `admin@demo.test` / `Password123!`
- `lecturer@demo.test` / `Password123!`
- `cr@demo.test` / `Password123!`

## Project Layout

- `public/` entrypoint and assets
- `app/` controllers, services, repositories, policies, validators, views, sync, jobs
- `database/migrations/` versioned SQL migrations
- `storage/private/signed-sheets` protected evidence storage
- `scripts/` migration/seed/cron job entry scripts
- `tests/` unit, integration, smoke tests

## Cron Jobs

- Escalation check:
```bash
php scripts/run_escalations.php
```
- Marazone sync:
```bash
php scripts/sync_marazone.php
```

## Test Scripts

```bash
./tests/run_all.sh
```

Tests auto-skip DB-dependent checks when required PDO drivers are not installed.

## Notes

- Marazone DB connection is enforced as read-only at app level.
- Signed sheets are never served directly from public web root.
