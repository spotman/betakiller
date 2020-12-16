<?php
namespace BetaKiller\Widget\Admin;

use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\RequestLanguageHelperInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\UrlElementException;
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
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * @var \BetaKiller\Acl\UrlElementAccessResolverInterface
     */
    private $elementAccessResolver;

    /**
     * BarWidget constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface           $tree
     * @param \BetaKiller\Acl\UrlElementAccessResolverInterface $elementAccessResolver
     * @param \BetaKiller\Helper\UrlElementHelper               $elementHelper
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UrlElementAccessResolverInterface $elementAccessResolver,
        UrlElementHelper $elementHelper
//        ContentHelper $contentHelper
    )
    {
        $this->tree = $tree;
//        $this->contentHelper  = $contentHelper;
        $this->elementHelper         = $elementHelper;
        $this->elementAccessResolver = $elementAccessResolver;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \Spotman\Acl\AclException
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
        UrlHelperInterface $helper
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
     * @throws \BetaKiller\Url\UrlElementException
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
     * @param \BetaKiller\Model\UserInterface                   $user
     * @param \BetaKiller\Helper\UrlHelperInterface             $urlHelper
     * @param \BetaKiller\Url\Container\UrlContainerInterface   $params
     * @param \BetaKiller\Helper\RequestLanguageHelperInterface $i18n
     *
     * @return array
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function getCreateButtonItems(
        UserInterface $user,
        UrlHelperInterface $urlHelper,
        UrlContainerInterface $params,
        RequestLanguageHelperInterface $i18n
    ): array {
        $items       = [];
        $urlElements = $this->tree->getByActionAndZone(CrudlsActionsInterface::ACTION_CREATE,
            ZoneInterface::ADMIN);

        foreach ($urlElements as $urlElement) {
            if (!$this->elementAccessResolver->isAllowed($user, $urlElement, $params)) {
                continue;
            }

            $items[] = [
                'label' => $this->elementHelper->getLabel($urlElement, $params, $i18n->getLang()),
                'url'   => $urlHelper->makeUrl($urlElement),
            ];
        }

        return $items;
    }

    /**
     * @return array|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \Spotman\Acl\AclException
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

//    /**
//     * @param \BetaKiller\Helper\UrlHelperInterface $helper
//     *
//     * @return IFaceModelInterface
//     * @throws \BetaKiller\Url\UrlElementException
//     * @uses \BetaKiller\IFace\Admin\Content\CommentListByStatusIFace
//     */
//    private function getCommentsListByStatusIface(UrlHelperInterface $helper): UrlElementInterface
//    {
//        return $helper->getUrlElementByCodename('Admin_Content_CommentListByStatus');
//    }
//
//    /**
//     * @param \BetaKiller\Helper\UrlHelperInterface $helper
//     *
//     * @return \BetaKiller\Url\UrlElementInterface
//     * @throws \BetaKiller\Url\UrlElementException
//     * @uses \BetaKiller\IFace\Admin\Content\CommentIndexIFace
//     */
//    private function getCommentsRootIface(UrlHelperInterface $helper): UrlElementInterface
//    {
//        return $helper->getUrlElementByCodename('Admin_Content_CommentIndex');
//    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param \BetaKiller\Url\UrlElementStack                    $stack
     * @param \BetaKiller\Helper\UrlHelperInterface              $helper
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getAdminEditButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelperInterface $helper
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
     * @param \BetaKiller\Helper\UrlHelperInterface              $helper
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getPublicReadButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelperInterface $helper
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
     * @param \BetaKiller\Helper\UrlHelperInterface              $helper
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getPreviewButtonUrl(
        ?DispatchableEntityInterface $entity,
        UrlElementStack $stack,
        UrlHelperInterface $helper
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
     * @param \BetaKiller\Helper\UrlHelperInterface              $helper
     * @param \BetaKiller\Model\DispatchableEntityInterface|null $entity
     * @param string                                             $targetZone
     * @param string                                             $targetAction
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function getPrimaryEntityActionUrl(
        UrlElementStack $stack,
        UrlHelperInterface $helper,
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
