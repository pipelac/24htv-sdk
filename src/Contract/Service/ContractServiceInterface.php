<?php

namespace TwentyFourTv\Contract\Service;

/**
 * Контракт сервиса расторжения договоров
 *
 * @since 1.0.0
 */
interface ContractServiceInterface
{
    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function terminate($userId);
}
