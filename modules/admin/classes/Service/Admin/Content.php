<?php

abstract class Service_Admin_Content extends \BetaKiller\Service
{
    use BetaKiller\Helper\Admin;

    /**
     * Custom HTML-tag name related to current service
     * 
     * @return string
     */
    abstract public function get_html_custom_tag_name();


    /**
     * @return Model_AdminContentFile
     */
    abstract protected function file_model_factory();

    /**
     * @return Model_AdminContentEntity
     */
    protected function entity_model_factory()
    {
        return $this->model_factory_admin_content_entity();
    }

    /**
     * @return Database_Result|Model_AdminContentEntity[]
     */
    public function get_entities_list()
    {
        return $this->entity_model_factory()->get_all();
    }

    /**
     * @param int $id
     * @return Model_AdminContentEntity
     * @throws Kohana_Exception
     */
    public function find_entity_by_id($id)
    {
        return $this->entity_model_factory()->get_by_id($id);
    }

    public function find_entity_by_slug($slug)
    {
        return $this->entity_model_factory()->find_by_slug($slug);
    }

    public function get_entity_items_ids(Model_AdminContentEntity $entity)
    {
        return $this->file_model_factory()->get_entity_items_ids($entity);
    }

    public function make_custom_html_tag(Model_AdminContentFile $content, $target_width = NULL, $target_height = NULL, array $attributes = [])
    {
        $attributes += [
            'width'     =>  $target_width,
            'height'    =>  $target_height,
        ];

        return $this->custom_tag_instance()
            ->generate($this->get_html_custom_tag_name(), $content->get_id(), $attributes);
    }

    public function get_entity_items(Model_AdminContentEntity $entity)
    {
        $ids = $this->get_entity_items_ids($entity);

        return $entity->get_related_model_instance()->get_titles_by_item_ids($ids);
    }

    public function get_files_list(Model_AdminContentEntityRelated $entity, $entity_item_id = NULL)
    {
        $files = $entity->get_files($entity_item_id);
        $output = [];

        foreach ($files as $file)
        {
            $output[] = $file->as_array();
        }

        return $output;
    }

    /**
     * @param $file_id
     * @return Model_AdminContentFile
     * @throws Kohana_Exception
     */
    public function get_file_by_id($file_id)
    {
        return $this->file_model_factory()->get_by_id($file_id);
    }

    /**
     * @param int $id
     * @return Model_AdminContentFile|null
     */
    public function find_file_by_wp_id($id)
    {
        return $this->file_model_factory()->find_by_wp_id($id);
    }
}
