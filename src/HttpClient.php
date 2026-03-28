<?php

namespace TwentyFourTv;

use TwentyFourTv\Contract\ConfigInterface;
use TwentyFourTv\Contract\HttpClientInterface;
use TwentyFourTv\Contract\LoggerInterface;
use TwentyFourTv\Exception\AuthenticationException;
use TwentyFourTv\Exception\ConflictException;
use TwentyFourTv\Exception\ConnectionException;
use TwentyFourTv\Exception\ForbiddenException;
use TwentyFourTv\Exception\NotFoundException;
use TwentyFourTv\Exception\RateLimitException;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Exception\ValidationException;
use TwentyFourTv\Http\CircuitBreaker;
use TwentyFourTv\Util\TokenMasker;

/**
 * HTTP-клиент для взаимодействия с API 24часаТВ
 *
 * Реализует все HTTP-методы (GET, POST, PATCH, PUT, DELETE) с:
 * - автоматической инъекцией TOKEN
 * - retry с экспоненциальным backoff при 429/5xx
 * - маскированием токена в логах
 * - типизированными исключениями
 * - поддержкой middleware (pre/post request hooks)
 *
 * <code>
 * $http = new HttpClient($config, $logger);
 * $users = $http->apiGet('/users', ['limit' => 10]);
 * $user  = $http->apiPost('/users', ['username' => 'test', 'phone' => '+71234567890']);
 * </code>
 *
 * @since 1.0.0
 */
class HttpClient implements HttpClientInterface
{
    /** @var int[] Статусы при которых выполняется повторный запрос */
    private static $retryableStatuses = [429, 500, 502, 503, 504];

    /** @var ConfigInterface */
    private $config;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var int Максимальное количество повторов */
    private $maxRetries;

    /** @var callable[] Middleware перед запросом: fn($method, $url, $body): void */
    private $beforeMiddleware = [];

    /** @var callable[] Middleware после запроса: fn($method, $url, $httpCode, $duration): void */
    private $afterMiddleware = [];

    /** @var CircuitBreaker */
    private $circuitBreaker;

    /**
     * @param ConfigInterface      $config Конфигурация SDK
     * @param LoggerInterface|null $logger Логгер
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->maxRetries = $config->getOrDefault('api.max_retries', Config::DEFAULT_MAX_RETRIES);

        $cbThreshold = (int) $config->getOrDefault('api.circuit_breaker_threshold', 5);
        $cbCooldown  = (int) $config->getOrDefault('api.circuit_breaker_cooldown', 30);
        $this->circuitBreaker = new CircuitBreaker($cbThreshold, $cbCooldown);
    }

    /**
     * Добавить middleware, выполняемый перед каждым запросом
     *
     * @param callable $middleware fn(string $method, string $url, mixed $body): void
     *
     * @return $this
     */
    public function addBeforeMiddleware(callable $middleware)
    {
        $this->beforeMiddleware[] = $middleware;

        return $this;
    }

    /**
     * Добавить middleware, выполняемый после каждого запроса
     *
     * @param callable $middleware fn(string $method, string $url, int $httpCode, float $duration): void
     *
     * @return $this
     */
    public function addAfterMiddleware(callable $middleware)
    {
        $this->afterMiddleware[] = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function apiGet($endpoint, array $query = [])
    {
        return $this->apiRequest('GET', $endpoint, ['query' => $query]);
    }

    /**
     * {@inheritdoc}
     */
    public function apiPost($endpoint, $body = null, array $query = [])
    {
        return $this->apiRequest('POST', $endpoint, [
            'body'  => $body,
            'query' => $query,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function apiPatch($endpoint, $body = null, array $query = [])
    {
        return $this->apiRequest('PATCH', $endpoint, [
            'body'  => $body,
            'query' => $query,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function apiPut($endpoint, $body = null, array $query = [])
    {
        return $this->apiRequest('PUT', $endpoint, [
            'body'  => $body,
            'query' => $query,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function apiDelete($endpoint, array $query = [])
    {
        return $this->apiRequest('DELETE', $endpoint, ['query' => $query]);
    }

    /**
     * Выполнить HTTP-запрос с retry-механизмом
     *
     * @param string $method   HTTP-метод (GET, POST, PATCH, PUT, DELETE)
     * @param string $endpoint API-путь (напр. "/users")
     * @param array  $options  Опции: body, query
     *
     * @throws TwentyFourTvException
     * @throws ConnectionException
     *
     * @return mixed Декодированный JSON
     */
    private function apiRequest($method, $endpoint, array $options = [])
    {
        $body = isset($options['body']) ? $options['body'] : null;
        $query = isset($options['query']) ? $options['query'] : [];

        // Circuit Breaker: проверка доступности
        if (!$this->circuitBreaker->isAvailable()) {
            $stats = $this->circuitBreaker->getStats();
            if ($this->logger) {
                $this->logger->warning('24HTV HTTP: Circuit Breaker OPEN, запрос отклонён', [
                    'endpoint' => $endpoint,
                    'failures' => $stats['failures'],
                ]);
            }

            throw new ConnectionException(
                "Circuit Breaker OPEN: API недоступен после {$stats['failures']} сбоев — {$method} {$endpoint}",
                0
            );
        }

        // Инъекция токена
        $query['token'] = $this->config->getToken();

        // Формирование URL
        $url = rtrim($this->config->getBaseUrl(), '/') . $endpoint;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        // Логирование запроса (с маскированным токеном)
        $this->logRequest($method, $endpoint, $query, $body);

        // Before middleware
        foreach ($this->beforeMiddleware as $middleware) {
            call_user_func($middleware, $method, TokenMasker::maskInUrl($url), $body);
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->maxRetries) {
            if ($attempt > 0) {
                // Использовать Retry-After заголовок если есть (429 Too Many Requests)
                $retryAfterDelay = $this->parseRetryAfter(
                    isset($lastHeaders) ? $lastHeaders : []
                );

                if ($retryAfterDelay !== null) {
                    // Используем значение из Retry-After (макс 30 секунд)
                    $delay = min($retryAfterDelay, 30) * 1000000; // в микросекунды
                } else {
                    // Экспоненциальный backoff с jitter
                    $baseDelay = (int) (pow(2, $attempt) * 100000); // 200ms, 400ms, 800ms
                    $jitter = (int) ($baseDelay * (0.5 + (mt_rand() / mt_getrandmax()) * 0.5));
                    $delay = $jitter;
                }

                if ($this->logger) {
                    $this->logger->info('24HTV HTTP: повтор запроса', [
                        'attempt'  => $attempt,
                        'delay_ms' => $delay / 1000,
                        'source'   => $retryAfterDelay !== null ? 'Retry-After' : 'backoff',
                    ]);
                }
                usleep($delay);
            }

            try {
                $result = $this->executeRequest($method, $url, $body);
                $httpCode = $result['httpCode'];
                $responseBody = $result['body'];
                $jsonData = $result['json'];
                $duration = $result['duration'];

                // After middleware
                foreach ($this->afterMiddleware as $middleware) {
                    call_user_func($middleware, $method, $endpoint, $httpCode, $duration);
                }

                // Успешный запрос
                if ($httpCode >= 200 && $httpCode < 300) {
                    $this->circuitBreaker->recordSuccess();

                    return $jsonData;
                }

                // Retryable ошибки
                if (in_array($httpCode, self::$retryableStatuses) && $attempt < $this->maxRetries) {
                    $lastException = $this->createException($method, $endpoint, $httpCode, $jsonData, $responseBody);
                    $lastHeaders = isset($result['headers']) ? $result['headers'] : [];
                    $attempt++;
                    continue;
                }

                // Финальная ошибка — бросаем типизированное исключение
                throw $this->createException($method, $endpoint, $httpCode, $jsonData, $responseBody);
            } catch (ConnectionException $e) {
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                $lastException = $e;
                $this->circuitBreaker->recordFailure();
                $attempt++;
            }
        }

        // Если дошли сюда — все попытки исчерпаны
        $this->circuitBreaker->recordFailure();
        if ($lastException !== null) {
            throw $lastException;
        }

        throw new TwentyFourTvException("Все попытки запроса исчерпаны: {$method} {$endpoint}");
    }

    /**
     * Выполнить cURL запрос
     *
     * @param string     $method HTTP-метод
     * @param string     $url    Полный URL
     * @param mixed|null $body   Тело запроса
     *
     * @throws ConnectionException При сетевой ошибке
     *
     * @return array ['httpCode' => int, 'body' => string, 'json' => mixed, 'duration' => float]
     */
    private function executeRequest($method, $url, $body)
    {
        $ch = curl_init();
        $responseHeaders = [];

        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->config->getTimeout());
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->config->getConnectTimeout());
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: ' . SdkVersion::userAgent(),
            ]);

            // Сбор заголовков ответа (для Retry-After и пр.)
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) use (&$responseHeaders) {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($header);
            });

            switch (strtoupper($method)) {
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($body !== null) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
                    }
                    break;

                case 'PATCH':
                case 'PUT':
                case 'DELETE':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
                    if ($body !== null) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
                    }
                    break;
            }

            $responseBody = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            $duration = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME), 3);
        } finally {
            curl_close($ch);
        }

        // Сетевая ошибка
        if ($errno !== 0) {
            throw new ConnectionException(
                "cURL ошибка [{$errno}]: {$error} — {$method} " . TokenMasker::maskInUrl($url),
                $errno
            );
        }

        // Логирование ответа
        if ($this->logger) {
            $this->logger->info('24HTV HTTP: ответ', [
                'httpCode' => $httpCode,
                'duration' => $duration . 's',
            ]);
        }

        $jsonData = null;
        if (!empty($responseBody)) {
            $jsonData = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonData = null;
            }
        }

        return [
            'httpCode' => $httpCode,
            'body'     => $responseBody,
            'json'     => $jsonData,
            'duration' => $duration,
            'headers'  => $responseHeaders,
        ];
    }

    /**
     * Создать типизированное исключение по HTTP-коду
     *
     * @param string          $method
     * @param string          $endpoint
     * @param int             $httpCode
     * @param mixed|null      $jsonData
     * @param string          $rawBody
     * @param \Exception|null $previous Предыдущее исключение для цепочки
     *
     * @return TwentyFourTvException
     */
    private function createException($method, $endpoint, $httpCode, $jsonData, $rawBody, $previous = null)
    {
        $errorMessage = $this->extractErrorMessage($jsonData);
        $message = "API {$method} {$endpoint}: HTTP {$httpCode}";
        if ($errorMessage) {
            $message .= " — {$errorMessage}";
        }

        $responseArray = is_array($jsonData) ? $jsonData : ['raw' => $rawBody];

        if ($this->logger) {
            $this->logger->error('24HTV HTTP: ошибка API', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'httpCode' => $httpCode,
                'error'    => $errorMessage,
            ]);
        }

        switch ($httpCode) {
            case 401:
                return new AuthenticationException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            case 403:
                return new ForbiddenException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            case 404:
                return new NotFoundException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            case 409:
                return new ConflictException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            case 429:
                return new RateLimitException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            case 400:
            case 422:
                return new ValidationException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
            default:
                return new TwentyFourTvException($message, $httpCode, $httpCode, $responseArray, $previous, $method, $endpoint);
        }
    }

    /**
     * Извлечь текст ошибки из JSON-тела ответа
     *
     * @param mixed $json
     *
     * @return string|null
     */
    private function extractErrorMessage($json)
    {
        if (!is_array($json)) {
            return null;
        }

        if (isset($json['detail'])) {
            return is_string($json['detail'])
                ? $json['detail']
                : json_encode($json['detail'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($json['error']['message'])) {
            return $json['error']['message'];
        }

        if (isset($json['errmsg'])) {
            return $json['errmsg'];
        }

        if (isset($json['message'])) {
            return $json['message'];
        }

        return null;
    }

    /**
     * Логирование запроса с маскированным токеном
     *
     * @param string     $method
     * @param string     $endpoint
     * @param array      $query
     * @param mixed|null $body
     */
    private function logRequest($method, $endpoint, array $query, $body)
    {
        if ($this->logger === null) {
            return;
        }

        $safeQuery = $query;
        if (isset($safeQuery['token'])) {
            $safeQuery['token'] = TokenMasker::mask($safeQuery['token']);
        }

        $context = [
            'method'   => $method,
            'endpoint' => $endpoint,
            'query'    => $safeQuery,
        ];

        if ($body !== null) {
            $context['body'] = $body;
        }

        $this->logger->info('24HTV HTTP: запрос', $context);
    }

    /**
     * Получить экземпляр Circuit Breaker
     *
     * @return CircuitBreaker
     */
    public function getCircuitBreaker()
    {
        return $this->circuitBreaker;
    }

    /**
     * Парсить заголовок Retry-After из ответа
     *
     * Поддерживает:
     * - числовое значение в секундах (Retry-After: 5)
     * - HTTP-дата (Retry-After: Fri, 31 Dec 2027 23:59:59 GMT)
     *
     * @param array $headers Заголовки ответа (lowercase keys)
     *
     * @return int|null Количество секунд для ожидания или null
     */
    private function parseRetryAfter(array $headers)
    {
        if (!isset($headers['retry-after'])) {
            return null;
        }

        $value = trim($headers['retry-after']);

        // Числовое значение в секундах
        if (ctype_digit($value)) {
            return (int) $value;
        }

        // HTTP-дата
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            $delay = $timestamp - time();

            return $delay > 0 ? $delay : 1;
        }

        return null;
    }
}
