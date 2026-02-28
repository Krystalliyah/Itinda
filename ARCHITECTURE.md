# 🛒 Storekoto — System Architecture Guide
> _For anyone new to the codebase. Read this first!_

---

## 🧠 What Is This App?

**Storekoto** is a **multi-vendor e-commerce platform**. Think of it like Shopify — one central system where vendors (store owners) apply to get their own store, and admins approve them.

It uses **Laravel** on the backend, **Vue.js (Inertia.js)** on the frontend, and a package called **`stancl/tenancy`** to give each approved vendor their very own separate database.

---

## 👥 Types of Users (Roles)

Every user that registers gets a **role** assigned to them. We use a package called `spatie/laravel-permission` to manage this.

| Role       | Description                                                              |
|------------|--------------------------------------------------------------------------|
| `admin`    | System administrator. Can approve/reject vendors and manage the platform |
| `vendor`   | Store owner. Submits a store setup form and waits for admin approval     |
| `customer` | End user who shops at vendor stores                                      |

Roles are stored in the `roles` and `model_has_roles` tables in the **central database** (managed by the migration in [`create_permission_tables.php`](database/migrations/2026_02_27_180308_create_permission_tables.php)).

---

## 🗄️ Databases — Two Layers

This is the most important concept in Storekoto. There are **two types of databases**:

### 1️⃣ Central Database (`storekoto`)
This is the main database. Everything global lives here.

| Table | What's In It |
|---|---|
| `users` | All user accounts (admins, vendors, customers) |
| `tenants` | One row per vendor store. Has `is_approved` and `user_id` |
| `domains` | The subdomain for each vendor (e.g., `hamstore.storekoto.test`) |
| `roles`, `permissions`, `model_has_roles` | Spatie role/permission tables |

### 2️⃣ Tenant Databases (`tenant{id}`)
Once a vendor is **approved**, a **separate database** is created for them automatically. Each vendor's store gets its own isolated database containing:

| Table | What's In It |
|---|---|
| `categories` | The vendor's product categories |
| `products` | The vendor's product listings |
| `staff` | Staff members assigned to the store |
| `suppliers` | Suppliers the store works with |
| `inventory_movements` | Stock tracking |

> 📁 These isolated tables are defined in [`database/migrations/tenant/`](database/migrations/tenant/).

---

## 🌊 The Vendor Lifecycle — Step by Step

Here's exactly what happens from a vendor registering to getting their store:

```
1. User registers → gets 'vendor' role
       ↓
2. Vendor logs in → sees an empty Dashboard with a "Set Up Your Store" form
       ↓
3. Vendor fills out the form (store name, subdomain, etc.)
       ↓
4. A 'Tenant' record is created in the central database with is_approved = false
   A 'Domain' record is also created (e.g., hamstore.storekoto.test)
       ↓
5. Vendor sees "Pending Approval" banner — they can't access Products/Inventory yet
       ↓
6. Admin sees the vendor in their Vendors page under "Pending Approval"
       ↓
7. Admin clicks "Approve"
       ↓
8. The system creates a brand new isolated database (tenant{id}) for this vendor
   and runs all the tenant migrations (categories, products, etc.)
       ↓
9. Vendor's dashboard is now fully unlocked! 🎉
```

---

## 📂 Key Files to Know

### 🗺️ Routing

| File | What It Does |
|---|---|
| [`routes/web.php`](routes/web.php) | The main entry point. Routes by role using `hasRole()`. Includes admin, customer, and settings routes |
| [`routes/admin.php`](routes/admin.php) | Admin-only routes. Protected by `role:admin` middleware. Includes vendor management (list, approve, delete) |
| [`routes/vendor.php`](routes/vendor.php) | Vendor routes. Split into 2 groups: **open** (dashboard, store setup) and **protected** (requires approval) |
| [`routes/settings.php`](routes/settings.php) | User settings (profile, password) — available to all roles |

### 🤖 Middleware

Middleware is like a "bouncer" that checks our credentials before letting us through a route.

| File | What It Does |
|---|---|
| [`app/Http/Middleware/EnsureVendorIsApproved.php`](app/Http/Middleware/EnsureVendorIsApproved.php) | Blocks unapproved vendors from accessing Products, Orders, etc. Redirects them to the dashboard |
| [`app/Http/Middleware/HandleInertiaRequests.php`](app/Http/Middleware/HandleInertiaRequests.php) | Shares data with the frontend globally (user info, roles, `hasStore`, `storeIsApproved`) |
| `role:admin` / `role:vendor` | Provided by the Spatie library. Checks if the user has the required role |

### 🏗️ Controllers

| File | What It Does |
|---|---|
| [`app/Http/Controllers/Vendor/StoreSetupController.php`](app/Http/Controllers/Vendor/StoreSetupController.php) | Handles the vendor store creation form. Creates a `Tenant` and `Domain` record with `is_approved = false` |
| [`app/Http/Controllers/Admin/VendorController.php`](app/Http/Controllers/Admin/VendorController.php) | Handles Admin's Vendor Management page. Contains `index`, `store`, `approve`, and `destroy` methods |

### 🏛️ Models

| File | What It Does |
|---|---|
| [`app/Models/User.php`](app/Models/User.php) | Standard Laravel user model. Uses `HasRoles` trait from Spatie |
| [`app/Models/Tenant.php`](app/Models/Tenant.php) | Represents a vendor's store. Has `user_id`, `name`, `email`, `is_approved`. Uses `stancl/tenancy` |

### 🛢️ Migrations (Central DB)

| File | What It Creates |
|---|---|
| [`0001_01_01_000000_create_users_table.php`](database/migrations/0001_01_01_000000_create_users_table.php) | The `users` table |
| [`2019_09_15_000010_create_tenants_table.php`](database/migrations/2019_09_15_000010_create_tenants_table.php) | The `tenants` table (base, created by the tenancy package) |
| [`2019_09_15_000020_create_domains_table.php`](database/migrations/2019_09_15_000020_create_domains_table.php) | The `domains` table (subdomains per tenant) |
| [`2026_02_27_000001_create_permission_tables.php`](database/migrations/2026_02_27_180308_create_permission_tables.php) | Spatie roles & permissions tables |
| [`2026_02_27_100000_add_name_email_to_tenants.php`](database/migrations/2026_02_27_100000_add_name_email_to_tenants_table.php) | Adds `name` and `email` to `tenants` |
| [`2026_02_28_062542_add_is_approved_to_tenants.php`](database/migrations/2026_02_28_062542_add_is_approved_to_tenants_table.php) | Adds `is_approved` to `tenants` |
| [`2026_02_28_065448_add_user_id_to_tenants.php`](database/migrations/2026_02_28_065448_add_user_id_to_tenants_table.php) | Links a vendor user to their tenant |

### 🛢️ Migrations (Tenant DB — inside `database/migrations/tenant/`)

These run inside **each vendor's individual database** when they get approved.

| File | What It Creates |
|---|---|
| `create_categories_table.php` | Product categories for this store |
| `create_products_table.php` | Product listings for this store |
| `create_staff_table.php` | Staff members of this store |
| `create_suppliers_table.php` | Suppliers for this store |
| `create_product_supplier_table.php` | Links products to their suppliers |
| `create_inventory_movements_table.php` | Tracks stock movements |

---

## 🖥️ Frontend Pages (Vue.js)

All frontend pages are in `resources/js/Pages/`.

| File | What It Shows |
|---|---|
| [`Pages/admin/Vendors.vue`](resources/js/Pages/admin/Vendors.vue) | The Admin's vendor management page. Shows pending/active tables |
| [`Pages/vendor/Dashboard.vue`](resources/js/Pages/vendor/Dashboard.vue) | The vendor's main dashboard. Shows setup form if no store, "Pending" banner if awaiting approval |
| `Pages/settings/Profile.vue` | User profile settings (works for all roles) |

### Shared Layouts

| File | What It Does |
|---|---|
| [`layouts/AppLayout.vue`](resources/js/layouts/AppLayout.vue) | Generic wrapper for pages like Settings. Reads user role and loads the correct Sidebar |
| [`layouts/VendorLayout.vue`](resources/js/layouts/VendorLayout.vue) | The layout shell used exclusively for vendor pages |
| [`components/Sidebar.vue`](resources/js/components/Sidebar.vue) | The custom dark-green sidebar shell (takes a `role` prop) |
| [`components/navigation/AdminNav.vue`](resources/js/components/navigation/AdminNav.vue) | Navigation links shown inside the sidebar for admins |
| [`components/navigation/VendorNav.vue`](resources/js/components/navigation/VendorNav.vue) | Navigation links shown inside the sidebar for vendors (hides links if store is unapproved) |
| [`components/VendorStoreSetupBanner.vue`](resources/js/components/VendorStoreSetupBanner.vue) | A banner shown at the top of vendor pages to nudge them toward setup or show "Pending" status |

---

## 🔀 Data Flow Summary

```
Browser Request
    │
    ▼
routes/*.php  ─── checks middleware (auth, role, vendor.is_approved)
    │
    ▼
Controller (e.g., VendorController, StoreSetupController)
    │  reads/writes to central DB (Tenant, User, Domain)
    ▼
Inertia::render('PageName', [...data...])
    │  passes PHP data as JS props to the Vue component
    ▼
Vue Page Component (Pages/vendor/Dashboard.vue, etc.)
    │  renders the UI, shows the right state
    ▼
User sees the page!
```

---

## 🚀 Quick Commands

```bash
# Run all migrations on central database
php artisan migrate

# Seed default roles + admin user
php artisan db:seed

# Start the development server
php artisan serve

# Build frontend assets
npm run build

# Watch frontend for changes (dev mode)
npm run dev
```

---

## 💡 Tips for Navigating the Code

- **Start with `routes/web.php`** – it's the entry point for all requests.
- **Follow the Controller** – routes call controller methods, which do the actual logic.
- **Check migrations in order** – the timestamp prefix tells you the order they were created/applied.
- **Look in `resources/js/Pages/`** – each `.vue` file is one "page" a user sees.
- **Look in `resources/js/components/`** – reusable UI parts like sidebars, banners, etc.
