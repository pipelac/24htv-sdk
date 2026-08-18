<?php

namespace TwentyFourTv\Exception;

/**
 * Ошибка валидации (HTTP 400, 422)
 *
 * Бросается при невалидных параметрах запроса или отсутствии обязательных полей.
 *
 * @since 1.0.0
 */
final class ValidationException extends TwentyFourTvException
{
}
