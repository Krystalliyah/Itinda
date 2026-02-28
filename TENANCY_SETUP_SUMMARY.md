# StoreKoto Multi-Tenancy Setup Summary

## What We Built
A multi-tenant system where admin can create vendors (tenants), each getting their own isolated database and subdomain.

---

## 1. Package Installation (Stancl Tenancy)

**Package:** `stancl/tenancy` - Laravel multi-tenancy package

**Installation:**
```bash
composer require stancl/tenancy
php artisan tenancy:install
```

**What it provides:**
- Automatic database switching per tenant
- Domain/subdomain identification
- Tenant isolation (database, cache, files)

---

## 2. Configuration Files Changed

### A. `.env`
**Location:** `.env`
**Changes:**
```env
APP_URL=http://storekoto.test
APP_DOMAIN=storekoto.test  # Added this
DB_DATABASE=Storekoto       # Central database
```

**Purpose:** Set the main domain for the application

---

### B. `config/app.php`
**Location:** `config/app.php`
**Changes:**
```php
'url' => env('APP_URL', 'http://localhost'),
'domain' => env('APP_DOMAIN', 'localhost'),  // Added this line
```

**Purpose:** Make APP_DOMAIN accessible via `config('app.domain')`

---

### C. `config/database.php`
**Location:** `config/database.php`
**Changes:** Added `central` connection
```php
'connections' => [
    'mysql' => [...],  // Default connection
    
    // Added this:
    'central' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'tindahan'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
],
```

**Purpose:** 
- Explicit connection for central/landlord database
- Sessions always use this connection (never switches to tenant DB)

---

### D. `config/tenancy.php`
**Location:** `config/tenancy.php`
**Changes:**
```php
return [
    'tenant_model' => App\Models\Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,
    
    // Added storekoto.test to central domains
    'central_domains' => [
        '127.0.0.1',
        'localhost',
        'storekoto.test',  // Added
    ],
    
    'bootstrappers' => [
        DatabaseTenancyBootstrapper::class,  // Switches DB
        CacheTenancyBootstrapper::class,     // Scopes cache
        FilesystemTenancyBootstrapper::class,// Scopes files
        QueueTenancyBootstrapper::class,     // Scopes jobs
    ],
    
    'database' => [
        'central_connection' => env('DB_CONNECTION', 'central'),
        'prefix' => 'tenant',  // Database name: tenant{id}
        'suffix' => '',
    ],
    
    'features' => [
        Stancl\Tenancy\Features\ViteBundler::class,  // Enabled for Vite assets
    ],
    
    'migration_parameters' => [
        '--force' => true,
        '--path' => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],
];
```

**Purpose:**
- Define which domains are central (admin/customer)
- Configure tenant database naming: `tenant{subdomain}`
- Enable Vite asset handling for tenant subdomains

---

## 3. Routes Configuration

### A. `routes/web.php`
**Location:** `routes/web.php`
**Changes:**
```php
// Pin central routes to main domain
Route::domain(config('app.domain'))->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    })->name('home');

    Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
        $user = auth()->user();
        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }
        if ($user->hasRole('vendor')) {
            return redirect()->route('vendor.dashboard');
        }
        abort(403, 'No role assigned.');
    })->name('dashboard');

    // Central domain routes
    require __DIR__.'/settings.php';
    require __DIR__.'/admin.php';
    require __DIR__.'/customer.php';
});

// Vendor routes
require __DIR__.'/vendor.php';
```

**Purpose:** Ensure admin/customer routes only work on `storekoto.test`, not tenant subdomains

---

### B. `routes/admin.php`
**Location:** `routes/admin.php`
**Changes:**
```php
use App\Http\Controllers\Admin\TenantController;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn() => inertia('admin/Dashboard'))->name('dashboard');
        Route::get('/vendors', fn() => inertia('admin/Vendors'))->name('vendors.index');
        Route::get('/customers', fn() => inertia('admin/Customers'))->name('customers.index');
        Route::get('/categories', fn() => inertia('admin/Categories'))->name('categories');
        Route::get('/reports', fn() => inertia('admin/Reports'))->name('reports');
        
        // Tenant management - Added these 3 routes
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });
```

**Purpose:** Admin can manage tenants (create, view, delete)

---

### C. `routes/tenant.php`
**Location:** `routes/tenant.php`
**Changes:**
```php
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,        // Identifies tenant by domain
    PreventAccessFromCentralDomains::class,  // Blocks central domain
])->group(function () {
    Route::redirect('/', '/dashboard');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', function () {
            return inertia('Dashboard', [
                'tenant' => tenant(),
                'tenantId' => tenant('id'),
            ]);
        })->name('tenant.dashboard');
    });
});
```

**Purpose:** Routes for tenant subdomains (e.g., `ham.storekoto.test`)

---

## 4. Models

### A. `app/Models/Tenant.php`
**Location:** `app/Models/Tenant.php`
**Existing code:**
```php
namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'email'];
    }
}
```

**Purpose:**
- Represents a tenant (vendor)
- Has relationship with domains
- Stores: id, name, email in real columns (not JSON)

---

## 5. Controllers

### A. `app/Http/Controllers/Admin/TenantController.php`
**Location:** `app/Http/Controllers/Admin/TenantController.php`
**Created new file:**
```php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    // Show all tenants
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->get();
        return inertia('admin/Tenants', [
            'tenants' => ['data' => $tenants],
        ]);
    }

    // Create new tenant
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subdomain' => 'required|string|max:63|regex:/^[a-z0-9-]+$/',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'id' => Str::slug($validated['subdomain']),
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Create domain
        $tenant->domains()->create([
            'domain' => $validated['subdomain'] . '.' . config('app.domain'),
        ]);

        return back()->with('success', "Tenant created! Database: tenant{$tenant->id}");
    }

    // Delete tenant
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();  // Triggers TenantDeleted event → deletes database
        return back()->with('success', 'Tenant and database deleted!');
    }
}
```

**Data Flow:**
1. **Validation:** Validates name, email, subdomain
2. **Create Tenant:** Inserts into `tenants` table in central DB
3. **Create Domain:** Inserts into `domains` table (e.g., `ham.storekoto.test`)
4. **Event Triggered:** `TenantCreated` event fires
5. **Job Pipeline:** Runs `CreateDatabase` → `MigrateDatabase` jobs
6. **Result:** New database `tenantham` created with tables

---

## 6. Frontend (Vue/Inertia)

### A. `resources/js/pages/admin/Tenants.vue`
**Location:** `resources/js/pages/admin/Tenants.vue`
**Created new file:**

**Key parts:**
```typescript
// Form handling
const form = useForm({
    name: '',
    email: '',
    subdomain: ''
});

// Submit to create tenant
const submit = () => {
    form.post('/admin/tenants', {
        onSuccess: () => form.reset(),
    });
};

// Delete tenant
const deleteTenant = (id: string) => {
    if (confirm('Delete this tenant and its database?')) {
        router.delete(`/admin/tenants/${id}`);
    }
};
```

**Template:**
- Form to create tenant (name, email, subdomain)
- Table showing all tenants with their domains and databases
- Delete button for each tenant

**Purpose:** Admin UI to manage tenants

---

## 7. Middleware Changes

### A. `bootstrap/app.php`
**Location:** `bootstrap/app.php`
**Changes:**
```php
// Removed duplicate 'role' middleware registration
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,  // Use Spatie's
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    'vendor.store_access' => \App\Http\Middleware\EnsureVendorHasStoreAccess::class,
]);
```

**Purpose:** Fix role checking to use Spatie permissions

---

### B. `app/Http/Middleware/RedirectBasedOnRole.php`
**Location:** `app/Http/Middleware/RedirectBasedOnRole.php`
**Changes:**
```php
// Changed from checking $user->role column to using Spatie roles
if ($request->user()->hasRole('admin')) {
    return redirect()->route('admin.dashboard');
}
if ($request->user()->hasRole('vendor')) {
    return redirect()->route('vendor.dashboard');
}
if ($request->user()->hasRole('customer')) {
    return redirect()->route('customer.dashboard');
}
```

**Purpose:** Redirect users to correct dashboard based on Spatie role

---

## 8. Database Structure

### Central Database: `Storekoto`
**Tables:**
- `users` - All users (admin, vendors, customers)
- `tenants` - Tenant records (id, name, email)
- `domains` - Domain mappings (domain, tenant_id)
- `roles` - Spatie roles
- `model_has_roles` - User-role assignments
- `sessions` - User sessions (always on central DB)
- Other central tables

### Tenant Databases: `tenant{subdomain}`
**Example:** `tenantham` for subdomain "ham"

**Tables (from `database/migrations/tenant/`):**
- `categories`
- `products`
- `staff`
- `suppliers`
- `product_supplier`
- `inventory_movements`

---

## 9. How Tenancy Works (Step by Step)

### Creating a Tenant:
```
1. Admin fills form: name="Ham Store", email="ham@example.com", subdomain="ham"
2. POST /admin/tenants
3. TenantController validates data
4. Creates tenant record in central DB:
   - tenants table: id="ham", name="Ham Store", email="ham@example.com"
5. Creates domain record:
   - domains table: domain="ham.storekoto.test", tenant_id="ham"
6. TenantCreated event fires
7. Job pipeline runs:
   - CreateDatabase job: Creates MySQL database "tenantham"
   - MigrateDatabase job: Runs migrations from database/migrations/tenant/
8. Result: New database with all tenant tables ready
```

### Accessing Tenant Subdomain:
```
1. User visits: ham.storekoto.test
2. InitializeTenancyByDomain middleware:
   - Reads hostname: "ham.storekoto.test"
   - Queries domains table in central DB
   - Finds tenant_id: "ham"
   - Calls tenancy()->initialize($tenant)
3. DatabaseTenancyBootstrapper runs:
   - Switches default DB connection to "tenantham"
4. All Eloquent queries now use tenant database
5. User sees tenant-specific data
6. When request ends, tenancy()->end() switches back to central DB
```

---

## 10. Key Files Summary

### Configuration:
- `.env` - APP_DOMAIN added
- `config/app.php` - domain config added
- `config/database.php` - central connection added
- `config/tenancy.php` - central domains, features configured

### Routes:
- `routes/web.php` - Domain-pinned central routes
- `routes/admin.php` - Tenant CRUD routes added
- `routes/tenant.php` - Tenant subdomain routes

### Backend:
- `app/Models/Tenant.php` - Tenant model (existing)
- `app/Http/Controllers/Admin/TenantController.php` - Tenant CRUD (created)
- `app/Http/Middleware/RedirectBasedOnRole.php` - Fixed role checking
- `bootstrap/app.php` - Fixed middleware aliases

### Frontend:
- `resources/js/pages/admin/Tenants.vue` - Tenant management UI (created)

### Database:
- `database/migrations/tenant/` - Tenant-specific migrations (existing)
- Central DB: `Storekoto`
- Tenant DBs: `tenant{subdomain}` (auto-created)

---

## 11. Testing the Setup

### Create Admin User:
```bash
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=AdminUserSeeder
```

**Login:** `admin@storekoto.test` / `password`

### Create a Tenant:
1. Visit: `http://storekoto.test/admin/tenants`
2. Fill form:
   - Store Name: "Ham Store"
   - Email: "ham@example.com"
   - Subdomain: "ham"
3. Click "Create Tenant"
4. Check MySQL: New database `tenantham` appears!

### Access Tenant:
1. Visit: `http://ham.storekoto.test`
2. Tenant-specific dashboard loads
3. All data isolated to `tenantham` database

---

## 12. What Stancl/Tenancy Provides

**Automatic Features:**
- ✅ Database creation per tenant
- ✅ Database switching based on domain
- ✅ Migration running for tenant databases
- ✅ Database deletion when tenant deleted
- ✅ Cache scoping per tenant
- ✅ File storage scoping per tenant
- ✅ Queue job scoping per tenant
- ✅ Vite asset handling for subdomains

**You only need to:**
- Configure domains
- Create tenant via controller
- Package handles the rest!

---

## Summary

**What we built:** Simple tenant CRUD where each tenant gets isolated database

**Key concept:** 
- Central domain (`storekoto.test`) = Admin manages tenants
- Tenant subdomains (`ham.storekoto.test`) = Vendor manages their data
- Each tenant = separate database (`tenantham`)
- Stancl handles all the switching automatically

**Files changed:** 11 files (configs, routes, controller, Vue page, middleware)

**Result:** Working multi-tenancy with database isolation per vendor!
