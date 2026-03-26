<?php
/**
 * Пример: обработка callback-запросов от 24ТВ (обратная интеграция)
 *
 * @since 1.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Resolver\UtmAuthResolver;
use TwentyFourTv\Resolver\UtmBalanceResolver;

// =============================================
// Вариант 1: UTM5 (встроенные резолверы)
// =============================================

$client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini', $logger);

// $db — реализация DatabaseInterface для вашего UTM5
$client->callbacks()
    ->setAuthResolver(new UtmAuthResolver($db, $logger))
    ->setBalanceResolver(new UtmBalanceResolver($db, $logger));

$response = $client->handleCallback();
$response->send();

// =============================================
// Вариант 2: Кастомный биллинг (callable)
// =============================================

// $client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini', $logger);
//
// $client->callbacks()
//     ->setAuthResolver(function ($params) use ($billing) {
//         // Ваша логика поиска абонента по IP
//         $ip = isset($params['ip']) ? $params['ip'] : null;
//         if ($ip === null) {
//             return ['result' => 'error', 'errmsg' => 'IP is required'];
//         }
//
//         $user = $billing->findByIp($ip);
//         if ($user) {
//             return ['result' => 'success', 'provider_uid' => $user['account_id']];
//         }
//
//         return ['result' => 'error', 'errmsg' => 'User not found'];
//     })
//     ->setBalanceResolver(function ($params) use ($billing) {
//         // Ваша логика получения баланса
//         $providerUid = isset($params['provider_uid']) ? $params['provider_uid'] : null;
//         if ($providerUid === null) {
//             return ['result' => 'error', 'errmsg' => 'provider_uid is required'];
//         }
//
//         $balance = $billing->getBalance($providerUid);
//         return ['result' => 'success', 'balance' => (string) $balance];
//     });
//
// $response = $client->handleCallback();
// $response->send();

// =============================================
// Вариант 3: Свой класс-резолвер (AuthResolverInterface)
// =============================================

// use TwentyFourTv\Contract\AuthResolverInterface;
//
// class MyBillingAuthResolver implements AuthResolverInterface
// {
//     private $pdo;
//
//     public function __construct(PDO $pdo) { $this->pdo = $pdo; }
//
//     public function __invoke(array $params)
//     {
//         $ip = isset($params['ip']) ? $params['ip'] : null;
//         $stmt = $this->pdo->prepare('SELECT id FROM subscribers WHERE ip_addr = ?');
//         $stmt->execute([$ip]);
//         $row = $stmt->fetch();
//
//         if ($row) {
//             return ['result' => 'success', 'provider_uid' => (string) $row['id']];
//         }
//         return ['result' => 'error', 'errmsg' => 'Subscriber not found'];
//     }
// }
//
// $client->callbacks()->setAuthResolver(new MyBillingAuthResolver($pdo));
