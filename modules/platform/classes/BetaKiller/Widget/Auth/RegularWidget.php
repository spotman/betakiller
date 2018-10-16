<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Widget\AbstractPublicWidget;
use Psr\Http\Message\ServerRequestInterface;

class RegularWidget extends AbstractPublicWidget
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @uses \BetaKiller\Action\Auth\RegularLoginAction
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $action = $urlHelper->getUrlElementByCodename('Auth_RegularLogin');

        return [
            'login_url' => $urlHelper->makeUrl($action),
//            'reset_password_url' => $this->getResetPasswordUrl(),
        ];
    }

//    private function getResetPasswordUrl(): string
//    {
//        /** @var PasswordReset $iface */
//        $iface = $this->urlElementHelper->createIFaceFromCodename('Auth_PasswordReset');
//
//        return $this->urlElementHelper->makeIFaceUrl($iface);
//    }
}
