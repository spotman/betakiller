<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Url\IFaceModelInterface;
use Psr\Http\Message\ServerRequestInterface;

class BreadcrumbsWidget extends AbstractWidget
{
    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $stack         = ServerRequestHelper::getUrlElementStack($request);
        $elementHelper = ServerRequestHelper::getUrlElementHelper($request);
        $urlHelper     = ServerRequestHelper::getUrlHelper($request);
        $params        = ServerRequestHelper::getUrlContainer($request);

        $data = [];

        foreach ($stack->getIterator() as $model) {
            // Show only ifaces
            if (!$model instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'url'    => $urlHelper->makeUrl($model, $params),
                'label'  => $elementHelper->getLabel($model, $params),
                'active' => $stack->isCurrent($model),
            ];
        }

        return [
            'breadcrumbs' => $data,
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
