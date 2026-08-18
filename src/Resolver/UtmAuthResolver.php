<?php

namespace TwentyFourTv\Resolver;

use Exception;
use TwentyFourTv\Contract\AuthResolverInterface;
use TwentyFourTv\Contract\DatabaseInterface;
use TwentyFourTv\Contract\LoggerInterface;

/**
 * Резолвер авторизации для биллинга UTM5
 *
 * Ищет абонента по IP-адресу в таблицах UTM5:
 * ip_groups → service_links → users_accounts → users
 *
 * <code>
 * $resolver = new UtmAuthResolver($db, $logger);
 * $handler->setAuthResolver($resolver);
 * </code>
 *
 * @since 1.1.0
 */
class UtmAuthResolver implements AuthResolverInterface
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
        $ip = isset($params['ip']) ? $params['ip'] : null;

        if (empty($ip)) {
            return [
                'result' => 'error',
                'errmsg' => 'IP address is required',
            ];
        }

        try {
            $row = $this->db->queryOne(
                'SELECT ua.account_id, u.login
                 FROM ip_groups ig
                 JOIN service_links sl ON sl.id = ig.ip_group_id
                 JOIN users_accounts ua ON ua.account_id = sl.account_id AND ua.is_deleted = 0
                 JOIN users u ON u.id = ua.uid AND u.is_deleted = 0
                 WHERE ig.ip = INET_ATON(?) AND ig.is_deleted = 0
                 LIMIT 1',
                [$ip]
            );

            if ($row) {
                if ($this->logger) {
                    $this->logger->info('24HTV AUTH: абонент найден по IP', [
                        'ip'         => $ip,
                        'account_id' => $row['account_id'],
                        'login'      => $row['login'],
                    ]);
                }

                return [
                    'result'       => 'success',
                    'provider_uid' => (string) $row['account_id'],
                ];
            }

            if ($this->logger) {
                $this->logger->info('24HTV AUTH: абонент не найден', ['ip' => $ip]);
            }

            return [
                'result' => 'error',
                'errmsg' => 'User not found',
            ];
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->error('24HTV AUTH: ошибка БД', [
                    'ip'    => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'result' => 'error',
                'errmsg' => 'Database error',
            ];
        }
    }
}
