<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\UserInterface;

class VerifyPasswordChangeTokenAction extends AbstractTokenVerificationAction
{
    /**
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    protected function getSuccessUrl(UrlHelper $urlHelper, UserInterface $user): string
    {
        $element = $urlHelper->getUrlElementByCodename(PasswordChangeIFace::codename());

        $params = $urlHelper->createUrlContainer()
            ->setEntity($user);

        return $urlHelper->makeUrl($element, $params, false);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return void
     */
    protected function processValid(UserInterface $user): void
    {
        // Nothing to do here, all processing is done on redirected URL
    }
}