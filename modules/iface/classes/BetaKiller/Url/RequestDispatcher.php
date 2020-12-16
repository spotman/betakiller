<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use Psr\Http\Message\ServerRequestInterface;

class RequestDispatcher
{
    /**
     * @var \BetaKiller\Url\UrlDispatcherInterface
     */
    private UrlDispatcherInterface $urlDispatcher;

    /**
     * @var \BetaKiller\Url\Zone\ZoneAccessSpecFactory
     */
    private ZoneAccessSpecFactory $specFactory;

    /**
     * @var \BetaKiller\Acl\UrlElementAccessResolverInterface
     */
    private UrlElementAccessResolverInterface $elementAccessResolver;

    /**
     * @var \BetaKiller\Acl\EntityPermissionResolverInterface
     */
    private EntityPermissionResolverInterface $entityPermissionResolver;

    /**
     * RequestDispatcher constructor.
     *
     * @param \BetaKiller\Url\UrlDispatcherInterface            $urlDispatcher
     * @param \BetaKiller\Factory\UrlElementInstanceFactory     $instanceFactory
     * @param \BetaKiller\Acl\UrlElementAccessResolverInterface $elementAccessResolver
     * @param \BetaKiller\Acl\EntityPermissionResolverInterface $entityPermissionResolver
     * @param \BetaKiller\Url\Zone\ZoneAccessSpecFactory        $specFactory
     */
    public function __construct(
        UrlDispatcherInterface $urlDispatcher,
        UrlElementAccessResolverInterface $elementAccessResolver,
        EntityPermissionResolverInterface $entityPermissionResolver,
        ZoneAccessSpecFactory $specFactory
    ) {
        $this->urlDispatcher            = $urlDispatcher;
        $this->elementAccessResolver    = $elementAccessResolver;
        $this->entityPermissionResolver = $entityPermissionResolver;
        $this->specFactory              = $specFactory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\MissingUrlElementException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \Spotman\Acl\AclException
     */
    public function process(ServerRequestInterface $request): void
    {
        $stack  = ServerRequestHelper::getUrlElementStack($request);
        $params = ServerRequestHelper::getUrlContainer($request);
        $user   = ServerRequestHelper::getUser($request);
        $i18n   = ServerRequestHelper::getI18n($request);
        $url    = ServerRequestHelper::getUrl($request);

        $this->urlDispatcher->process($url, $stack, $params, $user, $i18n);

        // Check current user access for all URL elements
        foreach ($stack as $urlElement) {
            // Check current user access (may depend on Entities injected in afterDispatching() hook)
            $this->checkUrlElementAccess($urlElement, $params, $user);
        }

        // Check access to UrlParameters
        foreach ($params->getAllParameters() as $urlParameter) {
            $this->checkUrlParameterAccess($urlParameter, $user);
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     * @param \BetaKiller\Model\UserInterface                 $user
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \Spotman\Acl\AclException
     */
    private function checkUrlElementAccess(
        UrlElementInterface $urlElement,
        UrlContainerInterface $urlParameters,
        UserInterface $user
    ): void {
        // Force authorization for non-public zones before security check
        $this->forceAuthorizationIfNeeded($urlElement, $user);

        if ($this->elementAccessResolver->isAllowed($user, $urlElement, $urlParameters)) {
            return;
        }

        $params = [];

        foreach ($urlParameters->getAllParameters() as $item) {
            $id = $item instanceof AbstractEntityInterface
                ? $item->getID()
                : null;

            $params[] = sprintf('%s (%s)', $item::getUrlContainerKey(), $id);
        }

        throw new AccessDeniedException('UrlElement ":name" is not allowed to User ":who" with :params', [
            ':name'   => $urlElement->getCodename(),
            ':who'    => $user->isGuest() ? 'Guest' : $user->getID(),
            ':params' => implode(', ', $params),
        ]);
    }

    private function checkUrlParameterAccess(
        UrlParameterInterface $param,
        UserInterface $user
    ): void {
        if (!$param instanceof DispatchableEntityInterface) {
            return;
        }

        $action = $param->getUrlParameterAccessAction() ?? CrudlsActionsInterface::ACTION_READ;

        // Perform Entity check
        if (!$this->entityPermissionResolver->isAllowed($user, $param, $action)) {
            throw new AccessDeniedException('Entity ":name" is not allowed to User ":who"', [
                ':name' => $param::getModelName(),
                ':who'  => $user->isGuest() ? 'Guest' : $user->getID(),
            ]);
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    private function forceAuthorizationIfNeeded(UrlElementInterface $urlElement, UserInterface $user): void
    {
        $zoneSpec = $this->specFactory->createFromUrlElement($urlElement);

        // User authorization is required for entering protected zones
        if ($zoneSpec->isAuthRequired() && $user->isGuest()) {
            $user->forceAuthorization();
        }
    }
}
