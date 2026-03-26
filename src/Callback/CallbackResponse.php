<?php

namespace TwentyFourTv\Callback;

/**
 * DTO ответа на callback от 24ТВ
 *
 * Вместо прямого вызова header()/echo в CallbackHandler,
 * используется объект ответа, который может быть обработан вызывающим кодом.
 *
 * <code>
 * $response = $handler->handle($uri, $params, $body);
 * http_response_code($response->getHttpCode());
 * header('Content-Type: application/json');
 * echo $response->toJson();
 * </code>
 *
 * @since 1.0.0
 */
class CallbackResponse
{
    /** @var array Данные ответа */
    private $data;

    /** @var int HTTP-код ответа */
    private $httpCode;

    /**
     * @param array $data     Данные для JSON-ответа
     * @param int   $httpCode HTTP-код ответа (по умолчанию 200)
     */
    public function __construct(array $data, $httpCode = 200)
    {
        $this->data = $data;
        $this->httpCode = $httpCode;
    }

    /**
     * @return array Данные ответа
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int HTTP-код
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @return bool Успешный ли ответ
     */
    public function isSuccess()
    {
        return isset($this->data['result']) && $this->data['result'] === 'success';
    }

    /**
     * Сериализовать ответ в JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Отправить ответ клиенту (для обратной совместимости)
     *
     * @return void
     */
    public function send()
    {
        if (!headers_sent()) {
            http_response_code($this->httpCode);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo $this->toJson();
    }

    /**
     * Фабрика: успешный ответ
     *
     * @param array $data Дополнительные данные
     *
     * @return self
     */
    public static function success(array $data = [])
    {
        return new self(array_merge(['result' => 'success'], $data));
    }

    /**
     * Фабрика: ответ с ошибкой
     *
     * @param string $message  Сообщение об ошибке
     * @param int    $httpCode
     *
     * @return self
     */
    public static function error($message, $httpCode = 200)
    {
        return new self([
            'result' => 'error',
            'errmsg' => $message,
        ], $httpCode);
    }
}
