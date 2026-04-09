<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\InactiveStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class StaffManagementController extends Controller
{
    public function index()
    {
        // Get all staff users (excluding the vendor owner)
        $staff = User::where('id', '!=', auth()->id())
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'vendor');
            })
            ->with('roles', 'permissions')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'login_id' => $user->login_id,
                    'roles' => $user->roles->map(fn ($role) => ['name' => $role->name]),
                    'permissions' => $user->getAllPermissions()->map(fn ($perm) => [
                        'id' => $perm->id,
                        'name' => $perm->name,
                    ]),
                ];
            });

        // Get all available permissions
        $availablePermissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
            ];
        });

        return Inertia::render('vendor/Staff', [
            'staff' => $staff,
            'availablePermissions' => $availablePermissions,
            'passwordRequirements' => [
                'minLength' => 8,
                'requireLetters' => true,
                'requireMixedCase' => true,
                'requireNumbers' => true,
                'requireSymbols' => true,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string|unique:users,login_id',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'login_id' => $validated['login_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('staff');

        // Send verification email
        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Staff account created successfully! A verification email has been sent.');
    }

    public function updatePermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'permissions' => 'array',
        ]);

        // Sync permissions - this will remove old permissions and add new ones
        $user->syncPermissions($validated['permissions']);

        return back()->with('success', 'Permissions updated successfully!');
    }

    public function destroy(Request $request, User $user)
    {
        // Don't allow deleting the vendor owner
        if ($user->hasRole('vendor')) {
            abort(403, 'Cannot delete vendor owner.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Move to inactive staff table
        InactiveStaff::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'login_id' => $user->login_id,
            'reason' => $validated['reason'],
            'deactivated_by' => auth()->id(),
            'deactivated_at' => now(),
        ]);

        // Remove staff role and permissions
        $user->removeRole('staff');
        $user->syncPermissions([]);

        // Log the action
        Log::info('Staff deactivated', [
            'staff_id' => $user->id,
            'staff_name' => $user->name,
            'deactivated_by' => auth()->id(),
            'reason' => $validated['reason'],
            'tenant_id' => tenant()->id ?? null,
        ]);

        return back()->with('success', 'Staff account deactivated successfully!');
    }
}
