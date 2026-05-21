<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\PoolRule;
use App\Services\ScoringService;
use PHPUnit\Framework\TestCase;

class ScoringServiceTest extends TestCase
{
    private ScoringService $service;
    private array $rules;

    protected function setUp(): void
    {
        $this->service = new ScoringService(
            $this->createMock(Bet::class),
            $this->createMock(GameMatch::class),
            $this->createMock(PoolRule::class)
        );

        $this->rules = [
            'exact_score_points'     => 10,
            'one_team_score_points'  => 5,
            'draw_points'            => 3,
            'goal_difference_points' => 2,
        ];
    }

    // ── Exact score ──────────────────────────────────────────────────────────

    public function testExactScoreReturnsMaxPoints(): void
    {
        $this->assertSame(10, $this->service->computePoints(2, 1, 2, 1, $this->rules));
    }

    public function testExactScoreZeroZero(): void
    {
        $this->assertSame(10, $this->service->computePoints(0, 0, 0, 0, $this->rules));
    }

    public function testExactScoreDrawBeatsCorrectDrawRule(): void
    {
        // Both a draw AND the exact same score → exact_score_points wins
        $this->assertSame(10, $this->service->computePoints(1, 1, 1, 1, $this->rules));
    }

    // ── Correct draw (non-exact) ──────────────────────────────────────────────

    public function testCorrectDrawReturnsDrawPoints(): void
    {
        // Bet is a draw, actual is also a draw, but different scores
        $this->assertSame(3, $this->service->computePoints(2, 2, 3, 3, $this->rules));
    }

    public function testCorrectDrawZeroZeroVsDifferentDraw(): void
    {
        $this->assertSame(3, $this->service->computePoints(0, 0, 1, 1, $this->rules));
    }

    public function testBetDrawButActualNotDrawScoresPartial(): void
    {
        // Bet is draw (1-1), actual is not draw (2-1); away score matches → one_team
        $this->assertSame(5, $this->service->computePoints(1, 1, 2, 1, $this->rules));
    }

    // ── One team score correct ────────────────────────────────────────────────

    public function testHomeScoreCorrect(): void
    {
        // Home team score matches (2==2), away does not (0 vs 1)
        $this->assertSame(5, $this->service->computePoints(2, 0, 2, 1, $this->rules));
    }

    public function testAwayScoreCorrect(): void
    {
        // Away score matches (1==1), home does not (0 vs 2)
        $this->assertSame(5, $this->service->computePoints(0, 1, 2, 1, $this->rules));
    }

    // ── Goal difference correct ───────────────────────────────────────────────

    public function testGoalDifferenceCorrect(): void
    {
        // Both diffs are +2 (2-0 and 3-1), no team score match
        $this->assertSame(2, $this->service->computePoints(2, 0, 3, 1, $this->rules));
    }

    public function testNegativeGoalDifferenceCorrect(): void
    {
        // Both diffs are -1 (1-2 and 0-1), no team score match
        $this->assertSame(2, $this->service->computePoints(1, 2, 0, 1, $this->rules));
    }

    // ── No points ────────────────────────────────────────────────────────────

    public function testNoMatchReturnsZero(): void
    {
        // home: 1≠0, away: 0≠3, diff: +1 vs -3 → 0
        $this->assertSame(0, $this->service->computePoints(1, 0, 0, 3, $this->rules));
    }

    public function testWrongSideWinnerReturnsZero(): void
    {
        // Bet: home wins (3-1), actual: away wins (1-3) → no match
        $this->assertSame(0, $this->service->computePoints(3, 1, 1, 3, $this->rules));
    }

    // ── Custom rules ─────────────────────────────────────────────────────────

    public function testCustomRulePointsAreApplied(): void
    {
        $custom = [
            'exact_score_points'     => 20,
            'one_team_score_points'  => 8,
            'draw_points'            => 6,
            'goal_difference_points' => 4,
        ];

        $this->assertSame(20, $this->service->computePoints(2, 1, 2, 1, $custom));
        $this->assertSame(6,  $this->service->computePoints(1, 1, 2, 2, $custom));
        $this->assertSame(8,  $this->service->computePoints(0, 1, 2, 1, $custom));
        $this->assertSame(4,  $this->service->computePoints(2, 0, 3, 1, $custom));
    }
}
