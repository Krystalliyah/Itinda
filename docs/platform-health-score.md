# Platform Health Score (PHS) — Design & Rationale

## What is PHS?

The Platform Health Score is a single 0–100 number that gives the admin an at-a-glance signal of how well the marketplace is operating. It is computed entirely server-side in `app/Services/PlatformHealthService.php` and passed as a prop to both the Dashboard and Reports pages, guaranteeing the same number appears everywhere.

---

## The Formula

```
PHS = (activeVendorRatio × 40)
    + (emailVerificationRate × 30)
    + (newCustomersScore × 20)
    + (newVendorsScore × 10)
```

Maximum possible score: **100 points**

---

## Components

### 1. Active Vendor Ratio — up to 40 pts

```
activeVendorRatio = approvedVendors / totalVendors
contribution      = activeVendorRatio × 40
```

**Why 40 pts (highest weight)?**
Vendors are the supply side of the marketplace. Without approved, active vendors there is nothing to sell. A platform where most registered vendors are still pending is functionally broken for customers. This component answers: *"Is the supply side healthy?"*

| Scenario | Ratio | Points |
|---|---|---|
| All vendors approved | 1.0 | 40 |
| Half approved | 0.5 | 20 |
| None approved | 0.0 | 0 |

---

### 2. Email Verification Rate — up to 30 pts

```
emailVerificationRate = verifiedCustomers / totalCustomers
contribution          = emailVerificationRate × 30
```

**Why 30 pts?**
Verified customers are real, reachable users. A high unverified rate signals either a spam/bot problem or a broken onboarding flow. It also directly affects order reliability and communication. This component answers: *"Is the demand side trustworthy?"*

| Scenario | Rate | Points |
|---|---|---|
| All customers verified | 1.0 | 30 |
| 70% verified | 0.7 | 21 |
| No customers yet | 0.0 | 0 |

---

### 3. New Customers This Month — up to 20 pts

```
newCustomersScore = min(newCustomersThisMonth / 10, 1)
contribution      = newCustomersScore × 20
```

**Why 20 pts? Why cap at 10?**
Growth momentum matters but shouldn't dominate the score — a platform with 0 new customers this month but 100% verified existing ones is still healthy. The cap of 10 new customers for full score is intentionally low; it rewards early-stage platforms and keeps the metric meaningful even at small scale. This component answers: *"Is the platform growing?"*

| New customers | Score | Points |
|---|---|---|
| 10 or more | 1.0 | 20 |
| 5 | 0.5 | 10 |
| 0 | 0.0 | 0 |

---

### 4. New Vendors This Month — up to 10 pts

```
newVendorsScore = min(newVendorsThisMonth / 5, 1)
contribution    = newVendorsScore × 10
```

**Why only 10 pts? Why cap at 5?**
Vendor acquisition is important but quality (approval rate, component 1) matters more than raw volume. Adding 50 vendors who never get approved hurts component 1 more than this helps. The lower weight keeps the incentive aligned: approve vendors, don't just register them. This component answers: *"Is the vendor pipeline active?"*

| New vendors | Score | Points |
|---|---|---|
| 5 or more | 1.0 | 10 |
| 2 | 0.4 | 4 |
| 0 | 0.0 | 0 |

---

## Weight Rationale Summary

| Component | Weight | Reasoning |
|---|---|---|
| Active vendor ratio | 40% | Supply side — core platform function |
| Email verification rate | 30% | Demand side trust & data quality |
| New customers this month | 20% | Growth signal |
| New vendors this month | 10% | Pipeline signal, quality > quantity |

The weights follow a **supply → trust → growth** priority order. A platform can score well without recent growth if its existing base is solid, but it cannot score well if vendors aren't approved or customers aren't verified.

---

## Score Bands

| Score | Label | Color |
|---|---|---|
| 80–100 | Excellent | `#10b981` (green) |
| 60–79 | Good | `#3b82f6` (blue) |
| 40–59 | Fair | `#f59e0b` (amber) |
| 0–39 | Needs Attention | `#ef4444` (red) |

---

## Worked Example

Assume:
- 10 total vendors, 8 approved, 2 pending
- 50 total customers, 40 verified
- 6 new customers this month
- 3 new vendors this month

```
activeVendorRatio     = 8/10  = 0.80  → 0.80 × 40 = 32.0 pts
emailVerificationRate = 40/50 = 0.80  → 0.80 × 30 = 24.0 pts
newCustomersScore     = min(6/10, 1)  = 0.60  → 0.60 × 20 = 12.0 pts
newVendorsScore       = min(3/5, 1)   = 0.60  → 0.60 × 10 =  6.0 pts

PHS = 32 + 24 + 12 + 6 = 74 → "Good"
```

---

## Implementation

Single source of truth: `app/Services/PlatformHealthService::compute()`

Both controllers call the same method:

```php
$phs = PlatformHealthService::compute(
    totalVendors:          $totalVendors,
    activeVendors:         $activeVendors,
    totalCustomers:        $totalCustomers,
    verifiedCustomers:     $verifiedCustomers,
    newCustomersThisMonth: $newCustomersThisMonth,
    newVendorsThisMonth:   $newVendorsThisMonth,
);

// $phs['score']      → int 0–100
// $phs['components'] → breakdown percentages for each factor
```

Controllers: `Admin\DashboardController` · `Admin\ReportsController`
Vue pages: `admin/Dashboard.vue` · `admin/Reports.vue`
