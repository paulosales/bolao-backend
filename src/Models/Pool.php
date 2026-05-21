<?php
declare(strict_types=1);

namespace App\Models;

class Pool extends BaseModel
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.name as creator_name FROM pools p
             JOIN users u ON u.id = p.creator_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pools WHERE code = ?');
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT p.*, u.name as creator_name,
                (SELECT COUNT(*) FROM pool_members pm WHERE pm.pool_id = p.id) as member_count
             FROM pools p
             JOIN users u ON u.id = p.creator_id
             LEFT JOIN pool_members pm2 ON pm2.pool_id = p.id AND pm2.user_id = ?
             WHERE p.creator_id = ? OR pm2.user_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pools (name, code, creator_id, quota_value, estimated_prize, bet_visibility)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['code'],
            $data['creator_id'],
            $data['quota_value'] ?? 0,
            $data['estimated_prize'] ?? 0,
            $data['bet_visibility'] ?? 'before_start',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach (['name', 'quota_value', 'estimated_prize', 'bet_visibility', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE pools SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return $this->db->prepare($sql)->execute($values);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pools WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function isFinished(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total,
             SUM(CASE WHEN m.status = 'finished' THEN 1 ELSE 0 END) as finished
             FROM pool_match_settings pms
             JOIN matches m ON m.id = pms.match_id
             WHERE pms.pool_id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row && $row['total'] > 0 && $row['total'] == $row['finished'];
    }
}
