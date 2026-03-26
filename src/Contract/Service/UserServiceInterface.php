<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\User;

/**
 * Контракт сервиса управления пользователями
 *
 * @since 1.0.0
 */
interface UserServiceInterface
{
    /**
     * @param array $data
     *
     * @return User
     */
    public function register(array $data);

    /**
     * @param int   $userId
     * @param array $data
     *
     * @return User
     */
    public function update($userId, array $data);

    /**
     * @param int $userId
     *
     * @return User
     */
    public function getById($userId);

    /**
     * @param array $options
     *
     * @return User[]
     */
    public function getAll(array $options = []);

    /**
     * @param string $phone
     *
     * @return User[]
     */
    public function findByPhone($phone);

    /**
     * @param string $username
     *
     * @return User[]
     */
    public function findByUsername($username);

    /**
     * @param string $providerUid
     *
     * @return User[]
     */
    public function findByProviderUid($providerUid);

    /**
     * @param string $email
     *
     * @return User[]
     */
    public function findByEmail($email);

    /**
     * @param string $query
     * @param int    $limit
     * @param int    $offset
     *
     * @return User[]
     */
    public function search($query, $limit = 20, $offset = 0);

    /**
     * @param int $userId
     *
     * @return User
     */
    public function block($userId);

    /**
     * @param int $userId
     *
     * @return User
     */
    public function unblock($userId);

    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function delete($userId);

    /**
     * @param int $userId
     *
     * @return User
     */
    public function archive($userId);
}
