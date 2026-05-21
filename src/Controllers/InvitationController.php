<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pool;
use App\Models\PoolInvitation;
use App\Models\PoolMember;
use App\Models\PoolRule;
use App\Models\User;
use App\Services\EmailService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;

class InvitationController
{
    public function __construct(
        private PoolInvitation $invitationModel,
        private Pool $poolModel,
        private PoolMember $memberModel,
        private PoolRule $ruleModel,
        private User $userModel,
        private EmailService $emailService
    ) {
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $invitations = $this->invitationModel->findByPool($poolId);
        return $this->json($response, ['invitations' => $invitations]);
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $data    = (array) $request->getParsedBody();
        $email   = trim(strtolower($data['email'] ?? ''));

        if (empty($email)) {
            return $this->error($response, 'E-mail é obrigatório.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error($response, 'E-mail inválido.', 422);
        }

        $token     = Uuid::uuid4()->toString();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $appUrl    = $_ENV['APP_URL'] ?? 'http://localhost:5173';
        $link      = "{$appUrl}/convite/{$token}";

        $this->invitationModel->create([
            'pool_id'    => $poolId,
            'invited_by' => $userId,
            'email'      => $email,
            'token'      => $token,
            'sent_via'   => 'email',
            'expires_at' => $expiresAt,
        ]);

        $toName = explode('@', $email)[0];
        $this->emailService->sendInvite($email, $toName, $pool['name'], $link);

        return $this->json($response, ['link' => $link, 'token' => $token], 201);
    }

    public function generateLink(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $poolId = (int) $args['id'];

        $pool = $this->poolModel->findById($poolId);
        if (!$pool || (int)$pool['creator_id'] !== $userId) {
            return $this->error($response, 'Acesso negado.', 403);
        }

        $data  = (array) $request->getParsedBody();

        $token     = Uuid::uuid4()->toString();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $appUrl    = $_ENV['APP_URL'] ?? 'http://localhost:5173';
        $link      = "{$appUrl}/convite/{$token}";

        $this->invitationModel->create([
            'pool_id'    => $poolId,
            'invited_by' => $userId,
            'email'      => null,
            'token'      => $token,
            'sent_via'   => 'link',
            'expires_at' => $expiresAt,
        ]);

        return $this->json($response, ['link' => $link, 'token' => $token]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $token      = $args['token'];
        $invitation = $this->invitationModel->findByToken($token);

        if (!$invitation) {
            return $this->error($response, 'Convite não encontrado.', 404);
        }
        if ($invitation['status'] !== 'pending') {
            return $this->error($response, 'Este convite já foi utilizado ou cancelado.', 410);
        }
        if ($invitation['expires_at'] && strtotime($invitation['expires_at']) < time()) {
            return $this->error($response, 'Este convite expirou.', 410);
        }

        $rules = $this->ruleModel->findByPool((int)$invitation['pool_id']);

        return $this->json($response, [
            'pool' => [
                'id'   => (string) $invitation['pool_id'],
                'name' => $invitation['pool_name'],
                'code' => $invitation['pool_code'],
            ],
            'rules' => [
                'exact_score_points'     => (int) ($rules['exact_score_points'] ?? 0),
                'one_team_score_points'  => (int) ($rules['one_team_score_points'] ?? 0),
                'draw_points'            => (int) ($rules['draw_points'] ?? 0),
                'goal_difference_points' => (int) ($rules['goal_difference_points'] ?? 0),
                'description'            => $rules['description'] ?? null,
            ],
            'inviter_name'  => $invitation['inviter_name'],
            'invitee_email' => $invitation['email'],
        ]);
    }

    public function accept(Request $request, Response $response, array $args): Response
    {
        $token      = $args['token'];
        $invitation = $this->invitationModel->findByToken($token);

        if (!$invitation) {
            return $this->error($response, 'Convite não encontrado.', 404);
        }
        if ($invitation['status'] !== 'pending') {
            return $this->error($response, 'Este convite já foi utilizado ou cancelado.', 410);
        }
        if ($invitation['expires_at'] && strtotime($invitation['expires_at']) < time()) {
            return $this->error($response, 'Este convite expirou.', 410);
        }

        $data     = (array) $request->getParsedBody();
        $name     = trim($data['name'] ?? '');
        $email    = trim(strtolower($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (empty($name) || empty($password)) {
            return $this->error($response, 'Nome e senha são obrigatórios.', 422);
        }
        if (empty($email)) {
            return $this->error($response, 'E-mail é obrigatório.', 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error($response, 'E-mail inválido.', 422);
        }

        // Find or create user
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            if (!password_verify($password, $user['password_hash'])) {
                return $this->error($response, 'Senha incorreta.', 401);
            }
        } else {
            $userId = $this->userModel->create([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ]);
            $user = $this->userModel->findById($userId);
        }

        $poolId = (int)$invitation['pool_id'];
        $userId = (int)$user['id'];

        // Add to pool
        $this->memberModel->add($poolId, $userId);

        // Mark invitation as accepted
        $this->invitationModel->updateStatus((int)$invitation['id'], 'accepted');

        $jwtService = new \App\Services\JwtService($_ENV['JWT_SECRET'] ?? 'default-secret');
        $token = $jwtService->encode($userId);

        unset($user['password_hash']);
        return $this->json($response, ['user' => $user, 'token' => $token, 'pool_id' => $poolId]);
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        $userId     = (int) $request->getAttribute('auth_user_id');
        $token      = $args['token'];
        $invitation = $this->invitationModel->findByToken($token);

        if (!$invitation) {
            return $this->error($response, 'Convite não encontrado.', 404);
        }
        if ($invitation['status'] !== 'pending') {
            return $this->error($response, 'Este convite já foi utilizado ou cancelado.', 410);
        }
        if ($invitation['expires_at'] && strtotime($invitation['expires_at']) < time()) {
            return $this->error($response, 'Este convite expirou.', 410);
        }

        $poolId = (int) $invitation['pool_id'];

        $this->memberModel->add($poolId, $userId);
        $this->invitationModel->updateStatus((int) $invitation['id'], 'accepted');

        return $this->json($response, ['pool_id' => $poolId]);
    }

    public function cancel(Request $request, Response $response, array $args): Response
    {
        $userId       = (int) $request->getAttribute('auth_user_id');
        $invitationId = (int) $args['invitationId'];

        $this->invitationModel->cancel($invitationId, $userId);
        return $this->json($response, ['message' => 'Convite cancelado.']);
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
