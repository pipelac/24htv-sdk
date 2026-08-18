<?php

namespace TwentyFourTv\Model;

/**
 * DTO подписки 24ТВ
 *
 * <code>
 * $sub = Subscription::fromArray($client->subscriptions()->getCurrent($userId)[0]);
 * echo $sub->getPacketId();    // 123
 * echo $sub->isRenew();       // true
 * echo $sub->getStartAt();    // '2026-03-01T00:00:00.000Z'
 * </code>
 *
 * @since 1.0.0
 */
final class Subscription extends AbstractModel
{
    /** @var string|null UUID подписки */
    private $id;

    /** @var int|null ID пакета */
    private $packetId;

    /** @var string|null Дата начала (ISO 8601) */
    private $startAt;

    /** @var string|null Дата окончания (ISO 8601) */
    private $endAt;

    /** @var bool Автопродление */
    private $renew = true;

    /** @var bool На паузе ли подписка */
    private $isPaused = false;

    /** @var string|null Статус подписки */
    private $status;

    /** @var array|null Информация о пакете (если запрошена через includes) */
    private $packet;

    /**
     * {@inheritdoc}
     */
    protected function hydrate(array $data)
    {
        $this->id = $this->get($data, 'id');
        $this->packetId = $this->get($data, 'packet_id');
        $this->startAt = $this->get($data, 'start_at');
        $this->endAt = $this->get($data, 'end_at');
        $this->renew = (bool) $this->get($data, 'renew', true);
        $this->isPaused = (bool) $this->get($data, 'is_paused', false);
        $this->status = $this->get($data, 'status');
        $this->packet = $this->get($data, 'packet');
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id'        => $this->id,
            'packet_id' => $this->packetId,
            'start_at'  => $this->startAt,
            'end_at'    => $this->endAt,
            'renew'     => $this->renew,
            'is_paused' => $this->isPaused,
            'status'    => $this->status,
            'packet'    => $this->packet,
        ];
    }

    /** @return string|null */
    public function getId()
    {
        return $this->id;
    }

    /** @return int|null */
    public function getPacketId()
    {
        return $this->packetId;
    }

    /** @return string|null */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /** @return string|null */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /** @return bool */
    public function isRenew()
    {
        return $this->renew;
    }

    /** @return bool */
    public function isPaused()
    {
        return $this->isPaused;
    }

    /** @return string|null */
    public function getStatus()
    {
        return $this->status;
    }

    /** @return array|null */
    public function getPacket()
    {
        return $this->packet;
    }
}
