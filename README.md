# Laravel 13 Admin Search App

A simple Laravel project with:

- Admin login
- User management list
- Product management list
- Combined search (User + Product)
- Seeded large sample data for testing performance

This guide is written for beginners and explains everything step by step.

---

## 1) Requirements

Make sure these are installed:

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- MySQL (or SQLite)

---

## 2) Project Setup (Step by Step)

### Step 1: Clone and open project

```bash
git clone <your-repo-url>
cd laravel-13
```

### Step 2: Install backend dependencies

```bash
composer install
```

### Step 3: Install frontend dependencies

```bash
npm install
```

### Step 4: Create environment file

```bash
cp .env.example .env
```

### Step 5: Generate app key

```bash
php artisan key:generate
```

### Step 6: Configure database

Open `.env` and set DB values, for example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_13
DB_USERNAME=root
DB_PASSWORD=password
```

### Step 7: Run migrations and seed data

```bash
php artisan migrate:fresh --seed
```

What this does:
- Creates all tables (`users`, `products`, auth/session tables, telescope tables)
- Creates an admin account
- Creates many users/products for testing search

### Step 8: Build assets

For development:

```bash
npm run dev
```

For production build:

```bash
npm run build
```

### Step 9: Start Laravel server

```bash
php artisan serve
```

Open: `http://127.0.0.1:8000`

---

## 3) Default Login

After seeding:

- Email: `admin@example.com`
- Password: `Admin@123456`

Only admin can access admin pages.

---

## 4) Main Routes

Public/Auth:
- `/login`
- `/register`
- `/forgot-password`

User:
- `/dashboard`
- `/profile`

Admin (requires `auth` + `admin` middleware):
- `/admin/dashboard`
- `/admin/users`
- `/admin/products`
- `/admin/search`

---

## 4.1) Screenshots (Reference)

Add your screenshots inside: `docs/screenshots/`

Recommended file names:

- `login-page.png`
- `admin-dashboard.png`
- `users-list.png`
- `products-list.png`
- `combined-search.png`

Then they will appear below:

### Login Page
![Login Page](docs/screenshots/login-page.png)

### Admin Dashboard
![Admin Dashboard](docs/screenshots/admin-dashboard.png)

### Users List
![Users List](docs/screenshots/users-list.png)

### Products List
![Products List](docs/screenshots/products-list.png)

### Combined Search
![Combined Search](docs/screenshots/combined-search.png)

---

## 5) How Search Works (Simple Explanation)

Search flow:

1. User enters text in admin search page.
2. `CombinedSearchController` calls `SearchService`.
3. `SearchService` calls `RunCombinedSearchAction`.
4. Action queries:
   - users from `UserRepository`
   - products from `ProductRepository`
5. Results are merged and duplicate rows are removed.
6. Final list is shown as:
   - `user_name`
   - `product_name`

Scout is configured with `database` driver in `config/scout.php`, so search works without external engines.

---

## 5.1) Redis Caching For Admin Lists + Combined Search

The admin users/products lists load rows via AJAX (infinite scroll + search). Those AJAX HTML responses are cached in Redis for 60 seconds (configurable) using Laravel Cache.

Key points:
- Cache logic lives in the service layer:
  - `app/Services/Admin/AdminListingService.php` (users/products list rows)
  - `app/Services/SearchService.php` (combined search)
  - `app/Services/SearchCacheService.php` (keys + TTL + locks + tags)
- Unique keys are generated per query + page and include a version number for invalidation:
  - `admin:users:rows:v{usersVersion}:...`
  - `admin:products:rows:v{productsVersion}:...`
  - `search:combined:u{usersVersion}:p{productsVersion}:...`
- Cache stampede protection:
  - Uses cache locks (`Cache::lock()->block()`)
  - Uses TTL jitter to avoid synchronized expiry
- Avoid over-caching:
  - Queries shorter than `SEARCH_CACHE_MIN_QUERY_LENGTH` are not cached (default: 2)

### Enable Redis cache store

In `.env`:

```env
CACHE_STORE=redis
# Laravel 10 and earlier used CACHE_DRIVER; Laravel 13 uses CACHE_STORE.
# CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Search cache settings:

```env
SEARCH_CACHE_ENABLED=true
SEARCH_CACHE_TTL_SECONDS=60
SEARCH_CACHE_TTL_JITTER_SECONDS=10
SEARCH_CACHE_LOCK_SECONDS=10
SEARCH_CACHE_LOCK_WAIT_SECONDS=2
SEARCH_CACHE_MIN_QUERY_LENGTH=2
```

Then run:

```bash
php artisan config:clear
```

### Bypass cache (for debugging / benchmarking)

Admin list AJAX requests support `bypass_cache=1`:

- `/admin/users?q=John&bypass_cache=1` (AJAX only)
- `/admin/products?q=Phone&bypass_cache=1` (AJAX only)

### How to test speed (cache vs no-cache)

Important:
- Caching is applied to the **AJAX rows** requests used by infinite-scroll and live search (requests sent with `X-Requested-With: XMLHttpRequest`).
- You must be logged in as an admin, otherwise these routes will redirect to `/login` and timings won’t be meaningful.

#### Option A: Browser (recommended)

1. Login as admin in your browser.
2. Open DevTools → Network tab.
3. Go to `/admin/users`, type a search term, and scroll down to trigger AJAX loads.
4. Compare timings for:
   - `.../admin/users?...&bypass_cache=1` (no-cache baseline)
   - `.../admin/users?...` (cache warm, after the first request)

Repeat the same for `/admin/products`.

#### Option B: CLI with `curl`

1. Clear cache to force a cold start:

```bash
php artisan cache:clear
```

2. Run a baseline (no-cache) request (AJAX header required):

```bash
curl -s -o /dev/null \
  -H 'X-Requested-With: XMLHttpRequest' \
  -b 'YOUR_COOKIE_HERE' \
  -w 'users no-cache: %{time_total}s\n' \
  'http://127.0.0.1:8000/admin/users?q=John&page=1&bypass_cache=1'
```

3. Run cache requests (first one fills cache, next ones are warm):

```bash
curl -s -o /dev/null \
  -H 'X-Requested-With: XMLHttpRequest' \
  -b 'YOUR_COOKIE_HERE' \
  -w 'users cached: %{time_total}s\n' \
  'http://127.0.0.1:8000/admin/users?q=John&page=1'
```

Use the same approach for products by switching the URL to:
- `http://127.0.0.1:8000/admin/products?...`

Tip: `YOUR_COOKIE_HERE` should include your logged-in session cookie (copy the `Cookie` header from DevTools → Network for a request to `/admin/users`).

---

## 5.2) Cache Invalidation Strategy (Users / Products)

When a `User` or `Product` is created/updated/deleted:

- observers bump a version counter in cache (`search:ver:users` / `search:ver:products`)
- cache keys include the version, so older entries become unreachable immediately
- if cache tags are supported (Redis), tags are flushed too (`search:*`)

Observers:
- `app/Observers/UserObserver.php`
- `app/Observers/ProductObserver.php`

---

## 6) Project Structure (Important Files)

- `app/Http/Controllers/Admin/*` - admin pages
- `app/Http/Controllers/Auth/*` - authentication flows
- `app/Actions/Search/RunCombinedSearchAction.php` - combined search logic
- `app/Repositories/*` - data access layer
- `app/Services/*` - business service layer
- `app/Services/Admin/AdminListingService.php` - cached HTML rows for admin lists
- `app/Services/SearchCacheService.php` - cache keys / TTL / locks / tags
- `app/Http/Middleware/EnsureAdmin.php` - admin-only guard
- `resources/views/admin/*` - admin Blade templates
- `routes/web.php` - main web routes
- `routes/auth.php` - auth routes
- `database/seeders/DatabaseSeeder.php` - demo data

---

## 7) Run Tests

```bash
php artisan test
```

If you get asset-related errors, run:

```bash
npm run build
```

---

## 8) Optional Useful Commands

Re-index Scout records:

```bash
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Product"
```

Or use one command:

```bash
composer search:sync
```

Clear caches:

```bash
php artisan optimize:clear
```

Code standard check (no file changes):

```bash
composer cs
```

Auto-fix code style:

```bash
composer format
```

---

## 9) Troubleshooting

- **Cannot login as admin**
  - Confirm seed completed successfully.
  - Verify admin email/password in `DatabaseSeeder`.

- **Database errors**
  - Recheck `.env` DB credentials.
  - Run `php artisan migrate:fresh --seed`.

- **Search seems empty**
  - Ensure users/products data exists.
  - Verify `SCOUT_DRIVER=database` in `.env`.
  - Run `composer search:sync` after large data changes.

- **Code style issues before commit**
  - Run `composer cs` to check style.
  - Run `composer format` to auto-fix style.

---

## 10) Implementation Summary (Step by Step)

High-level implementation order used in this project:

1. Added auth scaffolding controllers and views.
2. Added `role` field in users table and admin middleware.
3. Created Product model, migration, factory, and relations.
4. Added repositories for users/products (pagination + search).
5. Added services and actions for combined search.
6. Added admin controllers and Blade pages.
7. Added routes for auth, profile, dashboard, and admin area.
8. Added seeders for admin + bulk users/products.
9. Added Scout + Telescope configuration.
10. Added feature tests for auth/profile flows.

11. Added Redis-backed caching for admin lists + combined search.
12. Added cache invalidation via model observers.

---

## License

This project uses the MIT license.
