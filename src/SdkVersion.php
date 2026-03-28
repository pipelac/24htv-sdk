<?php

namespace TwentyFourTv;

/**
 * Единственная точка определения версии SDK
 *
 * Используется во всех компонентах, которым нужна версия:
 * HttpClient (User-Agent), Client (getVersion), CHANGELOG и т.д.
 *
 * <code>
 * echo SdkVersion::VERSION;      // '1.0.0'
 * echo SdkVersion::userAgent();  // '24htv-sdk/1.0.0 PHP/8.1.0'
 * </code>
 *
 * @since 1.0.0
 */
final class SdkVersion
{
    /** @var string Текущая версия SDK */
    public const VERSION = '1.0.0';

    /**
     * Получить строку User-Agent для HTTP-запросов
     *
     * @return string
     */
    public static function userAgent()
    {
        return '24htv-sdk/' . self::VERSION . ' PHP/' . PHP_VERSION;
    }
}
