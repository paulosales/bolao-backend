<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bet;
use App\Models\Pool;
use App\Models\PoolMember;
use App\Models\PoolRule;
use App\Models\User;
use App\Services\ScoringService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RankingController
{
    public function __construct(
        private Pool $poolModel,
        private PoolMember $memberModel,
        private Bet $betModel,
        private User $userModel,
        private PoolRule $ruleModel,
        private ScoringService $scoringService
    ) {
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool) {
            return $this->error($response, 'Bolão não encontrado.', 404);
        }

        $members = $this->memberModel->findByPool($poolId);
        $rules   = $this->ruleModel->findByPool($poolId);
        $ranking = [];

        foreach ($members as $member) {
            $userId = (int)$member['user_id'];
            $bets   = $this->betModel->findByPoolAndUser($poolId, $userId);

            $totalPoints = 0;
            $betsWithPoints = [];

            foreach ($bets as $bet) {
                $matchStatus = $bet['match_status'] ?? null;
                $actualHome  = isset($bet['match_home_score']) ? (int)$bet['match_home_score'] : null;
                $actualAway  = isset($bet['match_away_score']) ? (int)$bet['match_away_score'] : null;

                // Compute on-the-fly whenever actual scores are available and we have rules
                if ($actualHome !== null && $actualAway !== null && $rules) {
                    $betHome = (int)($bet['home_score_bet'] ?? $bet['home_score'] ?? 0);
                    $betAway = (int)($bet['away_score_bet'] ?? $bet['away_score'] ?? 0);
                    $points  = $this->scoringService->computePoints($betHome, $betAway, $actualHome, $actualAway, $rules);
                    // Persist computed points to DB if not already set (or outdated)
                    if ($bet['points_earned'] === null || (int)$bet['points_earned'] !== $points) {
                        $this->betModel->updatePoints((int)$bet['id'], $points);
                    }
                } elseif ($bet['points_earned'] !== null) {
                    $points = (int)$bet['points_earned'];
                } else {
                    $points = null;
                }

                $totalPoints += $points ?? 0;

                $betsWithPoints[] = [
                    'match_id'        => $bet['match_id'],
                    'match_number'    => $bet['match_number'] ?? null,
                    'home_team'       => $bet['home_team_name'],
                    'home_flag'       => $bet['home_flag'],
                    'away_team'       => $bet['away_team_name'],
                    'away_flag'       => $bet['away_flag'],
                    'home_score_bet'  => $bet['home_score_bet'] ?? $bet['home_score'] ?? 0,
                    'away_score_bet'  => $bet['away_score_bet'] ?? $bet['away_score'] ?? 0,
                    'actual_home'     => $bet['match_home_score'] ?? null,
                    'actual_away'     => $bet['match_away_score'] ?? null,
                    'match_status'    => $bet['match_status'],
                    'match_date'      => $bet['match_date'],
                    'points_earned'   => $points,
                ];
            }

            $ranking[] = [
                'user_id'      => $userId,
                'user_name'    => $member['name'],
                'email'        => $member['email'],
                'total_points' => $totalPoints,
                'bets_count'   => count($bets),
                'bets'         => $betsWithPoints,
            ];
        }

        // Sort by total points descending
        usort($ranking, fn($a, $b) => $b['total_points'] - $a['total_points']);

        // Add positions
        foreach ($ranking as $i => &$entry) {
            $entry['position'] = $i + 1;
        }
        unset($entry);

        $isFinished = $this->poolModel->isFinished($poolId);
        $champion   = $isFinished && !empty($ranking) ? $ranking[0] : null;

        return $this->json($response, [
            'ranking'    => $ranking,
            'is_finished' => $isFinished,
            'champion'   => $champion,
            'prize'      => $pool['estimated_prize'],
        ]);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function error(Response $response, string $message, int $status): Response
    {
        return $this->json($response, ['error' => $message], $status);
    }
}
