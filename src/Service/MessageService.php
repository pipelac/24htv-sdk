<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\MessageServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Message;

/**
 * Управление сообщениями пользователей 24часаТВ
 *
 * @since 1.0.0
 */
class MessageService extends AbstractService implements MessageServiceInterface
{
    /**
     * Получить список сообщений пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return Message[] Коллекция типизированных моделей сообщений
     */
    public function getAll($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            'TwentyFourTv\Model\Message',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_MESSAGES, $userId))
        );
    }

    /**
     * Получить сообщение по ID
     *
     * @param int    $userId
     * @param string $messageId
     *
     * @throws TwentyFourTvException
     *
     * @return Message Типизированная модель сообщения
     */
    public function getById($userId, $messageId)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\Message',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_MESSAGE_BY_ID, $userId, $messageId))
        );
    }

    /**
     * Создать сообщение для пользователя
     *
     * @param int   $userId
     * @param array $data   Данные сообщения
     *
     * @throws TwentyFourTvException
     *
     * @return Message Типизированная модель сообщения
     */
    public function create($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\Message',
            $this->api->apiPost(sprintf(ApiEndpoints::USER_MESSAGES, $userId), $data)
        );
    }

    /**
     * Удалить сообщение пользователя
     *
     * @param int    $userId
     * @param string $messageId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function delete($userId, $messageId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_MESSAGE_BY_ID, $userId, $messageId));
    }
}
