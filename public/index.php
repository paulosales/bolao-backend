<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Build DI container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(BASE_PATH . '/src/Dependencies.php');

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

// Add routing middleware (innermost — runs last so CORS can intercept OPTIONS first)
$app->addRoutingMiddleware();

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Add CORS middleware (outermost — runs first, short-circuits OPTIONS before routing)
$app->add(\App\Middleware\CorsMiddleware::class);

// Add error middleware (must be added after CORS so CORS headers appear on error responses too)
$errorMiddleware = $app->addErrorMiddleware(
    ($_ENV['APP_ENV'] ?? 'production') === 'development',
    true,
    true
);

// Register routes
(require BASE_PATH . '/src/Routes/api.php')($app);

$app->run();
