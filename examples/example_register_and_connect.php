<?php
/**
 * Пример: регистрация пользователя и подключение базового пакета
 *
 * @since 1.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;

// Создание клиента
$client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini');

try {
    // 1. Регистрация пользователя (возвращает User DTO)
    $user = $client->users()->register([
        'username'     => 'user_' . time(),
        'phone'        => '+79001234567',
        'provider_uid' => '12345',
    ]);

    echo "Пользователь создан: ID = " . $user->getId() . "\n";
    echo "Username: " . $user->getUsername() . "\n";

    // 2. Получение базовых пакетов (возвращает Packet[])
    $packets = $client->packets()->getBase();
    echo "Найдено базовых пакетов: " . count($packets) . "\n";

    if (count($packets) > 0) {
        // 3. Подключение первого базового пакета
        $subscription = $client->subscriptions()->connectSingle(
            $user->getId(),
            $packets[0]->getId(),
            true // renew = автопродление
        );

        echo "Подписка создана: ID = " . $subscription->getId() . "\n";
    }

    // 4. Получение текущих подписок (возвращает Subscription[])
    $subs = $client->subscriptions()->getCurrent($user->getId());
    echo "Активных подписок: " . count($subs) . "\n";

} catch (ValidationException $e) {
    echo "Ошибка валидации: " . $e->getMessage() . "\n";
} catch (TwentyFourTvException $e) {
    echo "Ошибка API: " . $e->getMessage() . "\n";
    echo "HTTP код: " . $e->getHttpCode() . "\n";
}
