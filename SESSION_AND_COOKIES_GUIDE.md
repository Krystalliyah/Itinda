# Session & Cookie Guide for Multi-Tenant Subdomains

## Quick Answer
**SESSION_DOMAIN=.storekoto.test** tells the browser: "This session cookie is valid on `storekoto.test`, `inasal.storekoto.test`, `vendor2.storekoto.test`, etc." — allowing a user logged in on the central domain to automatically be authenticated on all subdomains without re-logging in.

---

## Part 1: What Are Cookies and Sessions? (Beginner Concepts)

### 🍪 What is a Cookie?
A cookie is a small piece of data that the server sends to the browser, and the browser automatically sends it back with every request to that server.

**Example:**
1. User visits `storekoto.test/login` and logs in.
2. Server says: "Great! Here's a cookie called `storekoto-session` with ID `abc123`" (Set-Cookie header).
3. Browser stores it.
4. Next request to `storekoto.test/dashboard`? Browser automatically sends: "Cookie: storekoto-session=abc123".
5. Server reads the cookie and says "Oh, that's user 5, logged in!"

### 📝 What is a Session?
A session is server-side data tied to a cookie ID. The cookie is just the **identifier**; the session is the **actual data**.

**Real example:**
```
Browser sends: Cookie: SESSIONID=xyz789
   ↓
Server looks up: sessions table, row where id='xyz789'
   ↓
Server finds: {user_id: 5, role: 'vendor', created_at: ..., last_activity: ...}
   ↓
Laravel knows: This is user 5, they're a vendor
```

### 🌐 The Problem: Cookie Domain Scope
By default, a cookie is scoped to the **exact domain** it was created on:
- Cookie set on `storekoto.test` → only sent to `storekoto.test` requests
- Cookie NOT sent to `inasal.storekoto.test` requests (different host)
- **Result:** User logs in on `storekoto.test`, then visits `inasal.storekoto.test` and is treated as a guest

### ✅ The Solution: SESSION_DOMAIN=.storekoto.test
The leading dot (`.`) is a special browser cookie flag that means: "This cookie is valid for this domain AND all subdomains."

```
When server sets: Set-Cookie: storekoto-session=abc123; Domain=.storekoto.test
Browser behavior:
  → Valid for storekoto.test ✅
  → Valid for inasal.storekoto.test ✅
  → Valid for vendor2.storekoto.test ✅
  → NOT valid for other.com ❌
```

---

## Part 2: Login Flow (Vue → Fortify → Redirect to Subdomain)

### Step 0: Your .env Configuration
```env
APP_URL=http://storekoto.test
APP_DOMAIN=storekoto.test
SESSION_DRIVER=database
SESSION_DOMAIN=.storekoto.test           # ← This is the magic!
SESSION_SECURE_COOKIE=false              # Local dev (HTTP not HTTPS)
SESSION_SAME_SITE=lax                    # Allow cross-subdomain cookies
```

**What each line does:**
- `SESSION_DOMAIN=.storekoto.test` → Browser stores cookie for all `.storekoto.test` subdomains
- `SESSION_DRIVER=database` → Server stores session data in `sessions` table (not files)
- `SESSION_SECURE_COOKIE=false` → Allow cookies over HTTP (local dev); set to true in production
- `SESSION_SAME_SITE=lax` → Cookies sent with cross-site "safe" requests (like form submits)

---

### Step 1: User Clicks Login (Vue Component)

**File:** `resources/js/pages/auth/Login.vue` (or wherever your login form is)

```vue
<template>
  <form @submit.prevent="handleLogin">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button type="submit">Login</button>
  </form>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const email = ref('');
const password = ref('');

const handleLogin = async () => {
  // Send login credentials to backend via POST /login
  router.post('/login', {
    email: email.value,
    password: password.value,
  });
};
</script>
```

**What happens:**
- User enters `vendor@storekoto.test` and password.
- Vue makes a POST request to `/login` with the credentials.
- Request is sent to Central app (`storekoto.test`)

---

### Step 2: Laravel Fortify Receives Login (Backend)

**The request arrives at** `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController@store()`

Fortify does this:
```php
// Pseudo-code, actual Fortify source is in vendor/
namespace Laravel\Fortify\Http\Controllers;

class AuthenticatedSessionController {
    public function store(Request $request)
    {
        // 1. Validate credentials
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Check if user exists and password matches
        // Laravel uses Auth::attempt() which:
        //    a) Finds user by email
        //    b) Checks if password matches hash
        //    c) If yes, calls Auth::login($user) → creates session
        if (Auth::attempt($credentials)) {
            // 3. Session is now created!
            // Laravel automatically:
            //    - Generates random SESSIONID (e.g., xyz789abc)
            //    - Stores in sessions table: {id: xyz789abc, user_id: 5, ...}
            //    - Sends Set-Cookie header: storekoto-session=xyz789abc; Domain=.storekoto.test
            
            return $this->authenticated($request, Auth::user());
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }
}
```

**In your app**, you've customized the response in `app/Providers/FortifyServiceProvider.php`:

```php
$this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, function () {
    return new class implements \Laravel\Fortify\Contracts\LoginResponse {
        public function toResponse($request)
        {
            $user = $request->user(); // ← User is now authenticated!
            
            // If user is a vendor with an approved store:
            if ($user->hasRole('vendor')) {
                $tenant = \App\Models\Tenant::where('user_id', $user->id)->first();
                
                if ($tenant && $tenant->is_approved) {
                    $domain = $tenant->domains()->first();
                    if ($domain) {
                        // Redirect to: inasal.storekoto.test/vendor/dashboard
                        $url = $request->getScheme() . '://' . $domain->domain . '/vendor/dashboard';
                        
                        // Return 409 + Inertia header so frontend does window.location = url
                        return response('', 409)->header('X-Inertia-Location', $url);
                    }
                }
            }
            // ... other role redirects
        }
    };
});
```

**What happened here:**
1. Fortify validated credentials and created a session in the database.
2. Browser received `Set-Cookie: storekoto-session=xyz789abc; Domain=.storekoto.test; Path=/; HttpOnly; SameSite=Lax`
3. Your custom response told Vue/Inertia: "Redirect to `inasal.storekoto.test/vendor/dashboard`"
4. **The session cookie is now scoped to ALL `.storekoto.test` subdomains** because of the `.` prefix in `SESSION_DOMAIN=.storekoto.test`

---

### Step 3: Browser Navigates to Subdomain

**User is now redirected to** `inasal.storekoto.test/vendor/dashboard`

```
Browser action:
1. See redirect header → Go to inasal.storekoto.test/vendor/dashboard
2. Make GET request to that URL
3. Automatically include the cookie: Cookie: storekoto-session=xyz789abc
   (Browser sends because Domain=.storekoto.test matches inasal.storekoto.test)
```

---

### Step 4: Tenant App (Subdomain) Initializes Tenancy

**File:** `app/Http/Middleware/InitializeTenancyIfTenantDomain.php`

```php
class InitializeTenancyIfTenantDomain
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = config('tenancy.central_domains', 
            ['127.0.0.1', 'localhost', 'storekoto.test']
        );

        // Request is from inasal.storekoto.test (subdomain)
        // This is NOT in $centralDomains, so initialize tenancy
        if (!in_array($request->getHost(), $centralDomains)) {
            // Use Stancl Tenancy to resolve tenant from subdomain
            return app(InitializeTenancyByDomain::class)->handle($request, $next);
        }

        return $next($request);
    }
}
```

**What happens:**
1. Tenancy middleware sees `inasal.storekoto.test` and identifies it as a tenant subdomain.
2. Stancl Tenancy looks up tenant by domain: `SELECT * FROM domains WHERE domain='inasal.storekoto.test'`
3. Finds the tenant ID, switches database connection to that tenant's DB.
4. Now all queries hit the tenant's database.

---

### Step 5: Tenant Routes & Auth Guard

**File:** `routes/tenant.php`

```php
Route::middleware(['auth', 'verified', 'role:vendor'])->prefix('vendor')->group(function () {
    Route::get('/dashboard', function() {
        // At this point:
        // 1. User is authenticated (because Auth::check() found session via cookie)
        // 2. Tenancy is initialized (switched to inasal's database)
        // 3. User can access tenant-specific resources
        
        $products = Product::latest()->take(10)->get(); // ← Gets products from inasal's DB
        
        return inertia('vendor/Dashboard', [
            'products' => $products,
        ]);
    })->name('dashboard');
});
```

**Auth check works across subdomains because:**
1. Browser sent session cookie (automatically, because `Domain=.storekoto.test`)
2. Laravel's session middleware reads the cookie: `Cookie: storekoto-session=xyz789abc`
3. Looks up in `sessions` table: finds `user_id: 5, ...`
4. Calls `Auth::setUser(User::find(5))` internally
5. Now `auth()->check()` returns true and `auth()->user()` works

---

## Part 3: Logout Flow (The Back Button Problem)

### Step 1: User Clicks Logout

**File:** `resources/js/components/Sidebar.vue`

```vue
<script setup>
import { router } from '@inertiajs/vue3';

const logout = () => {
    // Make POST request to /logout
    router.post('/logout');
};
</script>

<template>
  <button @click="logout">Logout</button>
</template>
```

---

### Step 2: Fortify Logout (Backend)

**Default Fortify logout** (in `vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php`):

```php
public function destroy(Request $request)
{
    // 1. Delete the session from database
    Auth::logout(); 
    // ↓ This does:
    //    a) Delete from sessions table where id = current_session_id
    //    b) Call session()->invalidate() → clears session data
    //    c) Regenerate session token for CSRF protection
    //    d) Set session.lifetime cookie to a past time (so browser deletes it)

    // 2. Invalidate the browser's session cookie
    // Server sends: Set-Cookie: storekoto-session=; expires=Thu Jan 01 1970
    // (Empty value and expired time = delete cookie)

    $request->session()->regenerateToken();

    // 3. Return redirect response
    return redirect('/login');
}
```

**In your PreventBackHistory middleware** (`app/Http/Middleware/PreventBackHistory.php`):

```php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);

    // Add headers to prevent browser from caching authenticated pages
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', '0');

    // If logout POST and user is now guest, force Inertia to do full redirect
    if ($request->isMethod('post') && $request->is('logout') && auth()->guest()) {
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
        $location = $scheme . '://' . config('app.domain') . '/login';

        // Tell Inertia to use window.location instead of client-side routing
        $response->headers->set('X-Inertia-Location', $location);
        $response->setStatusCode(409);
    }

    return $response;
}
```

**What this does:**
- Fortify deletes the session from database and tells browser to delete the cookie.
- PreventBackHistory adds no-cache headers so browser doesn't cache the dashboard.
- Inertia full-redirect forces a hard page load to login, replacing browser history.
- **Result:** When user presses back, browser has no cached response and can't show dashboard.

---

### Step 3: Why Back Button Still Showed Dashboard (Before Fix)

**The issue was:**
1. User navigates: `inasal.storekoto.test/vendor/dashboard`
2. Browser caches this response in memory.
3. User clicks logout → session deleted server-side, but browser still has cached page.
4. User presses back button → browser shows cached dashboard from memory.
5. **Session cookie was deleted, but user still sees the page!**

**The fix:**
- `Cache-Control: no-cache` tells browser "never cache this page in memory"
- `X-Inertia-Location` with 409 status tells frontend to do `window.location` (hard reload) instead of client-side routing
- Now there's nothing to go back to, and if there was, it would reload and fail auth check.

---

## Part 4: Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│ USER'S BROWSER                                                      │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
                        Vue Login Form
                        (resources/js/pages/auth/Login.vue)
                              ↓
                  router.post('/login', {email, password})
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ CENTRAL LARAVEL APP (storekoto.test)                               │
│                                                                     │
│ POST /login                                                         │
│   → FortifyServiceProvider::LoginResponse                           │
│   → Fortify validates credentials                                  │
│   → Auth::login($user) → Creates session row in sessions table     │
│   → Set-Cookie: storekoto-session=xyz...; Domain=.storekoto.test   │
│   → CustomLoginResponse checks user.role                           │
│   → Vendor user? Get tenant domain (inasal.storekoto.test)         │
│   → Return 409 + X-Inertia-Location header                         │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
                     (Cookie stored in browser memory)
                      Domain: .storekoto.test
                     (Valid for all .storekoto.test subdomains)
                              ↓
            Browser redirects to inasal.storekoto.test/vendor/dashboard
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ TENANT LARAVEL APP (inasal.storekoto.test)                         │
│                                                                     │
│ GET /vendor/dashboard                                              │
│ (Auto-includes Cookie: storekoto-session=xyz...)                   │
│                                                                     │
│ InitializeTenancyIfTenantDomain middleware:                        │
│   → See subdomain 'inasal' → resolve tenant                        │
│   → Switch DB to 'tenantinasal' database                           │
│                                                                     │
│ Auth middleware:                                                    │
│   → Read session cookie ID                                         │
│   → Look up in CENTRAL sessions table                              │
│   → Find user_id: 5                                                │
│   → Auth::setUser(User::find(5))                                   │
│   → auth()->check() now returns true                               │
│                                                                     │
│ Route handler:                                                      │
│   → Product::latest()->get() → fetches from TENANT database        │
│   → Return inertia('vendor/Dashboard', [...])                      │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
                    Dashboard displayed to user
             (with Cache-Control: no-store, no-cache headers)
                              ↓
                        User clicks Logout
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ TENANT LARAVEL APP (inasal.storekoto.test)                         │
│                                                                     │
│ POST /logout                                                        │
│                                                                     │
│ Fortify::destroy()                                                  │
│   → Auth::logout()                                                  │
│   → DELETE from sessions WHERE id = 'xyz...'                       │
│   → Set-Cookie: storekoto-session=; expires=1970-01-01            │
│   → (Browser deletes the cookie)                                   │
│                                                                     │
│ PreventBackHistory middleware:                                     │
│   → auth()->guest() is now true                                    │
│   → Set Cache-Control headers                                      │
│   → Set X-Inertia-Location to storekoto.test/login                │
│   → Return 409 status                                              │
│                                                                     │
│ Result: Inertia does window.location = login page                 │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
                  Browser navigates to login page
                  (Hard reload, clears history)
                              ↓
                    User sees login form again
                              ↓
                  (If back button is pressed: nothing to go back to)
```

---

## Part 5: What Actually Validates the Session?

### Files & Code

**1. Session is stored in database** (`database/migrations/xxxx_create_sessions_table.php`):
```php
Schema::create('sessions', function (Blueprint $table) {
    $table->string('id')->primary();           // ← Session identifier (matches cookie)
    $table->foreignId('user_id')->nullable();  // ← Who owns this session
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->longText('payload');               // ← Serialized session data
    $table->integer('last_activity')->index(); // ← For garbage collection
});
```

**2. When request arrives, middleware reads session**:
```php
// Laravel\Session\Middleware\StartSession.php (simplified)
public function handle($request, Closure $next)
{
    // 1. Read session ID from cookie
    $sessionId = $request->cookie('storekoto-session'); // e.g., 'xyz789abc'

    // 2. Load session data from database
    $session = DB::table('sessions')->find($sessionId);
    // Result: {id: 'xyz789abc', user_id: 5, payload: {...}, ...}

    // 3. Deserialize and hydrate Laravel's session
    session()->put($session->payload);

    // 4. Continue to next middleware/route handler
    return $next($request);
}
```

**3. Auth middleware checks if user is authenticated**:
```php
// Illuminate\Auth\Middleware\Authenticate.php
public function handle($request, Closure $next, ...$guards)
{
    foreach ($guards as $guard) {
        if ($this->auth->guard($guard)->check()) {
            // User is authenticated!
            // Because session was loaded from DB above,
            // and Auth::check() finds user_id in session
            return $next($request);
        }
    }

    return redirect('/login'); // User not authenticated
}
```

**4. `hasRole()` uses Spatie Permissions**:
```php
// In your route: middleware(['auth', 'verified', 'role:vendor'])

// Spatie checks: Does the authenticated user have 'vendor' role?
// SELECT EXISTS(
//   SELECT 1 FROM role_has_users 
//   WHERE user_id = 5 AND role_id = (SELECT id FROM roles WHERE name='vendor')
// )
```

---

## Part 6: Key Takeaways

| Concept | Explanation |
|---------|-------------|
| **Cookie** | Sent by browser on every request to matching domain. |
| **Session** | Server-side data stored in DB, keyed by cookie ID. |
| **SESSION_DOMAIN=.storekoto.test** | Dot prefix = valid for domain AND subdomains. |
| **Central DB sessions table** | Shared by all subdomains; session persists across them. |
| **Tenant DB switch** | Tenancy middleware changes DB connection; auth still uses central session. |
| **Logout invalidation** | Delete session from DB; tell browser to delete cookie. |
| **No-cache headers** | Prevent browser from caching dashboard in memory. |
| **X-Inertia-Location** | Force hard page reload instead of client-side routing. |

---

## Troubleshooting Checklist

✅ **Session cookie persists across subdomains?**
- Check in browser DevTools → Application → Cookies
- Look for `storekoto-session` with `Domain: .storekoto.test`
- If Domain shows `inasal.storekoto.test` (without `.` prefix), re-read `.env`

✅ **Login redirects to subdomain but session is lost?**
- Session cookie exists but auth fails?
- Check `config/session.php` → `SESSION_CONNECTION` should point to central DB
- Tenant routes must not override the session/auth guards

✅ **Back button still shows dashboard?**
- Check HTTP response headers: should include `Cache-Control: no-cache, no-store`
- Check logout response: should have `X-Inertia-Location` header with 409 status
- If using traditional form logout (not Inertia), may need different approach

✅ **Logout doesn't clear session?**
- Check `sessions` table: are old sessions being deleted?
- Check browser cookies: is session cookie being deleted (expires=1970)?
- If using encrypted cookies, check `APP_KEY` hasn't changed

---

## Example: Complete Request → Response Flow

```bash
# 1. User submits login form
POST /login HTTP/1.1
Host: storekoto.test
Content-Type: application/x-www-form-urlencoded

email=vendor@storekoto.test&password=secret123

# 2. Server validates and creates session
# (Database insert): 
INSERT INTO sessions (id, user_id, payload, last_activity) 
VALUES ('xyz789abc', 5, '{...}', 1704067200)

# 3. Server responds:
HTTP/1.1 409 Conflict
Set-Cookie: storekoto-session=xyz789abc; Domain=.storekoto.test; Path=/; HttpOnly; SameSite=Lax; Max-Age=7200
X-Inertia-Location: http://inasal.storekoto.test/vendor/dashboard
Content-Type: application/json

{}

# 4. Browser stores cookie and navigates
# (Automatic from 409 + X-Inertia-Location)
GET /vendor/dashboard HTTP/1.1
Host: inasal.storekoto.test
Cookie: storekoto-session=xyz789abc

# 5. Server reads session from central DB:
SELECT user_id FROM sessions WHERE id='xyz789abc'
# Returns: user_id = 5

# 6. Auth middleware applies:
Auth::setUser(User::find(5))
auth()->checkRole('vendor') // ← true

# 7. Response:
HTTP/1.1 200 OK
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
Pragma: no-cache
Content-Type: application/json

{"component":"vendor/Dashboard","props":{"products":[...]}}
```

---

## Summary

You now understand:
1. **Cookies** are browser-side identifiers; **sessions** are server-side data.
2. **SESSION_DOMAIN=.storekoto.test** allows the cookie to be sent to all subdomains automatically.
3. **Central sessions table** stores user session data; **tenant DB** stores user products/data.
4. **Auth middleware** reads the session cookie, looks it up in the central DB, and authenticates the user.
5. **Logout** deletes the session and tells browser to delete the cookie.
6. **No-cache headers** + **Inertia full-redirect** prevent back button from showing cached pages.

