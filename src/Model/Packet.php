<?php

namespace TwentyFourTv\Model;

/**
 * DTO пакета (тарифа) 24ТВ
 *
 * <code>
 * $packet = Packet::fromArray($client->packets()->getById(123));
 * echo $packet->getName();    // 'Базовый'
 * echo $packet->isBase();     // true
 * echo $packet->getPrice();   // '199.00'
 * </code>
 *
 * @since 1.0.0
 */
final class Packet extends AbstractModel
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var string|null Цена (строка для точности) */
    private $price;

    /** @var string|null */
    private $description;

    /** @var bool Базовый пакет (взаимоисключающий) */
    private $isBase = false;

    /** @var bool Активен ли пакет */
    private $isActive = true;

    /** @var array Каналы пакета (если запрошены через includes) */
    private $channels = [];

    /** @var array Доступные дополнительные пакеты (если запрошены) */
    private $availables = [];

    /** @var array Включённые дополнительные пакеты (если запрошены) */
    private $includes = [];

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->name = $this->get($data, 'name');
        $this->price = $this->get($data, 'price');
        $this->description = $this->get($data, 'description');
        $this->isBase = (bool) $this->get($data, 'is_base', false);
        $this->isActive = (bool) $this->get($data, 'is_active', true);
        $this->channels = $this->get($data, 'channels', []);
        $this->availables = $this->get($data, 'availables', []);
        $this->includes = $this->get($data, 'includes', []);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'price'       => $this->price,
            'description' => $this->description,
            'is_base'     => $this->isBase,
            'is_active'   => $this->isActive,
            'channels'    => $this->channels,
            'availables'  => $this->availables,
            'includes'    => $this->includes,
        ];
    }

    /** @return int|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|null */
    public function getName()
    {
        return $this->name;
    }

    /** @return string|null */
    public function getPrice()
    {
        return $this->price;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }

    /** @return bool */
    public function isBase()
    {
        return $this->isBase;
    }

    /** @return bool */
    public function isActive()
    {
        return $this->isActive;
    }

    /** @return array */
    public function getChannels()
    {
        return $this->channels;
    }

    /** @return array */
    public function getAvailables()
    {
        return $this->availables;
    }

    /** @return array */
    public function getIncludes()
    {
        return $this->includes;
    }
}
