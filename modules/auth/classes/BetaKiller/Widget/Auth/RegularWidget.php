<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Action\Auth\RegularLoginAction;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Security\CsrfService;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

class RegularWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    private UrlHelperInterface $urlHelper;

    /**
     * @var \BetaKiller\Security\CsrfService
     */
    private $csrf;

    /**
     * RegularWidget constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $urlHelperFactory
     *
     * @param \BetaKiller\Security\CsrfService     $csrf
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(UrlHelperFactory $urlHelperFactory, CsrfService $csrf)
    {
        // Use separate instance coz error pages processing can be done before UrlHelper initialized in middleware
        $this->urlHelper = $urlHelperFactory->create();
        $this->csrf      = $csrf;
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

        $params = $this->urlHelper->createUrlContainer()
            ->setEntity($lang);

        return [
            'login_url'           => $this->urlHelper->makeCodenameUrl(RegularLoginAction::codename(), $params),
            'access_recovery_url' => $this->urlHelper->makeCodenameUrl(AccessRecoveryRequestIFace::codename(), $params),
            'token'               => $this->csrf->createRequestToken($request),
        ];
    }
}
