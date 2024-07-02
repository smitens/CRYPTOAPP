<?php

require_once 'vendor/autoload.php';

use CryptoApp\Services\DatabaseInitializer;

new DatabaseInitializer('storage/database.sqlite');

use CryptoApp\Controllers\TransactionController;
use CryptoApp\Controllers\WalletController;
use CryptoApp\Controllers\OptionController;
use CryptoApp\Controllers\UserController;
use CryptoApp\Controllers\CurrencyController;
use CryptoApp\Response;
use CryptoApp\RedirectResponse;
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

$container->set(CurrencyRepository::class, function() {
    return new CoinMarketApiCurrencyRepository($_ENV['APIKEY']);
});

$container->set(TransactionRepository::class, function() {
    return new SqliteTransactionRepository();
});

$container->set(WalletRepository::class, function() {
    return new SqliteWalletRepository();
});

$container->set(WalletService::class, function(Container $container) {
    $walletRepository = $container->get(WalletRepository::class);
    $transactionRepository = $container->get(TransactionRepository::class);
    $currencyRepository = $container->get(CurrencyRepository::class);
    $session = $container->get(SessionInterface::class);
    $userId = getUserIdFromSession($session);
    $wallet = new Wallet($userId, WalletService::INITIAL_BALANCE);
    return new WalletService(
        $wallet,
        $walletRepository,
        $transactionRepository,
        $currencyRepository,
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

$container->set(CurrencyController::class, function(Container $container) {
    return new CurrencyController(
        $container->get(BuyCurrencyService::class),
        $container->get(SellCurrencyService::class),
        $container->get(CurrencyRepository::class),
    );
});

$container->set(TransactionController::class, function(Container $container) {
    return new TransactionController(
        $container->get(BuyCurrencyService::class),
        $container->get(SellCurrencyService::class),
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


$container->set('routes', require __DIR__ . '/routes.php');

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

function getUserIdFromSession(SessionInterface $session): ?string
{
    if ($session->has('user')) {
        /** @var User $user */
        $user = $session->get('user');
        return $user->getId();
    }
    return null;
}

function handleResponse(Environment $twig, $response): void
{
    if ($response instanceof Response) {
        echo $twig->render($response->getTemplate(), $response->getData());
    } elseif ($response instanceof RedirectResponse) {
        header('Location: ' . $response->getLocation());
        exit();
    } else {
        throw new Exception('Unexpected response type.');
    }
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

            $result = $controllerInstance->{$methodName}($request, $vars);
        } else {
            throw new Exception("Method $methodName not found in $controllerClass");
        }

        handleResponse($twig, $result);
        break;
}