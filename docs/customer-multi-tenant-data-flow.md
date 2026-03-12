# Customer Dashboard Multi-Tenant Data Flow

This document explains how the customer dashboard on the central domain retrieves product information from **every tenant database** in the iTinda application. It covers:

- database structure and table relationships
- service logic that switches tenant contexts
- caching and error handling
- front-end consumption and the debug panel

---

## 1. Background

The application uses `stancl/tenancy` with a **central database** for shared data and **separate databases per tenant** (vendor store). Customers log in once on the central site and may browse multiple tenant stores without re-authenticating.

For debugging and verification purposes a special panel (`Tenant Products Proof Panel`) on the customer dashboard runs a proof-of-concept process that reads a few rows from the `products` table in each tenant database. It demonstrates that the central code can dynamically enter each tenant context, run queries, and then exit cleanly.

---

## 2. Database & models

### Central database

- `users` – all users (customers, vendors, admins)
- `tenants` – vendor records (`is_approved` flag, `user_id` reference)
- `domains` – domain rows linked to `tenants`
- `sessions`, `tenant_auth_tokens`, etc.

Model snippet (central `Tenant`):

```php
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'email', 'is_approved', 'user_id'];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Tenant databases

Each tenant DB contains its own `users`, `products`, `categories`, etc.  A `sessions` table was added via a tenant migration so tenant sessions are stored separately.

```php
// database/migrations/tenant/2026_03_03_000001_create_sessions_table.php
Schema::create('sessions', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->foreignId('user_id')->nullable()->index();
    // ...
});
```

---

## 3. Service logic: `ProductAggregatorService`

This service performs the cross-tenant operations. The proof panel method is defined as:

```php
public function tenantProductsProof(): array
{
    return Cache::remember('tenant_products_proof', 60, function () {
        return Tenant::where('is_approved', true)
            ->with('domains')
            ->get()
            ->map(function ($tenant) {
                $result = [
                    'tenant_id'      => $tenant->id,
                    'store_name'     => $tenant->name,
                    'domain'         => $tenant->domains->first()->domain ?? null,
                    'tenant_db'      => null,
                    'products_count' => 0,
                    'sample_products'=> [],
                    'status'         => 'ok',
                    'error_message'  => null,
                ];

                try {
                    $tenant->run(function () use (&$result) {
                        $result['tenant_db'] = DB::connection()->getDatabaseName();

                        if (!Schema::hasTable('products')) {
                            throw new Exception('products table does not exist');
                        }

                        $result['products_count'] = DB::table('products')->count();
                        $result['sample_products'] = DB::table('products')
                            ->select(['id', 'name', 'price'])
                            ->limit(5)
                            ->get()
                            ->map(fn($p) => [
                                'id'    => $p->id,
                                'name'  => $p->name,
                                'price' => $p->price,
                            ])->toArray();
                    });
                } catch (Throwable $e) {
                    $result['status'] = 'error';
                    $result['error_message'] = $e->getMessage();
                }

                return $result;
            })->toArray();
    });
}
```

- The `Tenant::...->get()` call happens on the central DB.
- For each tenant, `$tenant->run(...)` temporarily switches the database connection to that tenant, runs the closure, and reverts automatically.
- Errors are caught and recorded so one bad tenant doesn't break the entire dashboard.
- Results are cached for 60 seconds.

Other service methods (e.g. `getAllProducts`) follow the same pattern when building the central product catalog.

---

## 4. Routing and controller

The `/customer/dashboard` route in `routes/customer.php` was modified to call the service and provide Inertia props:

```php
Route::get('/dashboard', function () {
    $proof = Cache::remember('tenant_products_proof', 60, function () {
        return app(ProductAggregatorService::class)->tenantProductsProof();
    });

    return inertia('customer/Dashboard', [
        'tenantProductsProof' => $proof,
        'showDebugPanel'      => config('app.debug'),
    ]);
})->name('dashboard');
```

- `showDebugPanel` toggles visibility based on `APP_DEBUG` (can be changed to role check).
- The route remains under the `customer` middleware stack (`auth`, `verified`, `role:customer`).

---

## 5. Front-end Vue component

`resources/js/pages/customer/Dashboard.vue` now accepts the new props and renders the debug panel:

```vue
<script setup lang="ts">
const props = defineProps<{ tenantProductsProof?: Array<any>; showDebugPanel?: boolean }>();
</script>

<template>
  <!-- existing header and sidebar omitted -->
  <main :class="contentClass">
    <div class="container">
      <h1>Customer Dashboard</h1>
      <p>Browse stores, compare prices, and manage your orders</p>

      <div v-if="showDebugPanel && tenantProductsProof && tenantProductsProof.length" class="mt-8">
        <h2 class="text-lg font-bold mb-4">Tenant Products Proof Panel (Debug)</h2>
        <div class="grid ...">
          <div v-for="t in tenantProductsProof" :key="t.tenant_id" class="p-4 border rounded shadow-sm">
            <h3>{{ t.store_name || t.tenant_id }} ({{ t.tenant_id }})</h3>
            <p>{{ t.domain }} · DB: {{ t.tenant_db }}</p>
            <p>Status: <span :class="t.status==='ok'?'text-green-600':'text-red-600'">{{ t.status }}</span></p>
            <p v-if="t.status==='error'" class="text-xs text-red-600">Error: {{ t.error_message }}</p>
            <p class="mt-2">Products count: {{ t.products_count }}</p>
            <ul class="list-disc list-inside mt-2">
              <li v-for="p in t.sample_products" :key="p.id">
                {{ p.id }} - {{ p.name }} <span v-if="p.price">({{ p.price }})</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
```

This ensures the panel is unobtrusive and only appears during debugging.

---

## 6. Execution flow summary

1. Customer visits `/customer/dashboard` while logged in.
2. Route closure triggers, calling the `tenantProductsProof` service (cached).
3. Service iterates approved tenants, running closures to query each tenant DB.
4. Results (counts, samples, status) return to the central process and are cached.
5. Inertia response includes the `tenantProductsProof` array.
6. Vue component renders the panel showing per-tenant data.
7. PHP's tenancy state remains `false` globally; `tenant->run()` handles context switches.

---

## 7. Testing and validation

Manual steps:

1. Enable `APP_DEBUG=true` so panel appears.
2. Create or seed multiple tenants with distinct products.
3. Optionally create a tenant missing the `products` table to trigger error.
4. Visit dashboard; confirm each tenant card shows correct DB and product info.
5. Check logs for any warnings or errors; ensure original controller continues to function.

Troubleshooting:

- If the panel is missing, ensure `showDebugPanel` flag is true or the current user has correct role.
- If `tenantProductsProof` is empty, inspect cache (`php artisan tinker` or `Cache::get('tenant_products_proof')`).
- Verify that `tenancy()->initialized` remains false after the route by inserting a debug dump.

---

By following the patterns above, the central customer interface safely and efficiently reads from every tenant's database without compromising isolation or performance.