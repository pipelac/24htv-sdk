<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Message;

/**
 * Контракт сервиса управления сообщениями
 *
 * @since 1.0.0
 */
interface MessageServiceInterface
{
    /** @return Message[] */
    public function getAll($userId);

    /** @return Message */
    public function getById($userId, $messageId);

    /** @return Message */
    public function create($userId, array $data);

    /** @return mixed */
    public function delete($userId, $messageId);
}
