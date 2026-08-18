<?php

namespace TwentyFourTv\Contract\Service;

use TwentyFourTv\Model\Tag;

/**
 * Контракт сервиса управления тегами
 *
 * @since 1.0.0
 */
interface TagServiceInterface
{
    /** @return Tag[] */
    public function getAll(array $options = []);

    /** @return Tag */
    public function create(array $data);

    /** @return Tag */
    public function getById($tagId);

    /** @return Tag */
    public function update($tagId, array $data);

    /** @return mixed */
    public function delete($tagId);

    /** @return array */
    public function addToUser($userId, array $data);

    /** @return mixed */
    public function removeFromUser($userId, $tagId);
}
