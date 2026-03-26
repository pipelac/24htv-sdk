<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Device;

/**
 * Контракт сервиса управления устройствами
 *
 * @since 1.0.0
 */
interface DeviceServiceInterface
{
    /** @return Device[] */
    public function getAll(array $options = []);

    /** @return Device */
    public function create(array $data);

    /** @return Device[] */
    public function getUserDevices($userId);

    /** @return Device */
    public function getUserDevice($userId, $deviceId);

    /** @return Device */
    public function getUserDeviceByToken($userId, $accessToken);

    /** @return mixed */
    public function deleteUserDevice($userId, $deviceId);
}
