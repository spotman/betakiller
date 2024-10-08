<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Helper\UrlHelper;
use BetaKiller\Model\UserInterface;

interface UserUrlDetectorInterface
{
    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return string
     */
    public function detect(UserInterface $user): string;
}
