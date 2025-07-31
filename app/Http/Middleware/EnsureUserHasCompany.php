<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user has a company
            if (!$user->company_id) {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun Anda tidak terhubung dengan perusahaan manapun.']);
            }
            
            // Check if company is active
            if ($user->company && $user->company->status !== 'active') {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Perusahaan Anda tidak aktif. Silakan hubungi administrator.']);
            }
            
            // Check if user is active
            if ($user->status !== 'active') {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);
            }
            
            // Update last login information
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'last_login_device' => $request->userAgent(),
            ]);
        }

        return $next($request);
    }
}
