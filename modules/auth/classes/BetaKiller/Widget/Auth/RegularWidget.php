<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Action\Auth\RegularLoginAction;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Security\CsrfService;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

final class RegularWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Security\CsrfService
     */
    private $csrf;

    /**
     * @var \BetaKiller\Factory\UrlHelperFactory
     */
    private UrlHelperFactory $urlHelperFactory;

    /**
     * RegularWidget constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $urlHelperFactory
     *
     * @param \BetaKiller\Security\CsrfService     $csrf
     */
    public function __construct(UrlHelperFactory $urlHelperFactory, CsrfService $csrf)
    {
        $this->csrf             = $csrf;
        $this->urlHelperFactory = $urlHelperFactory;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     * @uses \BetaKiller\Action\Auth\RegularLoginAction
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $lang = ServerRequestHelper::getI18n($request)->getLang();

        // Use separate instance coz error pages processing can be done before UrlHelper initialized in middleware
        $urlHelper = ServerRequestHelper::hasUrlHelper($request)
            ? ServerRequestHelper::getUrlHelper($request)
            : $this->urlHelperFactory->create();

        $params = $urlHelper->createUrlContainer(true)
            ->setEntity($lang, true);

        return [
            'login_url'           => $urlHelper->makeCodenameUrl(RegularLoginAction::codename(), $params),
            'access_recovery_url' => $urlHelper->makeCodenameUrl(AccessRecoveryRequestIFace::codename(), $params),
            'token'               => $this->csrf->createRequestToken($request),
        ];
    }
}
