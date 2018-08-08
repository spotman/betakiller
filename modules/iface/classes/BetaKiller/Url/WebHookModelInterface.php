<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Url\Parameter\UrlParameterInterface;

interface WebHookModelInterface extends UrlElementInterface, UrlParameterInterface
{
    public const URL_CONTAINER_KEY = 'WebHook';
    public const URL_KEY           = 'codename';

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
