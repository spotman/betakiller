<?php

namespace BetaKiller\Content\IFace;

abstract class ContentPostBase extends Base
{
    /**
     * @var \Model_ContentItem
     */
    private $content_model;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [
            'post' =>  $this->get_post_data(),
        ];
    }

    protected function get_post_data()
    {
//        echo \Debug::vars($this->url_dispatcher()->parameters());

        $model = $this->get_content_model();

        $this->set_last_modified($model->get_last_modified());

        $user = $this->current_user(TRUE);

        // Count guest views only
        if (!$user) {
            $model->increment_views_count()->save();
        }

        $thumbnails = [];

        foreach ($model->get_thumbnails() as $thumb) {
            $thumbnails[] = $thumb->get_arguments_for_img_tag($thumb::SIZE_ORIGINAL);
            // TODO get image last_modified and set it to iface
        }

        return [
            'id'            =>  $model->get_id(),
            'label'         =>  $model->get_label(),
            'content'       =>  $model->get_content(),
            'created_at'    =>  $model->get_created_at(),
            'updated_at'    =>  $model->get_updated_at(),
            'thumbnails'    =>  $thumbnails,
            'is_page'       =>  $model->is_page(),
        ];
    }

    /**
     * @return \DateInterval
     */
    public function get_default_expires_interval()
    {
        return new \DateInterval('P1D'); // One day
    }

    /**
     * @return \Model_ContentItem
     * @throws \IFace_Exception
     */
    protected function detect_content_model()
    {
        $model_url_key = $this->get_content_model_url_key();
//
//        if ( $this->is_default() )
//        {
//            $uri = $this->get_index_uri();
//
//            $model = $this->content_model_factory()->filter_uri($uri)->find();
//
//            if (!$model->loaded())
//                throw new \IFace_Exception('Can not find default content with uri :value', [':value' => $uri]);
//
//            $this->url_parameters()->set($model_url_key, $model);
//        }
//        else
//        {
//        }

        return $this->url_parameters()->get($model_url_key);
    }

    /**
     * @return \Model_ContentItem
     */
    protected function get_content_model()
    {
        if (!$this->content_model)
        {
            $this->content_model = $this->detect_content_model();
        }

        return $this->content_model;
    }

//    /**
//     * @return \Model_ContentItem
//     */
//    abstract protected function content_model_factory();

    /**
     * @return string
     */
    abstract protected function get_content_model_url_key();
}
