<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт логгера SDK
 *
 * Совместим по сигнатуре с PSR-3 LoggerInterface,
 * но без зависимости от пакета psr/log.
 *
 * <code>
 * class MyLogger implements LoggerInterface
 * {
 *     public function info($message, array $context = array()) {
 *         file_put_contents('log.txt', $message . "\n", FILE_APPEND);
 *     }
 * }
 * </code>
 *
 * @since 1.0.0
 */
interface LoggerInterface
{
    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = []);

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = []);

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = []);

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = []);
}
