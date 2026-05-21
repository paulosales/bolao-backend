<?php
declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmailOrPhone(string $email, string $phone): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? OR phone = ?');
        $stmt->execute([$email, $phone]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['password_hash'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $values[] = $data['phone'];
        }
        if (isset($data['password_hash'])) {
            $fields[] = 'password_hash = ?';
            $values[] = $data['password_hash'];
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
}
