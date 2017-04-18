<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Widget\AbstractAdminWidget;

class MainWidget extends AbstractAdminWidget
{
    use ContentTrait;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData()
    {
        return [
            'items' => [
                $this->getPostsMenu(),
                $this->getCommentsMenu(),
            ],
        ];
    }

    protected function getPostsMenu()
    {
        /** @var \BetaKiller\IFace\Admin\Content\PostIndex $posts */
        $postsIndex = $this->iface_from_codename('Admin_Content_PostIndex');

        return $this->makeIFaceMenuItemData($postsIndex);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface        $iface
     * @param \BetaKiller\IFace\IFaceInterface[]|null $childrenIfacesData
     *
     * @return array
     */
    protected function makeIFaceMenuItemData(IFaceInterface $iface, array $childrenIfacesData = null)
    {
        $output             = $this->getIFaceMenuItemData($iface);
        $output['children'] = $childrenIfacesData;

        return $output;
    }

    protected function getIFaceMenuItemData(IFaceInterface $iface, UrlParameters $params = null)
    {
        return [
            'url'    => $iface->url($params, false), // Keep links always working
            'label'  => $iface->getLabel($params),
            'active' => $iface->isCurrent($params),
        ];
    }

    protected function getCommentsMenu()
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $comments */
        $commentsIndex = $this->iface_from_codename('Admin_Content_CommentIndex');

        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $comments */
        $commentListInStatus = $this->iface_from_codename('Admin_Content_CommentListByStatus');

        /** @var \Model_ContentCommentStatus[] $statuses */
        $statuses = $this->model_factory_content_comment_status()->get_all();

        $childrenData = [];

        foreach ($statuses as $status) {
            $params = UrlParameters::create()->set($status::URL_PARAM, $status);

            $childrenData[] = $this->getIFaceMenuItemData($commentListInStatus, $params);
        }

        return $this->makeIFaceMenuItemData($commentsIndex, $childrenData);
    }
}
