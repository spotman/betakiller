<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Auth\PasswordChangeIFace;
use BetaKiller\Model\UserInterface;

class VerifyPasswordChangeTokenAction extends AbstractTokenVerificationAction
{
    /**
     * @param \BetaKiller\Helper\UrlHelperInterface $urlHelper
     * @param \BetaKiller\Model\UserInterface       $user
     *
     * @return string
     */
    protected function getSuccessUrl(UrlHelperInterface $urlHelper, UserInterface $user): string
    {
        $params = $urlHelper->createUrlContainer()
            ->setEntity($user);

        return $urlHelper->makeCodenameUrl(PasswordChangeIFace::codename(), $params, false);
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

    /**
     * @inheritDoc
     */
    protected function isTokenReuseAllowed(): bool
    {
        return false;
    }
}
