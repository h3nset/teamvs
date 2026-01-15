<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaintenanceController extends Controller
{
    /**
     * Show the maintenance dashboard.
     */
    public function index(Request $request)
    {
        $query = Tournament::withCount(['teams', 'matches'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('older_than')) {
            $days = (int) $request->older_than;
            $query->where('created_at', '<', now()->subDays($days));
        }

        $tournaments = $query->get();

        // Get stats
        $stats = [
            'total' => Tournament::count(),
            'draft' => Tournament::where('status', 'draft')->count(),
            'active' => Tournament::where('status', 'active')->count(),
            'completed' => Tournament::where('status', 'completed')->count(),
            'old_completed' => Tournament::where('status', 'completed')
                ->where('ended_at', '<', now()->subDays(30))
                ->count(),
        ];

        return Inertia::render('Maintenance/Index', [
            'tournaments' => $tournaments,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status,
                'older_than' => $request->older_than,
            ],
        ]);
    }

    /**
     * Bulk delete selected tournaments.
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'tournament_ids' => 'required|array|min:1',
            'tournament_ids.*' => 'uuid|exists:tournaments,id',
            'confirm' => 'required|accepted',
        ]);

        $count = Tournament::whereIn('id', $validated['tournament_ids'])->delete();

        return redirect()->route('maintenance.index')
            ->with('success', "Successfully deleted {$count} tournament(s).");
    }

    /**
     * Clean up old completed tournaments.
     */
    public function cleanup(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'confirm' => 'required|accepted',
        ]);

        $cutoffDate = now()->subDays($validated['days']);
        
        $count = Tournament::where('status', 'completed')
            ->where('ended_at', '<', $cutoffDate)
            ->delete();

        return redirect()->route('maintenance.index')
            ->with('success', "Cleaned up {$count} tournament(s) older than {$validated['days']} days.");
    }
}
