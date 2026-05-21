<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\PoolRule;

class ScoringService
{
    public function __construct(
        private Bet $betModel,
        private GameMatch $matchModel,
        private PoolRule $ruleModel
    ) {
    }

    public function calculateForMatch(int $poolId, int $matchId): int
    {
        $match = $this->matchModel->findById($matchId);
        if (!$match || $match['status'] !== 'finished') {
            return 0;
        }

        $rules = $this->ruleModel->findByPool($poolId);
        if (!$rules) {
            return 0;
        }

        $bets = $this->betModel->findByMatch($poolId, $matchId);
        $updated = 0;

        foreach ($bets as $bet) {
            $points = $this->computePoints(
                (int)($bet['home_score_bet'] ?? $bet['home_score'] ?? 0),
                (int)($bet['away_score_bet'] ?? $bet['away_score'] ?? 0),
                (int)($match['home_score'] ?? 0),
                (int)($match['away_score'] ?? 0),
                $rules
            );
            $this->betModel->updatePoints((int)$bet['id'], $points);
            $updated++;
        }

        return $updated;
    }

    public function computePoints(
        int $betHome,
        int $betAway,
        int $actualHome,
        int $actualAway,
        array $rules
    ): int {
        // Exact score: exclusive — no other criteria apply
        if ($betHome === $actualHome && $betAway === $actualAway) {
            return (int)$rules['exact_score_points'];
        }

        // Correct draw: exclusive — no other criteria apply
        if ($betHome === $betAway && $actualHome === $actualAway) {
            return (int)$rules['draw_points'];
        }

        // Partial criteria (only reached when neither exact score nor correct draw)
        $points = 0;

        if ($betHome === $actualHome || $betAway === $actualAway) {
            $points += (int)$rules['one_team_score_points'];
        }

        if (($betHome - $betAway) === ($actualHome - $actualAway)) {
            $points += (int)$rules['goal_difference_points'];
        }

        return $points;
    }
}
