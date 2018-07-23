<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Url\UrlElementInterface;

class BreadcrumbsWidget extends AbstractWidget
{
    /**
     * @Inject
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $stack;

    /**
     * @Inject
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * Returns data for View rendering
     *
     * @return array
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        $data = [];

        foreach ($this->stack->getIterator() as $model) {
            $data[] = $this->makeBreadcrumbData($model);
        }

        return [
            'breadcrumbs' => $data,
        ];
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function makeBreadcrumbData(UrlElementInterface $urlElement): array
    {
        return [
            'url'    => $this->ifaceHelper->makeUrl($urlElement, $this->urlContainer),
            'label'  => $this->ifaceHelper->getLabel($urlElement),
            'active' => $this->stack->isCurrent($urlElement),
        ];
    }

    /**
     * Returns array of roles` codenames which are allowed to use this widget
     *
     * @return string[]
     */
    public function getAclRoles(): array
    {
        return [
            // Allow to guests and any logged in users
            RoleInterface::GUEST_ROLE_NAME,
            RoleInterface::LOGIN_ROLE_NAME,
        ];
    }

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        return true;
    }
}
