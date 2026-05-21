<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\BetController;
use App\Controllers\InvitationController;
use App\Controllers\MatchController;
use App\Controllers\PoolController;
use App\Controllers\RankingController;
use App\Controllers\RulesController;
use App\Middleware\AuthMiddleware;
use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\Pool;
use App\Models\PoolInvitation;
use App\Models\PoolMatchSetting;
use App\Models\PoolMember;
use App\Models\PoolRule;
use App\Models\Team;
use App\Models\User;
use App\Services\EmailService;
use App\Services\JwtService;
use App\Services\ScoringService;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    // Database PDO
    PDO::class => function () {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $name = $_ENV['DB_NAME'] ?? 'bolao_copa';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    },

    // Logger
    LoggerInterface::class => function () {
        $logger = new Logger('bolao');
        $logger->pushHandler(new StreamHandler(BASE_PATH . '/logs/app.log', Logger::DEBUG));
        return $logger;
    },

    // Services
    JwtService::class => function () {
        return new JwtService($_ENV['JWT_SECRET'] ?? 'default-secret');
    },

    EmailService::class => function (ContainerInterface $c) {
        return new EmailService(
            $_ENV['MAIL_HOST'] ?? '',
            (int)($_ENV['MAIL_PORT'] ?? 587),
            $_ENV['MAIL_USER'] ?? '',
            $_ENV['MAIL_PASS'] ?? '',
            $_ENV['MAIL_FROM'] ?? 'noreply@bolaodacopa.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Bolão da Copa 2026',
            $c->get(LoggerInterface::class)
        );
    },

    ScoringService::class => function (ContainerInterface $c) {
        return new ScoringService(
            $c->get(Bet::class),
            $c->get(GameMatch::class),
            $c->get(PoolRule::class)
        );
    },

    // Models
    User::class => fn(ContainerInterface $c) => new User($c->get(PDO::class)),
    Pool::class => fn(ContainerInterface $c) => new Pool($c->get(PDO::class)),
    GameMatch::class => fn(ContainerInterface $c) => new GameMatch($c->get(PDO::class)),
    Bet::class => fn(ContainerInterface $c) => new Bet($c->get(PDO::class)),
    Team::class => fn(ContainerInterface $c) => new Team($c->get(PDO::class)),
    PoolMember::class => fn(ContainerInterface $c) => new PoolMember($c->get(PDO::class)),
    PoolInvitation::class => fn(ContainerInterface $c) => new PoolInvitation($c->get(PDO::class)),
    PoolRule::class => fn(ContainerInterface $c) => new PoolRule($c->get(PDO::class)),
    PoolMatchSetting::class => fn(ContainerInterface $c) => new PoolMatchSetting($c->get(PDO::class)),

    // Middleware
    AuthMiddleware::class => fn(ContainerInterface $c) => new AuthMiddleware($c->get(JwtService::class)),

    // Controllers
    AuthController::class => fn(ContainerInterface $c) => new AuthController(
        $c->get(User::class),
        $c->get(JwtService::class)
    ),
    PoolController::class => fn(ContainerInterface $c) => new PoolController(
        $c->get(Pool::class),
        $c->get(PoolMember::class),
        $c->get(PoolRule::class),
        $c->get(PoolMatchSetting::class),
        $c->get(GameMatch::class)
    ),
    InvitationController::class => fn(ContainerInterface $c) => new InvitationController(
        $c->get(PoolInvitation::class),
        $c->get(Pool::class),
        $c->get(PoolMember::class),
        $c->get(PoolRule::class),
        $c->get(User::class),
        $c->get(EmailService::class)
    ),
    BetController::class => fn(ContainerInterface $c) => new BetController(
        $c->get(Bet::class),
        $c->get(PoolMember::class),
        $c->get(GameMatch::class),
        $c->get(PoolMatchSetting::class),
        $c->get(Pool::class)
    ),
    MatchController::class => fn(ContainerInterface $c) => new MatchController(
        $c->get(GameMatch::class),
        $c->get(Team::class),
        $c->get(ScoringService::class)
    ),
    RulesController::class => fn(ContainerInterface $c) => new RulesController(
        $c->get(PoolRule::class),
        $c->get(Pool::class),
        $c->get(PoolMember::class)
    ),
    RankingController::class => fn(ContainerInterface $c) => new RankingController(
        $c->get(Pool::class),
        $c->get(PoolMember::class),
        $c->get(Bet::class),
        $c->get(User::class),
        $c->get(PoolRule::class),
        $c->get(ScoringService::class)
    ),
];
