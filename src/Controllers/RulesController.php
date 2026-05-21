<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pool;
use App\Models\PoolMember;
use App\Models\PoolRule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RulesController
{
    public function __construct(
        private PoolRule $ruleModel,
        private Pool $poolModel,
        private PoolMember $memberModel
    ) {
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $poolId = (int) $args['id'];
        $rules  = $this->ruleModel->findByPool($poolId);
        if (!$rules) {
            return $this->error($response, 'Regras não encontradas.', 404);
        }
        return $this->json($response, $rules);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Apenas o criador do bolão pode editar as regras.', 403);
        }

        $data = (array) $request->getParsedBody();
        $required = ['exact_score_points', 'one_team_score_points', 'draw_points', 'goal_difference_points'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || !is_numeric($data[$field]) || (int)$data[$field] < 0) {
                return $this->error($response, "Campo {$field} inválido.", 422);
            }
        }

        $this->ruleModel->update($poolId, [
            'exact_score_points'      => (int)$data['exact_score_points'],
            'one_team_score_points'   => (int)$data['one_team_score_points'],
            'draw_points'             => (int)$data['draw_points'],
            'goal_difference_points'  => (int)$data['goal_difference_points'],
            'description'             => isset($data['description']) && $data['description'] !== '' ? (string)$data['description'] : null,
        ]);

        $rules = $this->ruleModel->findByPool($poolId);
        return $this->json($response, $rules);
    }

    public function accept(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $member = $this->memberModel->find($poolId, $userId);
        if (!$member) {
            return $this->error($response, 'Você não é participante deste bolão.', 403);
        }

        $this->memberModel->acceptRules($poolId, $userId);
        return $this->json($response, ['message' => 'Regras aceitas.']);
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
