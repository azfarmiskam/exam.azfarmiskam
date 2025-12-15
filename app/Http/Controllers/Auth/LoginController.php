<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Generate captcha numbers
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        
        session([
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
            'captcha_answer' => $num1 + $num2
        ]);
        
        return view('auth.login');
    }
    
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'captcha' => 'required|numeric',
        ], [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            'captcha.required' => 'Please solve the math problem',
            'captcha.numeric' => 'Captcha answer must be a number',
        ]);
        
        // Verify captcha
        $correctAnswer = session('captcha_answer');
        if ((int)$request->captcha !== (int)$correctAnswer) {
            // Generate new captcha
            $this->refreshCaptcha();
            
            return back()->withErrors([
                'captcha' => 'Incorrect answer. Please try again.'
            ])->withInput($request->except('password', 'captcha'));
        }
        
        // Attempt to log the user in
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');
        
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Check if user is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the administrator.'
                ])->withInput($request->except('password', 'captcha'));
            }
            
            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'login',
                'description' => 'User logged in successfully',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Generate new captcha on failed login
        $this->refreshCaptcha();
        
        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->except('password', 'captcha'));
    }
    
    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Log the activity
        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'logout',
                'description' => 'User logged out',
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        }
        
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }
    
    /**
     * Refresh captcha via AJAX
     */
    public function refreshCaptchaAjax(Request $request)
    {
        $num1 = $request->input('num1', rand(1, 10));
        $num2 = $request->input('num2', rand(1, 10));
        
        session([
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
            'captcha_answer' => $num1 + $num2
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Refresh captcha (internal method)
     */
    private function refreshCaptcha()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        
        session([
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
            'captcha_answer' => $num1 + $num2
        ]);
    }
}
