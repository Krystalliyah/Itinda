# 🛒 Storekoto — Setup & Data Flow Guide

> For contributors cloning this repo. Read this before touching any code.

---

## 📋 Requirements

Before you start, make sure you have these installed:

| Tool         | Version  | Notes                                                    |
| ------------ | -------- | -------------------------------------------------------- |
| PHP          | 8.2+     | With `pgsql`, `pdo_mysql`, `mbstring`, `xml` extensions  |
| Composer     | 2.x      | [getcomposer.org](https://getcomposer.org)               |
| Node.js      | 18+      | [nodejs.org](https://nodejs.org)                         |
| MySQL        | 8.0+     | A running local MySQL server                             |
| Laravel Herd | optional | Easiest way to manage PHP + local domains on Windows/Mac |

---

## 🚀 One-Time Setup (After Cloning)

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy the environment file
cp .env.example .env

# 3. Generate the app secret key
php artisan key:generate
```

### Configure your `.env`

Open `.env` and set your database credentials:

```env
APP_URL=http://storekoto.test
APP_DOMAIN=storekoto.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Storekoto         # Create this DB in MySQL first!
DB_USERNAME=root
DB_PASSWORD=your_password
```

> **Create the database first:** Run `CREATE DATABASE Storekoto;` in MySQL before the next step.

### Run Migrations & Seed

```bash
# Run all central database migrations
php artisan migrate

# Seed default roles and the admin user
php artisan db:seed
```

### Install & Build Frontend

```bash
npm install
npm run build        # For production
# OR
npm run dev          # For live development (keeps running)
```

---

## 📦 Key Packages Installed

These are the non-standard packages you need to know about:

### `stancl/tenancy` — Multi-tenancy

Handles creating separate databases for each vendor. When a vendor is approved, this package:

1. Creates a new MySQL database (e.g., `tenantabc-123`)
2. Runs all migrations in `database/migrations/tenant/` inside that new DB

**Config:** `config/tenancy.php`
**Service Provider:** `app/Providers/TenancyServiceProvider.php`

```bash
# Publish tenancy config (already done, just for reference)
php artisan vendor:publish --tag=tenancy
```

### `spatie/laravel-permission` — Roles

Manages `admin`, `vendor`, `customer` roles. Every user gets assigned a role on registration.

**Config:** `config/permission.php`

```bash
# Publish role tables (already done)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### `inertiajs/inertia-laravel` — Frontend Bridge

Connects Laravel controllers to Vue.js pages without building a separate API. Instead of returning JSON, controllers return `Inertia::render('PageName', $data)` and the Vue component receives `$data` as props.

### `laravel/fortify` — Authentication

Handles login, register, password reset, and 2FA. Routes like `/login` and `/register` are registered automatically.

---

## 🔄 Data Flow — How It All Connects

Here's a simple example: **a vendor fills out the "Set Up Your Store" form on their dashboard.**

```
[Vue Component] → [Inertia Form] → [Route] → [Controller] → [Database]
      ↑                                                            ↓
      └──────────────── [Inertia Response] ──────────────────────┘
```

### Step-by-step example:

#### 1. The Vue Form (`resources/js/Pages/vendor/Dashboard.vue`)

```javascript
const form = useForm({
    store_name: "",
    address: "",
    subdomain: "",
});

// When submitted:
form.post("/vendor/store/setup");
//  ↑↑↑ This sends a POST request to this URL with the form data
```

#### 2. The Route (`routes/vendor.php`)

```php
Route::post('/vendor/store/setup', [StoreSetupController::class, 'store'])
    ->name('vendor.store.create');
// This says: "when a POST hits /vendor/store/setup, call StoreSetupController@store"
```

#### 3. The Controller (`app/Http/Controllers/Vendor/StoreSetupController.php`)

```php
public function store(Request $request)
{
    $data = $request->validate([
        'store_name' => 'required|string',
        'subdomain'  => 'required|unique:domains,domain',
    ]);

    // Create a Tenant record in the central DB
    $tenant = Tenant::create([
        'name'        => $data['store_name'],
        'user_id'     => auth()->id(),
        'is_approved' => false,   // ← Pending until admin approves
    ]);

    // Create the subdomain record
    $tenant->domains()->create([
        'domain' => $data['subdomain'] . '.storekoto.test',
    ]);

    return redirect()->route('vendor.dashboard');
    //     ↑↑↑ Sends user back to the dashboard
}
```

#### 4. The Database

`Tenant::create()` writes a row to the **central** `tenants` table.
No vendor database is created yet — that only happens when the **admin approves**.

#### 5. Back to Vue

Inertia redirects back to the dashboard. `HandleInertiaRequests.php` automatically re-shares `hasStore: true` as a page prop, so the Vue component now shows the "Pending Approval" banner instead of the setup form.

---

## 🗂️ Where Things Live

```
storekoto/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/VendorController.php     ← Admin approves/deletes vendors
│   │   │   └── Vendor/StoreSetupController.php ← Handles store setup form
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php       ← Shares auth data with ALL Vue pages
│   │       └── EnsureVendorIsApproved.php      ← Blocks unapproved vendors from store pages
│   ├── Models/
│   │   ├── User.php                            ← Auth user, has roles via Spatie
│   │   └── Tenant.php                          ← Vendor store record (central DB)
│   └── Providers/
│       ├── TenancyServiceProvider.php          ← Controls when tenant DBs are created/deleted
│       └── FortifyServiceProvider.php          ← Customizes login/register views
│
├── routes/
│   ├── web.php         ← Entry point, domain-pinned, role-based redirects
│   ├── admin.php       ← Admin-only routes (requires role:admin)
│   ├── vendor.php      ← Vendor routes (requires role:vendor)
│   ├── customer.php    ← Customer routes
│   ├── settings.php    ← Profile/password settings (all roles)
│   └── tenant.php      ← Tenant subdomain routes (stancl stub)
│
├── database/
│   ├── migrations/          ← Central DB tables (users, tenants, domains, roles)
│   └── migrations/tenant/   ← Per-vendor DB tables (products, inventory, staff, etc.)
│
└── resources/js/
    ├── Pages/
    │   ├── admin/     ← Admin dashboard Vue pages
    │   ├── vendor/    ← Vendor dashboard Vue pages
    │   └── customer/  ← Customer-facing pages
    ├── layouts/       ← Page shell layouts (sidebar, header, etc.)
    └── components/    ← Reusable UI bits (Sidebar, Header, Nav, etc.)
```

---

## 👤 Default Admin Account

After seeding, you can log in with:

```
Email:    admin@storekoto.test
Password: (set in DatabaseSeeder.php)
```

---

## ⚡ Daily Development

```bash
# Start everything at once (server + queue + vite watcher)
composer run dev

# OR individually:
php artisan serve       # Laravel backend at http://storekoto.test
npm run dev             # Vite frontend watcher
php artisan queue:listen --tries=1   # Queue worker (needed for DB jobs)
```

---

## ❓ Common Issues

| Problem                        | Fix                                                                                       |
| ------------------------------ | ----------------------------------------------------------------------------------------- |
| `No role assigned` after login | Run `php artisan db:seed` to create roles                                                 |
| Tenant subdomain is 404        | Make sure the domain is in your hosts file and Herd is configured for wildcard subdomains |
| `SQLSTATE` errors              | Check `.env` DB credentials, and that the `Storekoto` database exists                     |
| White page after deploy        | Run `npm run build` and `php artisan config:clear`                                        |
