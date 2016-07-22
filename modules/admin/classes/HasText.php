<?php

interface HasText
{
    /**
     * @return array
     * @internal param string $lang
     */
    public function get_all_texts();

    /**
     * @param $id
     * @param string $text
     * @return
     * @internal param string $lang
     */
    public function update_text_at_item_id($id, $text);

    /**
     * Возвращает заголовки записей по их ID (используется для навигации по записям), массив вида ID => заголовок
     *
     * @param int[] $ids
     * @return string[]
     * @internal param string $lang
     */
    public function get_titles_by_item_ids(array $ids);

    /**
     * Возвращает заголовок записи по её ID (используется в навигации по записям)
     *
     * @param $id
     * @return string
     * @internal param string $lang
     */
    public function get_title_by_item_id($id);

    /**
     * Возвращает запись, найденную по ID элемента
     * В результате обязательно присутствие ключей item_id, text
     *
     * @param int $id
     * @return array
     * @internal param string $lang
     */
    public function get_record_by_item_id($id);
}
