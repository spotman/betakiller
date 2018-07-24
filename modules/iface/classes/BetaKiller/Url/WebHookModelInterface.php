<?php
declare(strict_types=1);

namespace BetaKiller\Url;

interface WebHookModelInterface extends UrlElementInterface
{
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
