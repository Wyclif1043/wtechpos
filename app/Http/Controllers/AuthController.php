<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch; // Add this import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // Log the login activity
            activity()
                ->causedBy(Auth::user())
                ->log('logged in');
                
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegistration()
    {
        // Only allow admins to access registration page
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $roles = [
            'admin' => 'Administrator',
            'manager' => 'Manager', 
            'cashier' => 'Cashier',
            'accountant' => 'Accountant'
        ];

        // Get active branches for branch assignment
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('roles', 'branches')); // Add branches to compact
    }

    public function register(Request $request)
    {
        // Only allow admins to register new users
        if (Auth::check() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,cashier,accountant',
            'branch_id' => 'nullable|exists:branches,id', // Add branch validation
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'branch_id' => $request->branch_id, // Add branch assignment
            'pin_code' => rand(1000, 9999), // Generate random PIN
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Log the user creation activity
        if (Auth::check()) {
            $logProperties = ['role' => $request->role];
            
            if ($request->branch_id) {
                $branch = Branch::find($request->branch_id);
                $logProperties['branch_id'] = $request->branch_id;
                $logProperties['branch_name'] = $branch->name;
            }
            
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->withProperties($logProperties)
                ->log('created user');
        }

        if (Auth::check()) {
            return redirect()->route('users.index')
                ->with('success', 'User registered successfully!');
        }

        // If no one is logged in (shouldn't happen), redirect to login
        return redirect()->route('login')
            ->with('success', 'User registered successfully! Please login.');
    }

    public function logout(Request $request)
    {
        // Log the logout activity
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->log('logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Password Reset Functionality
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', ['token' => $request->token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
                
                // Log password reset activity
                activity()
                    ->causedBy($user)
                    ->log('reset password');
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}