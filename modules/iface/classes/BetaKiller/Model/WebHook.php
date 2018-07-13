<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\WebHookModelInterface;

/**
 * Class WebHook
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class WebHook extends AbstractOrmModelContainsUrlElement implements WebHookModelInterface
{
    protected function configure(): void
    {
        $this->belongs_to([
            'service' => [
                'model' => 'WebHookService',
                'foreign_key' => 'service_id',
            ],
        ]);

        $this->load_with(['service']);

        parent::configure();
    }

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
        return $this->getService()->getCodename();
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

    /**
     * Returns ID provided by external service
     *
     * @return string
     */
    public function getExternalEventID(): string
    {
        return $this->external_event_id;
    }

    private function getService(): WebHookService
    {
        return $this->service;
    }
}
