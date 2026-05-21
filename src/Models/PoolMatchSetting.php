<?php
declare(strict_types=1);

namespace App\Models;

class PoolMatchSetting extends BaseModel
{
    public function findByPoolAndMatch(int $poolId, int $matchId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM pool_match_settings WHERE pool_id = ? AND match_id = ?'
        );
        $stmt->execute([$poolId, $matchId]);
        return $stmt->fetch() ?: null;
    }

    public function findByPool(int $poolId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pms.*, m.match_date,
             ht.name as home_team_name, at.name as away_team_name
             FROM pool_match_settings pms
             JOIN matches m ON m.id = pms.match_id
             JOIN teams ht ON ht.id = m.home_team_id
             JOIN teams at ON at.id = m.away_team_id
             WHERE pms.pool_id = ?
             ORDER BY m.match_date'
        );
        $stmt->execute([$poolId]);
        return $stmt->fetchAll();
    }

    public function upsert(int $poolId, int $matchId, ?string $openAt, ?string $closeAt): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pool_match_settings (pool_id, match_id, betting_open_at, betting_close_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE betting_open_at = VALUES(betting_open_at), betting_close_at = VALUES(betting_close_at)'
        );
        return $stmt->execute([$poolId, $matchId, $openAt, $closeAt]);
    }
}
