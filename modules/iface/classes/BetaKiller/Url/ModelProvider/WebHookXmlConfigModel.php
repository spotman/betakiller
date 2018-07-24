<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Url\WebHookModelInterface;

class WebHookXmlConfigModel extends AbstractXmlConfigModel implements WebHookModelInterface
{
    public const OPTION_SERVICE_NAME  = 'service';
    public const OPTION_SERVICE_EVENT = 'event';
    public const OPTION_DESCRIPTION   = 'description';

    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $description;

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

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_SERVICE_NAME  => $this->getServiceName(),
            self::OPTION_SERVICE_EVENT => $this->getEventName(),
            self::OPTION_DESCRIPTION   => $this->getEventDescription(),
        ]);
    }

    public function fromArray(array $data): void
    {
        $this->service     = $data[self::OPTION_SERVICE_NAME];
        $this->event       = $data[self::OPTION_SERVICE_EVENT];
        $this->description = $data[self::OPTION_DESCRIPTION] ?? null;

        parent::fromArray($data);
    }

    /**
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        // Always hide in sitemap
        return true;
    }
}
