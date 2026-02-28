<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Connect Local Vendors & Customers</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    
    <!-- Tailwind CSS via Vite -->
    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">LocalMarket</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-indigo-600 transition">Features</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-indigo-600 transition">How It Works</a>
                    <a href="#vendors" class="text-gray-700 hover:text-indigo-600 transition">For Vendors</a>
                    <a href="#customers" class="text-gray-700 hover:text-indigo-600 transition">For Customers</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-700 hover:text-indigo-600 transition">Login</a>
                    <a href="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="pt-24 pb-16 bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl font-extrabold text-gray-900 leading-tight">
                        Connect with Local <span class="text-indigo-600">Vendors</span> in Your Neighborhood
                    </h1>
                    <p class="mt-6 text-xl text-gray-600">
                        Browse, compare prices, and pre-order from nearby shops. 
                        Vendors can efficiently manage their stores with powerful analytics tools.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="/register/vendor" class="bg-indigo-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg">
                            Start Selling
                        </a>
                        <a href="/register/customer" class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold border-2 border-indigo-600 hover:bg-indigo-50 transition">
                            Start Shopping
                        </a>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">
                        Join 1000+ local businesses and 50,000+ happy customers
                    </p>
                </div>
                <div class="relative">
                    <img src="https://placehold.co/600x400/indigo/white?text=Platform+Preview" 
                         alt="Platform Preview" 
                         class="rounded-2xl shadow-2xl">
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Everything You Need in One Platform</h2>
                <p class="text-xl text-gray-600">Powerful features for both vendors and customers</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Admin Feature -->
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Admin Dashboard</h3>
                    <p class="text-gray-600">Complete platform oversight, user management, and moderation tools.</p>
                </div>
                
                <!-- Vendor Feature -->
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Vendor Tools</h3>
                    <p class="text-gray-600">Inventory management, expense tracking, and sales analytics for owners and staff.</p>
                </div>
                
                <!-- Customer Feature -->
                <div class="p-6 border rounded-xl hover:shadow-lg transition">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Customer Experience</h3>
                    <p class="text-gray-600">Search nearby stores, compare prices, and pre-order products easily.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How LocalMarket Works</h2>
                <p class="text-xl text-gray-600">Simple, transparent, and efficient for everyone</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-indigo-600">1</div>
                    <h3 class="text-xl font-bold mb-2">Sign Up</h3>
                    <p class="text-gray-600">Create your account as a vendor or customer in minutes</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-indigo-600">2</div>
                    <h3 class="text-xl font-bold mb-2">Connect</h3>
                    <p class="text-gray-600">Vendors set up stores, customers find nearby shops</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold text-indigo-600">3</div>
                    <h3 class="text-xl font-bold mb-2">Grow</h3>
                    <p class="text-gray-600">Start selling, shopping, and building your local community</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Role-Based Access -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Vendor Roles -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-8 rounded-2xl">
                    <h3 class="text-2xl font-bold mb-6">For Vendors</h3>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-green-600 text-white rounded-full px-3 py-1 text-sm font-bold mr-4">Owner</div>
                            <div>
                                <p class="font-semibold">Full Access</p>
                                <p class="text-gray-600">View total profits, expenses, and overall performance</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-600 text-white rounded-full px-3 py-1 text-sm font-bold mr-4">Staff</div>
                            <div>
                                <p class="font-semibold">Operational Access</p>
                                <p class="text-gray-600">Manage stock and view operational analytics</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Benefits -->
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-2xl">
                    <h3 class="text-2xl font-bold mb-6">For Customers</h3>
                    <ul class="space-y-4">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Search stores within your area
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Compare product prices across shops
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Pre-order products from selected vendors
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-indigo-600">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to Transform Local Commerce?</h2>
            <p class="text-xl text-indigo-100 mb-8">Join the platform that's connecting communities and empowering local businesses.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/register/vendor" class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-indigo-50 transition">
                    Become a Vendor
                </a>
                <a href="/register/customer" class="bg-transparent text-white px-8 py-4 rounded-lg text-lg font-semibold border-2 border-white hover:bg-indigo-700 transition">
                    Start Shopping
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-xl font-bold mb-4">LocalMarket</h4>
                    <p class="text-gray-400">Connecting local vendors with customers in your community.</p>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">For Vendors</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Success Stories</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">For Customers</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Find Stores</a></li>
                        <li><a href="#" class="hover:text-white transition">How It Works</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="font-semibold mb-4">Company</h5>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} LocalMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>