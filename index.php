<?php

require_once 'vendor/autoload.php';

use CryptoApp\Services\DatabaseInitializer;

new DatabaseInitializer('storage/database.sqlite');

use CryptoApp\Controllers\TransactionController;
use CryptoApp\Controllers\WalletController;
use CryptoApp\Controllers\OptionController;
use CryptoApp\Controllers\UserController;
use CryptoApp\Models\User;
use CryptoApp\Models\Wallet;
use CryptoApp\Repositories\Currency\CoinMarketApiCurrencyRepository;
use CryptoApp\Repositories\Currency\CurrencyRepository;
use CryptoApp\Repositories\Transaction\SqliteTransactionRepository;
use CryptoApp\Repositories\Transaction\TransactionRepository;
use CryptoApp\Repositories\User\SqliteUserRepository;
use CryptoApp\Repositories\Wallet\SqliteWalletRepository;
use CryptoApp\Repositories\Wallet\WalletRepository;
use CryptoApp\Services\BuyCurrencyService;
use CryptoApp\Services\SellCurrencyService;
use CryptoApp\Services\UserService;
use CryptoApp\Services\WalletService;
use DI\Container;
use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$container = new Container();

$container->set(CurrencyRepository::class, function(Container $container) {
    return new CoinMarketApiCurrencyRepository($_ENV['APIKEY']);
});

$container->set(TransactionRepository::class, function() {
    return new SqliteTransactionRepository();
});

$container->set(WalletRepository::class, function() {
    return new SqliteWalletRepository();
});

$container->set(WalletService::class, function(Container $container) {
    $session = $container->get(SessionInterface::class);
    $userId = getUserIdFromSession($session);

    if (!$userId) {

        throw new \Exception('User not authenticated.');
    }

    $wallet = new Wallet($userId, 1000.0);

    return new WalletService(
        $wallet,
        $container->get(WalletRepository::class),
        $container->get(TransactionRepository::class),
        $container->get(CurrencyRepository::class),
        $userId
    );
});

$container->set(SellCurrencyService::class, function(Container $container) {
    return new SellCurrencyService(
        $container->get(CurrencyRepository::class),
        $container->get(TransactionRepository::class),
        $container->get(WalletRepository::class),
        $container->get(SqliteUserRepository::class),
        $container->get(WalletService::class),
        getUserIdFromSession($container->get(SessionInterface::class))
    );
});

$container->set(BuyCurrencyService::class, function(Container $container) {
    return new BuyCurrencyService(
        $container->get(CurrencyRepository::class),
        $container->get(TransactionRepository::class),
        $container->get(WalletRepository::class),
        $container->get(SqliteUserRepository::class),
        getUserIdFromSession($container->get(SessionInterface::class))
    );
});

$container->set(TransactionController::class, function(Container $container) {
    return new TransactionController(
        $container->get(BuyCurrencyService::class),
        $container->get(SellCurrencyService::class),
        $container->get(TransactionRepository::class),
        $container->get(SessionInterface::class),
        $container->get(CurrencyRepository::class),
        $container->get(WalletService::class)
    );
});

$container->set(UserController::class, function(Container $container) {
    return new UserController(
        $container->get(UserService::class),
        $container->get(WalletRepository::class),
        $container->get(TransactionRepository::class),
        $container->get(CurrencyRepository::class),
        $container->get(SessionInterface::class)
    );
});

$container->set(WalletController::class, function(Container $container) {
    return new WalletController(
        $container->get(WalletService::class),
        $container->get(SessionInterface::class)
    );
});

$container->set(OptionController::class, function(Container $container) {
    return new OptionController(
        $container->get(SessionInterface::class)
    );
});


$container->set('Symfony\Component\HttpFoundation\Session\SessionInterface', function () {
    $session = new Session();
    $session->start();
    return $session;
});

$container->set(SqliteUserRepository::class, function() {
    return new SqliteUserRepository();
});

$container->set(UserService::class, function(Container $container) {
    return new UserService(
        $container->get(SqliteUserRepository::class),
    );
});

$container->set('routes', require __DIR__ . '/app/routes.php');

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

$dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) use ($container) {
    foreach ($container->get('routes') as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

function getUserIdFromSession(SessionInterface $session): ?string {
    if ($session->has('user')) {
        /** @var User $user */
        $user = $session->get('user');
        return $user->getId();
    }
    return null;
}

function renderTemplate(Environment $twig, array $result): void {
    echo $twig->render($result['template'], $result['data']);
}

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$controllerName, $methodName] = explode('@', $handler, 2);

        $controllerClass = "\\CryptoApp\\Controllers\\{$controllerName}";
        $controllerInstance = $container->get($controllerClass);

        $request = Request::createFromGlobals();

        if (method_exists($controllerInstance, $methodName)) {

            if ($controllerName === 'CurrencyController' && $methodName === 'searchCurrency') {
                $result = $controllerInstance->{$methodName}($request, $vars);
            } elseif ($controllerName === 'UserController' && $methodName === 'login') {
                $result = $controllerInstance->{$methodName}($request);
            } elseif ($controllerName === 'UserController' && $methodName === 'register') {
                $result = $controllerInstance->{$methodName}($request);
            } elseif ($controllerName === 'TransactionController' && $methodName === 'displayTransactions') {
                $result = $controllerInstance->{$methodName}($request);
            } elseif ($controllerName === 'TransactionController' && $methodName === 'buy') {
                $result = $controllerInstance->{$methodName}($request);
            } elseif ($controllerName === 'TransactionController' && $methodName === 'sell') {
                $result = $controllerInstance->{$methodName}($request);
            } elseif ($controllerName === 'WalletController' && $methodName === 'displayWallet') {
                $result = $controllerInstance->{$methodName}($request);
            } else {
                $result = $controllerInstance->{$methodName}($vars);
            }
        } else {
            throw new Exception("Method $methodName not found in $controllerClass");
        }

        renderTemplate($twig, $result);
        break;
}