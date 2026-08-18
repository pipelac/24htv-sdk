<?php

namespace TwentyFourTv\Model;

/**
 * DTO баланса / аккаунта провайдера
 *
 * @since 1.0.0
 */
final class Balance extends AbstractModel
{
    /** @var string|null ID аккаунта в биллинге */
    private $id;

    /** @var string|null Сумма баланса */
    private $amount;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->amount = $this->get($data, 'amount');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'     => $this->id,
            'amount' => $this->amount,
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
}
