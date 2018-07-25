<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\WebHookModelInterface;

/**
 * Class WebHook
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller\Url
 */
class WebHook extends AbstractOrmModelContainsUrlElement implements WebHookModelInterface
{
    use WebHookModelTrait;

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
    public function getEventName(): string
    {
        return $this->event;
    }

    /**
     * Returns target service event description (a case when event fired, limitations, etc)
     *
     * @return string|null
     */
    public function getEventDescription(): ?string
    {
        return $this->description;
    }

    private function getService(): WebHookService
    {
        return $this->service;
    }
}
