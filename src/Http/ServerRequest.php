<?php

namespace TwentyFourTv\Http;

/**
 * Value Object представляющий входящий HTTP-запрос
 *
 * Заменяет прямой доступ к суперглобалам ($_SERVER, $_GET, php://input),
 * что делает код тестируемым без манипуляции глобальным состоянием.
 *
 * <code>
 * // В entry point (webhook.php):
 * $request = ServerRequest::fromGlobals();
 *
 * // В тестах:
 * $request = new ServerRequest('/auth', 'GET', ['ip' => '10.0.0.1']);
 *
 * // Использование:
 * $response = $client->handleCallback($request);
 * </code>
 *
 * @since 1.0.0
 */
class ServerRequest
{
    /** @var string URI запроса */
    private $uri;

    /** @var string HTTP-метод */
    private $method;

    /** @var array GET-параметры */
    private $queryParams;

    /** @var string Тело запроса */
    private $body;

    /** @var array HTTP-заголовки */
    private $headers;

    /** @var array Серверные параметры ($_SERVER) */
    private $serverParams;

    /**
     * @param string $uri          URI запроса
     * @param string $method       HTTP-метод
     * @param array  $queryParams  GET-параметры
     * @param string $body         Тело запроса
     * @param array  $headers      Заголовки
     * @param array  $serverParams Серверные параметры
     */
    public function __construct(
        $uri = '/',
        $method = 'GET',
        array $queryParams = [],
        $body = '',
        array $headers = [],
        array $serverParams = []
    ) {
        $this->uri = $uri;
        $this->method = strtoupper($method);
        $this->queryParams = $queryParams;
        $this->body = $body;
        $this->headers = $headers;
        $this->serverParams = $serverParams;
    }

    /**
     * Фабрика: создать из PHP суперглобалов
     *
     * @return self
     */
    public static function fromGlobals()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        $rawBody = file_get_contents('php://input');
        if ($rawBody === false) {
            $rawBody = '';
        }

        return new self(
            $uri,
            $method,
            $_GET,
            $rawBody,
            self::extractHeaders($_SERVER),
            $_SERVER
        );
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getQueryParam($key, $default = null)
    {
        return isset($this->queryParams[$key]) ? $this->queryParams[$key] : $default;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Декодировать тело как JSON
     *
     * @return array
     */
    public function getJsonBody()
    {
        if (empty($this->body)) {
            return [];
        }

        $decoded = json_decode($this->body, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function getHeader($name, $default = '')
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Определить IP клиента из заголовков
     *
     * @return string
     */
    public function getClientIp()
    {
        // X-Real-IP (nginx reverse proxy)
        $realIp = $this->getServerParam('HTTP_X_REAL_IP');
        if (!empty($realIp)) {
            return $realIp;
        }

        // X-Forwarded-For (стандартный прокси)
        $forwarded = $this->getServerParam('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            $ips = explode(',', $forwarded);

            return trim($ips[0]);
        }

        $remoteAddr = $this->getServerParam('REMOTE_ADDR');

        return !empty($remoteAddr) ? $remoteAddr : '0.0.0.0';
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getServerParam($key, $default = null)
    {
        return isset($this->serverParams[$key]) ? $this->serverParams[$key] : $default;
    }

    /**
     * Извлечь HTTP-заголовки из $_SERVER
     *
     * @param array $server
     *
     * @return array Заголовки в формате [lowercase-name => value]
     */
    private static function extractHeaders(array $server)
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        if (isset($server['CONTENT_TYPE'])) {
            $headers['content-type'] = $server['CONTENT_TYPE'];
        }

        return $headers;
    }
}
