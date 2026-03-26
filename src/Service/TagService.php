<?php

namespace TwentyFourTv\Service;

use TwentyFourTv\ApiEndpoints;
use TwentyFourTv\Contract\Service\TagServiceInterface;
use TwentyFourTv\Exception\TwentyFourTvException;
use TwentyFourTv\Model\Tag;

/**
 * Управление тегами (группами) пользователей 24часаТВ
 *
 * @since 1.0.0
 */
class TagService extends AbstractService implements TagServiceInterface
{
    /**
     * Получить список тегов
     *
     * @param array $options ['name' => string, 'search' => string, 'shortname' => string]
     *
     * @throws TwentyFourTvException
     *
     * @return Tag[] Коллекция типизированных моделей тегов
     */
    public function getAll(array $options = [])
    {
        return $this->createCollection(
            'TwentyFourTv\Model\Tag',
            $this->api->apiGet(ApiEndpoints::TAGS, $options)
        );
    }

    /**
     * Создать тег
     *
     * @param array $data
     *
     * @throws TwentyFourTvException
     *
     * @return Tag Типизированная модель тега
     */
    public function create(array $data)
    {
        return $this->createModel(
            'TwentyFourTv\Model\Tag',
            $this->api->apiPost(ApiEndpoints::TAGS, $data)
        );
    }

    /**
     * Получить тег по ID
     *
     * @param string $tagId
     *
     * @throws TwentyFourTvException
     *
     * @return Tag Типизированная модель тега
     */
    public function getById($tagId)
    {
        $this->requireId($tagId, 'tagId');

        return $this->createModel(
            'TwentyFourTv\Model\Tag',
            $this->api->apiGet(sprintf(ApiEndpoints::TAG_BY_ID, $tagId))
        );
    }

    /**
     * Изменить тег
     *
     * @param string $tagId
     * @param array  $data
     *
     * @throws TwentyFourTvException
     *
     * @return Tag Обновлённая модель тега
     */
    public function update($tagId, array $data)
    {
        $this->requireId($tagId, 'tagId');

        return $this->createModel(
            'TwentyFourTv\Model\Tag',
            $this->api->apiPatch(sprintf(ApiEndpoints::TAG_BY_ID, $tagId), $data)
        );
    }

    /**
     * Удалить тег
     *
     * @param string $tagId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function delete($tagId)
    {
        $this->requireId($tagId, 'tagId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::TAG_BY_ID, $tagId));
    }

    /**
     * Добавить тег к пользователю
     *
     * @param int   $userId
     * @param array $data
     *
     * @throws TwentyFourTvException
     *
     * @return array
     */
    public function addToUser($userId, array $data)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiPost(sprintf(ApiEndpoints::USER_TAGS, $userId), $data);
    }

    /**
     * Удалить тег у пользователя
     *
     * @param int    $userId
     * @param string $tagId
     *
     * @throws TwentyFourTvException
     *
     * @return mixed
     */
    public function removeFromUser($userId, $tagId)
    {
        $this->requireId($userId, 'userId');

        return $this->api->apiDelete(sprintf(ApiEndpoints::USER_TAG_BY_ID, $userId, $tagId));
    }
}
