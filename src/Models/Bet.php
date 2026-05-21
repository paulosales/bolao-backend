<?php
declare(strict_types=1);

namespace App\Models;

class Bet extends BaseModel
{
    public function findByPoolAndUser(int $poolId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, b.home_score AS home_score_bet, b.away_score AS away_score_bet,
             m.match_date, m.home_score as match_home_score, m.away_score as match_away_score, m.status as match_status,
             ht.name as home_team_name, ht.flag_url as home_flag,
             at.name as away_team_name, at.flag_url as away_flag
             FROM bets b
             JOIN matches m ON m.id = b.match_id
             JOIN teams ht ON ht.id = m.home_team_id
             JOIN teams at ON at.id = m.away_team_id
             WHERE b.pool_id = ? AND b.user_id = ?
             ORDER BY m.match_date"
        );
        $stmt->execute([$poolId, $userId]);
        return $stmt->fetchAll();
    }

    public function findByPool(int $poolId, bool $includePreMatch = true): array
    {
        $visibilityCond = $includePreMatch
            ? ''
            : "AND (m.status != 'scheduled' OR b.user_id = ?)";

        $stmt = $this->db->prepare(
            "SELECT b.*, b.home_score AS home_score_bet, b.away_score AS away_score_bet, u.name as user_name,
             m.match_date, m.status as match_status,
             ht.name as home_team_name, ht.flag_url as home_flag,
             at.name as away_team_name, at.flag_url as away_flag
             FROM bets b
             JOIN users u ON u.id = b.user_id
             JOIN matches m ON m.id = b.match_id
             JOIN teams ht ON ht.id = m.home_team_id
             JOIN teams at ON at.id = m.away_team_id
             WHERE b.pool_id = ?
             ORDER BY u.name, m.match_date"
        );
        $stmt->execute([$poolId]);
        return $stmt->fetchAll();
    }

    public function findOne(int $poolId, int $userId, int $matchId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, b.home_score AS home_score_bet, b.away_score AS away_score_bet FROM bets b WHERE pool_id = ? AND user_id = ? AND match_id = ?'
        );
        $stmt->execute([$poolId, $userId, $matchId]);
        return $stmt->fetch() ?: null;
    }

    public function upsert(int $poolId, int $userId, int $matchId, int $homeScore, int $awayScore): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bets (pool_id, user_id, match_id, home_score, away_score)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE home_score = VALUES(home_score), away_score = VALUES(away_score)'
        );
        return $stmt->execute([$poolId, $userId, $matchId, $homeScore, $awayScore]);
    }

    public function updatePoints(int $id, int $points): bool
    {
        $stmt = $this->db->prepare('UPDATE bets SET points_earned = ? WHERE id = ?');
        return $stmt->execute([$points, $id]);
    }

    public function findByMatch(int $poolId, int $matchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, b.home_score AS home_score_bet, b.away_score AS away_score_bet, u.name as user_name FROM bets b
             JOIN users u ON u.id = b.user_id
             WHERE b.pool_id = ? AND b.match_id = ?'
        );
        $stmt->execute([$poolId, $matchId]);
        return $stmt->fetchAll();
    }
}
