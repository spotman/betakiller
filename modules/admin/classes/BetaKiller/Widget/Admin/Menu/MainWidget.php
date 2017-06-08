<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Helper\ContentTrait;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\Widget\AbstractAdminWidget;

class MainWidget extends AbstractAdminWidget
{
    use ContentTrait;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ContentUrlParametersHelper
     */
    private $contentUrlParametersHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     */
    public function getData(): array
    {
        $items = [
            $this->getPostsMenu(),
            $this->getCommentsMenu(),
            $this->getErrorsMenu(),
        ];

        return [
            'items' => array_filter($items),
        ];
    }

    protected function getPostsMenu()
    {
        /** @var \BetaKiller\IFace\Admin\Content\PostIndex $posts */
        $postsIndex = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_PostIndex');

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

    protected function getIFaceMenuItemData(IFaceInterface $iface, UrlParametersInterface $params = null)
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
        $commentsIndex = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');

        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $comments */
        $commentListInStatus = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');

        /** @var \Model_ContentCommentStatus[] $statuses */
        // TODO Get comment statuses only allowed by ACL
        $statuses = $this->model_factory_content_comment_status()->get_all();

        $childrenData = [];

        foreach ($statuses as $status) {
            $params = $this->contentUrlParametersHelper
                ->createEmpty()
                ->setEntity($status);

            $childrenData[] = $this->getIFaceMenuItemData($commentListInStatus, $params);
        }

        return $this->makeIFaceMenuItemData($commentsIndex, $childrenData);
    }

    protected function getErrorsMenu()
    {
        if (!$this->user->isDeveloper()) {
            return null;
        }

        /** @var \BetaKiller\IFace\Admin\Error\Index $iface */
        $errorsIndex = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_Index');

        /** @var \BetaKiller\IFace\Admin\Error\UnresolvedPhpExceptionIndex $iface */
        $unresolvedErrors = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_UnresolvedPhpExceptionIndex');

        /** @var \BetaKiller\IFace\Admin\Error\ResolvedPhpExceptionIndex $iface */
        $resolvedErrors = $this->ifaceHelper->createIFaceFromCodename('Admin_Error_ResolvedPhpExceptionIndex');

        $childrenData = [
            $this->makeIFaceMenuItemData($unresolvedErrors),
            $this->makeIFaceMenuItemData($resolvedErrors),
        ];

        return $this->makeIFaceMenuItemData($errorsIndex, $childrenData);
    }
}
