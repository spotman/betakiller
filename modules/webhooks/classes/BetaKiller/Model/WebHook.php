<?php
namespace BetaKiller\Model;

class WebHook extends AbstractConfigBasedDispatchableEntity implements WebHookModelInterface
{
    public const OPTION_SERVICE_NAME  = 'service';
    public const OPTION_SERVICE_EVENT = 'event';
    public const OPTION_DESCRIPTION   = 'description';

    /**
     * Returns target service name (website domain or company name)
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return (string)$this->getConfigOption(self::OPTION_SERVICE_NAME);
    }

    /**
     * Returns target service event name (for information purpose)
     *
     * @return string
     */
    public function getEventName(): string
    {
        return (string)$this->getConfigOption(self::OPTION_SERVICE_EVENT);
    }

    /**
     * Returns target service event description (a case when event fired, limitations, etc)
     *
     * @return string|null
     */
    public function getEventDescription(): ?string
    {
        return $this->getConfigOption(self::OPTION_DESCRIPTION);
    }

    /**
     * @return string
     */
    public static function getModelName(): string
    {
        return WebHookModelInterface::MODEL_NAME;
    }
}
