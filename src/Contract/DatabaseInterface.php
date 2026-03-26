<?php

namespace TwentyFourTv\Contract;

/**
 * Контракт соединения с биллинговой базой данных
 *
 * Используется в резолверах (UtmAuthResolver, UtmBalanceResolver)
 * для авторизации абонентов и получения баланса из биллинга провайдера.
 *
 * <code>
 * class UtmDatabase implements DatabaseInterface
 * {
 *     public function queryOne($sql, array $params = array()) {
 *         // выполнить SQL и вернуть одну строку
 *     }
 *     public function query($sql, array $params = array()) {
 *         // выполнить SQL и вернуть массив строк
 *     }
 * }
 * </code>
 *
 * @since 1.0.0
 */
interface DatabaseInterface
{
    /**
     * Выполнить SQL-запрос и вернуть одну строку
     *
     * @param string $sql    SQL-запрос с плейсхолдерами (?)
     * @param array  $params Параметры для подстановки
     *
     * @return array|null Ассоциативный массив или null если результат пуст
     */
    public function queryOne($sql, array $params = []);

    /**
     * Выполнить SQL-запрос и вернуть все строки
     *
     * @param string $sql    SQL-запрос с плейсхолдерами (?)
     * @param array  $params Параметры для подстановки
     *
     * @return array Массив ассоциативных массивов
     */
    public function query($sql, array $params = []);
}
