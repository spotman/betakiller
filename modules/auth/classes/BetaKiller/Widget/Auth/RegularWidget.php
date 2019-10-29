<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Action\Auth\RegularLoginAction;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\AccessRecoveryRequestIFace;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

class RegularWidget extends AbstractPublicWidget
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     * @uses \BetaKiller\Action\Auth\RegularLoginAction
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $i18n          = ServerRequestHelper::getI18n($request);
        $urlHelper     = ServerRequestHelper::getUrlHelper($request);
        $recoveryIFace = $urlHelper->getUrlElementByCodename(AccessRecoveryRequestIFace::codename());
        $loginAction   = $urlHelper->getUrlElementByCodename(RegularLoginAction::codename());

        $lang = $i18n->getLang();

        $params = $urlHelper->createUrlContainer()
            ->setEntity($lang);

        return [
            'login_url'           => $urlHelper->makeUrl($loginAction, $params),
            'access_recovery_url' => $urlHelper->makeUrl($recoveryIFace, $params),
        ];
    }
}
