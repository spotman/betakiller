<?php

namespace BetaKiller\Content\IFace;

use BetaKiller\Content\IFace\Base;

abstract class ContentItemBase extends Base
{
    /**
     * @var \Model_ORM_ContentBase
     */
    private $content_model;

    protected function get_content_data()
    {
        $model = $this->get_content_model();

        $this->set_last_modified($model->get_last_modified());

        $user = $this->current_user(TRUE);

        // Count guest views only
        if (!$user) {
            $model->increment_views_count()->save();
        }

        return [
            'id'            =>  $model->get_id(),
            'label'         =>  $model->get_label(),
            'content'       =>  $model->get_content(),
            'created_at'    =>  $model->get_created_at(),
            'updated_at'    =>  $model->get_updated_at(),
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
     * @return \Model_ORM_ContentBase
     * @throws \IFace_Exception
     */
    protected function detect_content_model()
    {
        $model_url_key = $this->get_content_model_url_key();

        if ( $this->is_default() )
        {
            $uri = $this->get_index_uri();

            $model = $this->content_model_factory()->filter_uri($uri)->find();

            if (!$model->loaded())
                throw new \IFace_Exception('Can not find default content with uri :value', [':value' => $uri]);

            $this->url_parameters()->set($model_url_key, $model);
        }
        else
        {
            $model = $this->url_parameters()->get($model_url_key);
        }

        return $model;
    }

    /**
     * @return \Model_ORM_ContentBase
     */
    protected function get_content_model()
    {
        if (!$this->content_model)
        {
            $this->content_model = $this->detect_content_model();
        }

        return $this->content_model;
    }

    /**
     * @return \Model_ORM_ContentBase
     */
    abstract protected function content_model_factory();

    /**
     * @return string
     */
    abstract protected function get_content_model_url_key();

    protected function get_index_uri()
    {
        return 'index';
    }
}
