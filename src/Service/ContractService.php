<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\ContractServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;

/**
 * Расторжение договора — перевод абонента на оплату через 24ТВ
 *
 * @since 1.0.0
 */
class ContractService extends AbstractService implements ContractServiceInterface
{
    /**
     * Расторжение договора с абонентом
     *
     * Переводит абонента к провайдеру «24ТВ» (id=1),
     * обеспечивая возможность оплаты банковской картой напрямую.
     *
     * @param int $userId ID пользователя в платформе 24ТВ
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function terminate($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('warning', '24HTV: расторжение договора', [
            'userId' => $userId,
        ]);

        return $this->api->apiPut(sprintf(ApiEndpoints::USER_CHANGE_PROVIDER, $userId));
    }
}
