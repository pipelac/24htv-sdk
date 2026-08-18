<?php

namespace TwentyFourTv\Exception;

/**
 * Конфликт данных (HTTP 409)
 *
 * Бросается при попытке создать дубликат (например, повторная регистрация пользователя).
 *
 * @since 1.0.0
 */
final class ConflictException extends TwentyFourTvException
{
}
