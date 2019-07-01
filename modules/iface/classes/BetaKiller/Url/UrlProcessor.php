<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Factory\UrlElementInstanceFactory;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlProcessor
{
    /**
     * @var \BetaKiller\Url\UrlDispatcherInterface
     */
    private $urlDispatcher;

    /**
     * @var \BetaKiller\Factory\UrlElementInstanceFactory
     */
    private $instanceFactory;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * UrlProcessor constructor.
     *
     * @param \BetaKiller\Url\UrlDispatcherInterface        $urlDispatcher
     * @param \BetaKiller\Factory\UrlElementInstanceFactory $instanceFactory
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     */
    public function __construct(
        UrlDispatcherInterface $urlDispatcher,
        UrlElementInstanceFactory $instanceFactory,
        AclHelper $aclHelper
    ) {
        $this->urlDispatcher   = $urlDispatcher;
        $this->instanceFactory = $instanceFactory;
        $this->aclHelper       = $aclHelper;
    }

    public function process(
        string $url,
        UrlElementStack $stack,
        UrlContainerInterface $params,
        UserInterface $user
    ): void {
        $this->urlDispatcher->process($url, $stack, $params);

        // Check current user access for all URL elements
        foreach ($stack as $urlElement) {
            $instance = $this->instanceFactory->createFromUrlElement($urlElement);

            // Process afterDispatching() hooks on every UrlElement in stack
            if ($instance && $instance instanceof AfterDispatchingInterface) {
                $instance->afterDispatching($stack, $params, $user);
            }

            // Check current user access (may depend on Entities injected in afterDispatching() hook)
            $this->checkUrlElementAccess($urlElement, $params, $user);
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Spotman\Acl\Exception
     */
    private function checkUrlElementAccess(
        UrlElementInterface $urlElement,
        UrlContainerInterface $urlParameters,
        UserInterface $user
    ): void {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($urlElement, $user);

        if (!$this->aclHelper->isUrlElementAllowed($user, $urlElement, $urlParameters)) {
            throw new AccessDeniedException();
        }
    }
}
