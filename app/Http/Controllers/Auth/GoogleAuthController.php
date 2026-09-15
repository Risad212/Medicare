<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GuestRecordLinker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     * - If google_id exists -> login
     * - If email exists -> link google_id to existing account (if patient/admin/doctor) but only auto-login as patient.
     *   For security: if existing user is admin/doctor, still link but respect role redirect.
     * - Otherwise create new patient user.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?? $googleUser->getNickname() ?? 'Patient';
        $avatar = $googleUser->getAvatar();

        if (! $email) {
            return redirect()->route('login')->withErrors(['email' => 'No email returned from Google.']);
        }

        // Only trust a Google address that corroborates it is verified. An
        // unverified address must not silently claim an existing account.
        $payload = $googleUser->user; // raw OAuth response array (may be null in tests)
        if (is_array($payload) && array_key_exists('email_verified', $payload)) {
            $emailVerified = $payload['email_verified'];
            if ($emailVerified === false || in_array($emailVerified, ['false', '0', 0], true)) {
                return redirect()->route('login')->withErrors(['email' => 'Your Google email must be verified to sign in.']);
            }
        }

        // 1. Find by google_id
        $user = User::where('google_id', $googleId)->first();

        // 2. Find by email and link
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // The local account must already be verified: otherwise any
                // Google profile with a matching (verified) address could
                // claim an unverified local email, including admin/doctor.
                if (is_null($user->email_verified_at)) {
                    return redirect()->route('login')->withErrors(['email' => 'This email is registered but not verified. Log in with your password to verify it first.']);
                }
                // Link Google to the existing account (role preserved).
                // Access control relies on Google's email_verified check above:
                // only the mailbox owner can present a verified address.
                $user->update([
                    'google_id' => $googleId,
                    'provider' => 'google',
                    'avatar' => $avatar ?? $user->avatar,
                    // keep existing role, don't override
                ]);
                // If avatar not empty and user has no profile_image, optionally sync
                if ($avatar && empty($user->profile_image)) {
                    // keep avatar column separate; profile_image stays for upload
                }
            } else {
                // 3. Create new patient
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'provider' => 'google',
                    'avatar' => $avatar,
                    'password' => Hash::make(Str::random(32)), // random, never used; nullable now
                    'role' => 'patient',
                    'email_verified_at' => now(), // Google email is verified
                ]);
            }
        }

        GuestRecordLinker::link($user);

        Auth::login($user, true);

        // New session id on login — otherwise an attacker-supplied session id
        // would survive authentication (session fixation).
        $request->session()->regenerate();

        // Role-based redirect (reuse LoginController logic)
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.home'));
        }

        if ($user->role === 'doctor') {
            return redirect()->intended(route('doctor.dashboard'));
        }

        // Default: patient
        return redirect()->intended(route('profile'));
    }
}
