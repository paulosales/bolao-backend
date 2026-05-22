<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Services\JwtService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private JwtService $jwtService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // Apache's various modes put the header in different places.
        // Try every known fallback in order of specificity.
        if (empty($authHeader)) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? '';
        }
        // Some FastCGI setups expose it via getenv
        if (empty($authHeader)) {
            $authHeader = getenv('HTTP_AUTHORIZATION') ?: (getenv('REDIRECT_HTTP_AUTHORIZATION') ?: '');
        }
        // mod_php exposes apache_request_headers()
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Token de autenticação não fornecido.');
        }

        $token = substr($authHeader, 7);

        $payload = $this->jwtService->decode($token);
        if ($payload === null) {
            return $this->unauthorizedResponse('Token inválido ou expirado.');
        }

        $request = $request->withAttribute('auth_user_id', $payload->sub);
        $request = $request->withAttribute('auth_payload', $payload);

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = new Response();
        $body = json_encode(['error' => $message]);
        $response->getBody()->write($body);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
