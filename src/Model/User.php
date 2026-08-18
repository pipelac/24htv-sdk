<?php

namespace TwentyFourTv\Model;

/**
 * DTO пользователя 24ТВ
 *
 * <code>
 * $user = User::fromArray($client->users()->register([...]))
 * echo $user->getId();          // 42
 * echo $user->getUsername();    // 'user_12345'
 * echo $user->isActive();      // true
 * </code>
 *
 * @since 1.0.0
 */
final class User extends AbstractModel
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $phone;

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string|null */
    private $providerUid;

    /** @var bool */
    private $isActive = true;

    /** @var bool */
    private $isProviderFree = false;

    /** @var string|null Дата создания (ISO 8601) */
    private $createdAt;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->username = $this->get($data, 'username');
        $this->phone = $this->get($data, 'phone');
        $this->email = $this->get($data, 'email');
        $this->firstName = $this->get($data, 'first_name');
        $this->lastName = $this->get($data, 'last_name');
        $this->providerUid = $this->get($data, 'provider_uid');
        $this->isActive = (bool) $this->get($data, 'is_active', true);
        $this->isProviderFree = (bool) $this->get($data, 'is_provider_free', false);
        $this->createdAt = $this->get($data, 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'               => $this->id,
            'username'         => $this->username,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'first_name'       => $this->firstName,
            'last_name'        => $this->lastName,
            'provider_uid'     => $this->providerUid,
            'is_active'        => $this->isActive,
            'is_provider_free' => $this->isProviderFree,
            'created_at'       => $this->createdAt,
        ];
    }

    /** @return int|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|null */
    public function getUsername()
    {
        return $this->username;
    }

    /** @return string|null */
    public function getPhone()
    {
        return $this->phone;
    }

    /** @return string|null */
    public function getEmail()
    {
        return $this->email;
    }

    /** @return string|null */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /** @return string|null */
    public function getLastName()
    {
        return $this->lastName;
    }

    /** @return string|null */
    public function getProviderUid()
    {
        return $this->providerUid;
    }

    /** @return bool */
    public function isActive()
    {
        return $this->isActive;
    }

    /** @return bool */
    public function isProviderFree()
    {
        return $this->isProviderFree;
    }

    /** @return string|null */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
