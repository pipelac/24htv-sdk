<?php
/**
 * Пример: обработка ошибок SDK с использованием иерархии исключений
 *
 * @since 1.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use TwentyFourTv\ClientFactory;
use TwentyFourTv\Exception\AuthenticationException;
use TwentyFourTv\Exception\ConfigException;
use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\RateLimitException;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;

try {
    $client = ClientFactory::create(__DIR__ . '/../cfg/24htv.ini');
} catch (ConfigException $e) {
    echo "Ошибка конфигурации: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    // Получение пользователя по ID
    $user = $client->users()->getById(12345);
    echo "Пользователь: " . $user->getUsername() . "\n";

    // Обновление данных пользователя
    $updated = $client->users()->update($user->getId(), [
        'phone' => '+79009876543',
    ]);
    echo "Обновлён, телефон: " . $updated->getPhone() . "\n";

} catch (NotFoundException $e) {
    echo "Пользователь не найден\n";

} catch (AuthenticationException $e) {
    echo "Ошибка аутентификации: невалидный токен\n";

} catch (RateLimitException $e) {
    echo "Превышен лимит запросов, повторите позже\n";
    echo "HTTP: " . $e->getHttpCode() . "\n";

} catch (ConnectionException $e) {
    echo "Ошибка сети: " . $e->getMessage() . "\n";

} catch (ValidationException $e) {
    echo "Ошибка валидации: " . $e->getMessage() . "\n";

} catch (TwentyFourTvException $e) {
    echo "Неизвестная ошибка API: " . $e->getMessage() . "\n";
    echo "HTTP код: " . $e->getHttpCode() . "\n";
    echo "Endpoint: " . $e->getEndpoint() . "\n";
    echo "Метод: " . $e->getMethod() . "\n";
}
