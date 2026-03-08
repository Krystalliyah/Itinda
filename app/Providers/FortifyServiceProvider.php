<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override login redirect based on user role and tenancy
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();
                    
                    // Set flag in session to indicate user just logged in
                    // Dashboard pages will use this to clean browser history
                    $request->session()->flash('just_logged_in', true);
                    
                    // Vendor redirects to their tenant subdomain
                    if ($user->hasRole('vendor')) {
                        $tenant = \App\Models\Tenant::where('user_id', $user->id)->first();
                        
                        if ($tenant && $tenant->is_approved) {
                            $domain = $tenant->domains()->first();
                            if ($domain) {
                                $url = $request->getScheme() . '://' . $domain->domain . '/vendor/dashboard';
                                
                                // Force full page redirect by returning 409 with location header
                                // Inertia will detect this and do window.location = url
                                return response('', 409)->header('X-Inertia-Location', $url);
                            }
                        }
                        
                        // Not approved yet, stay on central
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->intended('/vendor/dashboard');
                    }
                    
                    // Admin stays on central domain
                    if ($user->hasRole('admin')) {
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->intended('/admin/dashboard');
                    }
                    
                    // Customer stays on central domain
                    if ($user->hasRole('customer')) {
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->intended('/customer/dashboard');
                    }
                    
                    return $request->wantsJson()
                        ? response()->json(['two_factor' => false])
                        : redirect()->intended('/dashboard');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();

        // Dynamic username field (login_id vs email)
        Fortify::username(function () {
            if (function_exists('tenancy') && tenancy()->initialized) {
                return 'login_id';
            }
            return 'email';
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        // Custom redirect after login based on role
        Fortify::authenticateUsing(function (Request $request) {
            // Determine which identifier to search by
            if (function_exists('tenancy') && tenancy()->initialized) {
                $loginId = $request->input('login_id');
                $user = \App\Models\User::where('login_id', $loginId)->first();
            } else {
                $user = \App\Models\User::where('email', $request->input('email'))->first();
            }

            if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                return $user;
            }
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(function (Request $request) {
            // Redirect authenticated users to their dashboard
            if ($request->user()) {
                return redirect()->route('dashboard');
            }

            if (function_exists('tenancy') && tenancy()->initialized) {
                // Tenant-specific admin login page
                return Inertia::render('auth/LoginAdminTenant', [
                    'status' => $request->session()->get('status'),
                ]);
            }

            // Central login
            return Inertia::render('auth/Login', [
                'canResetPassword' => Features::enabled(Features::resetPasswords()),
                'canRegister' => Features::enabled(Features::registration()),
                'status' => $request->session()->get('status'),
            ]);
        });

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(function (Request $request) {
            // Redirect authenticated users to their dashboard
            if ($request->user()) {
                return redirect()->route('dashboard');
            }
            return Inertia::render('auth/Register');
        });

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
