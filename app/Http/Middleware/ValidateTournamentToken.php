<?php

namespace App\Http\Middleware;

use App\Models\Tournament;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTournamentToken
{
    /**
     * Routes that require token validation for tournament management.
     */
    protected array $protectedActions = [
        'edit', 'update', 'destroy', 
        'generate-schedule', 'start', 'complete',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tournament = $request->route('tournament');
        
        if (!$tournament instanceof Tournament) {
            return $next($request);
        }

        // If tournament is not locked, allow access
        if (!$tournament->is_locked) {
            return $next($request);
        }

        // Check if token is already validated in session
        $validatedTournaments = session('validated_tournaments', []);
        
        if (in_array($tournament->id, $validatedTournaments)) {
            return $next($request);
        }

        // Check for token in request header or query parameter
        $providedToken = $request->header('X-Tournament-Token') 
            ?? $request->input('access_token')
            ?? $request->session()->get("tournament_token_{$tournament->id}");

        if ($providedToken && hash_equals($tournament->access_token ?? '', $providedToken)) {
            // Store validated tournament in session
            $validatedTournaments[] = $tournament->id;
            session(['validated_tournaments' => array_unique($validatedTournaments)]);
            
            return $next($request);
        }

        // Redirect to unlock page
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Access token required',
                'message' => 'This tournament is protected. Please provide the access token.',
            ], 403);
        }

        return redirect()->route('tournaments.unlock', $tournament)
            ->with('error', 'This tournament is protected. Please enter the access token to continue.');
    }
}
