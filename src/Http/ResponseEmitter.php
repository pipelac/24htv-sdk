<?php

namespace TwentyFourTv\Http;

use TwentyFourTv\Callback\CallbackResponse;

/**
 * Отправка HTTP-ответа клиенту
 *
 * Разделение ответственности: CallbackResponse — DTO, ResponseEmitter — I/O.
 * Поддерживает тестирование без отправки реальных заголовков.
 *
 * <code>
 * $response = $handler->handle('/auth', ['ip' => '10.0.0.1']);
 * ResponseEmitter::emit($response);
 * </code>
 *
 * @since 1.1.0
 */
class ResponseEmitter
{
    /**
     * Отправить CallbackResponse как HTTP-ответ
     *
     * @param CallbackResponse $response
     *
     * @return void
     */
    public static function emit(CallbackResponse $response)
    {
        if (!headers_sent()) {
            http_response_code($response->getHttpCode());
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate');
        }

        echo json_encode($response->getData(), JSON_UNESCAPED_UNICODE);
    }
}
