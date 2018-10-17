<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Url\UrlElementWithZoneInterface;

abstract class AbstractPlainUrlElementWithZone extends AbstractPlainUrlElementModel implements
    UrlElementWithZoneInterface
{
    public const OPTION_ZONE = 'zone';

    /**
     * @var string
     */
    private $zone;

    public function fromArray(array $data): void
    {

        if (isset($data[self::OPTION_ZONE])) {
            $this->zone = mb_strtolower($data[self::OPTION_ZONE]);
        }

        parent::fromArray($data);
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_ZONE => $this->getZoneName(),
        ]);
    }

    /**
     * Returns zone codename where this IFace is placed
     *
     * @return string
     */
    public function getZoneName(): string
    {
        return $this->zone;
    }
}
