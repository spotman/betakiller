<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementWithLabelInterface;
use Psr\Http\Message\ServerRequestInterface;

class BreadcrumbsWidget extends AbstractWidget
{
    /**
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * BreadcrumbsWidget constructor.
     *
     * @param \BetaKiller\Helper\UrlElementHelper $elementHelper
     */
    public function __construct(UrlElementHelper $elementHelper)
    {
        $this->elementHelper = $elementHelper;
    }

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
        $stack     = ServerRequestHelper::getUrlElementStack($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $params    = ServerRequestHelper::getUrlContainer($request);
        $i18n      = ServerRequestHelper::getI18n($request);

        $skipRoot = $context['skip_root'] ?? false;

        $data = [];

        foreach ($stack->getIterator() as $model) {
            // Show only ifaces
            if (!$model instanceof IFaceModelInterface) {
                continue;
            }

            $data[] = [
                'url'    => $urlHelper->makeUrl($model, $params),
                'label'  => $this->elementHelper->getLabel($model, $params, $i18n->getLang()),
                'active' => $stack->isCurrent($model),
            ];
        }

        if ($skipRoot) {
            \array_shift($data);
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
            // Allow to guests and any logged-in users
            RoleInterface::GUEST,
            RoleInterface::LOGIN,
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
