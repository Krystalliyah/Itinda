<template>
    <div class="it-root">

        <!-- ── NAVBAR ──────────────────────────────────── -->
        <nav class="it-nav">
            <div class="it-container">
                <div class="it-nav-inner">

                    <!-- Logo -->
                    <Link href="/" class="it-nav-logo">
                        <div class="it-nav-logo-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        iTinda
                    </Link>

                    <!-- Desktop nav links -->
                    <ul class="it-nav-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#vendors">For Vendors</a></li>
                        <li><a href="#customers">For Customers</a></li>
                    </ul>

                    <!-- Desktop actions -->
                    <div class="it-nav-actions">
                        <Link href="/login" class="it-btn-ghost-nav">Login</Link>
                        <Link href="/register" class="it-btn-gold-nav">Get Started</Link>
                    </div>

                    <!-- Mobile hamburger -->
                    <button class="it-hamburger" @click="menuOpen = !menuOpen" aria-label="Toggle menu">
                        <span :class="['it-ham-line', { 'it-ham-open-1': menuOpen }]"></span>
                        <span :class="['it-ham-line', { 'it-ham-open-2': menuOpen }]"></span>
                        <span :class="['it-ham-line', { 'it-ham-open-3': menuOpen }]"></span>
                    </button>
                </div>
            </div>

            <!-- Mobile dropdown -->
            <div :class="['it-mobile-menu', { 'it-mobile-menu-open': menuOpen }]">
                <ul class="it-mobile-links">
                    <li><a href="#features" @click="menuOpen = false">Features</a></li>
                    <li><a href="#how-it-works" @click="menuOpen = false">How It Works</a></li>
                    <li><a href="#vendors" @click="menuOpen = false">For Vendors</a></li>
                    <li><a href="#customers" @click="menuOpen = false">For Customers</a></li>
                </ul>
                <div class="it-mobile-actions">
                    <Link href="/login" class="it-mobile-login" @click="menuOpen = false">Login</Link>
                    <Link href="/register" class="it-mobile-register" @click="menuOpen = false">Get Started</Link>
                </div>
            </div>
        </nav>

        <!-- ── HERO ────────────────────────────────────── -->
        <header class="it-hero">
            <div class="it-container">
                <div class="it-hero-inner">

                    <!-- Left: copy -->
                    <div class="it-hero-copy">
                        <div class="it-hero-badge">
                            <div class="it-hero-badge-dot"></div>
                            Now live in your community
                        </div>
                        <h1 class="it-hero-h1">
                            Connect with Local <span class="it-accent">Vendors</span> in Your Neighborhood
                        </h1>
                        <p class="it-hero-desc">
                            Browse, compare prices, and pre-order from nearby shops.
                            Vendors manage their entire store — inventory, expenses, analytics — all in one place.
                        </p>
                        <div class="it-hero-cta">
                            <Link href="/register/vendor" class="it-btn-primary">Start Selling</Link>
                            <Link href="/register/customer" class="it-btn-outline">Start Shopping</Link>
                        </div>
                      
                         <div class="it-hero-stats">
                            <div class="it-stat">
                                <div class="it-stat-num">1,000+</div>
                                <div class="it-stat-label">Local Businesses</div>
                            </div>
                            
                            <div class="it-stat-divider"></div>
                            <div class="it-stat">
                                <div class="it-stat-num">10k+</div>
                                <div class="it-stat-label">Happy Customers</div>
                            </div>
                            
                        </div> 
                    </div>

                    <!-- Right: mock inventory card -->
                    <div class="it-hero-visual">
                        <div class="it-floating-tag it-tag-order">
                            <div class="it-tag-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="it-tag-label">New Pre-order</div>
                                <div class="it-tag-val">Organic Rice · 5kg</div>
                            </div>
                        </div>

                        <div class="it-hero-card">
                            <div class="it-hero-card-title">Store Inventory — Santos Grocery</div>
                            <div class="it-hero-card-row">
                                <span class="it-card-item">Fresh Eggs (tray)</span>
                                <span class="it-card-price">₱185</span>
                                <span class="it-card-badge it-badge-in">In Stock</span>
                            </div>
                            <div class="it-hero-card-row">
                                <span class="it-card-item">Organic Rice (5kg)</span>
                                <span class="it-card-price">₱320</span>
                                <span class="it-card-badge it-badge-in">In Stock</span>
                            </div>
                            <div class="it-hero-card-row">
                                <span class="it-card-item">Brown Sugar (1kg)</span>
                                <span class="it-card-price">₱95</span>
                                <span class="it-card-badge it-badge-low">Low Stock</span>
                            </div>
                            <div class="it-hero-card-row">
                                <span class="it-card-item">Cooking Oil (1L)</span>
                                <span class="it-card-price">₱140</span>
                                <span class="it-card-badge it-badge-in">In Stock</span>
                            </div>
                        </div>

                        <div class="it-floating-tag it-tag-vendor">
                            <div class="it-tag-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <div class="it-tag-label">This Week</div>
                                <div class="it-tag-val">Sales +24%</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- ── FEATURES ────────────────────────────────── -->
        <section id="features" class="it-features">
            <div class="it-container">
                <div class="it-section-header it-center">
                    <div class="it-section-label">Platform Features</div>
                    <h2 class="it-section-title">Everything You Need in One Platform</h2>
                    <p class="it-section-sub">Powerful, role-based tools built for vendors, customers, and administrators.</p>
                </div>
                <div class="it-features-grid">
                    <div class="it-feature-card">
                        <div class="it-feature-icon it-icon-purple">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <h3>Admin Dashboard</h3>
                        <p>Full platform oversight — manage users, monitor activity, and moderate stores with ease.</p>
                    </div>
                    <div class="it-feature-card">
                        <div class="it-feature-icon it-icon-emerald">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <h3>Vendor Tools</h3>
                        <p>Inventory management, expense tracking, stock alerts, and full sales analytics in one place.</p>
                    </div>
                    <div class="it-feature-card">
                        <div class="it-feature-icon it-icon-gold">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3>Customer Experience</h3>
                        <p>Search nearby stores, compare live prices across shops, and pre-order your essentials.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── HOW IT WORKS ─────────────────────────────── -->
        <section id="how-it-works" class="it-how">
            <div class="it-container">
                <div class="it-section-header it-center">
                    <div class="it-section-label">Simple Process</div>
                    <h2 class="it-section-title">How iTinda Works</h2>
                    <p class="it-section-sub">Three easy steps to start buying or selling locally.</p>
                </div>
                <div class="it-steps-grid">
                    <div class="it-step">
                        <div class="it-step-num">1</div>
                        <h3>Sign Up</h3>
                        <p>Create your account as a vendor or customer in minutes — no complicated setup required.</p>
                    </div>
                    <div class="it-step">
                        <div class="it-step-num">2</div>
                        <h3>Connect</h3>
                        <p>Vendors set up their store and inventory. Customers discover nearby shops in their area.</p>
                    </div>
                    <div class="it-step">
                        <div class="it-step-num">3</div>
                        <h3>Grow</h3>
                        <p>Start selling, pre-ordering, and building a stronger local commerce community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── ROLE CARDS ───────────────────────────────── -->
        <section id="vendors" class="it-roles">
            <div class="it-container">
                <div class="it-section-header it-center">
                    <div class="it-section-label">Role-Based Access</div>
                    <h2 class="it-section-title">Built for Vendors &amp; Customers</h2>
                    <p class="it-section-sub">Each role gets exactly the tools they need — nothing more, nothing less.</p>
                </div>
                <div class="it-roles-grid">
                    <div id="vendors-detail" class="it-role-card it-role-vendor">
                        <h3>For Vendors</h3>
                        <p class="it-role-subtitle">Two access levels tailored for your team structure.</p>
                        <div class="it-role-row">
                            <span class="it-role-tag it-tag-owner">Owner</span>
                            <div class="it-role-text">
                                <strong>Full Access</strong>
                                View total profits, expenses, and overall store performance with complete analytics.
                            </div>
                        </div>
                        <div class="it-role-row">
                            <span class="it-role-tag it-tag-staff">Staff</span>
                            <div class="it-role-text">
                                <strong>Operational Access</strong>
                                Manage stock levels, handle orders, and view day-to-day operational reports.
                            </div>
                        </div>
                    </div>
                    <div id="customers" class="it-role-card it-role-customer">
                        <h3>For Customers</h3>
                        <p class="it-role-subtitle it-muted">Find the best deals at stores near you.</p>
                        <ul class="it-check-list">
                            <li>
                                <div class="it-check-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                Search stores within your area
                            </li>
                            <li>
                                <div class="it-check-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                View and compare product prices across different shops
                            </li>
                            <li>
                                <div class="it-check-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                Pre-order products from your selected vendors
                            </li>
                            <li>
                                <div class="it-check-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                Transparent, real-time pricing — no surprises
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ─────────────────────────────────────── -->
        <section class="it-cta">
            <div class="it-container">
                <div class="it-cta-inner">
                    <h2>Ready to <span class="it-accent-gold">Transform</span><br />Local Commerce?</h2>
                    <p>Join the platform that's connecting communities and empowering local businesses to thrive.</p>
                    <div class="it-cta-btns">
                        <Link href="/register/vendor" class="it-btn-cta-gold">Become a Vendor</Link>
                        <Link href="/register/customer" class="it-btn-cta-outline">Start Shopping</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FOOTER ──────────────────────────────────── -->
        <footer class="it-footer">
            <div class="it-container">
                <div class="it-footer-grid">
                    <div>
                        <div class="it-footer-brand">iTinda</div>
                        <p class="it-footer-desc">Connecting local vendors with customers in your community — one neighborhood at a time.</p>
                    </div>
                    <div class="it-footer-col">
                        <div class="it-footer-col-title">For Vendors</div>
                        <ul>
                            <li><a href="#">Pricing</a></li>
                            <li><a href="#">Features</a></li>
                            <li><a href="#">Success Stories</a></li>
                        </ul>
                    </div>
                    <div class="it-footer-col">
                        <div class="it-footer-col-title">For Customers</div>
                        <ul>
                            <li><a href="#">Find Stores</a></li>
                            <li><a href="#">How It Works</a></li>
                            <li><a href="#">FAQs</a></li>
                        </ul>
                    </div>
                    <div class="it-footer-col">
                        <div class="it-footer-col-title">Company</div>
                        <ul>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Contact</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="it-footer-bottom">
                    <p class="it-footer-copy">&copy; {{ new Date().getFullYear() }} <span>iTinda</span>. All rights reserved.</p>
                    <p class="it-footer-copy">Made for local communities.</p>
                </div>
            </div>
        </footer>

    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const menuOpen = ref(false);
</script>

<style scoped>
/* ── Google Fonts ──────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Lato:wght@300;400;700&family=Bebas+Neue&display=swap');

/* ── CSS Variables ─────────────────────────────────────────── */
.it-root {
    --emerald:     #1B4D3E;
    --emerald-mid: #245c4a;
    --gold:        #C5A059;
    --gold-light:  #d9b87a;
    --gold-pale:   #f5ead4;
    --smoke:       #F5F5F5;
    --white:       #ffffff;
    --text-dark:   #0f2820;
    --text-body:   #3a4a44;
    --text-muted:  #7a8f88;
    --border:      rgba(27, 77, 62, 0.12);

    font-family: 'Lato', sans-serif;
    color: var(--text-dark);
    background: var(--smoke);
    overflow-x: hidden;
}

.it-root h1,
.it-root h2,
.it-root h3,
.it-root h4 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    margin: 0;
}

.it-root a  { text-decoration: none; color: inherit; }
.it-root ul { list-style: none; margin: 0; padding: 0; }
.it-root p  { margin: 0; }

/* ── Container ─────────────────────────────────────────────── */
.it-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 48px;
}

/* ════════════════════════════════════════════════════════════
   NAVBAR
   ════════════════════════════════════════════════════════════ */
.it-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 200;
    background: var(--emerald);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.14);
}

.it-nav-inner {
    display: flex;
    align-items: center;
    height: 72px;
    gap: 0;
}

/* Logo — flush left */
.it-nav-logo {
    font-family: 'Outfit', sans-serif;
    font-weight: 700;
    font-size: 1.6rem;
    letter-spacing: 1px;
    color: #fff8e3 !important;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    margin-right: 40px;
}
.it-nav-logo-icon {
    width: 34px; height: 34px;
    background: var(--gold);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
}

.it-nav-logo-icon svg {
    width: 18px; height: 18px;
    color: var(--emerald);
}

/* Nav links — spread evenly in the middle */
.it-nav-links {
    display: flex;
    align-items: center;
    gap: 4px;
    flex: 1;
}

.it-nav-links li a {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.7);
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 8px 14px;
    border-radius: 6px;
    transition: color 0.2s, background 0.2s;
    white-space: nowrap;
    display: block;
}

.it-nav-links li a:hover {
    color: var(--gold);
    background: rgba(255, 255, 255, 0.06);
}

/* Actions — flush right */
.it-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    margin-left: 24px;
}

.it-btn-ghost-nav {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    color: #fff8e3 !important;
    padding: 9px 20px;
    border-radius: 7px;
    white-space: nowrap;
    transition: color 0.2s, background 0.2s;
}

.it-btn-ghost-nav:hover {
    color: #fff8e3 !important;
    background: rgba(255, 255, 255, 0.08);
}

.it-btn-gold-nav {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    background: var(--gold);
    color: var(--emerald);
    padding: 10px 22px;
    border-radius: 7px;
    white-space: nowrap;
    transition: background 0.2s, transform 0.15s;
}

.it-btn-gold-nav:hover {
    background: var(--gold-light);
    transform: translateY(-1px);
}

/* Hamburger — hidden on desktop */
.it-hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 40px; height: 40px;
    padding: 6px;
    background: transparent;
    border: none;
    cursor: pointer;
    margin-left: auto;
    flex-shrink: 0;
}

.it-ham-line {
    display: block;
    width: 100%; height: 2px;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 2px;
    transition: transform 0.25s, opacity 0.2s;
    transform-origin: center;
}

.it-ham-open-1 { transform: translateY(7px) rotate(45deg); }
.it-ham-open-2 { opacity: 0; transform: scaleX(0); }
.it-ham-open-3 { transform: translateY(-7px) rotate(-45deg); }

/* Mobile dropdown */
.it-mobile-menu {
    background: var(--emerald-mid);
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.35s ease;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.it-mobile-menu-open {
    max-height: 420px;
}

.it-mobile-links {
    padding: 8px 24px 4px;
}

.it-mobile-links li a {
    display: block;
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.88rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.72);
    padding: 13px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    transition: color 0.2s;
}

.it-mobile-links li:last-child a { border-bottom: none; }
.it-mobile-links li a:hover { color: var(--gold); }

.it-mobile-actions {
    display: flex;
    gap: 12px;
    padding: 16px 24px 20px;
}

.it-mobile-login {
    flex: 1;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.82);
    padding: 11px 0;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 7px;
    transition: border-color 0.2s, background 0.2s;
}

.it-mobile-login:hover {
    border-color: rgba(255, 255, 255, 0.45);
    background: rgba(255, 255, 255, 0.05);
}

.it-mobile-register {
    flex: 1;
    text-align: center;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.875rem;
    background: var(--gold);
    color: var(--emerald);
    padding: 11px 0;
    border-radius: 7px;
    transition: background 0.2s;
}

.it-mobile-register:hover { background: var(--gold-light); }

/* ════════════════════════════════════════════════════════════
   HERO  — light background, NOT emerald
   ════════════════════════════════════════════════════════════ */
.it-hero {
    padding-top: 72px;
    background: var(--smoke);
    position: relative;
    min-height: 90vh;
    display: flex;
    align-items: center;
}

/* Barely-there warm gradient accent on the right half */
.it-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 65% 75% at 90% 45%, rgba(197, 160, 89, 0.07) 0%, transparent 65%),
        radial-gradient(ellipse 40% 50% at 5%  90%, rgba(27, 77, 62, 0.04) 0%, transparent 55%);
    pointer-events: none;
}

.it-hero-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 72px;
    align-items: center;
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 72px 0 80px;
}

/* Hero badge */
.it-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(27, 77, 62, 0.07);
    border: 1px solid rgba(27, 77, 62, 0.16);
    color: var(--emerald);
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 7px 14px;
    border-radius: 100px;
    margin-bottom: 24px;
    animation: itFadeUp 0.6s ease both;
}

.it-hero-badge-dot {
    width: 6px; height: 6px;
    background: var(--emerald);
    border-radius: 50%;
    animation: itPulse 2s infinite;
}

/* Hero heading */
.it-hero-h1 {
    font-size: clamp(2.1rem, 3.6vw, 3.2rem);
    line-height: 1.13;
    color: var(--text-dark);
    margin-bottom: 20px;
    animation: itFadeUp 0.6s 0.1s ease both;
}

.it-accent { color: var(--emerald); }

.it-hero-desc {
    font-size: 1.02rem;
    line-height: 1.75;
    color: var(--text-muted);
    max-width: 460px;
    margin-bottom: 36px;
    animation: itFadeUp 0.6s 0.2s ease both;
}

/* CTA buttons */
.it-hero-cta {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    animation: itFadeUp 0.6s 0.3s ease both;
}

.it-btn-primary {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    background: var(--emerald-mid);
    color: #ffffff !important;
    padding: 14px 30px;
    border-radius: 8px;
    transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(9, 99, 37, 0.356);
}

.it-btn-primary:hover {
    background: var(--emerald);
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(27, 77, 62, 0.32);
}

.it-btn-outline {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 0.95rem;
    background: transparent;
    color: var(--emerald);
    padding: 14px 30px;
    border-radius: 8px;
    border: 2px solid rgba(27, 77, 62, 0.28);
    transition: border-color 0.2s, background 0.2s, transform 0.15s;
}

.it-btn-outline:hover {
    border-color: var(--emerald);
    background: rgba(27, 77, 62, 0.05);
    transform: translateY(-2px);
}

/* Stats */
.it-hero-stats {
    display: flex;
    gap: 28px;
    align-items: center;
    margin-top: 44px;
    animation: itFadeUp 0.6s 0.4s ease both;
}

.it-stat-divider {
    width: 1px; height: 36px;
    background: rgba(27, 77, 62, 0.14);
    flex-shrink: 0;
}

.it-stat-num {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.4rem;
    color: var(--emerald);
}

.it-stat-label {
    font-size: 0.72rem;
    color: var(--text-muted);
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 2px;
}

/* ── Hero Visual ───────────────────────────────────────────── */
.it-hero-visual {
    position: relative;
    animation: itFadeUp 0.6s 0.2s ease both;
}

/* Inventory card — soft emerald wash, reduced opacity */
.it-hero-card {
    background: rgba(27, 77, 62, 0.055);
    border: 1px solid rgba(27, 77, 62, 0.16);
    border-radius: 16px;
    padding: 28px 32px;
    position: relative;
    z-index: 1;
}

.it-hero-card-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: var(--emerald);
    opacity: 0.65;
    margin-bottom: 18px;
}

.it-hero-card-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 0;
    border-bottom: 1px solid rgba(27, 77, 62, 0.07);
    gap: 12px;
}

.it-hero-card-row:last-child { border-bottom: none; }

.it-card-item {
    color: var(--text-body);
    font-size: 0.9rem;
    flex: 1;
}

.it-card-price {
    color: var(--emerald);
    font-weight: 700;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
}

.it-card-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 4px;
    font-family: 'Lato', sans-serif;
    white-space: nowrap;
}

.it-badge-in  { background: rgba(27, 77, 62, 0.1);  color: var(--emerald); }
.it-badge-low { background: rgba(197, 160, 89, 0.16); color: #8a6520; }

/* Floating tags */
.it-floating-tag {
    position: absolute;
    background: var(--white);
    border: 1px solid rgba(27, 77, 62, 0.13);
    border-radius: 10px;
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
    z-index: 2;
}

.it-tag-order  { top: -18px;  right: -16px; animation: itFloat 3s ease-in-out infinite; }
.it-tag-vendor { bottom: -16px; left: -16px; animation: itFloat 3s 1.5s ease-in-out infinite; }

.it-tag-icon {
    width: 28px; height: 28px;
    background: var(--emerald);
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-tag-icon svg { width: 14px; height: 14px; color: #fff; }

.it-tag-label {
    font-size: 0.63rem;
    color: var(--text-muted);
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.it-tag-val {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.85rem;
    color: var(--text-dark);
    font-weight: 700;
}

/* ════════════════════════════════════════════════════════════
   FEATURES
   ════════════════════════════════════════════════════════════ */
.it-features {
    background: var(--white);
    padding: 96px 0;
}

.it-section-label {
    font-family: 'Lato', sans-serif;
    font-weight: 700;
    font-size: 0.72rem;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--gold);
    margin-bottom: 12px;
}

.it-section-title {
    font-size: clamp(1.75rem, 3vw, 2.5rem);
    color: var(--text-dark);
    line-height: 1.2;
    margin-bottom: 14px;
}

.it-section-sub {
    font-size: 1rem;
    color: var(--text-muted);
    line-height: 1.7;
    max-width: 480px;
}

.it-section-header { margin-bottom: 52px; }
.it-center         { text-align: center; }
.it-center .it-section-sub { margin: 0 auto; }

.it-features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.it-feature-card {
    background: var(--smoke);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 32px;
    transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
    position: relative;
    overflow: hidden;
}

.it-feature-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--gold);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s;
}

.it-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(27, 77, 62, 0.09);
    border-color: rgba(197, 160, 89, 0.3);
}

.it-feature-card:hover::before { transform: scaleX(1); }

.it-feature-icon {
    width: 48px; height: 48px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
}

.it-icon-purple { background: #ede9fe; }
.it-icon-purple svg { color: #7c3aed; }
.it-icon-emerald { background: rgba(27, 77, 62, 0.1); }
.it-icon-emerald svg { color: var(--emerald); }
.it-icon-gold { background: var(--gold-pale); }
.it-icon-gold svg { color: var(--gold); }

.it-feature-card h3 {
    font-size: 1.08rem;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.it-feature-card p {
    color: var(--text-muted);
    font-size: 0.91rem;
    line-height: 1.65;
}

/* ════════════════════════════════════════════════════════════
   HOW IT WORKS
   ════════════════════════════════════════════════════════════ */
.it-how {
    background: var(--smoke);
    padding: 96px 0;
}

.it-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    position: relative;
}

.it-steps-grid::before {
    content: '';
    position: absolute;
    top: 26px;
    left: calc(16.6% + 28px);
    right: calc(16.6% + 28px);
    height: 2px;
    background: repeating-linear-gradient(
        90deg,
        var(--gold) 0, var(--gold) 8px,
        transparent 8px, transparent 16px
    );
}

.it-step { text-align: center; padding: 0 32px; }

.it-step-num {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: var(--emerald);
    color: var(--gold);
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    border: 3px solid var(--gold-pale);
    box-shadow: 0 0 0 6px rgba(27, 77, 62, 0.07);
    position: relative; z-index: 1;
}

.it-step h3 { font-size: 1.1rem; color: var(--text-dark); margin-bottom: 10px; }
.it-step p  { font-size: 0.9rem; color: var(--text-muted); line-height: 1.65; }

/* ════════════════════════════════════════════════════════════
   ROLE CARDS
   ════════════════════════════════════════════════════════════ */
.it-roles {
    background: var(--white);
    padding: 96px 0;
}

.it-roles-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
}

.it-role-card {
    border-radius: 18px;
    padding: 40px;
    position: relative;
    overflow: hidden;
}

.it-role-card::after {
    content: '';
    position: absolute;
    bottom: -40px; right: -40px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    pointer-events: none;
}

.it-role-vendor   { background: var(--emerald); color: var(--white); }
.it-role-customer { background: var(--smoke); border: 1px solid var(--border); }

.it-role-card h3 { font-size: 1.4rem; margin-bottom: 6px; }
.it-role-vendor h3   { color: var(--gold); }
.it-role-customer h3 { color: var(--emerald); }

.it-role-subtitle { font-size: 0.88rem; font-weight: 400; margin-bottom: 28px; margin-top: 4px; }
.it-role-vendor .it-role-subtitle { color: rgba(255, 255, 255, 0.58); }
.it-muted { color: var(--text-muted); }

.it-role-row {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 20px;
}

.it-role-tag {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 5px 13px;
    border-radius: 100px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 1px;
}

.it-tag-owner { background: var(--gold); color: var(--emerald); }
.it-tag-staff { background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.24); }

.it-role-text { font-size: 0.88rem; line-height: 1.55; color: rgba(255,255,255,0.76); }
.it-role-text strong { display: block; font-weight: 700; color: #fff; margin-bottom: 2px; }

.it-check-list li {
    display: flex; align-items: flex-start;
    gap: 12px; margin-bottom: 16px;
    font-size: 0.91rem; color: var(--text-body);
}

.it-check-list li:last-child { margin-bottom: 0; }

.it-check-icon {
    width: 20px; height: 20px;
    background: rgba(27, 77, 62, 0.1);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}

.it-check-icon svg { width: 10px; height: 10px; color: var(--emerald); }

/* ════════════════════════════════════════════════════════════
   CTA
   ════════════════════════════════════════════════════════════ */
.it-cta {
    background: var(--emerald);
    padding: 96px 0;
    position: relative;
    overflow: hidden;
}

.it-cta::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 60% at 100% 0%,   rgba(197,160,89,0.14) 0%, transparent 60%),
        radial-gradient(ellipse 40% 40% at 0%   100%, rgba(197,160,89,0.08) 0%, transparent 50%);
    pointer-events: none;
}

.it-cta-inner {
    position: relative; z-index: 2;
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.it-cta h2 {
    font-size: clamp(1.9rem, 3.5vw, 2.8rem);
    color: var(--white);
    margin-bottom: 16px;
}

.it-accent-gold { color: var(--gold); }

.it-cta p {
    font-size: 1.02rem;
    color: rgba(255,255,255,0.68);
    line-height: 1.7;
    margin-bottom: 36px;
}

.it-cta-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.it-btn-cta-gold {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700; font-size: 0.95rem;
    background: var(--gold); color: var(--emerald);
    padding: 14px 34px; border-radius: 8px;
    transition: background 0.2s, transform 0.15s;
    box-shadow: 0 4px 18px rgba(197,160,89,0.3);
}

.it-btn-cta-gold:hover { background: var(--gold-light); transform: translateY(-2px); }

.it-btn-cta-outline {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700; font-size: 0.95rem;
    background: transparent; color: var(--white);
    padding: 14px 34px; border-radius: 8px;
    border: 2px solid rgba(255,255,255,0.32);
    transition: border-color 0.2s, background 0.2s, transform 0.15s;
}

.it-btn-cta-outline:hover {
    border-color: rgba(255,255,255,0.68);
    background: rgba(255,255,255,0.07);
    transform: translateY(-2px);
}

/* ════════════════════════════════════════════════════════════
   FOOTER
   ════════════════════════════════════════════════════════════ */
.it-footer {
    background: var(--text-dark);
    color: var(--white);
    padding: 64px 0 28px;
}

.it-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 44px;
}

.it-footer-brand {
    font-family: 'Montserrat', sans-serif;
    font-weight: 900; font-size: 1.35rem;
    color: var(--gold); margin-bottom: 12px;
}

.it-footer-desc {
    font-size: 0.87rem;
    color: rgba(255,255,255,0.4);
    line-height: 1.65;
    max-width: 230px;
}

.it-footer-col-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700; font-size: 0.74rem;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--gold); margin-bottom: 16px;
}

.it-footer-col ul li { margin-bottom: 10px; }

.it-footer-col ul li a {
    font-size: 0.87rem;
    color: rgba(255,255,255,0.45);
    transition: color 0.2s;
}

.it-footer-col ul li a:hover { color: var(--white); }

.it-footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-top: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.it-footer-copy { font-size: 0.82rem; color: rgba(255,255,255,0.27); }
.it-footer-copy span { color: var(--gold); }

/* ════════════════════════════════════════════════════════════
   KEYFRAMES
   ════════════════════════════════════════════════════════════ */
@keyframes itFadeUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes itPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.75); }
}

@keyframes itFloat {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — tablet ≤ 1024px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .it-container { padding: 0 32px; }

    .it-nav-links li a  { font-size: 0.78rem; padding: 7px 10px; }
    .it-nav-logo        { margin-right: 24px; }
    .it-nav-actions     { margin-left: 16px; gap: 8px; }
    .it-btn-ghost-nav   { padding: 8px 14px; }
    .it-btn-gold-nav    { padding: 9px 16px; }

    .it-hero-inner    { gap: 48px; }
    .it-features-grid { gap: 18px; }

    .it-footer-grid { grid-template-columns: 2fr 1fr 1fr; }
    .it-footer-grid > div:last-child { grid-column: 2; }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — mobile ≤ 768px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .it-container { padding: 0 20px; }

    /* Navbar */
    .it-nav-links   { display: none; }
    .it-nav-actions { display: none; }
    .it-hamburger   { display: flex; }
    .it-nav-inner   { height: 64px; }
    .it-nav-logo    { margin-right: 0; }

    /* Hero */
    .it-hero-inner {
        grid-template-columns: 1fr;
        gap: 0;
        padding: 52px 0 60px;
    }
    .it-hero-visual { display: none; }
    .it-hero-desc   { max-width: 100%; }
    .it-hero-stats  { gap: 20px; }

    /* Sections */
    .it-features-grid { grid-template-columns: 1fr; gap: 16px; }
    .it-steps-grid    { grid-template-columns: 1fr; gap: 40px; }
    .it-steps-grid::before { display: none; }
    .it-roles-grid    { grid-template-columns: 1fr; }

    /* Footer */
    .it-footer-grid { grid-template-columns: 1fr 1fr; gap: 28px; }
    .it-footer-grid > div:last-child { grid-column: unset; }
    .it-footer-bottom { flex-direction: column; text-align: center; }
}

/* ════════════════════════════════════════════════════════════
   RESPONSIVE — small mobile ≤ 480px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 480px) {
    .it-hero-h1  { font-size: 1.95rem; }
    .it-hero-cta { flex-direction: column; }
    .it-btn-primary,
    .it-btn-outline { width: 100%; text-align: center; }

    .it-hero-stats  { flex-wrap: wrap; gap: 16px; }
    .it-stat-divider { display: none; }

    .it-footer-grid { grid-template-columns: 1fr; }

    .it-cta-btns { flex-direction: column; }
    .it-btn-cta-gold,
    .it-btn-cta-outline { width: 100%; text-align: center; }
}
</style>