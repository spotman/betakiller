<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Action\Auth\RegularLoginAction;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

class RegularWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * RegularWidget constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $urlHelperFactory
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(UrlHelperFactory $urlHelperFactory)
    {
        // Use separate instance coz error pages processing can be done before UrlHelper initialized in middleware
        $this->urlHelper = $urlHelperFactory->create();
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

        $recoveryIFace = $this->urlHelper->getUrlElementByCodename(AccessRecoveryRequestIFace::codename());
        $loginAction   = $this->urlHelper->getUrlElementByCodename(RegularLoginAction::codename());

        $params = $this->urlHelper->createUrlContainer()
            ->setEntity($lang);

        return [
            'login_url'           => $this->urlHelper->makeUrl($loginAction, $params),
            'access_recovery_url' => $this->urlHelper->makeUrl($recoveryIFace, $params),
        ];
    }
}
