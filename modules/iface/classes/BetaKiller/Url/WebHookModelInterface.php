<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Model\DispatchableEntityInterface;

interface WebHookModelInterface extends UrlElementInterface, DispatchableEntityInterface
{
    public const URL_CONTAINER_KEY = 'WebHook';

    /**
     * Returns target service name (website domain or company name)
     *
     * @return string
     */
    public function getServiceName(): string;

    /**
     * Returns target service event name (for information purpose)
     *
     * @return string
     */
    public function getEventName(): string;

    /**
     * Returns target service event description (a case when event fired, limitations, etc)
     *
     * @return string|null
     */
    public function getEventDescription(): ?string;
}
