<?php

namespace TwentyFourTv\Model;

/**
 * DTO устройства 24ТВ
 *
 * @since 1.0.0
 */
final class Device extends AbstractModel
{
    /** @var string|null */
    private $id;

    /** @var string|null Серийный номер */
    private $serial;

    /** @var string|null Тип устройства */
    private $deviceType;

    /** @var string|null MAC-адрес */
    private $interfaceMac;

    /** @var string|null */
    private $providerUid;

    /** @var string|null Дата создания */
    private $createdAt;

    /** @var string|null Дата последнего входа */
    private $lastLoginAt;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->serial = $this->get($data, 'serial');
        $this->deviceType = $this->get($data, 'device_type');
        $this->interfaceMac = $this->get($data, 'interface_mac');
        $this->providerUid = $this->get($data, 'provider_uid');
        $this->createdAt = $this->get($data, 'created_at');
        $this->lastLoginAt = $this->get($data, 'last_login_at');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'            => $this->id,
            'serial'        => $this->serial,
            'device_type'   => $this->deviceType,
            'interface_mac' => $this->interfaceMac,
            'provider_uid'  => $this->providerUid,
            'created_at'    => $this->createdAt,
            'last_login_at' => $this->lastLoginAt,
        ];
    }

    /** @return string|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|null */
    public function getSerial()
    {
        return $this->serial;
    }

    /** @return string|null */
    public function getDeviceType()
    {
        return $this->deviceType;
    }

    /** @return string|null */
    public function getInterfaceMac()
    {
        return $this->interfaceMac;
    }

    /** @return string|null */
    public function getProviderUid()
    {
        return $this->providerUid;
    }

    /** @return string|null */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /** @return string|null */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }
}
