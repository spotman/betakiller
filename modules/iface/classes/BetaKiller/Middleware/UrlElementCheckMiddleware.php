<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\EntityNotAllowedException;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementNotAllowedException;
use BetaKiller\Url\Zone\ZoneAccessSpecFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class UrlElementCheckMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UrlElementAccessResolverInterface $elementAccessResolver,
        private EntityPermissionResolverInterface $entityPermissionResolver,
        private ZoneAccessSpecFactory $specFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->checkAccessRestrictions($request);

        return $handler->handle($request);
    }

    private function checkAccessRestrictions(ServerRequestInterface $request): void
    {
        $stack  = ServerRequestHelper::getUrlElementStack($request);
        $params = ServerRequestHelper::getUrlContainer($request);
        $user   = ServerRequestHelper::getUser($request);

        $skippedEntities = [];

        // Check current user access for all URL elements
        foreach ($stack as $urlElement) {
            if ($urlElement->isAclBypassed()) {
                if ($urlElement instanceof EntityLinkedUrlElementInterface) {
                    $skippedEntities[] = $urlElement->getEntityModelName();
                }
                continue;
            }

            // Check current user access (may depend on Entities injected in afterDispatching() hook)
            $this->checkUrlElementAccess($urlElement, $params, $user);
        }

        $currentUrlElement = $stack->getCurrent();

        // Check access to UrlParameters
        foreach ($params->getAllParameters() as $urlParameter) {
            if (in_array($urlParameter::getUrlContainerKey(), $skippedEntities, true)) {
                continue;
            }

            $this->checkUrlParameterAccess($currentUrlElement, $urlParameter, $user);
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

        if (!$this->elementAccessResolver->isAllowed($user, $urlElement, $urlParameters)) {
            throw new UrlElementNotAllowedException($urlElement, $urlParameters, $user);
        }
    }

    private function checkUrlParameterAccess(
        UrlElementInterface $element,
        UrlParameterInterface $param,
        UserInterface $user
    ): void {
        if (!$param instanceof DispatchableEntityInterface) {
            return;
        }

        $action = $param->getUrlParameterAccessAction() ?? CrudlsActionsInterface::ACTION_READ;

        // Perform Entity check
        if (!$this->entityPermissionResolver->isAllowed($user, $param, $action)) {
            throw new EntityNotAllowedException($param, $action, $element, $user);
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
