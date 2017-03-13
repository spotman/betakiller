<?php

class API_Model_ContentComment extends API_Model
{
    use \BetaKiller\Helper\ContentTrait;

    public function approve($id)
    {
        $model = $this->model((int) $id);

        $model->approve()->save();
    }

    public function reject($id)
    {
        $model = $this->model((int) $id);

        $model->reject()->save();
    }

    public function markAsSpam($id)
    {
        $model = $this->model((int) $id);

        $model->mark_as_spam()->save();
    }

    public function moveToTrash($id)
    {
        $model = $this->model((int) $id);

        $model->move_to_trash()->save();
    }

    public function restoreFromTrash($id)
    {
        $model = $this->model((int) $id);

        $model->move_to_trash()->save();
    }

//    public function delete($id)
//    {
//        $model = $this->model((int) $id);
//
//        $model->delete()->save();
//    }

    /**
     * Override this method
     *
     * @param Model_ContentComment $model
     * @param       $data
     *
     * @throws HTTP_Exception_501
     * @return mixed|NULL
     */
    protected function _save($model, $data)
    {
        if (isset($data->author_name)) {
            $model->set_guest_author_name($this->sanitize_string($data->author_name));
        }

        if (isset($data->message)) {
            $model->set_message($data->message);
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
     * @param int|null $id
     *
     * @return Model_ContentComment
     */
    protected function model($id = NULL)
    {
        return $this->model_factory_content_comment($id);
    }
}
