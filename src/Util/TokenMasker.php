<?php

namespace TwentyFourTv\Util;

/**
 * Утилита для безопасного маскирования токенов в логах
 *
 * <code>
 * echo TokenMasker::mask('abc123xyz789');  // 'abc123***9789'
 * echo TokenMasker::maskInUrl('https://api.com?token=secret'); // 'https://api.com?token=***'
 * </code>
 *
 * @since 1.0.0
 */
class TokenMasker
{
    /**
     * Замаскировать токен: оставить первые 6 и последние 4 символа
     *
     * @param string $token
     *
     * @return string
     */
    public static function mask($token)
    {
        if (strlen($token) <= 10) {
            return '***';
        }

        return substr($token, 0, 6) . '***' . substr($token, -4);
    }

    /**
     * Замаскировать токен в URL-строке
     *
     * @param string $url
     *
     * @return string
     */
    public static function maskInUrl($url)
    {
        return preg_replace('/token=[a-z0-9]+/i', 'token=***', $url);
    }
}
