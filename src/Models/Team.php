<?php
declare(strict_types=1);

namespace App\Models;

class Team extends BaseModel
{
    public function findAll(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams ORDER BY group_name, name');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
