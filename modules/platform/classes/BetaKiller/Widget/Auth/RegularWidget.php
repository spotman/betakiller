<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Action\Auth\RegularLoginAction;
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
        return [
            'login_url' => RegularLoginAction::URL,
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
