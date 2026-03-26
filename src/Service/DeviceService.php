<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\DeviceServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Device;

/**
 * Управление устройствами пользователей 24часаТВ
 *
 * @since 1.0.0
 */
class DeviceService extends AbstractService implements DeviceServiceInterface
{
    /**
     * Получить список устройств провайдера
     *
     * @param array $options ['provider_uid' => string, 'serial' => string, 'limit' => int, 'offset' => int, ...]
     *
     * @throws TwentyFourTvException
     *
     * @return Device[] Коллекция типизированных моделей устройств
     */
    public function getAll(array $options = [])
    {
        return $this->createCollection(
            'TwentyFourTv\Model\Device',
            $this->api->apiGet(ApiEndpoints::DEVICES, $options)
        );
    }

    /**
     * Создать устройство
     *
     * @param array $data Данные устройства
     *
     * @throws TwentyFourTvException
     *
     * @return Device Типизированная модель созданного устройства
     */
    public function create(array $data)
    {
        return $this->createModel(
            'TwentyFourTv\Model\Device',
            $this->api->apiPost(ApiEndpoints::DEVICES, $data)
        );
    }

    /**
     * Получить список устройств пользователя
     *
     * @param int $userId
     *
     * @throws TwentyFourTvException
     *
     * @return Device[] Коллекция типизированных моделей устройств
     */
    public function getUserDevices($userId)
    {
        $this->requireId($userId, 'userId');

        return $this->createCollection(
            'TwentyFourTv\Model\Device',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_DEVICES, $userId))
        );
    }

    /**
     * Получить устройство пользователя по ID
     *
     * @param int    $userId
     * @param string $deviceId
     *
     * @throws TwentyFourTvException
     *
     * @return Device Типизированная модель устройства
     */
    public function getUserDevice($userId, $deviceId)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\Device',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_DEVICE_BY_ID, $userId, $deviceId))
        );
    }

    /**
     * Получить устройство пользователя по access_token
     *
     * @param int    $userId
     * @param string $accessToken
     *
     * @throws TwentyFourTvException
     *
     * @return Device Типизированная модель устройства
     */
    public function getUserDeviceByToken($userId, $accessToken)
    {
        $this->requireId($userId, 'userId');

        return $this->createModel(
            'TwentyFourTv\Model\Device',
            $this->api->apiGet(sprintf(ApiEndpoints::USER_DEVICE_BY_TOKEN, $userId), [
                'access_token' => $accessToken,
            ])
        );
    }

    /**
     * Удалить устройство пользователя
     *
     * @param int    $userId
     * @param string $deviceId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function deleteUserDevice($userId, $deviceId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_DEVICE_BY_ID, $userId, $deviceId));
    }
}
