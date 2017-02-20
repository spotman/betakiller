<?php

use BetaKiller\Content\ContentElementInterface;

trait Model_ORM_ContentElementTrait
{
    use BetaKiller\Helper\CurrentUserTrait;
    use Model_ORM_ContentRelatedTrait;

    /**
     * @return Database_Result|ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_all_files()
    {
        if (!$this->current_user(TRUE))
        {
            // Кешируем запрос для всех кроме админов
            $this->cached();
        }

        return $this->find_all();
    }
}
