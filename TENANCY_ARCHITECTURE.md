# StoreKoto Multi-Tenancy Architecture

## Overview
StoreKoto uses a hybrid multi-tenancy model where:
- **Central Domain** (`storekoto.test`) - Admin and customer access
- **Tenant Subdomains** (`ham.storekoto.test`, `acme.storekoto.test`) - Vendor management

## User Roles & Access

### 1. Admin (Central Domain Only)
- Access: `storekoto.test/admin`
- Can create/manage tenants (vendors)
- Each tenant gets a unique subdomain
- Manages global system settings

### 2. Vendors (Tenant Subdomains)
- Access: `{subdomain}.storekoto.test` (e.g., `ham.storekoto.test`)
- Each vendor has isolated database
- Manages their own:
  - Products
  - Categories
  - Inventory
  - Staff
  - Suppliers

### 3. Customers (Central Domain)
- Access: `storekoto.test/customer`
- Browse products from ALL tenants
- Compare products across vendors
- Place orders (products from multiple vendors)

## Database Architecture

### Central Database (`Storekoto`)
Tables:
- `users` - All users (admin, vendors, customers)
- `tenants` - Vendor/tenant records
- `domains` - Subdomain mappings
- `sessions` - User sessions
- Other central tables

### Tenant Databases (`tenant{id}`)
Each vendor gets their own database:
- `tenant{subdomain}` (e.g., `tenantham`)

Tables per tenant:
- `products`
- `categories`
- `staff`
- `suppliers`
- `product_supplier`
- `inventory_movements`

## How It Works

### Creating a Tenant (Admin)

```php
// Admin creates tenant via TenantController
POST /admin/tenants
{
    "name": "Ham Store",
    "email": "ham@example.com",
    "subdomain": "ham"
}

// System automatically:
// 1. Creates tenant record in central DB
// 2. Creates domain: ham.storekoto.test
// 3. Creates database: tenantham
// 4. Runs tenant migrations
```

### Vendor Access (Tenant Subdomain)

```
1. Vendor visits: ham.storekoto.test
2. InitializeTenancyByDomain middleware:
   - Reads hostname: ham.storekoto.test
   - Looks up domain in central DB
   - Finds tenant: ham
   - Switches DB connection to: tenantham
3. All queries now go to tenant database
4. Vendor manages their inventory
```

### Customer Browsing (Central Domain)

```php
// Customer visits: storekoto.test/customer/products
// ProductAggregatorService:
// 1. Gets all tenants from central DB
// 2. For each tenant:
//    - Initialize tenancy (switch to tenant DB)
//    - Query products
//    - Add tenant info to products
//    - End tenancy (switch back to central DB)
// 3. Merge all products
// 4. Display to customer
```

## Key Files

### Configuration
- `config/tenancy.php` - Tenancy settings
- `config/database.php` - Database connections
- `.env` - APP_DOMAIN=storekoto.test

### Routes
- `routes/web.php` - Central domain routes (pinned)
- `routes/admin.php` - Admin tenant management
- `routes/customer.php` - Customer product browsing
- `routes/tenant.php` - Tenant subdomain routes

### Controllers
- `app/Http/Controllers/Admin/TenantController.php` - Create/manage tenants
- `app/Http/Controllers/Customer/ProductBrowseController.php` - Browse all products
- Tenant controllers (in tenant routes) - Vendor inventory management

### Services
- `app/Services/ProductAggregatorService.php` - Aggregate products from all tenants

### Models
- `app/Models/Tenant.php` - Tenant model
- `app/Models/User.php` - User model (central)

## Testing

### Create Test Tenant
```bash
php artisan tenant:create-test ham "Ham Store" "ham@example.com"
```

### Access Points
```
Admin:    http://storekoto.test/admin/tenants
Vendor:   http://ham.storekoto.test/dashboard
Customer: http://storekoto.test/customer/products
```

### Add to hosts file (Windows)
```
C:\Windows\System32\drivers\etc\hosts

127.0.0.1 storekoto.test
127.0.0.1 ham.storekoto.test
127.0.0.1 acme.storekoto.test
```

## Important Notes

1. **Session Handling**: Sessions use 'central' connection to prevent tenant switching issues

2. **Product Aggregation**: ProductAggregatorService properly initializes/ends tenancy for each query

3. **Domain Validation**: Subdomains must be unique and follow DNS naming rules

4. **Database Cleanup**: Deleting a tenant automatically deletes its database

5. **Middleware Order**:
   - Central routes: No tenancy middleware
   - Tenant routes: InitializeTenancyByDomain + PreventAccessFromCentralDomains

## Next Steps

1. Create admin UI for tenant management (Inertia pages)
2. Create customer product browsing UI
3. Implement vendor dashboard on tenant subdomains
4. Add product images and file storage (tenant-scoped)
5. Implement order system (cross-tenant orders)
