<?php

namespace TwentyFourTv\Callback;

use Exception;
use TwentyFourTv\Contract\CallbackHandlerInterface;
use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\LoggerInterface;

/**
 * Обработчик обратной интеграции (callback-запросы от 24ТВ к провайдеру)
 *
 * Реализует 4 endpoint обратной интеграции:
 * - AUTH               — авторизация абонента по IP / телефону
 * - PACKET             — подключение пакета из приложения 24ТВ
 * - DELETE_SUBSCRIPTION — отключение автопродления из приложения
 * - BALANCE            — запрос баланса для отображения в приложении
 *
 * Схема «48 часов»: запрос PACKET при автопродлении НЕ отправляется.
 *
 * Для авторизации и баланса используйте готовые резолверы или реализуйте свой:
 *
 * <code>
 * // Вариант 1: UTM5 резолверы (из коробки)
 * $handler = new CallbackHandler($config, $logger);
 * $handler->setAuthResolver(new UtmAuthResolver($db, $logger));
 * $handler->setBalanceResolver(new UtmBalanceResolver($db, $logger));
 *
 * // Вариант 2: callable для любого биллинга
 * $handler->setAuthResolver(function ($params) {
 *     return ['result' => 'success', 'provider_uid' => '12345'];
 * });
 *
 * $response = $handler->handle('/auth', ['ip' => '10.0.0.1']);
 * $response->send();
 * </code>
 *
 * @see \TwentyFourTv\Resolver\UtmAuthResolver     Встроенный резолвер для UTM5
 * @see \TwentyFourTv\Resolver\UtmBalanceResolver   Встроенный резолвер для UTM5
 * @see https://24tv.atlassian.net/wiki/spaces/SPC/pages/53379013/48
 * @since 1.0.0
 */
class CallbackHandler implements CallbackHandlerInterface
{
    /** @var ConfigInterface */
    private $config;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var string|null Prefix пути (например '24htv') */
    private $pathPrefix;

    /** @var callable|null Кастомный обработчик авторизации */
    private $authResolver;

    /** @var callable|null Кастомный обработчик баланса */
    private $balanceResolver;

    /** @var callable|null Кастомный обработчик пакета */
    private $packetHandler;

    /** @var callable|null Кастомный обработчик отключения подписки */
    private $deleteSubscriptionHandler;

    /**
     * @param ConfigInterface      $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->pathPrefix = null;
    }

    /**
     * Установить prefix пути для корректного парсинга URI
     *
     * Если callback endpoint доступен по /24htv/auth, prefix = '24htv'
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPathPrefix($prefix)
    {
        $this->pathPrefix = trim($prefix, '/');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthResolver(callable $resolver)
    {
        $this->authResolver = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBalanceResolver(callable $resolver)
    {
        $this->balanceResolver = $resolver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPacketHandler(callable $handler)
    {
        $this->packetHandler = $handler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeleteSubscriptionHandler(callable $handler)
    {
        $this->deleteSubscriptionHandler = $handler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($requestUri, array $params = [], $rawBody = '', $clientIp = null)
    {
        if ($clientIp === null) {
            $clientIp = '0.0.0.0';
        }

        if ($this->logger) {
            $this->logger->info('24HTV callback: входящий запрос', [
                'uri'    => $requestUri,
                'params' => $params,
                'ip'     => $clientIp,
            ]);
        }

        // Проверка HMAC-подписи (если настроен callback_secret)
        $signatureError = $this->verifySignature($params, $rawBody);
        if ($signatureError !== null) {
            if ($this->logger) {
                $this->logger->warning('24HTV callback: ошибка подписи', [
                    'ip'    => $clientIp,
                    'error' => $signatureError,
                ]);
            }

            return CallbackResponse::error($signatureError);
        }

        $path = $this->extractCallbackPath($requestUri);

        // Парсинг JSON-тела
        $body = [];
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $body = $decoded;
            }
        }

        try {
            switch ($path) {
                case 'auth':
                    $responseData = $this->handleAuth($params);
                    break;

                case 'packet':
                    $responseData = $this->handlePacket($params, $body);
                    break;

                case 'delete_subscription':
                    $responseData = $this->handleDeleteSubscription($params, $body);
                    break;

                case 'balance':
                    $responseData = $this->handleBalance($params, $body);
                    break;

                default:
                    if ($this->logger) {
                        $this->logger->warning('24HTV callback: неизвестный тип', [
                            'path' => $path,
                        ]);
                    }

                    return CallbackResponse::error("Unknown callback type: {$path}");
            }
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('24HTV callback: исключение', [
                    'path'  => $path,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            return CallbackResponse::error('Internal error: ' . $e->getMessage());
        }

        return new CallbackResponse($responseData);
    }

    /**
     * Проверить HMAC-подпись входящего callback-запроса
     *
     * Если в конфигурации задан `callback.secret`, вычисляется HMAC-SHA256
     * от тела запроса и сравнивается с переданной подписью (timing-safe).
     * Если секрет не настроен — проверка пропускается.
     *
     * @param array  $params  GET-параметры (ожидается 'signature')
     * @param string $rawBody Тело запроса для вычисления HMAC
     *
     * @return string|null Сообщение об ошибке или null если проверка пройдена
     */
    private function verifySignature(array $params, $rawBody)
    {
        $secret = $this->config->getOrDefault('callback.secret', null);

        if ($secret === null || $secret === '') {
            return null; // Секрет не настроен — верификация отключена
        }

        if (!isset($params['signature'])) {
            return 'Missing signature parameter';
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        if (!hash_equals($expected, $params['signature'])) {
            return 'Invalid signature';
        }

        return null;
    }

    /**
     * Извлечь имя callback из URI с учётом prefix
     *
     * @param string $requestUri
     *
     * @return string
     */
    private function extractCallbackPath($requestUri)
    {
        $path = strtolower(trim(parse_url($requestUri, PHP_URL_PATH), '/'));

        if ($this->pathPrefix !== null && strpos($path, $this->pathPrefix . '/') === 0) {
            $path = substr($path, strlen($this->pathPrefix) + 1);
        }

        return $path;
    }

    /**
     * AUTH — авторизация абонента по IP или телефону
     *
     * @param array $params
     *
     * @return array
     */
    private function handleAuth(array $params)
    {
        if ($this->logger) {
            $this->logger->info('24HTV AUTH: запрос авторизации', [
                'ip'           => isset($params['ip']) ? $params['ip'] : null,
                'phone'        => isset($params['phone']) ? $params['phone'] : null,
                'provider_uid' => isset($params['provider_uid']) ? $params['provider_uid'] : null,
            ]);
        }

        if ($this->authResolver !== null) {
            return call_user_func($this->authResolver, $params);
        }

        if ($this->logger) {
            $this->logger->error('24HTV AUTH: не настроен обработчик авторизации');
        }

        return [
            'result' => 'error',
            'errmsg' => 'Auth handler not configured. Use setAuthResolver() to set up auth handling.',
        ];
    }

    /**
     * PACKET — подключение пакета из приложения
     *
     * @param array $params
     * @param array $body
     *
     * @return array
     */
    private function handlePacket(array $params, array $body)
    {
        if ($this->logger) {
            $this->logger->info('24HTV PACKET: подключение пакета', [
                'user_id'     => isset($params['user_id']) ? $params['user_id'] : null,
                'trf_id'      => isset($params['trf_id']) ? $params['trf_id'] : null,
                'packet_name' => isset($body['packet']['name']) ? $body['packet']['name'] : null,
                'price'       => isset($body['packet']['price']) ? $body['packet']['price'] : null,
            ]);
        }

        if ($this->packetHandler !== null) {
            return call_user_func($this->packetHandler, $params, $body);
        }

        return ['result' => 'success'];
    }

    /**
     * DELETE_SUBSCRIPTION — отключение подписки из приложения
     *
     * @param array $params
     * @param array $body
     *
     * @return array
     */
    private function handleDeleteSubscription(array $params, array $body)
    {
        if ($this->logger) {
            $this->logger->info('24HTV DELETE_SUBSCRIPTION: отключение подписки', [
                'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
                'sub_id'  => isset($params['sub_id']) ? $params['sub_id'] : null,
            ]);
        }

        if ($this->deleteSubscriptionHandler !== null) {
            return call_user_func($this->deleteSubscriptionHandler, $params, $body);
        }

        return ['result' => 'success'];
    }

    /**
     * BALANCE — запрос баланса для отображения в приложении
     *
     * @param array $params
     * @param array $body
     *
     * @return array
     */
    private function handleBalance(array $params, array $body)
    {
        $providerUid = isset($params['provider_uid']) ? $params['provider_uid'] : null;
        if ($providerUid === null) {
            $providerUid = isset($body['provider_uid']) ? $body['provider_uid'] : null;
        }
        if ($providerUid === null && isset($body['user']['provider_uid'])) {
            $providerUid = $body['user']['provider_uid'];
        }

        if ($this->logger) {
            $this->logger->info('24HTV BALANCE: запрос баланса', [
                'provider_uid' => $providerUid,
            ]);
        }

        if ($this->balanceResolver !== null) {
            return call_user_func($this->balanceResolver, array_merge($params, $body));
        }

        return [
            'result' => 'error',
            'errmsg' => 'Balance handler not configured. Use setBalanceResolver() to set up balance handling.',
        ];
    }
}
