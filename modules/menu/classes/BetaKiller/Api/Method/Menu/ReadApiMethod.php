<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\Menu;

use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Exception\SecurityException;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\MenuService;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementStack;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class ReadApiMethod extends AbstractApiMethod
{
    /**
     * Menu name
     */
    private const ARG_MENU = 'menu';

    /**
     * Current URL for detecting active state for items
     */
    private const ARG_URL = 'url';

    /**
     * Optional level for start displaying items at
     */
    private const ARG_LEVEL = 'level';

    /**
     * Optional level for menu depth
     */
    private const ARG_DEPTH = 'depth';

    /**
     * @var \BetaKiller\Service\MenuService
     */
    private MenuService $service;

    /**
     * @var \BetaKiller\Url\UrlDispatcherInterface
     */
    private UrlDispatcherInterface $urlDispatcher;

    /**
     * @var \BetaKiller\Acl\UrlElementAccessResolverInterface
     */
    private UrlElementAccessResolverInterface $elementAccessResolver;

    /**
     * ReadApiMethod constructor.
     *
     * @param \BetaKiller\Service\MenuService                   $service
     * @param \BetaKiller\Url\UrlDispatcherInterface            $urlDispatcher
     * @param \BetaKiller\Acl\UrlElementAccessResolverInterface $elementAccessResolver
     */
    public function __construct(
        MenuService $service,
        UrlDispatcherInterface $urlDispatcher,
        UrlElementAccessResolverInterface $elementAccessResolver
    ) {
        $this->service               = $service;
        $this->urlDispatcher         = $urlDispatcher;
        $this->elementAccessResolver = $elementAccessResolver;
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     *
     * @return void
     */
    public function defineArguments(DefinitionBuilderInterface $builder): void
    {
        $builder
            ->string(self::ARG_MENU)
            ->string(self::ARG_URL)
            ->int(self::ARG_LEVEL)->optional()->default(1)->positive()
            ->int(self::ARG_DEPTH)->optional()->default(1)->positive();
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $menuName = $arguments->getString(self::ARG_MENU);
        $url      = $arguments->getString(self::ARG_URL);
        $level    = $arguments->getInt(self::ARG_LEVEL);
        $depth    = $arguments->getInt(self::ARG_DEPTH);

        $params = ResolvingUrlContainer::create();
        $stack  = new UrlElementStack($params);

        // Parse provided URL for active items detection
        $this->urlDispatcher->process($url, $stack, $params);

        $urlElement = $stack->getCurrent();

        if (!$this->elementAccessResolver->isAllowed($user, $urlElement, $params)) {
            throw new SecurityException('Menu for current UrlElement ":name" is not allowed to User ":who"', [
                ':name' => $urlElement->getCodename(),
                ':who'  => $user->isGuest() ? 'Guest' : $user->getID(),
            ]);
        }

        return $this->response(
            $this->service->getItems($menuName, $user, $level, $depth, $params, $stack)
        );
    }
}
