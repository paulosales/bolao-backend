<?php
declare(strict_types=1);

namespace App\Models;

class PoolInvitation extends BaseModel
{
    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT pi.*, p.name as pool_name, p.code as pool_code, u.name as inviter_name
             FROM pool_invitations pi
             JOIN pools p ON p.id = pi.pool_id
             JOIN users u ON u.id = pi.invited_by
             WHERE pi.token = ?'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function findByPool(int $poolId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pi.*, u.name as inviter_name
             FROM pool_invitations pi
             JOIN users u ON u.id = pi.invited_by
             WHERE pi.pool_id = ?
             ORDER BY pi.created_at DESC'
        );
        $stmt->execute([$poolId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO pool_invitations (pool_id, invited_by, email, token, sent_via, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['pool_id'],
            $data['invited_by'],
            $data['email'] ?? null,
            $data['token'],
            $data['sent_via'],
            $data['expires_at'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE pool_invitations SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public function cancel(int $id, int $invitedBy): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE pool_invitations SET status = 'cancelled' WHERE id = ? AND invited_by = ?"
        );
        return $stmt->execute([$id, $invitedBy]);
    }
}
