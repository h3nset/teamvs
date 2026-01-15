<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMaintenanceToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenanceToken = config('app.maintenance_token');
        
        // If no token is configured, deny access
        if (empty($maintenanceToken)) {
            abort(403, 'Maintenance mode is not configured.');
        }

        // Check if token is validated in session
        if (session('maintenance_validated')) {
            return $next($request);
        }

        // Check for token in request
        $providedToken = $request->input('maintenance_token') 
            ?? $request->header('X-Maintenance-Token');

        if ($providedToken && hash_equals($maintenanceToken, $providedToken)) {
            session(['maintenance_validated' => true]);
            return $next($request);
        }

        // If requesting validation via POST, show error
        if ($request->isMethod('POST') && $request->has('maintenance_token')) {
            return back()->withErrors([
                'maintenance_token' => 'Invalid maintenance token.',
            ]);
        }

        // Show the maintenance login page
        return response()->view('maintenance.login', [], 403);
    }
}
