<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\AwardsService;
use App\Services\SchedulingService;
use App\Services\ScoringService;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TournamentController extends Controller
{
    public function __construct(
        private SchedulingService $schedulingService,
        private ScoringService $scoringService,
        private StatisticsService $statisticsService,
        private AwardsService $awardsService
    ) {}

    public function index()
    {
        $tournaments = Tournament::withCount(['teams', 'matches'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Tournaments/Index', [
            'tournaments' => $tournaments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Tournaments/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pairs_per_team' => 'required|integer|min:2|max:10',
            'rounds' => 'required|integer|min:1|max:20',
            'max_matches_per_pair' => 'required|integer|min:1|max:20',
            'points_per_set' => 'required|integer|min:1|max:100',
        ]);

        $tournament = Tournament::create($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Tournament created successfully.');
    }

    public function show(Tournament $tournament)
    {
        $tournament->load(['teams.pairs', 'matches.homePair.team', 'matches.awayPair.team', 'matches.scores']);
        
        $standings = $this->scoringService->getTournamentStandings($tournament);
        $scheduleStats = $this->schedulingService->getScheduleStats($tournament);

        return Inertia::render('Tournaments/Show', [
            'tournament' => $tournament,
            'standings' => $standings,
            'scheduleStats' => $scheduleStats,
        ]);
    }

    public function edit(Tournament $tournament)
    {
        return Inertia::render('Tournaments/Edit', [
            'tournament' => $tournament,
        ]);
    }

    public function update(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pairs_per_team' => 'required|integer|min:2|max:10',
            'rounds' => 'required|integer|min:1|max:20',
            'max_matches_per_pair' => 'required|integer|min:1|max:20',
            'points_per_set' => 'required|integer|min:1|max:100',
        ]);

        $tournament->update($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Tournament updated successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()->route('tournaments.index')
            ->with('success', 'Tournament deleted successfully.');
    }

    public function generateSchedule(Tournament $tournament)
    {
        try {
            $this->schedulingService->generateAndSaveSchedule($tournament);
            
            return redirect()->route('tournaments.show', $tournament)
                ->with('success', 'Schedule generated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('tournaments.show', $tournament)
                ->with('error', $e->getMessage());
        }
    }

    public function start(Tournament $tournament)
    {
        $tournament->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Tournament started!');
    }

    public function complete(Tournament $tournament)
    {
        $tournament->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return redirect()->route('tournaments.complete.show', $tournament)
            ->with('success', 'Tournament completed!');
    }

    /**
     * Display tournament statistics page.
     */
    public function statistics(Tournament $tournament)
    {
        $stats = $this->statisticsService->getTournamentStats($tournament);
        $highlights = $this->statisticsService->getMatchHighlights($tournament);
        $leaderboard = $this->statisticsService->getLeaderboard($tournament);
        
        return Inertia::render('Tournaments/Statistics', [
            'tournament' => $tournament,
            'stats' => $stats,
            'highlights' => $highlights,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Display tournament completion/awards page.
     */
    public function showComplete(Tournament $tournament)
    {
        $tournament->load('teams');
        
        $stats = $this->statisticsService->getTournamentStats($tournament);
        $awards = $this->awardsService->calculateAwards($tournament);
        
        return Inertia::render('Tournaments/Complete', [
            'tournament' => $tournament,
            'stats' => $stats,
            'awards' => $awards,
        ]);
    }
}

