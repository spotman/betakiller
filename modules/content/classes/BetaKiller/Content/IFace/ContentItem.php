<?php

namespace BetaKiller\Content\IFace;

class ContentItem extends Base
{
    /**
     * @var \Model_ContentPost
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
        $model = $this->get_content_model();

//        if ($model->is_default())
//        {
//            $parent = $this->get_parent();
//            $url = $parent ? $parent->url() : '/';
//
//            $this->redirect($url);
//        }

        return [
            'post' =>  $this->get_post_data($model),
        ];
    }

    protected function get_post_data(\Model_ContentPost $model)
    {
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
     * @return \Model_ContentPost
     * @throws \IFace_Exception
     */
    private function detect_content_model()
    {
        $key = $this->get_content_model_url_param_key();

        return $this->url_parameters()->get($key);
    }

    /**
     * @return string
     */
    protected function get_content_model_url_param_key()
    {
        return \Model_ContentPost::URL_PARAM;
    }

    /**
     * @return \Model_ContentPost
     */
    protected function get_content_model()
    {
        if (!$this->content_model)
        {
            $this->content_model = $this->detect_content_model();
        }

        return $this->content_model;
    }
}
