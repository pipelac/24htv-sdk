<?php

namespace TwentyFourTv\Model;

/**
 * DTO транзакции
 *
 * @since 1.0.0
 */
final class Transaction extends AbstractModel
{
    /** @var string|null */
    private $id;

    /** @var string|null Сумма */
    private $amount;

    /** @var string|null Тип транзакции */
    private $type;

    /** @var string|null */
    private $description;

    /** @var string|null Дата создания */
    private $createdAt;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->amount = $this->get($data, 'amount');
        $this->type = $this->get($data, 'type');
        $this->description = $this->get($data, 'description');
        $this->createdAt = $this->get($data, 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'type'        => $this->type,
            'description' => $this->description,
            'created_at'  => $this->createdAt,
        ];
    }

    /** @return string|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|null */
    public function getAmount()
    {
        return $this->amount;
    }

    /** @return string|null */
    public function getType()
    {
        return $this->type;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }

    /** @return string|null */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
