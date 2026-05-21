<?php
declare(strict_types=1);

namespace App\Models;

class GameMatch extends BaseModel
{
    public function findAll(): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*,
             ht.name as home_team_name, ht.short_name as home_team_short, ht.flag_url as home_flag,
             at.name as away_team_name, at.short_name as away_team_short, at.flag_url as away_flag
             FROM matches m
             JOIN teams ht ON ht.id = m.home_team_id
             JOIN teams at ON at.id = m.away_team_id
             ORDER BY m.match_date ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT m.*,
             ht.name as home_team_name, ht.short_name as home_team_short, ht.flag_url as home_flag,
             at.name as away_team_name, at.short_name as away_team_short, at.flag_url as away_flag
             FROM matches m
             JOIN teams ht ON ht.id = m.home_team_id
             JOIN teams at ON at.id = m.away_team_id
             WHERE m.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateResult(int $id, int $homeScore, int $awayScore, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE matches SET home_score = ?, away_score = ?, status = ? WHERE id = ?'
        );
        return $stmt->execute([$homeScore, $awayScore, $status, $id]);
    }
}
