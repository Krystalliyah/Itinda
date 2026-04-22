<template>
    <div class="it-root">

        <!-- ── NAVBAR ──────────────────────────────────── -->
        <nav class="it-nav" :class="{ 'it-nav-scrolled': scrolled }">
            <div class="it-container">
                <div class="it-nav-inner">
                    <Link href="/" class="it-nav-logo">
                         <span class="it-logo-text">iTinda</span>
                        <img src="/itinda-logo-2.png" alt="iTinda Logo" class="it-nav-logo-img" />
                       
                    </Link>

                    <ul class="it-nav-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#stores">Live Stores</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                    </ul>

                    <div class="it-nav-actions">
                        <Link href="/login" class="it-btn-ghost-nav">Login</Link>
                        <Link href="/register" class="it-btn-primary-nav">
                            <span>Get Started</span>
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <button class="it-hamburger" @click="menuOpen = !menuOpen" aria-label="Toggle menu">
                        <span :class="['it-ham-line', { 'it-ham-open-1': menuOpen }]"></span>
                        <span :class="['it-ham-line', { 'it-ham-open-2': menuOpen }]"></span>
                        <span :class="['it-ham-line', { 'it-ham-open-3': menuOpen }]"></span>
                    </button>
                </div>
            </div>

            <div :class="['it-mobile-menu', { 'it-mobile-menu-open': menuOpen }]">
                <ul class="it-mobile-links">
                    <li><a href="#features" @click="menuOpen = false">Features</a></li>
                    <li><a href="#stores" @click="menuOpen = false">Live Stores</a></li>
                    <li><a href="#how-it-works" @click="menuOpen = false">How It Works</a></li>
                </ul>
                <div class="it-mobile-actions">
                    <Link href="/login" class="it-mobile-login" @click="menuOpen = false">Login</Link>
                    <Link href="/register" class="it-mobile-register" @click="menuOpen = false">Get Started</Link>
                </div>
            </div>
        </nav>

        <!-- ── HERO ────────────────────────────────────── -->
        <header class="it-hero">
            <div class="it-hero-bg-shapes">
                <div class="it-shape it-shape-1"></div>
                <div class="it-shape it-shape-2"></div>
                <div class="it-shape it-shape-3"></div>
                <div class="it-shape it-shape-4"></div>
            </div>

            <div class="it-container">
                <div class="it-hero-inner">
                    <div class="it-hero-copy">
                        <div class="it-hero-eyebrow">
                            <div class="it-eyebrow-dot"></div>
                            <span>Now live in your community</span>
                        </div>

                        <h1 class="it-hero-h1">
                            Connect with Local<br>
                            <span class="it-h1-highlight">Vendors</span>
                            <span class="it-h1-sub"> Near You</span>
                        </h1>

                        <p class="it-hero-desc">
                            Browse, compare prices, and pre-order from nearby shops.
                            Vendors manage their entire store — inventory, expenses, analytics — all in one place.
                        </p>

                        <div class="it-hero-cta">
                            <Link href="/register?role=vendor" class="it-cta-primary">
                                Start Selling
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </Link>
                            <Link href="/register?role=customer" class="it-cta-secondary">
                                Start Shopping
                            </Link>
                        </div>

                        <div class="it-hero-stats">
                        <div class="it-stat">
                            <div class="it-stat-num">{{ formatCount(props.vendorCount) }}</div>
                            <div class="it-stat-label">Local Businesses</div>
                        </div>
                        <div class="it-stat-sep"></div>
                        <div class="it-stat">
                            <div class="it-stat-num">{{ props.openStoreCount }}<span class="it-stat-live">live</span></div>
                            <div class="it-stat-label">Open Right Now</div>
                        </div>
                        <div class="it-stat-sep"></div>
                        <div class="it-stat">
                            <div class="it-stat-num">{{ formatCount(props.customerCount) }}</div>
                            <div class="it-stat-label">Happy Customers</div>
                        </div>
                    </div>
                    </div>
                    <!-- Right: mock inventory card -->
                    <div class="it-hero-visual">
                        <div class="it-visual-glow"></div>

                        <div class="it-float-badge it-badge-top">
                            <div class="it-badge-icon it-badge-icon-green">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div>
                                <div class="it-badge-label">New Pre-order</div>
                                <div class="it-badge-val">Organic Rice · 5kg</div>
                            </div>
                        </div>

                        <div class="it-inventory-card">
                            <div class="it-card-header">
                                <div class="it-card-header-left">
                                    <div class="it-store-avatar">SG</div>
                                    <div>
                                        <div class="it-card-store-name">Santos Grocery</div>
                                        <div class="it-card-status">
                                            <span class="it-status-dot"></span> 
                                        </div>
                                    </div>
                                </div>
                                <div class="it-card-items-count">4 items</div>
                            </div>

                            <div class="it-card-divider"></div>

                            <div class="it-card-row">
                                <span class="it-ci-name">Fresh Eggs (tray)</span>
                                <span class="it-ci-price">₱185</span>
                                <span class="it-ci-tag it-tag-in">In Stock</span>
                            </div>
                            <div class="it-card-row">
                                <span class="it-ci-name">Organic Rice (5kg)</span>
                                <span class="it-ci-price">₱320</span>
                                <span class="it-ci-tag it-tag-in">In Stock</span>
                            </div>
                            <div class="it-card-row">
                                <span class="it-ci-name">Brown Sugar (1kg)</span>
                                <span class="it-ci-price">₱95</span>
                                <span class="it-ci-tag it-tag-low">Low Stock</span>
                            </div>
                            <div class="it-card-row">
                                <span class="it-ci-name">Cooking Oil (1L)</span>
                                <span class="it-ci-price">₱140</span>
                                <span class="it-ci-tag it-tag-in">In Stock</span>
                            </div>
                        </div>

                        <div class="it-float-badge it-badge-bottom">
                            <div class="it-badge-icon it-badge-icon-amber">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div>
                                <div class="it-badge-label">This Week</div>
                                <div class="it-badge-val">Sales +24%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave transition -->
            <div class="it-wave-bottom">
                <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z" fill="#ded18a"/>
                </svg>
            </div>
        </header>

        <!-- ── LIVE STORES SECTION ───────────────────────── -->
        <section id="stores" class="it-live-stores">
            <div class="it-wave-top-stores">
                <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,60 C480,0 960,60 1440,0 L1440,0 L0,0 Z" fill="#ded18a"/>
                </svg>
            </div>

            <div class="it-container">
                <div class="it-live-header">
                    <div>
                        <div class="it-section-eyebrow it-eyebrow-light">Explore Nearby</div>
                        <h2 class="it-section-title it-title-light">Available <span class="it-title-gold">Stores</span></h2>
                        <p class="it-section-sub it-sub-light">Discover local vendors in your area. Fresh products, best prices.</p>
                    </div>
                    <div class="it-live-pulse">
                        <span class="it-pulse-ring"></span>
                        <span class="it-pulse-dot"></span>
                        <span class="it-pulse-label">{{ props.openStoreCount }} Open Now</span>
                    </div>
                </div>

                <!-- Stores grid - showing all open stores with a View All button -->
                <div>
                    <div class="it-big-stores-list">
                        <div v-for="store in props.openStores" :key="store.id" class="it-big-store-card">
                            <!-- Store Info Side -->
                            <div class="it-big-store-info">
                                <div class="it-big-store-cover">
                                    <img :src="store.cover || `https://picsum.photos/600/400?random=${store.id}`" :alt="store.name" @error="(e) => e.target.src = `https://picsum.photos/600/400?random=${store.id}`" />
                                    <div class="it-store-open-badge">
                                        <span class="it-open-dot"></span> Open Now
                                    </div>
                                </div>
                                <div class="it-big-store-details">
                                    <div class="it-store-avatar-row">
                                        <div class="it-store-logo">
                                            <img v-if="store.logo" :src="store.logo" :alt="store.name" />
                                            <span v-else>{{ store.name.slice(0, 2).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="it-big-store-name">{{ store.name }}</div>
                                            <div class="it-big-store-address">{{ store.address }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Products Side -->
                            <div class="it-big-store-products">
                                <template v-if="store.products && store.products.length > 0">
                                    <div v-for="product in store.products" :key="product.id" class="it-bscp-card">
                                        <div class="it-bscp-img">
                                             <svg width="24" height="24" fill="none" class="it-bscp-placeholder" viewBox="0 0 24 24" stroke="currentColor">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                             </svg>
                                        </div>
                                        <div class="it-bscp-info">
                                            <div class="it-bscp-name">{{ product.name }}</div>
                                            <div class="it-bscp-price">₱{{ Number(product.price).toLocaleString() }}</div>
                                        </div>
                                    </div>
                    
                                    <div class="it-bscp-more-pill-wrapper" v-if="store.products.length >= 4">
                                        <Link href="/register" class="it-bscp-more-pill">
                                            View more products &rarr;
                                        </Link>
                                    </div>
                                </template>
                                <div v-else class="it-bscp-empty">
                                    <span>Updating inventory...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Show login gate only if more stores exist -->
                <div v-if="props.openStoreCount > props.openStores.length" class="it-stores-gate">
                    <div class="it-gate-content">
                        <div class="it-gate-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3>{{ props.openStoreCount - props.openStores.length }} more stores available</h3>
                        <p>Create a free account to view all stores, compare prices, and start pre-ordering.</p>
                        <div class="it-gate-btns">
                            <Link href="/register" class="it-gate-btn-primary">Create Free Account</Link>
                            <Link href="/login" class="it-gate-btn-ghost">Login</Link>
                        </div>
                    </div>
                </div>

                    <!-- View All Stores button -->
                    <div class="it-view-all-wrapper">
                        <Link href="/register" class="it-view-all-btn">
                            View All Stores
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="it-wave-to-white">
                <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,0 C480,60 960,0 1440,60 L1440,60 L0,60 Z" fill="#ffffff"/>
                </svg>
            </div>
        </section>

        <!-- ── FEATURES ──────────────────────────────────── -->
        <section id="features" class="it-features">
            <div class="it-container">
                <div class="it-section-heading-wrapper">
                    <div class="it-section-eyebrow">Platform Features</div>
                    <h2 class="it-section-title">Everything You Need<br><span class="it-title-accent">in One Platform</span></h2>
                    <p class="it-section-sub">Powerful, role-based tools built for vendors, customers, and administrators.</p>
                </div>

                <div class="it-features-grid">
                    <div class="it-feature-card it-feature-purple">
                        <div class="it-feature-num">01</div>
                        <div class="it-feature-icon-wrap">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <h3>Admin Dashboard</h3>
                        <p>Full platform oversight — manage users, monitor activity, and moderate stores with ease.</p>
                    </div>

                    <div class="it-feature-card it-feature-teal">
                        <div class="it-feature-num">02</div>
                        <div class="it-feature-icon-wrap">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h3>Vendor Tools</h3>
                        <p>Inventory management, expense tracking, stock alerts, and full sales analytics in one place.</p>
                    </div>

                    <div class="it-feature-card it-feature-amber">
                        <div class="it-feature-num">03</div>
                        <div class="it-feature-icon-wrap">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                <div class="it-section-heading-wrapper">
                    <div class="it-section-eyebrow">Simple Process</div>
                    <h2 class="it-section-title">How <span class="it-title-accent">iTinda</span> Works</h2>
                    <p class="it-section-sub">Three easy steps to start buying or selling locally.</p>
                </div>

                <div class="it-steps">
                    <div class="it-step-connector"></div>

                    <div class="it-step">
                        <div class="it-step-bubble">
                            <span>1</span>
                        </div>
                        <div class="it-step-content">
                            <h3>Sign Up</h3>
                            <p>Create your account as a vendor or customer in minutes — no complicated setup required.</p>
                        </div>
                    </div>

                    <div class="it-step">
                        <div class="it-step-bubble">
                            <span>2</span>
                        </div>
                        <div class="it-step-content">
                            <h3>Connect</h3>
                            <p>Vendors set up their store and inventory. Customers discover nearby shops in their area.</p>
                        </div>
                    </div>

                    <div class="it-step">
                        <div class="it-step-bubble">
                            <span>3</span>
                        </div>
                        <div class="it-step-content">
                            <h3>Grow</h3>
                            <p>Start selling, pre-ordering, and building a stronger local commerce community.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="it-wave-to-amber">
                <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,30 C360,0 1080,60 1440,30 L1440,60 L0,60 Z" fill="#FEFCE8"/>
                </svg>
            </div>
        </section>

        <!-- ── ROLE CARDS ─────────────────────────────── -->
        <section id="vendors" class="it-roles">
            <div class="it-container">
                <div class="it-section-heading-wrapper">
                    <div class="it-section-eyebrow">Role-Based Access</div>
                    <h2 class="it-section-title">Built for <span class="it-title-accent">Vendors</span> &amp; Customers</h2>
                    <p class="it-section-sub">Each role gets exactly the tools they need — nothing more, nothing less.</p>
                </div>

                <div class="it-roles-grid">
                    <!-- Vendor card -->
                    <div class="it-role-vendor-card">
                        <div class="it-role-card-eyebrow">For Vendors</div>
                        <h3>Two access levels<br>for your team</h3>

                        <div class="it-role-tier">
                            <div class="it-tier-badge it-tier-owner">Owner</div>
                            <div class="it-tier-info">
                                <strong>Full Access</strong>
                                <span>View total profits, expenses, and overall store performance with complete analytics.</span>
                            </div>
                        </div>

                        <div class="it-role-tier">
                            <div class="it-tier-badge it-tier-staff">Staff</div>
                            <div class="it-tier-info">
                                <strong>Operational Access</strong>
                                <span>Manage stock levels, handle orders, and view day-to-day operational reports.</span>
                            </div>
                        </div>

                        <Link href="/register?role=vendor" class="it-role-cta">
                            Become a Vendor
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Customer card -->
                    <div id="customers" class="it-role-customer-card">
                        <div class="it-role-card-eyebrow it-eyebrow-dark">For Customers</div>
                        <h3 class="it-dark-heading">Find the best deals<br>near you</h3>

                        <ul class="it-check-list">
                            <li>
                                <div class="it-check">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                Search stores within your area
                            </li>
                            <li>
                                <div class="it-check">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                Compare product prices across different shops
                            </li>
                            <li>
                                <div class="it-check">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                Pre-order products from your selected vendors
                            </li>
                            <li>
                                <div class="it-check">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                Transparent, real-time pricing — no surprises
                            </li>
                        </ul>

                        <Link href="/register?role=customer" class="it-role-cta-alt">
                            Start Shopping
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA ────────────────────────────────────── -->
        <section class="it-cta">
            <div class="it-cta-bg-waves">
                <svg viewBox="0 0 1440 200" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0,100 C120,150 240,50 360,100 C480,150 600,50 720,100 C840,150 960,50 1080,100 C1200,150 1320,50 1440,100 L1440,200 L0,200 Z" fill="rgba(27, 67, 50, 0.07)"/>
                    <path d="M0,140 C200,100 400,180 600,140 C800,100 1000,180 1200,140 L1440,120 L1440,200 L0,200 Z" fill="rgba(27, 67, 50, 0.05)"/>
                </svg>
            </div>
            <div class="it-container">
                <div class="it-cta-inner">
                    <div class="it-cta-badge">Ready to get started?</div>
                    <h2>Transform<br><span class="it-cta-accent">Local Commerce</span></h2>
                    <p>Join the platform that's connecting communities and empowering local businesses to thrive.</p>
                    <div class="it-cta-btns">
                        <Link href="/register?role=vendor" class="it-cta-btn-gold">Become a Vendor</Link>
                        <Link href="/register?role=customer" class="it-cta-btn-outline">Start Shopping</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FOOTER ──────────────────────────────────── -->
        <footer class="it-footer">
            <div class="it-container">
                <div class="it-footer-grid">
                    <div class="it-footer-brand-col">
                        <div class="it-footer-logo">
                            <img src="/itinda-logo-2.png" alt="iTinda Logo" class="it-footer-logo-img" />
                            iTinda
                        </div>
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
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    canRegister:    Boolean,
    vendorCount:    { type: Number, default: 0 },
    customerCount:  { type: Number, default: 0 },
    openStoreCount: { type: Number, default: 0 },
    openStores:     { type: Array, default: () => [] }
});

const menuOpen = ref(false);
const scrolled = ref(false);

const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

const formatCount = (num) => {
    if (num === undefined || num === null) {
        return '0';
    }
    const n = Number(num);
    if (isNaN(n)) {
        return '0';
    }
    if (n >= 1000000) {
        return (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    }
    if (n >= 1000) {
        return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
    }
    return n.toString();
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,600;9..144,700&family=DM+Sans:wght@300;400;500;600&display=swap');

/* ──────────────────────────────────────────────────────────
   TOKENS
   ────────────────────────────────────────────────────────── */
.it-root {
    --pine:        #1B4332;
    --pine-mid:    #2D6A4F;
    --pine-light:  #40916C;
    --pine-pale:   #D8F3DC;
    --pine-wash:   #F0FAF4;

    --gold:        #B7791F;
    --gold-mid:    #C9923E;
    --gold-light:  #F6AD55;
    --gold-pale:   #FEFCE8;

    --sand:        #FAF9F6;
    --smoke:       #F7F6F3;
    --linen:       #F1EDE4;
    --white:       #FFFFFF;

    --ink:         #1A1A1A;
    --ink-mid:     #374151;
    --ink-soft:    #6B7280;
    --ink-muted:   #9CA3AF;

    --border:      rgba(27, 67, 50, 0.12);
    --border-soft: rgba(27, 67, 50, 0.07);

    --radius-sm:   8px;
    --radius-md:   14px;
    --radius-lg:   22px;
    --radius-xl:   32px;

    font-family: 'DM Sans', sans-serif;
    color: var(--ink);
    background: var(--white);
    overflow-x: hidden;
}

.it-root h1, .it-root h2, .it-root h3 {
    font-family: 'Fraunces', serif;
    margin: 0;
    line-height: 1.15;
}

.it-root a  { text-decoration: none; color: inherit; }
.it-root ul { list-style: none; margin: 0; padding: 0; }
.it-root p  { margin: 0; }
.it-root *  { box-sizing: border-box; }

.it-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 48px;
}

/* ──────────────────────────────────────────────────────────
   SHARED SECTION LABELS
   ────────────────────────────────────────────────────────── */
.it-section-heading-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 100%;
}

.it-section-eyebrow {
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    font-size: 0.7rem;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: var(--pine-light);
    margin-bottom: 14px;
    text-align: center;
}

.it-eyebrow-light { color: rgba(214, 243, 220, 0.75); }
.it-eyebrow-dark  { color: var(--pine-mid); }

.it-section-title {
    font-size: clamp(1.9rem, 3.2vw, 2.8rem);
    font-weight: 600;
    color: var(--ink);
    text-align: center;
    margin-bottom: 14px;
}

.it-title-accent { color: var(--pine-mid); }
.it-title-light  { color: #fff; }
.it-title-gold   { color: var(--gold-light); }
.it-dark-heading { color: var(--ink); }

.it-section-sub {
    font-size: 1rem;
    color: var(--ink-soft);
    line-height: 1.75;
    text-align: center;
    max-width: 600px;
    margin: 0 auto 56px;
}

.it-sub-light { color: rgba(255,255,255,0.6); }

/* ──────────────────────────────────────────────────────────
   NAVBAR
   ────────────────────────────────────────────────────────── */
.it-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 200;
    background: rgba(14, 37, 15, 0.96);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    transition: background 0.3s ease, box-shadow 0.3s ease;
}

.it-nav-scrolled {
    background: rgba(14, 37, 15, 1);
    box-shadow: 0 4px 32px rgba(0,0,0,0.18);
}

.it-nav-inner {
    display: flex;
    align-items: center;
    height: 72px;
    gap: 0;
}

.it-nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-right: 48px;
    flex-shrink: 0;
}

.it-nav-logo-img {
    height: 36px;
    width: auto;
    object-fit: contain;
}

.it-logo-text {
    font-family: 'Fraunces', serif;
    font-weight: 700;
    font-size: 1.5rem;
    color: #f4e187;
    letter-spacing: 0.5px;
}

.it-nav-links {
    display: flex;
    align-items: center;
    gap: 2px;
    flex: 1;
}

.it-nav-links li a {
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    font-size: 0.83rem;
    color: rgba(255,255,255,0.65);
    letter-spacing: 0.3px;
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    transition: color 0.2s, background 0.2s;
}

.it-nav-links li a:hover {
    color: var(--gold-light);
    background: rgba(255,255,255,0.06);
}

.it-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 24px;
    flex-shrink: 0;
}

.it-btn-ghost-nav {
    font-weight: 500;
    font-size: 0.875rem;
    color: rgba(255,255,255,0.8) !important;
    padding: 9px 18px;
    border-radius: var(--radius-sm);
    transition: color 0.2s, background 0.2s;
}

.it-btn-ghost-nav:hover {
    color: #fff !important;
    background: rgba(255,255,255,0.08);
}

.it-btn-primary-nav {
    display: flex;
    align-items: center;
    gap: 7px;
    font-weight: 600;
    font-size: 0.875rem;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine) !important;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    transition: opacity 0.2s, transform 0.15s;
}

.it-btn-primary-nav:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

/* Hamburger */
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
}

.it-ham-line {
    display: block;
    width: 100%; height: 2px;
    background: rgba(255,255,255,0.8);
    border-radius: 2px;
    transition: transform 0.25s, opacity 0.2s;
    transform-origin: center;
}

.it-ham-open-1 { transform: translateY(7px) rotate(45deg); }
.it-ham-open-2 { opacity: 0; transform: scaleX(0); }
.it-ham-open-3 { transform: translateY(-7px) rotate(-45deg); }

.it-mobile-menu {
    background: rgba(20, 55, 38, 0.98);
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    border-top: 1px solid rgba(255,255,255,0.07);
}

.it-mobile-menu-open { max-height: 420px; }

.it-mobile-links { padding: 8px 24px 4px; }

.it-mobile-links li a {
    display: block;
    font-weight: 500;
    font-size: 0.88rem;
    letter-spacing: 0.3px;
    color: rgba(255,255,255,0.7);
    padding: 13px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    transition: color 0.2s;
}

.it-mobile-links li:last-child a { border-bottom: none; }
.it-mobile-links li a:hover { color: var(--gold-light); }

.it-mobile-actions {
    display: flex;
    gap: 12px;
    padding: 16px 24px 20px;
}

.it-mobile-login {
    flex: 1; text-align: center;
    font-weight: 600; font-size: 0.875rem;
    color: rgba(255,255,255,0.8);
    padding: 11px 0;
    border: 1.5px solid rgba(255,255,255,0.2);
    border-radius: var(--radius-sm);
    transition: border-color 0.2s;
}

.it-mobile-login:hover { border-color: rgba(255,255,255,0.45); }

.it-mobile-register {
    flex: 1; text-align: center;
    font-weight: 600; font-size: 0.875rem;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine);
    padding: 11px 0;
    border-radius: var(--radius-sm);
}

/* ──────────────────────────────────────────────────────────
   HERO
   ────────────────────────────────────────────────────────── */
.it-hero {
    padding-top: 72px;
    background: var(--sand);
    position: relative;
    min-height: 92vh;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.it-hero-bg-shapes {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.it-shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.18;
}

.it-shape-1 {
    width: 520px; height: 520px;
    background: var(--pine-pale);
    top: -100px; right: -80px;
    animation: shapeDrift 18s ease-in-out infinite alternate;
}

.it-shape-2 {
    width: 320px; height: 320px;
    background: var(--gold-pale);
    bottom: 60px; left: 5%;
    animation: shapeDrift 14s ease-in-out infinite alternate-reverse;
}

.it-shape-3 {
    width: 200px; height: 200px;
    background: var(--pine-pale);
    top: 50%; right: 30%;
    animation: shapeDrift 20s ease-in-out infinite alternate;
}

.it-shape-4 {
    width: 180px; height: 180px;
    background: #C084FC;
    bottom: 20%; left: 15%;
    filter: blur(100px);
    opacity: 0.08;
    animation: shapeDrift 25s ease-in-out infinite alternate-reverse;
}

.it-hero-waves {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.15;
}

.it-wave-path {
    fill: none;
    stroke: var(--pine-light);
    stroke-width: 1.5;
}

.it-wave-1 { animation: waveDrift1 12s ease-in-out infinite alternate; }
.it-wave-2 { animation: waveDrift2 15s ease-in-out infinite alternate-reverse; }
.it-wave-3 { animation: waveDrift3 18s ease-in-out infinite alternate; }

.it-hero-inner {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 72px;
    align-items: center;
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 80px 0 100px;
}

.it-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    font-weight: 500;
    font-size: 0.78rem;
    letter-spacing: 1px;
    color: var(--pine-mid);
    background: rgba(27, 67, 50, 0.08);
    border: 1px solid rgba(27, 67, 50, 0.15);
    padding: 7px 15px;
    border-radius: 100px;
    margin-bottom: 28px;
    animation: fadeSlideUp 0.7s ease both;
}

.it-eyebrow-dot {
    width: 6px; height: 6px;
    background: var(--pine-light);
    border-radius: 50%;
    animation: pulseDot 2s ease-in-out infinite;
}

.it-hero-h1 {
    font-size: clamp(2.4rem, 4vw, 3.6rem);
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 22px;
    animation: fadeSlideUp 0.7s 0.08s ease both;
    line-height: 1.1;
}

.it-h1-highlight {
    color: var(--pine-mid);
    position: relative;
}

.it-h1-highlight::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0;
    width: 100%; height: 3px;
    background: linear-gradient(90deg, var(--gold-light), var(--gold));
    border-radius: 2px;
}

.it-h1-sub { color: var(--ink-mid); }

.it-hero-desc {
    font-size: 1.05rem;
    line-height: 1.78;
    color: var(--ink-soft);
    max-width: 440px;
    margin-bottom: 36px;
    animation: fadeSlideUp 0.7s 0.15s ease both;
}

.it-hero-cta {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    animation: fadeSlideUp 0.7s 0.22s ease both;
    margin-bottom: 48px;
}

.it-cta-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    background: var(--pine-mid);
    color: #fff !important;
    padding: 14px 28px;
    border-radius: var(--radius-sm);
    transition: background 0.2s, transform 0.15s;
    box-shadow: 0 6px 24px rgba(27, 67, 50, 0.28);
}

.it-cta-primary:hover {
    background: var(--pine);
    transform: translateY(-2px);
}

.it-cta-secondary {
    display: inline-flex;
    align-items: center;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--pine-mid);
    padding: 14px 28px;
    border-radius: var(--radius-sm);
    border: 1.5px solid rgba(27, 67, 50, 0.28);
    transition: border-color 0.2s, background 0.2s, transform 0.15s;
}

.it-cta-secondary:hover {
    border-color: var(--pine-mid);
    background: rgba(27, 67, 50, 0.05);
    transform: translateY(-2px);
}

/* Stats */
.it-hero-stats {
    display: flex;
    align-items: center;
    gap: 28px;
    animation: fadeSlideUp 0.7s 0.3s ease both;
}

.it-stat-sep {
    width: 1px; height: 32px;
    background: var(--border);
    flex-shrink: 0;
}

.it-stat-num {
    font-family: 'Fraunces', serif;
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--pine-mid);
    display: flex;
    align-items: baseline;
    gap: 4px;
    margin-bottom: 2px;
}

.it-stat-live {
    font-family: 'DM Sans', sans-serif;
    font-size: 0.65rem;
    font-weight: 600;
    color: #fff;
    background: #16a34a;
    padding: 2px 7px;
    border-radius: 100px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-left: 4px;
}

.it-stat-label {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--ink-muted);
}

/* Hero Visual */
.it-hero-visual {
    position: relative;
    animation: fadeSlideUp 0.7s 0.15s ease both;
}

.it-visual-glow {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 340px; height: 340px;
    background: radial-gradient(circle, rgba(27, 67, 50, 0.08) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.it-inventory-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 28px;
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 40px rgba(27, 67, 50, 0.1), 0 2px 8px rgba(0,0,0,0.04);
}

.it-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.it-card-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.it-store-avatar {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--pine-mid), var(--pine-light));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
    color: #fff;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.it-card-store-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--ink);
    margin-bottom: 3px;
}

.it-card-status {
    font-size: 0.72rem;
    font-weight: 600;
    color: #16a34a;
    display: flex;
    align-items: center;
    gap: 5px;
}

.it-status-dot {
    width: 6px; height: 6px;
    background: #16a34a;
    border-radius: 50%;
    display: inline-block;
    animation: pulseDot 2s ease-in-out infinite;
}

.it-card-items-count {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--ink-soft);
    background: var(--smoke);
    padding: 4px 10px;
    border-radius: 100px;
}

.it-card-divider {
    height: 1px;
    background: var(--border-soft);
    margin-bottom: 16px;
}

.it-card-row {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-soft);
    gap: 10px;
}

.it-card-row:last-child { border-bottom: none; }

.it-ci-name {
    color: var(--ink-mid);
    font-size: 0.88rem;
    flex: 1;
}

.it-ci-price {
    font-family: 'Fraunces', serif;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--pine-mid);
}

.it-ci-tag {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 4px;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.it-tag-in  { background: rgba(27, 67, 50, 0.08);  color: var(--pine-mid); }
.it-tag-low { background: rgba(183, 121, 31, 0.1); color: var(--gold); }

/* Floating badges */
.it-float-badge {
    position: absolute;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 11px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.09);
    white-space: nowrap;
    z-index: 2;
}

.it-badge-top    { top: -18px; right: -20px; animation: floatY 3.5s ease-in-out infinite; }
.it-badge-bottom { bottom: -18px; left: -20px; animation: floatY 3.5s 1.75s ease-in-out infinite; }

.it-badge-icon {
    width: 30px; height: 30px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.it-badge-icon-green { background: rgba(22, 163, 74, 0.12); }
.it-badge-icon-green svg { width: 14px; height: 14px; color: #16a34a; }
.it-badge-icon-amber { background: rgba(183, 121, 31, 0.12); }
.it-badge-icon-amber svg { width: 14px; height: 14px; color: var(--gold); }

.it-badge-label {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin-bottom: 2px;
}

.it-badge-val {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--ink);
}

/* Wave */
.it-wave-bottom {
    position: absolute;
    bottom: -1px; left: 0; right: 0;
    line-height: 0;
}

.it-wave-bottom svg {
    width: 100%; height: 80px;
    display: block;
}

/* ──────────────────────────────────────────────────────────
   FEATURES
   ────────────────────────────────────────────────────────── */
.it-features {
    background: var(--white);
    padding: 80px 0 40px;
    position: relative;
}

.it-features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.it-feature-card {
    border-radius: var(--radius-lg);
    padding: 36px 32px;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: default;
}

.it-feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 48px rgba(0,0,0,0.1);
}

.it-feature-purple {
    background: #FAF8FF;
    border: 1px solid rgba(139, 92, 246, 0.15);
}

.it-feature-teal {
    background: var(--pine-wash);
    border: 1px solid rgba(27, 67, 50, 0.15);
}

.it-feature-amber {
    background: var(--gold-pale);
    border: 1px solid rgba(183, 121, 31, 0.15);
}

.it-feature-num {
    font-family: 'Fraunces', serif;
    font-size: 3rem;
    font-weight: 700;
    opacity: 0.08;
    position: absolute;
    top: 16px; right: 24px;
    line-height: 1;
    color: var(--ink);
}

.it-feature-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 22px;
}

.it-feature-purple .it-feature-icon-wrap {
    background: rgba(139, 92, 246, 0.12);
}

.it-feature-purple .it-feature-icon-wrap svg { color: #7c3aed; }

.it-feature-teal .it-feature-icon-wrap {
    background: rgba(27, 67, 50, 0.1);
}

.it-feature-teal .it-feature-icon-wrap svg { color: var(--pine-mid); }

.it-feature-amber .it-feature-icon-wrap {
    background: rgba(183, 121, 31, 0.12);
}

.it-feature-amber .it-feature-icon-wrap svg { color: var(--gold); }

.it-feature-card h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 10px;
}

.it-feature-card p {
    font-size: 0.9rem;
    color: var(--ink-soft);
    line-height: 1.7;
}

.it-wave-to-smoke {
    line-height: 0;
    margin-top: 40px;
}
.it-wave-to-smoke svg { width: 100%; display: block; }

/* ──────────────────────────────────────────────────────────
   LIVE STORES
   ────────────────────────────────────────────────────────── */
.it-live-stores {
    background: var(--pine);
    padding: 0 0 80px;
    position: relative;
}

.it-wave-top-stores {
    line-height: 0;
    margin-bottom: 60px;
}
.it-wave-top-stores svg { width: 100%; display: block; }

.it-live-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 40px;
    gap: 24px;
}

.it-live-pulse {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 100px;
    padding: 10px 20px;
    flex-shrink: 0;
    position: relative;
}

.it-pulse-ring {
    width: 16px; height: 16px;
    border-radius: 50%;
    border: 2px solid rgba(74, 222, 128, 0.5);
    position: absolute;
    animation: pulseRing 2s ease-out infinite;
}

.it-pulse-dot {
    width: 10px; height: 10px;
    background: #4ade80;
    border-radius: 50%;
    display: block;
    margin-left: 3px;
}

.it-pulse-label {
    font-weight: 700;
    font-size: 0.9rem;
    color: #fff;
    margin-left: 6px;
}

.it-stores-loading {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.it-skeleton {
    height: 280px;
    background: rgba(255,255,255,0.07);
    border-radius: var(--radius-lg);
    animation: shimmer 1.5s ease-in-out infinite alternate;
}

.it-stores-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}

.it-store-card {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.it-store-card:hover {
    transform: translateY(-3px);
    background: rgba(255,255,255,0.09);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2);
}

.it-store-cover {
    position: relative;
    height: 150px;
    overflow: hidden;
}

.it-store-cover img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.it-store-card:hover .it-store-cover img {
    transform: scale(1.04);
}

.it-store-cover-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(27,67,50,0.6) 0%, transparent 60%);
}

.it-store-open-badge {
    position: absolute;
    top: 12px; right: 12px;
    background: rgba(22, 163, 74, 0.9);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 100px;
    display: flex;
    align-items: center;
    gap: 5px;
    letter-spacing: 0.3px;
}

.it-open-dot {
    width: 5px; height: 5px;
    background: #fff;
    border-radius: 50%;
    display: inline-block;
    animation: pulseDot 2s ease-in-out infinite;
}

.it-store-body { padding: 18px 20px 20px; }

.it-store-avatar-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.it-store-logo {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--pine-light), var(--pine-mid));
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    font-weight: 700; font-size: 0.72rem;
    color: #fff;
    letter-spacing: 0.5px;
}

.it-store-logo img {
    width: 100%; height: 100%;
    object-fit: cover;
}

.it-store-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: #fff;
    margin-bottom: 3px;
}

.it-store-address {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

.it-store-meta {
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.it-store-hours {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
}

.it-store-phone {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.4);
}

.it-stores-gate {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-xl);
    overflow: hidden;
    text-align: center;
    padding: 52px 40px;
    margin-bottom: 32px;
}

.it-gate-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
    color: rgba(255,255,255,0.6);
}

.it-stores-gate h3 {
    font-size: 1.4rem;
    color: #fff;
    margin-bottom: 10px;
}

.it-stores-gate p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.55);
    max-width: 380px;
    margin: 0 auto 28px;
    line-height: 1.65;
}

.it-gate-btns {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.it-gate-btn-primary {
    font-weight: 600;
    font-size: 0.9rem;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine) !important;
    padding: 12px 28px;
    border-radius: var(--radius-sm);
    transition: opacity 0.2s, transform 0.15s;
}

.it-gate-btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

.it-gate-btn-ghost {
    font-weight: 600;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.75) !important;
    padding: 12px 28px;
    border-radius: var(--radius-sm);
    border: 1.5px solid rgba(255,255,255,0.2);
    transition: border-color 0.2s;
}

.it-gate-btn-ghost:hover { border-color: rgba(255,255,255,0.45); }

.it-view-all-wrapper {
    text-align: center;
    margin-top: 24px;
}

.it-view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--gold-light) !important;
    padding: 14px 32px;
    border: 1.5px solid rgba(246, 173, 85, 0.4);
    border-radius: var(--radius-sm);
    transition: all 0.2s;
}

.it-view-all-btn:hover {
    border-color: var(--gold-light);
    background: rgba(246, 173, 85, 0.1);
    transform: translateY(-2px);
}

.it-wave-to-white { line-height: 0; margin-top: 40px; }
.it-wave-to-white svg { width: 100%; display: block; }

/* ──────────────────────────────────────────────────────────
   HOW IT WORKS
   ────────────────────────────────────────────────────────── */
.it-how {
    background: var(--smoke);
    padding: 80px 0 40px;
    position: relative;
}

.it-steps {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    position: relative;
}

.it-step-connector {
    position: absolute;
    top: 28px;
    left: calc(16.5% + 32px);
    right: calc(16.5% + 32px);
    height: 2px;
    background: linear-gradient(90deg, var(--pine-pale), var(--gold-pale));
    border-radius: 2px;
}

.it-step {
    text-align: center;
    padding: 0 16px;
}

.it-step-bubble {
    width: 56px; height: 56px;
    border-radius: 50%;
    background: var(--pine-mid);
    color: var(--gold-light);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
    position: relative; z-index: 1;
    font-family: 'Fraunces', serif;
    font-size: 1.2rem;
    font-weight: 700;
    box-shadow: 0 0 0 6px rgba(27, 67, 50, 0.1), 0 4px 16px rgba(27, 67, 50, 0.2);
    transition: transform 0.2s ease;
}

.it-step:hover .it-step-bubble { transform: scale(1.08); }

.it-step-content h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 10px;
}

.it-step-content p {
    font-size: 0.9rem;
    color: var(--ink-soft);
    line-height: 1.7;
}

.it-wave-to-amber { line-height: 0; margin-top: 40px; }
.it-wave-to-amber svg { width: 100%; display: block; }

/* ──────────────────────────────────────────────────────────
   ROLES
   ────────────────────────────────────────────────────────── */
.it-roles {
    background: var(--gold-pale);
    padding: 80px 0;
}

.it-roles-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 28px;
}

.it-role-vendor-card {
    background: var(--pine);
    border-radius: var(--radius-xl);
    padding: 44px;
    position: relative;
    overflow: hidden;
}

.it-role-vendor-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}

.it-role-vendor-card .it-role-card-eyebrow {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(214, 243, 220, 0.6);
    margin-bottom: 10px;
}

.it-role-vendor-card h3 {
    color: var(--gold-light);
    font-size: 1.5rem;
    margin-bottom: 32px;
}

.it-role-tier {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 20px;
}

.it-tier-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 5px 13px;
    border-radius: 100px;
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 1px;
    letter-spacing: 0.5px;
}

.it-tier-owner {
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine);
}

.it-tier-staff {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,255,255,0.18);
}

.it-tier-info {
    font-size: 0.875rem;
    line-height: 1.55;
    color: rgba(255,255,255,0.6);
}

.it-tier-info strong {
    display: block;
    font-weight: 700;
    color: rgba(255,255,255,0.9);
    margin-bottom: 3px;
}

.it-role-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 32px;
    font-weight: 600;
    font-size: 0.875rem;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine) !important;
    padding: 13px 24px;
    border-radius: var(--radius-sm);
    transition: opacity 0.2s, transform 0.15s;
}

.it-role-cta:hover { opacity: 0.9; transform: translateY(-1px); }

.it-role-customer-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 44px;
}

.it-role-customer-card .it-role-card-eyebrow {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.it-role-customer-card h3 {
    font-size: 1.5rem;
    margin-bottom: 32px;
}

.it-check-list li {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    margin-bottom: 18px;
    font-size: 0.9rem;
    color: var(--ink-mid);
}

.it-check-list li:last-child { margin-bottom: 0; }

.it-check {
    width: 22px; height: 22px;
    background: rgba(27, 67, 50, 0.1);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}

.it-check svg { width: 10px; height: 10px; color: var(--pine-mid); }

.it-role-cta-alt {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 28px;
    font-weight: 600;
    font-size: 0.875rem;
    background: var(--pine-mid);
    color: #fff !important;
    padding: 13px 24px;
    border-radius: var(--radius-sm);
    transition: background 0.2s, transform 0.15s;
    box-shadow: 0 4px 16px rgba(27, 67, 50, 0.2);
}

.it-role-cta-alt:hover { background: var(--pine); transform: translateY(-1px); }

/* ──────────────────────────────────────────────────────────
   CTA
   ────────────────────────────────────────────────────────── */
.it-cta {
    background: var(--sand);
    padding: 100px 0;
    position: relative;
}

.it-cta-bg-waves {
    position: absolute;
    top: 0; left: 0; right: 0;
    pointer-events: none;
}

.it-cta-bg-waves svg {
    width: 100%;
    display: block;
}

.it-cta-inner {
    text-align: center;
    max-width: 580px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.it-cta-badge {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--pine-light);
    background: rgba(27, 67, 50, 0.08);
    border: 1px solid rgba(27, 67, 50, 0.15);
    padding: 6px 16px;
    border-radius: 100px;
    margin-bottom: 22px;
}

.it-cta h2 {
    font-size: clamp(2rem, 3.5vw, 3rem);
    color: var(--ink);
    margin-bottom: 16px;
}

.it-cta-accent { color: var(--pine-mid); }

.it-cta p {
    font-size: 1rem;
    color: var(--ink-soft);
    line-height: 1.72;
    margin-bottom: 36px;
}

.it-cta-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}

.it-cta-btn-gold {
    font-weight: 700;
    font-size: 0.95rem;
    background: linear-gradient(135deg, var(--gold-light), var(--gold));
    color: var(--pine) !important;
    padding: 15px 36px;
    border-radius: var(--radius-sm);
    transition: opacity 0.2s, transform 0.15s;
    box-shadow: 0 6px 24px rgba(183, 121, 31, 0.3);
}

.it-cta-btn-gold:hover { opacity: 0.9; transform: translateY(-2px); }

.it-cta-btn-outline {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--pine-mid) !important;
    padding: 15px 36px;
    border-radius: var(--radius-sm);
    border: 1.5px solid rgba(27, 67, 50, 0.3);
    transition: border-color 0.2s, background 0.2s, transform 0.15s;
}

.it-cta-btn-outline:hover {
    border-color: var(--pine-mid);
    background: rgba(27, 67, 50, 0.05);
    transform: translateY(-2px);
}

/* ──────────────────────────────────────────────────────────
   FOOTER
   ────────────────────────────────────────────────────────── */
.it-footer {
    background: var(--ink);
    color: var(--white);
    padding: 64px 0 28px;
}

.it-footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 48px;
}

.it-footer-logo {
    font-family: 'Fraunces', serif;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--gold-light);
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 14px;
}

.it-footer-logo-img {
    height: 28px;
    width: auto;
    object-fit: contain;
}

.it-footer-desc {
    font-size: 0.86rem;
    color: rgba(255,255,255,0.38);
    line-height: 1.68;
    max-width: 230px;
}

.it-footer-col-title {
    font-weight: 700;
    font-size: 0.7rem;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: var(--gold-light);
    margin-bottom: 18px;
    opacity: 0.75;
}

.it-footer-col ul li { margin-bottom: 11px; }

.it-footer-col ul li a {
    font-size: 0.86rem;
    color: rgba(255,255,255,0.38);
    transition: color 0.2s;
}

.it-footer-col ul li a:hover { color: rgba(255,255,255,0.8); }

.it-footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.06);
    padding-top: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.it-store-closed-badge {
    background: rgba(239, 68, 68, 0.9) !important;
}

.it-closed-dot {
    background: #fff;
    animation: none !important;
    opacity: 0.7;
}

.it-footer-copy { font-size: 0.8rem; color: rgba(255,255,255,0.22); }
.it-footer-copy span { color: var(--gold-light); opacity: 0.7; }

/* ──────────────────────────────────────────────────────────
   KEYFRAMES
   ────────────────────────────────────────────────────────── */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

@keyframes pulseDot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.7); }
}

@keyframes pulseRing {
    0%   { transform: scale(0.8); opacity: 0.8; }
    100% { transform: scale(2.2); opacity: 0; }
}

@keyframes floatY {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-10px); }
}

@keyframes shapeDrift {
    from { transform: translate(0, 0) scale(1); }
    to   { transform: translate(30px, 20px) scale(1.05); }
}

@keyframes waveDrift1 {
    from { transform: translateX(0) translateY(0); }
    to   { transform: translateX(-30px) translateY(15px); }
}

@keyframes waveDrift2 {
    from { transform: translateX(0) translateY(0); }
    to   { transform: translateX(20px) translateY(-10px); }
}

@keyframes waveDrift3 {
    from { transform: translateX(0) translateY(0); }
    to   { transform: translateX(-15px) translateY(10px); }
}

@keyframes shimmer {
    from { opacity: 0.5; }
    to   { opacity: 0.8; }
}

/* ──────────────────────────────────────────────────────────
   RESPONSIVE — tablet ≤ 1024px
   ────────────────────────────────────────────────────────── */
@media (max-width: 1024px) {
    .it-container { padding: 0 32px; }
    .it-hero-inner { gap: 40px; }
    .it-features-grid { gap: 18px; }
    .it-stores-grid { grid-template-columns: repeat(2, 1fr); }
    .it-footer-grid { grid-template-columns: 2fr 1fr 1fr; }
    .it-footer-grid > .it-footer-brand-col { grid-column: 1 / -1; }
}

/* ──────────────────────────────────────────────────────────
   RESPONSIVE — mobile ≤ 768px
   ────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .it-container { padding: 0 20px; }

    .it-nav-links, .it-nav-actions { display: none; }
    .it-hamburger { display: flex; }
    .it-nav-inner { height: 64px; }
    .it-nav-logo  { margin-right: 0; }

    .it-hero-inner {
        grid-template-columns: 1fr;
        padding: 56px 0 80px;
        gap: 0;
    }
    .it-hero-visual { display: none; }
    .it-hero-desc { max-width: 100%; }
    .it-hero-stats { flex-wrap: wrap; gap: 20px; }

    .it-features-grid { grid-template-columns: 1fr; gap: 16px; }
    .it-stores-grid { grid-template-columns: 1fr; gap: 16px; }
    .it-stores-loading { grid-template-columns: 1fr; }
    .it-steps { grid-template-columns: 1fr; gap: 36px; }
    .it-step-connector { display: none; }
    .it-roles-grid { grid-template-columns: 1fr; }

    .it-live-header { flex-direction: column; align-items: flex-start; }
    .it-section-title, .it-section-eyebrow, .it-section-sub { text-align: left; }
    .it-live-header .it-section-title { text-align: left; }

    .it-footer-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
    .it-footer-grid > .it-footer-brand-col { grid-column: 1 / -1; }
    .it-footer-bottom { flex-direction: column; text-align: center; }
}

/* ──────────────────────────────────────────────────────────
   RESPONSIVE — small ≤ 480px
   ────────────────────────────────────────────────────────── */
@media (max-width: 480px) {
    .it-hero-h1 { font-size: 2rem; }
    .it-hero-cta { flex-direction: column; }
    .it-cta-primary, .it-cta-secondary { width: 100%; justify-content: center; }
    .it-stat-sep { display: none; }
    .it-hero-stats { gap: 16px; }
    .it-cta-btns { flex-direction: column; }
    .it-cta-btn-gold, .it-cta-btn-outline { width: 100%; text-align: center; }
    .it-footer-grid { grid-template-columns: 1fr; }
    .it-roles-grid > .it-role-vendor-card,
    .it-roles-grid > .it-role-customer-card { padding: 32px 24px; }
    .it-gate-btns { flex-direction: column; }
    .it-view-all-btn { width: 100%; justify-content: center; }
}

/* BIG STORE CARDS */
.it-big-stores-list {
    display: flex;
    flex-direction: column;
    gap: 32px;
    margin-bottom: 48px;
}

.it-big-store-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    display: flex;
    overflow: hidden;
    border: 1px solid var(--border-soft);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.it-big-store-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.1);
}

.it-big-store-info {
    width: 320px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border-soft);
    background: var(--smoke);
}

.it-big-store-cover {
    height: 160px;
    position: relative;
}

.it-big-store-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.it-big-store-details {
    padding: 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}



.it-big-store-products {
    flex: 1;
    padding: 24px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 16px;
    background: var(--white);
    align-items: start;
}

.it-bscp-card {
    border: 1px solid var(--border-soft);
    border-radius: var(--radius-md);
    padding: 12px;
    text-align: center;
    background: var(--sand);
    transition: border-color 0.2s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.it-bscp-card:hover {
    border-color: var(--pine-light);
}

.it-bscp-img {
    height: 80px;
    background: var(--white);
    border-radius: var(--radius-sm);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--pine-light);
    opacity: 0.6;
}

.it-bscp-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.3;
}

.it-bscp-price {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--pine-mid);
    margin-top: auto;
}

.it-bscp-more-pill-wrapper {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    margin-top: 8px;
}

.it-bscp-more-pill {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--pine-mid);
    background: var(--pine-pale);
    padding: 8px 20px;
    border-radius: 100px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
}

.it-bscp-more-pill:hover {
    background: var(--pine-light);
    color: var(--white);
    border-color: var(--pine-light);
}

.it-big-store-name {
    font-weight: 700;
    font-size: 1.05rem;
    color: var(--ink);
    margin-bottom: 4px;
    line-height: 1.2;
}

.it-big-store-address {
    font-size: 0.82rem;
    color: var(--ink-soft);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.it-bscp-empty {
    grid-column: 1 / -1;
    display: flex; align-items: center; justify-content: center;
    color: var(--ink-soft); font-style: italic; font-size: 0.9rem;
    height: 100%; min-height: 120px;
}

@media (max-width: 768px) {
    .it-big-store-card {
        flex-direction: column;
    }
    .it-big-store-info {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid var(--border-soft);
    }
    .it-big-store-products {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>