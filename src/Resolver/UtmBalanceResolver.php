<?php

namespace TwentyFourTv\Resolver;

use Exception;
use TwentyFourTv\Contract\BalanceResolverInterface;
use TwentyFourTv\Contract\DatabaseInterface;
use TwentyFourTv\Contract\LoggerInterface;

/**
 * Резолвер баланса для биллинга UTM5
 *
 * Получает баланс абонента из таблицы UTM5 `accounts`.
 *
 * <code>
 * $resolver = new UtmBalanceResolver($db, $logger);
 * $handler->setBalanceResolver($resolver);
 * </code>
 *
 * @since 1.1.0
 */
class UtmBalanceResolver implements BalanceResolverInterface
{
    /** @var DatabaseInterface */
    private $db;

    /** @var LoggerInterface|null */
    private $logger;

    /**
     * @param DatabaseInterface    $db     Соединение с UTM БД
     * @param LoggerInterface|null $logger Логгер
     */
    public function __construct(DatabaseInterface $db, LoggerInterface $logger = null)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $params)
    {
        $providerUid = isset($params['provider_uid']) ? $params['provider_uid'] : null;

        if ($providerUid === null) {
            return [
                'result' => 'error',
                'errmsg' => 'provider_uid is required',
            ];
        }

        try {
            $row = $this->db->queryOne(
                'SELECT balance FROM accounts WHERE id = ? AND is_deleted = 0 LIMIT 1',
                [$providerUid]
            );

            if ($row) {
                return [
                    'result'  => 'success',
                    'balance' => (string) round((float) $row['balance'], 2),
                ];
            }

            return [
                'result' => 'error',
                'errmsg' => 'Account not found',
            ];
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('24HTV BALANCE: ошибка БД', [
                    'provider_uid' => $providerUid,
                    'error'        => $e->getMessage(),
                ]);
            }

            return [
                'result' => 'error',
                'errmsg' => 'Database error',
            ];
        }
    }
}
