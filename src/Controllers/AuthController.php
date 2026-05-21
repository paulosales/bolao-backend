<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(
        private User $userModel,
        private JwtService $jwtService
    ) {
    }

    public function register(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

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
        if (strlen($password) < 6) {
            return $this->error($response, 'A senha deve ter pelo menos 6 caracteres.', 422);
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error($response, 'E-mail inválido.', 422);
        }

        $existing = $this->userModel->findByEmailOrPhone($email, $phone);
        if ($existing) {
            return $this->error($response, 'E-mail ou telefone já cadastrado.', 409);
        }

        $userId = $this->userModel->create([
            'name'          => $name,
            'email'         => $email ?: null,
            'phone'         => $phone ?: null,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $user  = $this->userModel->findById($userId);
        $token = $this->jwtService->encode($userId);

        return $this->json($response, ['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request, Response $response): Response
    {
        $data     = (array) $request->getParsedBody();
        $login    = trim(strtolower($data['login'] ?? ''));
        $password = $data['password'] ?? '';

        if (empty($login) || empty($password)) {
            return $this->error($response, 'Login e senha são obrigatórios.', 422);
        }

        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $this->userModel->findByEmail($login)
            : $this->userModel->findByPhone($login);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->error($response, 'Credenciais inválidas.', 401);
        }

        $token = $this->jwtService->encode((int)$user['id']);
        unset($user['password_hash']);

        return $this->json($response, ['user' => $user, 'token' => $token]);
    }

    public function me(Request $request, Response $response): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $user   = $this->userModel->findById($userId);
        if (!$user) {
            return $this->error($response, 'Usuário não encontrado.', 404);
        }
        return $this->json($response, $user);
    }

    public function updateProfile(Request $request, Response $response): Response
    {
        $userId = (int) $request->getAttribute('auth_user_id');
        $data   = (array) $request->getParsedBody();

        $updates = [];
        if (!empty($data['name'])) {
            $updates['name'] = trim($data['name']);
        }
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->error($response, 'E-mail inválido.', 422);
            }
            $updates['email'] = trim(strtolower($data['email']));
        }
        if (!empty($data['phone'])) {
            $updates['phone'] = trim($data['phone']);
        }
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return $this->error($response, 'A senha deve ter pelo menos 6 caracteres.', 422);
            }
            $updates['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!empty($updates)) {
            $this->userModel->update($userId, $updates);
        }

        $user = $this->userModel->findById($userId);
        return $this->json($response, $user);
    }

    public function refresh(Request $request, Response $response): Response
    {
        $data  = (array) $request->getParsedBody();
        $token = $data['token'] ?? '';
        $payload = $this->jwtService->decode($token);
        if (!$payload) {
            return $this->error($response, 'Token inválido.', 401);
        }
        $newToken = $this->jwtService->encode((int)$payload->sub);
        return $this->json($response, ['token' => $newToken]);
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
