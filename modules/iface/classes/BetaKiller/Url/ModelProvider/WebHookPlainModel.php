<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\WebHookModelTrait;
use BetaKiller\Url\WebHookModelInterface;

class WebHookPlainModel extends AbstractPlainUrlElementModel implements WebHookModelInterface
{
    use WebHookModelTrait;

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
     * Returns key which will be used for storing model in UrlContainer registry.
     *
     * @return string
     */
    public static function getUrlContainerKey(): string
    {
        return WebHookModelInterface::URL_CONTAINER_KEY;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return WebHookModelInterface::URL_CONTAINER_KEY;
    }

    /**
     * Returns value of the $key property
     *
     * @param string $key
     *
     * @return string
     */
    public function getUrlKeyValue(string $key): string
    {
        if ($key !== WebHookModelInterface::URL_KEY) {
            throw new UrlElementException('WebHook model may be mapped through codename only');
        }

        return $this->getCodename();
    }

    /**
     * Entity may return instances of linked entities if it have.
     * This method is used to fetch missing entities in UrlContainer walking through links between them
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface[]
     */
    public function getLinkedEntities(): array
    {
        return [];
    }

    /**
     * Returns true if this entity has linked one with provided key
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasLinkedEntity(string $key): bool
    {
        return false;
    }
}
