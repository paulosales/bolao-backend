<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\GameMatch;
use App\Models\Team;
use App\Services\ScoringService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MatchController
{
    public function __construct(
        private GameMatch $matchModel,
        private Team $teamModel,
        private ScoringService $scoringService
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        $matches = $this->matchModel->findAll();

        // map flat row fields into nested team objects expected by frontend
        $matches = array_map(function ($m) {
            $m['home_team'] = [
                'id' => (int)$m['home_team_id'],
                'name' => $m['home_team_name'] ?? null,
                'short_name' => $m['home_team_short'] ?? null,
                'flag_url' => $m['home_flag'] ?? null,
            ];
            $m['away_team'] = [
                'id' => (int)$m['away_team_id'],
                'name' => $m['away_team_name'] ?? null,
                'short_name' => $m['away_team_short'] ?? null,
                'flag_url' => $m['away_flag'] ?? null,
            ];
            return $m;
        }, $matches);

        return $this->json($response, $matches);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $match = $this->matchModel->findById((int)$args['id']);
        if (!$match) {
            return $this->error($response, 'Jogo não encontrado.', 404);
        }
            $match['home_team'] = [
                'id' => (int)$match['home_team_id'],
                'name' => $match['home_team_name'] ?? null,
                'short_name' => $match['home_team_short'] ?? null,
                'flag_url' => $match['home_flag'] ?? null,
            ];
            $match['away_team'] = [
                'id' => (int)$match['away_team_id'],
                'name' => $match['away_team_name'] ?? null,
                'short_name' => $match['away_team_short'] ?? null,
                'flag_url' => $match['away_flag'] ?? null,
            ];
            return $this->json($response, $match);
    }

    public function teams(Request $request, Response $response): Response
    {
        $teams = $this->teamModel->findAll();
            return $this->json($response, $teams);
    }

    public function updateResult(Request $request, Response $response, array $args): Response
    {
        $matchId = (int) $args['id'];
        $data    = (array) $request->getParsedBody();

        $homeScore = isset($data['home_score']) ? (int)$data['home_score'] : null;
        $awayScore = isset($data['away_score']) ? (int)$data['away_score'] : null;
        // Default to 'finished' when scores are provided, otherwise keep current status
        $hasScores = $homeScore !== null && $awayScore !== null;
        $status    = $data['status'] ?? ($hasScores ? 'finished' : null);
        $homeScore = $homeScore ?? 0;
        $awayScore = $awayScore ?? 0;

        $match = $this->matchModel->findById($matchId);
        if (!$match) {
            return $this->error($response, 'Jogo não encontrado.', 404);
        }

        // If status not provided and no scores given either, keep existing status
        if ($status === null) {
            $status = $match['status'];
        }

        if (!in_array($status, ['scheduled', 'live', 'finished'])) {
            return $this->error($response, 'Status inválido.', 422);
        }

        $this->matchModel->updateResult($matchId, $homeScore, $awayScore, $status);
        return $this->json($response, ['message' => 'Resultado atualizado.']);
    }

    public function calculateScores(Request $request, Response $response, array $args): Response
    {
        $matchId = (int) $args['id'];
        $data    = (array) $request->getParsedBody();
        $poolId  = (int)($data['pool_id'] ?? 0);

        if (!$poolId) {
            return $this->error($response, 'pool_id é obrigatório.', 422);
        }

        $updated = $this->scoringService->calculateForMatch($poolId, $matchId);
        return $this->json($response, ['message' => "Pontuação calculada para {$updated} apostas."]);
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
