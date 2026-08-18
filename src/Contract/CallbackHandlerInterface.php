<?php

namespace TwentyFourTv\Contract;

use TwentyFourTv\Callback\CallbackResponse;

/**
 * Контракт обработчика callback-запросов от 24ТВ
 *
 * @since 1.0.0
 */
interface CallbackHandlerInterface
{
    /**
     * Обработать входящий callback-запрос
     *
     * @param string      $requestUri URI запроса
     * @param array       $params     GET-параметры
     * @param string      $rawBody    Тело запроса (JSON)
     * @param string|null $clientIp   IP-адрес клиента
     *
     * @return CallbackResponse
     */
    public function handle($requestUri, array $params = [], $rawBody = '', $clientIp = null);

    /**
     * Установить резолвер авторизации
     *
     * Принимает callable или экземпляр AuthResolverInterface.
     * Для UTM5 используйте {@see \TwentyFourTv\Resolver\UtmAuthResolver}.
     *
     * @param callable $resolver fn(array $params): array
     *
     * @return $this
     */
    public function setAuthResolver(callable $resolver);

    /**
     * Установить резолвер баланса
     *
     * Принимает callable или экземпляр BalanceResolverInterface.
     * Для UTM5 используйте {@see \TwentyFourTv\Resolver\UtmBalanceResolver}.
     *
     * @param callable $resolver fn(array $params): array
     *
     * @return $this
     */
    public function setBalanceResolver(callable $resolver);

    /**
     * @param callable $handler fn(array $params, array $body): array
     *
     * @return $this
     */
    public function setPacketHandler(callable $handler);

    /**
     * @param callable $handler fn(array $params, array $body): array
     *
     * @return $this
     */
    public function setDeleteSubscriptionHandler(callable $handler);
}
