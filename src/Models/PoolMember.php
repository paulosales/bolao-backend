<?php
declare(strict_types=1);

namespace App\Models;

class PoolMember extends BaseModel
{
    public function find(int $poolId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM pool_members WHERE pool_id = ? AND user_id = ?'
        );
        $stmt->execute([$poolId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function findByPool(int $poolId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pm.*, u.name, u.email, u.phone
             FROM pool_members pm
             JOIN users u ON u.id = pm.user_id
             WHERE pm.pool_id = ?
             ORDER BY u.name'
        );
        $stmt->execute([$poolId]);
        return $stmt->fetchAll();
    }

    public function add(int $poolId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO pool_members (pool_id, user_id) VALUES (?, ?)'
        );
        return $stmt->execute([$poolId, $userId]);
    }

    public function remove(int $poolId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM pool_members WHERE pool_id = ? AND user_id = ?'
        );
        return $stmt->execute([$poolId, $userId]);
    }

    public function acceptRules(int $poolId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pool_members SET accepted_rules = 1 WHERE pool_id = ? AND user_id = ?'
        );
        return $stmt->execute([$poolId, $userId]);
    }

    public function count(int $poolId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pool_members WHERE pool_id = ?');
        $stmt->execute([$poolId]);
        return (int) $stmt->fetchColumn();
    }
}
