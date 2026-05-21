<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\GameMatch;
use App\Models\Pool;
use App\Models\PoolMatchSetting;
use App\Models\PoolMember;
use App\Models\PoolRule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;

class PoolController
{
    public function __construct(
        private Pool $poolModel,
        private PoolMember $memberModel,
        private PoolRule $ruleModel,
        private PoolMatchSetting $matchSettingModel,
        private GameMatch $matchModel
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $pools  = $this->poolModel->findByUserId($userId);
        return $this->json($response, $pools);
    }

    public function create(Request $request, Response $response): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $data   = (array) $request->getParsedBody();

        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            return $this->error($response, 'Nome do bolão é obrigatório.', 422);
        }

        $code = strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));

        $poolId = $this->poolModel->create([
            'name'            => $name,
            'code'            => $code,
            'creator_id'      => $userId,
            'quota_value'     => (float)($data['quota_value'] ?? 0),
            'estimated_prize' => (float)($data['estimated_prize'] ?? 0),
            'bet_visibility'  => $data['bet_visibility'] ?? 'before_start',
        ]);

        // Add creator as member
        $this->memberModel->add($poolId, $userId);
        $this->memberModel->acceptRules($poolId, $userId);

        // Create default rules
        $this->ruleModel->createDefault($poolId);

        $pool = $this->poolModel->findById($poolId);
        return $this->json($response, $pool, 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool) {
            return $this->error($response, 'Bolão não encontrado.', 404);
        }

        $member = $this->memberModel->find($poolId, $userId);
        if (!$member && (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $pool['member_count'] = $this->memberModel->count($poolId);
        $pool['rules']        = $this->ruleModel->findByPool($poolId);
        $pool['is_finished']  = $this->poolModel->isFinished($poolId);
        $pool['is_creator']   = (int)$pool['creator_id'] === $userId;
        $pool['accepted_rules'] = $member ? (bool)$member['accepted_rules'] : false;

        return $this->json($response, $pool);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $data = (array) $request->getParsedBody();
        $allowed = ['name', 'quota_value', 'estimated_prize', 'bet_visibility'];
        $updates = array_intersect_key($data, array_flip($allowed));

        $this->poolModel->update($poolId, $updates);
        $pool = $this->poolModel->findById($poolId);
        return $this->json($response, ['pool' => $pool]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $this->poolModel->delete($poolId);
        return $this->json($response, ['message' => 'Bolão removido.']);
    }

    public function members(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool) {
            return $this->error($response, 'Bolão não encontrado.', 404);
        }

        $member = $this->memberModel->find($poolId, $userId);
        if (!$member && (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $members = $this->memberModel->findByPool($poolId);
        return $this->json($response, $members);
    }

    public function removeMember(Request $request, Response $response, array $args): Response
    {
        $userId       = (int) $request->getAttribute('auth_user_id');
        $poolId       = (int) $args['id'];
        $targetUserId = (int) $args['userId'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        if ($targetUserId === $userId) {
            return $this->error($response, 'O criador não pode ser removido.', 400);
        }

        $this->memberModel->remove($poolId, $targetUserId);
        return $this->json($response, ['message' => 'Participante removido.']);
    }

    public function matchSettings(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $settings = $this->matchSettingModel->findByPool($poolId);
        return $this->json($response, $settings);
    }

    public function updateMatchSetting(Request $request, Response $response, array $args): Response
    {
        $userId  = (int) $request->getAttribute('auth_user_id');
        $poolId  = (int) $args['id'];
        $matchId = (int) $args['matchId'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $data    = (array) $request->getParsedBody();
        $openAt  = $data['betting_open_at'] ?? null;
        $closeAt = $data['betting_close_at'] ?? null;

        $this->matchSettingModel->upsert($poolId, $matchId, $openAt, $closeAt);
        return $this->json($response, ['message' => 'Configuração atualizada.']);
    }

    private function json(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function error(Response $response, string $message, int $status): Response
    {
        return $this->json($response, ['error' => $message], $status);
    }
}
