<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;

abstract class AbstractWebAuthRequestedEvent implements EventMessageInterface
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlParams;

    /**
     * AbstractWebAuthRequestedEvent constructor.
     *
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     */
    public function __construct(UserInterface $user, UrlContainerInterface $urlParams)
    {
        $this->user      = $user;
        $this->urlParams = $urlParams;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getUrlParams(): UrlContainerInterface
    {
        return $this->urlParams;
    }
}
