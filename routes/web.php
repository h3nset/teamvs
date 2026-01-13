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

// Tournament routes
Route::resource('tournaments', TournamentController::class);
Route::post('tournaments/{tournament}/generate-schedule', [TournamentController::class, 'generateSchedule'])
    ->name('tournaments.generate-schedule');
Route::post('tournaments/{tournament}/start', [TournamentController::class, 'start'])
    ->name('tournaments.start');
Route::post('tournaments/{tournament}/complete', [TournamentController::class, 'complete'])
    ->name('tournaments.complete');
Route::get('tournaments/{tournament}/statistics', [TournamentController::class, 'statistics'])
    ->name('tournaments.statistics');
Route::get('tournaments/{tournament}/complete-view', [TournamentController::class, 'showComplete'])
    ->name('tournaments.complete.show');

// Leaderboard routes
Route::get('tournaments/{tournament}/leaderboard', [LeaderboardController::class, 'show'])
    ->name('tournaments.leaderboard');
Route::get('tournaments/{tournament}/leaderboard/{team}', [LeaderboardController::class, 'teamLeaderboard'])
    ->name('tournaments.leaderboard.team');

// Team routes (nested under tournaments)
Route::get('tournaments/{tournament}/teams', [TeamController::class, 'index'])
    ->name('tournaments.teams.index');
Route::post('tournaments/{tournament}/teams', [TeamController::class, 'store'])
    ->name('tournaments.teams.store');
Route::put('tournaments/{tournament}/teams/{team}', [TeamController::class, 'update'])
    ->name('tournaments.teams.update');
Route::delete('tournaments/{tournament}/teams/{team}', [TeamController::class, 'destroy'])
    ->name('tournaments.teams.destroy');
Route::post('tournaments/{tournament}/teams/reorder', [TeamController::class, 'reorder'])
    ->name('tournaments.teams.reorder');

// Pair routes (nested under teams)
Route::post('tournaments/{tournament}/teams/{team}/pairs', [PairController::class, 'store'])
    ->name('tournaments.teams.pairs.store');
Route::put('tournaments/{tournament}/teams/{team}/pairs/{pair}', [PairController::class, 'update'])
    ->name('tournaments.teams.pairs.update');
Route::delete('tournaments/{tournament}/teams/{team}/pairs/{pair}', [PairController::class, 'destroy'])
    ->name('tournaments.teams.pairs.destroy');
Route::post('tournaments/{tournament}/teams/{team}/pairs/reorder', [PairController::class, 'reorder'])
    ->name('tournaments.teams.pairs.reorder');

// Match routes
Route::get('tournaments/{tournament}/matches', [MatchController::class, 'index'])
    ->name('tournaments.matches.index');
Route::get('tournaments/{tournament}/matches/{match}', [MatchController::class, 'show'])
    ->name('tournaments.matches.show');
Route::get('tournaments/{tournament}/matches/{match}/score', [MatchController::class, 'scoreInput'])
    ->name('tournaments.matches.score');
Route::post('tournaments/{tournament}/matches/{match}/score', [MatchController::class, 'storeScore'])
    ->name('tournaments.matches.score.store');
Route::post('tournaments/{tournament}/matches/{match}/start', [MatchController::class, 'start'])
    ->name('tournaments.matches.start');
Route::post('tournaments/{tournament}/matches/{match}/complete', [MatchController::class, 'complete'])
    ->name('tournaments.matches.complete');

// Scoreboard routes
Route::get('tournaments/{tournament}/scoreboard', [ScoreboardController::class, 'show'])
    ->name('tournaments.scoreboard');
Route::get('tournaments/{tournament}/tv', [ScoreboardController::class, 'tv'])
    ->name('tournaments.tv');

