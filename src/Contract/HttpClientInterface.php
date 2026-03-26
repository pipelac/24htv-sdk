<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт HTTP-клиента для обращений к API 24часаТВ
 *
 * <code>
 * $client->apiGet('/users', ['limit' => 10]);
 * $client->apiPost('/users', ['username' => 'test', 'phone' => '+71234567890']);
 * </code>
 *
 * @since 1.0.0
 */
interface HttpClientInterface
{
    /**
     * GET-запрос к API
     *
     * @param string $endpoint Путь без base_url (напр. "/users")
     * @param array  $query    Дополнительные query-параметры
     *
     * @return mixed Декодированный JSON-ответ
     */
    public function apiGet($endpoint, array $query = []);

    /**
     * POST-запрос к API
     *
     * @param string $endpoint
     * @param mixed  $body     Тело запроса (будет отправлено как JSON)
     * @param array  $query    Дополнительные query-параметры
     *
     * @return mixed
     */
    public function apiPost($endpoint, $body = null, array $query = []);

    /**
     * PATCH-запрос к API
     *
     * @param string $endpoint
     * @param mixed  $body
     * @param array  $query
     *
     * @return mixed
     */
    public function apiPatch($endpoint, $body = null, array $query = []);

    /**
     * PUT-запрос к API
     *
     * @param string $endpoint
     * @param mixed  $body
     * @param array  $query
     *
     * @return mixed
     */
    public function apiPut($endpoint, $body = null, array $query = []);

    /**
     * DELETE-запрос к API
     *
     * @param string $endpoint
     * @param array  $query
     *
     * @return mixed
     */
    public function apiDelete($endpoint, array $query = []);
}
