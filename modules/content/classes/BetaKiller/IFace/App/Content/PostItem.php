<?php
namespace BetaKiller\IFace\App\Content;

use BetaKiller\Helper\CurrentUserTrait;
use BetaKiller\Helper\ContentUrlParametersHelper;

class PostItem extends AppBase
{
    use CurrentUserTrait;

    /**
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    protected $urlParametersHelper;

    /**
     * @var \Model_ContentPost
     */
    private $contentModel;

    /**
     * PostItem constructor.
     *
     * @param \BetaKiller\Helper\ContentUrlParametersHelper $urlParametersHelper
     */
    public function __construct(ContentUrlParametersHelper $urlParametersHelper)
    {
        parent::__construct();
        $this->urlParametersHelper = $urlParametersHelper;
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before()
    {
        $user = $this->current_user(TRUE);

        // Count guest views only
        if (!$user) {
            $this->getContentModel()->incrementViewsCount()->save();
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
        $model = $this->getContentModel();

//        if ($model->isDefault())
//        {
//            $parent = $this->getParent();
//            $url = $parent ? $parent->url() : '/';
//
//            $this->redirect($url);
//        }

        return [
            'post' =>  $this->getPostData($model),
        ];
    }

    protected function getPostData(\Model_ContentPost $model)
    {
        $this->setLastModified($model->getApiLastModified());

        $thumbnails = [];

        foreach ($model->getThumbnails() as $thumb) {
            $thumbnails[] = $thumb->getAttributesForImgTag($thumb::SIZE_ORIGINAL);

            // Get image last modified and set it to iface
            if ($thumbLastModified = $thumb->getLastModifiedAt()) {
                $this->setLastModified($thumbLastModified);
            }
        }

        return [
            'id'            =>  $model->get_id(),
            'label'         =>  $model->getLabel(),
            'content'       =>  $model->getContent(),
            'created_at'    =>  $model->getCreatedAt(),
            'updated_at'    =>  $model->getUpdatedAt(),
            'thumbnails'    =>  $thumbnails,
            'is_page'       =>  $model->isPage(),
            'is_default'    =>  $model->isDefault(),
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
    protected function detectContentModel()
    {
        return $this->urlParametersHelper->getContentPost();
    }

    /**
     * @return \Model_ContentPost
     */
    protected function getContentModel()
    {
        if (!$this->contentModel) {
            $this->contentModel = $this->detectContentModel();
        }

        return $this->contentModel;
    }
}
