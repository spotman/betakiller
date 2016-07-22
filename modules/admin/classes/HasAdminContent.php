<?php

interface HasAdminContent
{
    /**
     * Возвращает ID из таблицы admin_content_entities, к которому привязана текущая модель
     *
     * @return int
     */
    public function get_admin_content_entity_id();
}
