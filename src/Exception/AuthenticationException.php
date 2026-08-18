<?php

namespace TwentyFourTv\Exception;

/**
 * Ошибка аутентификации (HTTP 401)
 *
 * Бросается при невалидном или истёкшем TOKEN.
 *
 * @since 1.0.0
 */
final class AuthenticationException extends TwentyFourTvException
{
}
