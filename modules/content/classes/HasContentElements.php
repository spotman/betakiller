<?php

interface HasContentElements
{
    /**
     * Возвращает ID из таблицы content_entities, к которому привязана текущая модель
     *
     * @return int
     */
    public function get_content_entity_id();
}
