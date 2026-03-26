<?php

namespace TwentyFourTv\Model;

/**
 * DTO канала 24ТВ
 *
 * @since 1.0.0
 */
final class Channel extends AbstractModel
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var int|null Порядковый номер */
    private $number;

    /** @var string|null URL логотипа */
    private $logoUrl;

    /** @var string|null Категория */
    private $category;

    /** @var bool */
    private $isActive = true;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->name = $this->get($data, 'name');
        $this->number = $this->get($data, 'number');
        $this->logoUrl = $this->get($data, 'logo_url');
        $this->category = $this->get($data, 'category');
        $this->isActive = (bool) $this->get($data, 'is_active', true);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'number'    => $this->number,
            'logo_url'  => $this->logoUrl,
            'category'  => $this->category,
            'is_active' => $this->isActive,
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

    /** @return int|null */
    public function getNumber()
    {
        return $this->number;
    }

    /** @return string|null */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /** @return string|null */
    public function getCategory()
    {
        return $this->category;
    }

    /** @return bool */
    public function isActive()
    {
        return $this->isActive;
    }
}
