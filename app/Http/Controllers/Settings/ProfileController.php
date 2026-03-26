<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     * Syncs changes to both central and tenant databases when on a tenant domain.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Sync to the other database (central <-> tenant)
        $this->syncProfileToOtherDatabase($user, $validated);

        return redirect('/customer/profile');
    }

    /**
     * Keep the user record in sync across central and tenant databases.
     */
    protected function syncProfileToOtherDatabase(User $user, array $data): void
    {
        $isTenantInitialized = function_exists('tenancy') && tenancy()->initialized;

        $syncFields = array_intersect_key($data, array_flip(['name', 'email']));
        if (isset($data['email'])) {
            $syncFields['email_verified_at'] = null;
        }

        if ($isTenantInitialized) {
            // On tenant domain — also update the central DB copy
            DB::connection('mysql')
                ->table('users')
                ->where('email', $user->getOriginal('email') ?? $user->email)
                ->update($syncFields);
        } else {
            // On central domain — also update the tenant DB copy if one exists
            $tenant = \App\Models\Tenant::where('user_id', $user->id)
                ->orWhere('email', $user->getOriginal('email') ?? $user->email)
                ->first();

            if ($tenant) {
                $tenant->run(function () use ($user, $syncFields) {
                    DB::table('users')
                        ->where('email', $user->getOriginal('email') ?? $user->email)
                        ->update($syncFields);
                });
            }
        }
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
