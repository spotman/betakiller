<?php
namespace BetaKiller\IFace\App\Content;

class PostItem extends AppBase
{
    use \BetaKiller\Helper\CurrentUserTrait;

    /**
     * @var \Model_ContentPost
     */
    private $content_model;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before()
    {
        $user = $this->current_user(TRUE);

        // Count guest views only
        if (!$user) {
            $this->get_content_model()->increment_views_count()->save();
        }
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData()
    {
        $model = $this->get_content_model();

//        if ($model->is_default())
//        {
//            $parent = $this->getParent();
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
        $this->setLastModified($model->getApiLastModified());

        $thumbnails = [];

        foreach ($model->get_thumbnails() as $thumb) {
            $thumbnails[] = $thumb->getAttributesForImgTag($thumb::SIZE_ORIGINAL);
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
            'is_default'    =>  $model->is_default(),
        ];
    }

    /**
     * @return \DateInterval
     */
    public function getDefaultExpiresInterval()
    {
        return new \DateInterval('P1D'); // One day
    }

    /**
     * @return \Model_ContentPost
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    protected function detect_content_model()
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
