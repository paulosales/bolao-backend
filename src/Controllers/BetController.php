<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\Pool;
use App\Models\PoolMatchSetting;
use App\Models\PoolMember;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BetController
{
    public function __construct(
        private Bet $betModel,
        private PoolMember $memberModel,
        private GameMatch $matchModel,
        private PoolMatchSetting $settingModel,
        private Pool $poolModel
    ) {
    }

    public function myBets(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $member = $this->memberModel->find($poolId, $userId);
        if (!$member) {
            return $this->error($response, 'Você não é participante deste bolão.', 403);
        }

        $bets = $this->betModel->findByPoolAndUser($poolId, $userId);
        return $this->json($response, $bets);
    }

    public function index(Request $request, Response $response, array $args): Response
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

        $showAll = $pool['bet_visibility'] === 'before_start'
            || (int)$pool['creator_id'] === $userId;

        $bets = $this->betModel->findByPool($poolId, $showAll);

        // If visibility is "during_match", hide other users' bets for scheduled matches
        if ($pool['bet_visibility'] === 'during_match') {
            $bets = array_filter($bets, function ($bet) use ($userId) {
                return (int)$bet['user_id'] === $userId || $bet['match_status'] !== 'scheduled';
            });
            $bets = array_values($bets);
        }

        return $this->json($response, $bets);
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $member = $this->memberModel->find($poolId, $userId);
        if (!$member) {
            return $this->error($response, 'Você não é participante deste bolão.', 403);
        }
        if (!(bool)$member['accepted_rules']) {
            return $this->error($response, 'Você deve aceitar as regras antes de apostar.', 403);
        }

        $data    = (array) $request->getParsedBody();
        $matchId = (int)($data['match_id'] ?? 0);

        if (!$matchId) {
            return $this->error($response, 'Jogo inválido.', 422);
        }

        $match = $this->matchModel->findById($matchId);
        if (!$match) {
            return $this->error($response, 'Jogo não encontrado.', 404);
        }
        if ($match['status'] !== 'scheduled') {
            return $this->error($response, 'As apostas para este jogo já foram encerradas.', 400);
        }

        // Check pool betting window
        $setting = $this->settingModel->findByPoolAndMatch($poolId, $matchId);
        $now     = time();

        if ($setting) {
            if ($setting['betting_close_at'] && strtotime($setting['betting_close_at']) < $now) {
                return $this->error($response, 'O período de apostas para este jogo foi encerrado.', 400);
            }
            if ($setting['betting_open_at'] && strtotime($setting['betting_open_at']) > $now) {
                return $this->error($response, 'O período de apostas para este jogo ainda não começou.', 400);
            }
        }

        // Default: close bets at match start
        if (!$setting && strtotime($match['match_date']) <= $now) {
            return $this->error($response, 'As apostas para este jogo já foram encerradas.', 400);
        }

        // Accept both `home_score`/`away_score` and frontend `home_score_bet`/`away_score_bet`
        if ((!isset($data['home_score']) && !isset($data['home_score_bet'])) || (!isset($data['away_score']) && !isset($data['away_score_bet']))) {
            return $this->error($response, 'Placar é obrigatório.', 422);
        }

        $homeScore = max(0, (int)($data['home_score'] ?? $data['home_score_bet'] ?? 0));
        $awayScore = max(0, (int)($data['away_score'] ?? $data['away_score_bet'] ?? 0));

        $this->betModel->upsert($poolId, $userId, $matchId, $homeScore, $awayScore);

        // Return the inserted/updated bet so frontend can update state immediately
        $bet = $this->betModel->findOne($poolId, $userId, $matchId);
        if (!$bet) {
            return $this->json($response, ['message' => 'Aposta registrada com sucesso.'], 201);
        }
        return $this->json($response, $bet, 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        return $this->store($request, $response, $args);
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
