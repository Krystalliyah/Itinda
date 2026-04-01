<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    /**
     * Verify email
     */
    public function verify(Request $request, $id, $hash)
    {
        // Find the user in the current database (tenant or central)
        $user = \App\Models\User::findOrFail($id);
        
        // Verify the hash
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }
        
        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            // Log them in if not already
            if (!auth()->check()) {
                auth()->login($user);
            }
            
            // Redirect based on role
            if ($user->hasRole('vendor') || $user->hasRole('staff')) {
                // If on subdomain, stay there; if on central, go to vendor dashboard
                if (request()->getHost() !== config('app.domain')) {
                    return redirect()->to('/vendor/dashboard')->with('info', 'Email already verified.');
                }
                return redirect()->route('vendor.dashboard')->with('info', 'Email already verified.');
            }
            
            return redirect()->route('dashboard')->with('info', 'Email already verified.');
        }
        
        // Mark as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        
        // Log the user in
        auth()->login($user);
        
        // Redirect based on role
        if ($user->hasRole('vendor') || $user->hasRole('staff')) {
            return redirect()->to('/vendor/dashboard')->with('success', 'Email verified successfully!');
        }
        
        return redirect()->route('dashboard')->with('success', 'Email verified successfully!');
    }
    
    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }
        
        $request->user()->sendEmailVerificationNotification();
        
        return back()->with('status', 'verification-link-sent');
    }
}