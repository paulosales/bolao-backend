<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BetController;
use App\Controllers\InvitationController;
use App\Controllers\MatchController;
use App\Controllers\PoolController;
use App\Controllers\RankingController;
use App\Controllers\RulesController;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Public routes (no auth)
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Auth
        $group->post('/auth/register', [AuthController::class, 'register']);
        $group->post('/auth/login', [AuthController::class, 'login']);
        $group->post('/auth/refresh', [AuthController::class, 'refresh']);

        // Invitations (public - for accepting)
        $group->get('/invitations/{token}', [InvitationController::class, 'show']);
        $group->post('/invitations/{token}/accept', [InvitationController::class, 'accept']);

        // Matches (public - for viewing)
        $group->get('/matches', [MatchController::class, 'index']);
        $group->get('/matches/{id}', [MatchController::class, 'show']);
        $group->get('/teams', [MatchController::class, 'teams']);

        // Pool ranking (public)
        $group->get('/pools/{id}/ranking', [RankingController::class, 'index']);
    });

    // Protected routes (auth required)
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Current user
        $group->get('/auth/me', [AuthController::class, 'me']);
        $group->put('/auth/me', [AuthController::class, 'updateProfile']);

        // Pools
        $group->get('/pools', [PoolController::class, 'index']);
        $group->post('/pools', [PoolController::class, 'create']);
        $group->get('/pools/{id}', [PoolController::class, 'show']);
        $group->put('/pools/{id}', [PoolController::class, 'update']);
        $group->delete('/pools/{id}', [PoolController::class, 'delete']);
        $group->get('/pools/{id}/members', [PoolController::class, 'members']);
        $group->delete('/pools/{id}/members/{userId}', [PoolController::class, 'removeMember']);

        // Pool match settings
        $group->get('/pools/{id}/match-settings', [PoolController::class, 'matchSettings']);
        $group->put('/pools/{id}/match-settings/{matchId}', [PoolController::class, 'updateMatchSetting']);

        // Invitations
        $group->get('/pools/{id}/invitations', [InvitationController::class, 'index']);
        $group->post('/pools/{id}/invitations', [InvitationController::class, 'create']);
        $group->post('/pools/{id}/invitations/link', [InvitationController::class, 'generateLink']);
        $group->post('/invitations/{token}/join', [InvitationController::class, 'join']);
        $group->delete('/invitations/{invitationId}', [InvitationController::class, 'cancel']);

        // Bets
        $group->get('/pools/{id}/bets', [BetController::class, 'index']);
        $group->post('/pools/{id}/bets', [BetController::class, 'store']);
        $group->put('/pools/{id}/bets/{betId}', [BetController::class, 'update']);
        $group->get('/pools/{id}/bets/my', [BetController::class, 'myBets']);

        // Rules
        $group->get('/pools/{id}/rules', [RulesController::class, 'show']);
        $group->put('/pools/{id}/rules', [RulesController::class, 'update']);
        $group->post('/pools/{id}/rules/accept', [RulesController::class, 'accept']);

        // Scoring (admin - calculate points after match finishes)
        $group->post('/matches/{id}/calculate-scores', [MatchController::class, 'calculateScores']);
        $group->put('/matches/{id}/result', [MatchController::class, 'updateResult']);
    })->add(AuthMiddleware::class);
};
