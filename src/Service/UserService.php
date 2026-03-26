<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\UserServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\User;

/**
 * Управление пользователями платформы 24часаТВ
 *
 * <code>
 * $user = $client->users()->register([
 *     'username' => 'ivan_petrov',
 *     'phone'    => '+79001234567',
 *     'provider_uid' => '12345',
 * ]);
 * echo $user->getId();          // 42
 * echo $user->getUsername();    // 'ivan_petrov'
 * echo $user->isActive();      // true
 * </code>
 *
 * @since 1.0.0
 */
class UserService extends AbstractService implements UserServiceInterface
{
    /**
     * Зарегистрировать нового пользователя
     *
     * @param array $data [
     *                    'username'     => string (обязательно),
     *                    'phone'        => string (обязательно),
     *                    'first_name'   => string,
     *                    'last_name'    => string,
     *                    'email'        => string,
     *                    'provider_uid' => string (ID абонента в биллинге),
     *                    'password'     => string,
     *                    ]
     *
     * @throws TwentyFourTvException
     *
     * @return User Типизированная модель созданного пользователя
     */
    public function register(array $data)
    {
        $this->requireFields($data, ['username', 'phone']);

        $this->log('info', '24HTV: регистрация пользователя', [
            'username'     => $data['username'],
            'phone'        => $data['phone'],
            'provider_uid' => isset($data['provider_uid']) ? $data['provider_uid'] : null,
        ]);

        return $this->createModel(
            'TwentyFourTv\Model\User',
            $this->api->apiPost(ApiEndpoints::USERS, $data)
        );
    }

    /**
     * Обновить данные пользователя
     *
     * @param int   $userId ID пользователя в 24ТВ
     * @param array $data   Обновляемые поля
     *
     * @throws TwentyFourTvException
     *
     * @return User Обновлённая модель пользователя
     */
    public function update($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\User',
            $this->api->apiPatch(sprintf(ApiEndpoints::USER_BY_ID, $userId), $data)
        );
    }

    /**
     * Получить пользователя по ID
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return User
     */
    public function getById($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_BY_ID, $userId))
        );
    }

    /**
     * Получить список пользователей с фильтрацией
     *
     * @param array $options [
     *                       'phone'        => string,
     *                       'username'     => string,
     *                       'provider_uid' => string,
     *                       'search'       => string,
     *                       'limit'        => int (1-100),
     *                       'offset'       => int,
     *                       ]
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function getAll(array $options = [])
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, $options)
        );
    }

    /**
     * Найти пользователя по номеру телефона
     *
     * @param string $phone
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function findByPhone($phone)
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, ['phone' => $phone])
        );
    }

    /**
     * Найти пользователя по username
     *
     * @param string $username
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function findByUsername($username)
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, ['username' => $username])
        );
    }

    /**
     * Найти пользователя по provider_uid (ID в биллинге провайдера)
     *
     * @param string $providerUid
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function findByProviderUid($providerUid)
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, ['provider_uid' => $providerUid])
        );
    }

    /**
     * Найти пользователя по email
     *
     * @param string $email
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function findByEmail($email)
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, ['email' => $email])
        );
    }

    /**
     * Полнотекстовый поиск пользователей
     *
     * @param string $query  Строка поиска
     * @param int    $limit  Лимит результатов
     * @param int    $offset Сдвиг
     *
     * @throws TwentyFourTvException
     *
     * @return User[] Коллекция типизированных моделей пользователей
     */
    public function search($query, $limit = 20, $offset = 0)
    {
        return $this->createCollection(
            'TwentyFourTv\Model\User',
            $this->api->apiGet(ApiEndpoints::USERS, [
                'search' => $query,
                'limit'  => $limit,
                'offset' => $offset,
            ])
        );
    }

    /**
     * Заблокировать пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return User Обновлённая модель пользователя
     */
    public function block($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('warning', '24HTV: блокировка пользователя', ['userId' => $userId]);

        return $this->createModel(
            'TwentyFourTv\Model\User',
            $this->api->apiPatch(sprintf(ApiEndpoints::USER_BY_ID, $userId), ['is_active' => false])
        );
    }

    /**
     * Разблокировать пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return User Обновлённая модель пользователя
     */
    public function unblock($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\User',
            $this->api->apiPatch(sprintf(ApiEndpoints::USER_BY_ID, $userId), ['is_active' => true])
        );
    }

    /**
     * Удалить пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function delete($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('warning', '24HTV: удаление пользователя', ['userId' => $userId]);

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_BY_ID, $userId));
    }

    /**
     * Архивировать пользователя
     *
     * Отправляет DELETE /users/{id}/archive.
     * Аналогично деактивации, но с сохранением истории для возможного восстановления или отчетности (зависит от логики платформы).
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed пустой ответ (HTTP 204)
     */
    public function archive($userId)
    {
        $this->requireId($userId, 'userId');

        $this->log('info', '24HTV: архивирование пользователя', ['userId' => $userId]);

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_ARCHIVE, $userId));
    }
}
