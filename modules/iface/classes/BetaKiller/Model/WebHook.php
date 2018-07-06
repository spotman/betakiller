<?php
namespace BetaKiller\Model;

use BetaKiller\Url\WebHookModelInterface;

/**
 * Class IFace
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class WebHook extends AbstractOrmModelContainsUrlElement implements WebHookModelInterface
{
    /**
     * Returns TRUE if current URL element is hidden in sitemap
     *
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        // Webhooks are always hidden in sitemap
        return true;
    }

    /**
     * Returns target service name (website domain or company name)
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->service;
    }

    /**
     * Returns target service event name (for information purpose)
     *
     * @return string
     */
    public function getServiceEventName(): string
    {
        return $this->event;
    }

    /**
     * Returns target service event description (a case when event fired, limitations, etc)
     *
     * @return string
     */
    public function getServiceEventDescription(): string
    {
        return $this->description;
    }
}
