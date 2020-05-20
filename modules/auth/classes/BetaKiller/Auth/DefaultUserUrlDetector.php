<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\IFace\Auth\BlockedIFace;
use BetaKiller\IFace\Auth\SuspendedIFace;
use BetaKiller\Model\UserInterface;

class DefaultUserUrlDetector implements UserUrlDetectorInterface
{
    /**
     * @var \BetaKiller\Helper\UrlHelperInterface
     */
    protected UrlHelperInterface $urlHelper;

    /**
     * DefaultUserUrlDetector constructor.
     *
     * @param \BetaKiller\Factory\UrlHelperFactory $urlHelperFactory
     */
    public function __construct(UrlHelperFactory $urlHelperFactory)
    {
        $this->urlHelper = $urlHelperFactory->create();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    public function detect(UserInterface $user): string
    {
        if ($user->isGuest()) {
            return '/';
        }

        $commonUrl = $this->commonChecks($user);

        if ($commonUrl) {
            return $commonUrl;
        }

        return $this->customChecks($user) ?: '/';
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string|null
     * @throws \BetaKiller\Url\UrlElementException
     */
    protected function commonChecks(UserInterface $user): ?string
    {
        if ($user->isBlocked()) {
            return $this->urlHelper->makeCodenameUrl(BlockedIFace::codename());
        }

        if ($user->isSuspended()) {
            return $this->urlHelper->makeCodenameUrl(SuspendedIFace::codename());
        }

        return null;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string|null
     */
    protected function customChecks(UserInterface $user): ?string
    {
        // Override this if needed
        return null;
    }
}
