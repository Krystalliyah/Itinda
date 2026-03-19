<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        $this->app->singleton(\Laravel\Fortify\Contracts\RegisterResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\RegisterResponse
            {
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->route('verification.notice');
                    }

                    return $request->wantsJson()
                        ? response()->json(['two_factor' => false])
                        : redirect()->intended('/dashboard');
                }
            };
        });

        // Override login redirect based on user role and tenancy
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, function () {
            return new class implements \Laravel\Fortify\Contracts\LoginResponse
            {
                public function toResponse($request)
                {
                    $user = $request->user();

                    // Set flag in session to indicate user just logged in
                    $request->session()->flash('just_logged_in', true);

                    if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->route('verification.notice');
                    }

                    // If we are on a tenant subdomain, always redirect to vendor dashboard
                    if (function_exists('tenancy') && tenancy()->initialized) {
                        return $request->wantsJson()
                            ? response()->json(['two_factor' => false])
                            : redirect()->to('/vendor/dashboard');
                    }

                    // Vendor redirects to their tenant subdomain if logging in from central
                    if ($user->hasRole('vendor')) {
                        $tenant = \App\Models\Tenant::where('user_id', $user->id)->first();

                        if ($tenant && $tenant->is_approved) {
                            $domain = $tenant->domains()->first();
                            if ($domain) {
                                $url = $request->getScheme().'://'.$domain->domain.'/vendor/dashboard';

                                return response('', 409)->header('X-Inertia-Location', $url);
                            }
                        }

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
        $this->configureEmailVerificationUrls();

        // Standardize on 'email' as the request parameter name to satisfy Fortify validation.
        // We will handle the actual lookup (login_id vs email) in authenticateUsing.
        Fortify::username('email');
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        // Custom authentication logic
        Fortify::authenticateUsing(function (Request $request) {
            $username = $request->input('email'); // This will contain login_id on tenant, email on central

            if (function_exists('tenancy') && tenancy()->initialized) {
                $user = \App\Models\User::where('login_id', $username)
                    ->orWhere('email', $username)
                    ->first();
            } else {
                $user = \App\Models\User::where('email', $username)->first();
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
                return Inertia::render('auth/LoginTenant', [
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

    private function configureEmailVerificationUrls(): void
    {
        VerifyEmail::createUrlUsing(function ($notifiable): string {
            $relativeSignedUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                absolute: false
            );

            return rtrim(config('app.url'), '/').$relativeSignedUrl;
        });
    }
}
