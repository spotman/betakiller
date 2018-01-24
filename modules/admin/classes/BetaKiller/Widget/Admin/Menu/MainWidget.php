<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Widget\AbstractAdminWidget;
use BetaKiller\Url\UrlContainerInterface;

class MainWidget extends AbstractAdminWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParametersHelper;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentCommentStatusRepository
     */
    private $commentStatusRepository;

    /**
     * @Inject
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @Inject
     * @var \BetaKiller\Helper\StringPatternHelper
     */
    private $patternHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
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

    /**
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    protected function getPostsMenu(): array
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
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    protected function makeIFaceMenuItemData(IFaceInterface $iface, array $childrenIfacesData = null): array
    {
        $output = $this->getIFaceMenuItemData($iface);

        // If iface not allowed, do not show itself and its children
        if (!$output) {
            return [];
        }

        $output['children'] = $childrenIfacesData;

        return $output;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface           $iface
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    protected function getIFaceMenuItemData(IFaceInterface $iface, UrlContainerInterface $params = null): array
    {
        if (!$this->aclHelper->isIFaceAllowed($iface->getModel(), $params)) {
            return [];
        }

        $url = $this->ifaceHelper->makeUrl($iface->getModel(), $params, false); // Keep links always working

        return [
            'url'    => $url,
            'label'  => $this->patternHelper->processPattern($iface->getLabel(), null, $params),
            'active' => $this->ifaceHelper->isCurrentIFace($iface, $params),
        ];
    }

    /**
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    protected function getCommentsMenu(): array
    {
        /** @var \BetaKiller\IFace\Admin\Content\CommentIndex $comments */
        $commentsIndex = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentIndex');

        /** @var \BetaKiller\IFace\Admin\Content\CommentListByStatus $comments */
        $commentListInStatus = $this->ifaceHelper->createIFaceFromCodename('Admin_Content_CommentListByStatus');

        /** @var \BetaKiller\Model\ContentCommentStatus[] $statuses */
        $statuses = $this->commentStatusRepository->getAll();

        $childrenData = [];

        foreach ($statuses as $status) {
            $params = $this->urlParametersHelper
                ->createSimple()
                ->setParameter($status);

            $childrenData[] = $this->getIFaceMenuItemData($commentListInStatus, $params);
        }

        return $this->makeIFaceMenuItemData($commentsIndex, $childrenData);
    }

    /**
     * @return array|null
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    protected function getErrorsMenu(): ?array
    {
        if (!$this->userService->isDeveloper($this->user)) {
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
