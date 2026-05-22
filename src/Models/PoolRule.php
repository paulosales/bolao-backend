<?php
declare(strict_types=1);

namespace App\Models;

class PoolRule extends BaseModel
{
    public function findByPool(int $poolId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pool_rules WHERE pool_id = ?');
        $stmt->execute([$poolId]);
        return $stmt->fetch() ?: null;
    }

    public function createDefault(int $poolId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pool_rules (pool_id, exact_score_points, one_team_score_points, draw_points, goal_difference_points, winner_points)
             VALUES (?, 10, 5, 3, 2, 3)'
        );
        $stmt->execute([$poolId]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $poolId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pool_rules SET
             exact_score_points = ?,
             one_team_score_points = ?,
             draw_points = ?,
             goal_difference_points = ?,
             winner_points = ?,
             description = ?
             WHERE pool_id = ?'
        );
        return $stmt->execute([
            $data['exact_score_points'],
            $data['one_team_score_points'],
            $data['draw_points'],
            $data['goal_difference_points'],
            $data['winner_points'],
            $data['description'] ?? null,
            $poolId,
        ]);
    }
}
