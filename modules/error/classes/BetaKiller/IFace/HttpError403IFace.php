<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HttpError403IFace extends AbstractHttpErrorIFace
{
    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * HttpError403IFace constructor.
     *
     * @param \BetaKiller\Helper\AclHelper            $aclHelper
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     */
    public function __construct(AclHelper $aclHelper, UrlElementTreeInterface $tree)
    {
        $this->aclHelper = $aclHelper;
        $this->tree      = $tree;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $urlHelper      = ServerRequestHelper::getUrlHelper($request);
        $allowedElement = $this->findAllowedUrlElement($request);

        return array_merge(parent::getData($request), [
            'allowed_url' => $urlHelper->makeUrl($allowedElement),
        ]);
    }

    private function findAllowedUrlElement(ServerRequestInterface $request): UrlElementInterface
    {
        $stack  = ServerRequestHelper::getUrlElementStack($request);
        $params = ServerRequestHelper::getUrlContainer($request);
        $user   = ServerRequestHelper::getUser($request);

        $current = $stack->getCurrent();

        foreach ($this->tree->getReverseBreadcrumbsIterator($current) as $urlElement) {
            if ($this->aclHelper->isUrlElementAllowed($user, $urlElement, $params)) {
                return $urlElement;
            }
        }

        return $this->tree->getDefault();
    }
}
