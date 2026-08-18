<?php

namespace TwentyFourTv\Model;

/**
 * DTO тега (группы) 24ТВ
 *
 * @since 1.0.0
 */
final class Tag extends AbstractModel
{
    /** @var string|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $shortname;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->name = $this->get($data, 'name');
        $this->shortname = $this->get($data, 'shortname');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'shortname' => $this->shortname,
        ];
    }

    /** @return string|null */
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
    public function getShortname()
    {
        return $this->shortname;
    }
}
