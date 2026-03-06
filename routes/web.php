<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Pin the home route to the exact central domain
Route::domain(config('app.domain'))->group(function () {
    Route::get('/', function () {
        // Redirect authenticated users to their appropriate dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    })->name('home');

    Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
        $user = auth()->user();
        $justLoggedIn = request()->session()->get('just_logged_in', false);

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }

        if ($user->hasRole('vendor')) {
            return redirect()->route('vendor.dashboard');
        }

        abort(403, 'No role assigned.');
    })->name('dashboard');

    // Central domain routes (admin, customer, settings)
    require __DIR__.'/settings.php';
    require __DIR__.'/admin.php';
    require __DIR__.'/customer.php';
    
    // Vendor routes (can be on central or tenant domain)
    require __DIR__.'/vendor.php';
});
