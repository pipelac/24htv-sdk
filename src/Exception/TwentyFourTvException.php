<?php

namespace TwentyFourTv\Exception;

use Exception;

/**
 * Базовое исключение SDK 24часаТВ
 *
 * Все остальные исключения SDK наследуют от этого класса.
 *
 * <code>
 * try {
 *     $client->users()->register($data);
 * } catch (TwentyFourTvException $e) {
 *     echo $e->getHttpCode();      // 400
 *     echo $e->getResponseBody();   // ['detail' => '...']
 * }
 * </code>
 *
 * @since 1.0.0
 */
class TwentyFourTvException extends Exception
{
    /** @var int|null HTTP-код ответа API */
    private $httpCode;

    /** @var array|null Тело ответа API */
    private $responseBody;

    /** @var string|null HTTP-метод запроса */
    private $method;

    /** @var string|null API endpoint */
    private $endpoint;

    /**
     * @param string         $message      Сообщение об ошибке
     * @param int            $code         Код ошибки
     * @param int|null       $httpCode     HTTP-код ответа
     * @param array|null     $responseBody Тело ответа
     * @param Exception|null $previous     Предыдущее исключение
     * @param string|null    $method       HTTP-метод
     * @param string|null    $endpoint     API endpoint
     */
    public function __construct($message, $code = 0, $httpCode = null, $responseBody = null, Exception $previous = null, $method = null, $endpoint = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpCode = $httpCode;
        $this->responseBody = $responseBody;
        $this->method = $method;
        $this->endpoint = $endpoint;
    }

    /**
     * @return int|null HTTP-код ответа API
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return array|null Тело ответа API
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * @return string|null HTTP-метод (GET, POST, PATCH, PUT, DELETE)
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string|null API endpoint (напр. "/users")
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }
}
