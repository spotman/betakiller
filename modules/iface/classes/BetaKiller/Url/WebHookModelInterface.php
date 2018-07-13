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
    public function getServiceEventName(): string;

    /**
     * Returns target service event description (a case when event fired, limitations, etc)
     *
     * @return string
     */
    public function getServiceEventDescription(): string;

    /**
     * Returns ID provided by external service
     *
     * @return string
     */
    public function getExternalEventID(): string;
}
