<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\Url\WebHookModelInterface;

class WebHookXmlConfigModel extends AbstractXmlConfigModel implements WebHookModelInterface
{
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
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            'service'     => $this->getServiceName(),
            'event'       => $this->getServiceEventName(),
            'description' => $this->getServiceEventDescription(),
        ]);
    }

    public function fromArray(array $data): void
    {
        $this->service     = $data['service'];
        $this->event       = $data['event'];
        $this->description = $data['description'];

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
