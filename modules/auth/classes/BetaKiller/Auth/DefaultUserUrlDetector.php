<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Auth\BlockedIFace;
use BetaKiller\IFace\Auth\SuspendedIFace;
use BetaKiller\Model\UserInterface;

class DefaultUserUrlDetector implements UserUrlDetectorInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     *
     * @return string
     */
    public function detect(UserInterface $user, UrlHelper $urlHelper): string
    {
        $commonUrl = $this->commonChecks($user, $urlHelper);

        if ($commonUrl) {
            return $commonUrl;
        }

        return $this->customChecks($user, $urlHelper) ?: '/';
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     *
     * @return string|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    protected function commonChecks(UserInterface $user, UrlHelper $urlHelper): ?string
    {
        if ($user->isGuest()) {
            return null;
        }

        if ($user->isBlocked()) {
            $blocked = $urlHelper->getUrlElementByCodename(BlockedIFace::codename());

            return $urlHelper->makeUrl($blocked);
        }

        if ($user->isSuspended()) {
            $suspended = $urlHelper->getUrlElementByCodename(SuspendedIFace::codename());

            return $urlHelper->makeUrl($suspended);
        }

        return null;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     *
     * @return string|null
     */
    protected function customChecks(UserInterface $user, UrlHelper $urlHelper): ?string
    {
        // Override this if needed
        return null;
    }
}
