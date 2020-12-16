<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Service\MenuService;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractMenuWidget extends AbstractWidget
{
    /**
     * @var \BetaKiller\Service\MenuService
     */
    private $service;

    /**
     * AbstractMenuWidget constructor.
     *
     * @param \BetaKiller\Service\MenuService $service
     */
    public function __construct(MenuService $service)
    {
        $this->service = $service;
    }

    /**
     * Returns data for View rendering: menu links
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array [[string url, string label, bool active, array children], ...]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $user      = ServerRequestHelper::getUser($request);
        $urlParams = ServerRequestHelper::getUrlContainer($request);
        $stack     = ServerRequestHelper::getUrlElementStack($request);

        // Menu codename from widget context
        $menuCodename = (string)$context['menu'];
        $level        = $context['level'] ?? 1;
        $depth        = $context['depth'] ?? 1;

        $items = $this->service->getItems($menuCodename, $level, $depth, $urlParams, $stack, $user);

        return [
            'items' => $this->service->convertToJson($items),
        ];
    }

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        // Always visible
        return false;
    }
}
