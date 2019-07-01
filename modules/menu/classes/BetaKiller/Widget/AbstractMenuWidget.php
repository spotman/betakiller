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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $user      = ServerRequestHelper::getUser($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        // Menu codename from widget context
        $menuCodename = (string)$context['menu'];
        $level        = $context['level'] ?? 1;
        $depth        = $context['depth'] ?? 1;

        $items = $this->service->getItems($menuCodename, $urlHelper, $user, $level, $depth);

        return [
            'items' => $this->convertData($items),
        ];
    }

    /**
     * @param \BetaKiller\Menu\MenuItem[] $items
     *
     * @return mixed[]
     */
    private function convertData(array $items): array
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = $item->jsonSerialize();
        }

        return $data;
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
