<?php
declare(strict_types=1);

namespace App\Models;

abstract class BaseModel
{
    public function __construct(protected \PDO $db)
    {
    }

    protected function jsonResponse(mixed $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
