<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Pool;
use App\Models\PoolInvitation;
use App\Models\PoolMember;
use App\Models\PoolRule;
use App\Models\User;
use App\Services\EmailService;
use App\Services\SmsService;
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
        private EmailService $emailService,
        private SmsService $smsService
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
        $phone   = trim($data['phone'] ?? '');
        $sentVia = $data['sent_via'] ?? 'email';

        if (empty($email) && empty($phone)) {
            return $this->error($response, 'E-mail ou telefone é obrigatório.', 422);
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error($response, 'E-mail inválido.', 422);
        }

        $token     = Uuid::uuid4()->toString();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $appUrl    = $_ENV['APP_URL'] ?? 'http://localhost:5173';
        $link      = "{$appUrl}/convite/{$token}";

        $this->invitationModel->create([
            'pool_id'    => $poolId,
            'invited_by' => $userId,
            'email'      => $email ?: null,
            'phone'      => $phone ?: null,
            'token'      => $token,
            'sent_via'   => $sentVia,
            'expires_at' => $expiresAt,
        ]);

        $inviterName = $this->userModel->findById($userId)['name'] ?? '';

        if ($sentVia === 'email' && !empty($email)) {
            $toName = explode('@', $email)[0];
            $this->emailService->sendInvite($email, $toName, $pool['name'], $link);
        } elseif ($sentVia === 'sms' && !empty($phone)) {
            $this->smsService->sendInvite($phone, $pool['name'], $link);
        }

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
        $email = trim(strtolower($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');

        $token     = Uuid::uuid4()->toString();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        $appUrl    = $_ENV['APP_URL'] ?? 'http://localhost:5173';
        $link      = "{$appUrl}/convite/{$token}";

        $this->invitationModel->create([
            'pool_id'    => $poolId,
            'invited_by' => $userId,
            'email'      => $email ?: null,
            'phone'      => $phone ?: null,
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
        $invitation['rules'] = $rules;

        return $this->json($response, ['invitation' => $invitation]);
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
        $phone    = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($name) || empty($password)) {
            return $this->error($response, 'Nome e senha são obrigatórios.', 422);
        }
        if (empty($email) && empty($phone)) {
            return $this->error($response, 'E-mail ou telefone é obrigatório.', 422);
        }

        // Find or create user
        $user = null;
        if (!empty($email)) {
            $user = $this->userModel->findByEmail($email);
        }
        if (!$user && !empty($phone)) {
            $user = $this->userModel->findByPhone($phone);
        }

        if (!$user) {
            $userId = $this->userModel->create([
                'name'          => $name,
                'email'         => $email ?: null,
                'phone'         => $phone ?: null,
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
