<?php

namespace TwentyFourTv\Model;

/**
 * DTO сообщения 24ТВ
 *
 * @since 1.0.0
 */
final class Message extends AbstractModel
{
    /** @var string|null */
    private $id;

    /** @var string|null */
    private $title;

    /** @var string|null */
    private $text;

    /** @var string|null Дата создания */
    private $createdAt;

    /** @var bool Прочитано */
    private $isRead = false;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->title = $this->get($data, 'title');
        $this->text = $this->get($data, 'text');
        $this->createdAt = $this->get($data, 'created_at');
        $this->isRead = (bool) $this->get($data, 'is_read', false);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'text'       => $this->text,
            'created_at' => $this->createdAt,
            'is_read'    => $this->isRead,
        ];
    }

    /** @return string|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return string|null */
    public function getTitle()
    {
        return $this->title;
    }

    /** @return string|null */
    public function getText()
    {
        return $this->text;
    }

    /** @return string|null */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /** @return bool */
    public function isRead()
    {
        return $this->isRead;
    }
}
