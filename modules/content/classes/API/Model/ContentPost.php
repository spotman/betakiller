<?php

class API_Model_ContentPost extends API_Model
{
    use \BetaKiller\Helper\ContentTrait;

    public function publish($id)
    {
        $id = (int) $id;

        $model = $this->model($id);

        $model->publish()->save();
    }

    /**
     * Override this method
     *
     * @param Model_ContentPost $model
     * @param       $data
     *
     * @throws HTTP_Exception_501
     * @return mixed|NULL
     */
    protected function _save($model, $data)
    {
        if (isset($data->uri)) {
            $model->set_uri($this->sanitize_string($data->uri));
        }

        if (isset($data->title)) {
            $model->set_title($this->sanitize_string($data->title));
        }

        if (isset($data->description)) {
            $model->set_description($this->sanitize_string($data->description));
        }

        if (isset($data->content)) {
            $model->set_content($data->content);
        }

        $model->save();

        // Return updated model data
        return $model;
    }

    protected function sanitize_string($value)
    {
        return HTML::chars(strip_tags($value));
    }

    /**
     * Returns new model or performs search by id
     *
     * @param null $id
     *
     * @return Model_ContentPost
     */
    protected function model($id = NULL)
    {
        return $this->model_factory_content_post($id);
    }
}
