<?php

use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\PairController;
use App\Http\Controllers\ScoreboardController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

// Home redirects to tournaments
Route::get('/', function () {
    return redirect()->route('tournaments.index');
});

// Tournament routes - Public (no token required)
Route::resource('tournaments', TournamentController::class)
    ->only(['index', 'create', 'store', 'show']);

// Tournament unlock routes (public)
Route::get('tournaments/{tournament}/unlock', [TournamentController::class, 'showUnlock'])
    ->name('tournaments.unlock');
Route::post('tournaments/{tournament}/unlock', [TournamentController::class, 'unlock'])
    ->name('tournaments.unlock.submit');

// Tournament routes - Protected (token required)
Route::middleware(['tournament.token'])->group(function () {
    // Resource routes that modify tournaments
    Route::resource('tournaments', TournamentController::class)
        ->only(['edit', 'update', 'destroy']);
    
    // Additional management routes
    Route::post('tournaments/{tournament}/generate-schedule', [TournamentController::class, 'generateSchedule'])
        ->name('tournaments.generate-schedule');
    Route::post('tournaments/{tournament}/start', [TournamentController::class, 'start'])
        ->name('tournaments.start');
    Route::post('tournaments/{tournament}/complete', [TournamentController::class, 'complete'])
        ->name('tournaments.complete');
});

// Public tournament view routes
Route::get('tournaments/{tournament}/statistics', [TournamentController::class, 'statistics'])
    ->name('tournaments.statistics');
Route::get('tournaments/{tournament}/complete-view', [TournamentController::class, 'showComplete'])
    ->name('tournaments.complete.show');

// Leaderboard routes (public)
Route::get('tournaments/{tournament}/leaderboard', [LeaderboardController::class, 'show'])
    ->name('tournaments.leaderboard');
Route::get('tournaments/{tournament}/leaderboard/{team}', [LeaderboardController::class, 'teamLeaderboard'])
    ->name('tournaments.leaderboard.team');

// Team view route (public)
Route::get('tournaments/{tournament}/teams', [TeamController::class, 'index'])
    ->name('tournaments.teams.index');

// Protected team/pair management routes (require token)
Route::middleware(['tournament.token'])->group(function () {
    // Team management
    Route::post('tournaments/{tournament}/teams', [TeamController::class, 'store'])
        ->name('tournaments.teams.store');
    Route::put('tournaments/{tournament}/teams/{team}', [TeamController::class, 'update'])
        ->name('tournaments.teams.update');
    Route::delete('tournaments/{tournament}/teams/{team}', [TeamController::class, 'destroy'])
        ->name('tournaments.teams.destroy');
    Route::post('tournaments/{tournament}/teams/reorder', [TeamController::class, 'reorder'])
        ->name('tournaments.teams.reorder');

    // Pair management
    Route::post('tournaments/{tournament}/teams/{team}/pairs', [PairController::class, 'store'])
        ->name('tournaments.teams.pairs.store');
    Route::put('tournaments/{tournament}/teams/{team}/pairs/{pair}', [PairController::class, 'update'])
        ->name('tournaments.teams.pairs.update');
    Route::delete('tournaments/{tournament}/teams/{team}/pairs/{pair}', [PairController::class, 'destroy'])
        ->name('tournaments.teams.pairs.destroy');
    Route::post('tournaments/{tournament}/teams/{team}/pairs/reorder', [PairController::class, 'reorder'])
        ->name('tournaments.teams.pairs.reorder');
});

// Match routes - Public (view only)
Route::get('tournaments/{tournament}/matches', [MatchController::class, 'index'])
    ->name('tournaments.matches.index');
Route::get('tournaments/{tournament}/matches/{match}', [MatchController::class, 'show'])
    ->name('tournaments.matches.show');

// Match routes - Protected (scoring requires token)
Route::middleware(['tournament.token'])->group(function () {
    Route::get('tournaments/{tournament}/matches/{match}/score', [MatchController::class, 'scoreInput'])
        ->name('tournaments.matches.score');
    Route::post('tournaments/{tournament}/matches/{match}/score', [MatchController::class, 'storeScore'])
        ->name('tournaments.matches.score.store');
    Route::post('tournaments/{tournament}/matches/{match}/start', [MatchController::class, 'start'])
        ->name('tournaments.matches.start');
    Route::post('tournaments/{tournament}/matches/{match}/complete', [MatchController::class, 'complete'])
        ->name('tournaments.matches.complete');
});

// Scoreboard routes (public)
Route::get('tournaments/{tournament}/scoreboard', [ScoreboardController::class, 'show'])
    ->name('tournaments.scoreboard');
Route::get('tournaments/{tournament}/tv', [ScoreboardController::class, 'tv'])
    ->name('tournaments.tv');

// Maintenance routes (protected by maintenance token)
Route::prefix('maintenance')->middleware(['maintenance.token'])->group(function () {
    Route::match(['get', 'post'], '/', [\App\Http\Controllers\MaintenanceController::class, 'index'])
        ->name('maintenance.index');
    Route::delete('/bulk-delete', [\App\Http\Controllers\MaintenanceController::class, 'bulkDelete'])
        ->name('maintenance.bulk-delete');
    Route::delete('/cleanup', [\App\Http\Controllers\MaintenanceController::class, 'cleanup'])
        ->name('maintenance.cleanup');
});

