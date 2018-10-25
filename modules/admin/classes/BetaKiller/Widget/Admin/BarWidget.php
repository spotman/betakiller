<?php
namespace BetaKiller\Widget\Admin;

use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\Widget\AbstractAdminWidget;
use Psr\Http\Message\ServerRequestInterface;

//use BetaKiller\Helper\ContentHelper;

class BarWidget extends AbstractAdminWidget
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

//    /**
//     * @var \BetaKiller\Helper\ContentHelper
//     */
//    private $contentHelper;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    public function __construct(
        UrlElementTreeInterface $tree,
        AclHelper $aclHelper,
        UrlElementHelper $elementHelper
//        ContentHelper $contentHelper
    )
    {
        $this->tree      = $tree;
        $this->aclHelper = $aclHelper;
//        $this->contentHelper  = $contentHelper;
        $this->elementHelper = $elementHelper;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \Spotman\Acl\Exception
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $user      = ServerRequestHelper::getUser($request);
        $stack     = ServerRequestHelper::getUrlElementStack($request);
        $params    = ServerRequestHelper::getUrlContainer($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $i18n      = ServerRequestHelper::getI18n($request);

        return [
            'enabled'           => false,
//            'comments'          => $this->getCommentsData(),
            'comments'          => null,
            'createButtonItems' => $this->getCreateButtonItems($user, $urlHelper, $params, $i18n),
            'primaryEntity'     => $this->getPrimaryEntityData($stack, $params, $urlHelper),
        ];
    }

    public function isEmptyResponseAllowed(): bool
    {
        // If user is not authorized, then silently exiting
        return true;
    }

    protected function getPrimaryEntityData(
        UrlElementStack $stack,
        UrlContainerInterface $params,
        UrlHelper $helper
    ): array {
        $currentUrlElement = UrlElementHelper::getCurrentIFaceModel($stack);

        $primaryEntity = $currentUrlElement
            ? $this->detectPrimaryEntity($currentUrlElement, $params)
            : null;

        return [
            'previewUrl' => $this->getPreviewButtonUrl($primaryEntity, $stack, $helper),
            'publicUrl'  => $this->getPublicReadButtonUrl($primaryEntity, $stack, $helper),
            'adminUrl'   => $this->getAdminEditButtonUrl($primaryEntity, $stack, $helper),
        ];
    }

    /**
     * @param \BetaKiller\Url\EntityLinkedUrlElementInterface $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    protected function detectPrimaryEntity(
        EntityLinkedUrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): ?DispatchableEntityInterface {
        $current = $urlElement;

        do {
            $name   = $current->getEntityModelName();
            $entity = $name ? $params->getEntity($name) : null;
        } while (!$entity && $current = $this->tree->getParent($current));

        return $entity;
    }

    /**
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \Spotman\Acl\Exception
     */
    protected function getCreateButtonItems(
        UserInterface $user,
        UrlHelper $urlHelper,
        UrlContainerInterface $params,
        I18nHelper $i18n
    ): array {
        $items       = [];
        $urlElements = $this->tree->getIFacesByActionAndZone(CrudlsActionsInterface::ACTION_CREATE,
            ZoneInterface::ADMIN);

        foreach ($urlElements as $urlElement) {
            if (!$this->aclHelper->isUrlElementAllowed($user, $urlElement)) {
                continue;
            }

            $items[] = [
                'label' => $this->elementHelper->getLabel($urlElement, $params, $i18n),
                'url'   => $urlHelper->makeUrl($urlElement),
            ];
        }

        return $items;
    }

    /**
     * @return array|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Spotman\Acl\Exception
     */
//    private function getCommentsData(): ?array
//    {
//        $pendingStatus = $this->contentHelper->getCommentStatusRepository()->getPendingStatus();
//        $pendingCount  = $this->contentHelper->getCommentRepository()->getCommentsCount($pendingStatus);
//
//        $iface = $pendingCount
//            ? $this->getCommentsListByStatusIface()
//            : $this->getCommentsRootIface();
//
//        $params = $this->urlParamHelper
//            ->createSimple()
//            ->setParameter($pendingStatus);
//
//        $url = $this->aclHelper->isIFaceAllowed($iface, $params)
//            ? $this->ifaceHelper->makeIFaceUrl($iface, $params)
//            : null;
//
//        return [
//            'url'   => $url,
//            'count' => $pendingCount,
//        ];
//    }

    /**
     * @param \BetaKiller\Helper\UrlHelper $helper
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\IFace\Admin\Content\CommentListByStatus
     */
    private function getCommentsListByStatusIface(UrlHelper $helper): UrlElementInterface
    {
        return $helper->getUrlElementByCodename('Admin_Content_CommentListByStatus');
    }

    /**
     * @param \BetaKiller\Helper\UrlHelper $helper
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\IFace\Admin\Content\CommentIndex
     */
    private function getCommentsRootIface(UrlHelper $helper): UrlElementInterface
    {
        return $helper->getUrlElementByCodename('Admin_Content_CommentIndex');
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param \BetaKiller\Url\UrlElementStack                    $stack
     * @param \BetaKiller\Helper\UrlHelper                       $helper
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getAdminEditButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelper $helper
    ): ?string {
        return $this->getPrimaryEntityActionUrl(
            $stack,
            $helper,
            $entity,
            ZoneInterface::ADMIN,
            CrudlsActionsInterface::ACTION_READ
        );
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param \BetaKiller\Url\UrlElementStack                    $stack
     * @param \BetaKiller\Helper\UrlHelper                       $helper
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getPublicReadButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelper $helper
    ): ?string {
        return $this->getPrimaryEntityActionUrl(
            $stack,
            $helper,
            $entity,
            ZoneInterface::PUBLIC,
            CrudlsActionsInterface::ACTION_READ
        );
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param \BetaKiller\Url\UrlElementStack                    $stack
     *
     * @param \BetaKiller\Helper\UrlHelper                       $helper
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getPreviewButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelper $helper
    ): ?string {
        return $this->getPrimaryEntityActionUrl(
            $stack,
            $helper,
            $entity,
            ZoneInterface::PREVIEW,
            CrudlsActionsInterface::ACTION_READ
        );
    }

    /**
     * @param \BetaKiller\Url\UrlElementStack                    $stack
     * @param \BetaKiller\Helper\UrlHelper                       $helper
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param string                                             $targetZone
     * @param string                                             $targetAction
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function getPrimaryEntityActionUrl(
        UrlElementStack $stack,
        UrlHelper $helper,
        ?DispatchableEntityInterface $entity,
        string $targetZone,
        string $targetAction
    ): ?string {
        if (!$entity) {
            return null;
        }

        if (UrlElementHelper::isCurrentZone($targetZone, $stack)) {
            return null;
        }

        try {
            return $helper->getEntityUrl($entity, $targetAction, $targetZone);
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (UrlElementException $e) {
            // No IFace found for provided zone/action
            return null;
        }
    }
}
