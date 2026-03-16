# Quiz System (Laravel)

A quiz application built with PHP and Laravel.

It supports:
- Authentication (register/login)
- Role-aware UI (admin and normal user)
- Quiz attempts with timer
- Auto-save and resume on page reload
- Multiple question types:
	- Single choice
	- Multiple choice
	- Text input
	- Numerical input (tolerance-based scoring)
	- Binary (Yes/No or True/False)
- Result summary with scoring breakdown
- Admin quiz and question management screens

## Tech Stack

- PHP 8.3+
- Laravel 12
- Composer
- Node.js + npm (for frontend assets)
- SQLite (default in this project setup)

## Setup After Cloning

### 1. Clone repository

```bash
git clone https://github.com/siddharthgupta5/Quiz-system.git
cd quiz
```

### 2. Install backend dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Create environment file

```bash
cp .env.example .env
```

On Windows PowerShell, you can use:

```powershell
Copy-Item .env.example .env
```

### 5. Generate app key

```bash
php artisan key:generate
```

### 6. Prepare database

This project uses SQLite by default.

If needed, create the SQLite file:

```bash
touch database/database.sqlite
```

On Windows PowerShell:

```powershell
New-Item -Path database/database.sqlite -ItemType File -Force
```

Then run migrations and seed data:

```bash
php artisan migrate:fresh --seed
```

### 7. Build frontend assets

For one-time build:

```bash
npm run build
```

For active development (watch mode):

```bash
npm run dev
```

### 8. Run the app

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Open:

```text
http://127.0.0.1:8000/login
```

## Seeded Users

After running the seeder, you can log in with:

- Admin user:
	- Email: admin@example.com
	- Password: password

- Normal user:
	- Email: test@example.com
	- Password: password

Or create a normal user profile through the admin panel by registering and logging in.


## Useful Commands

Run tests:

```bash
php artisan test
```

Clear caches:

```bash
php artisan optimize:clear
```

## Notes

- The root URL redirects to the login page.
- Admin-only quiz CRUD is available from the admin navigation/menu after logging in as admin.
