<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Model\AbstractEntityInterface;

class DummyIdentityConverter implements IdentityConverterInterface
{
    /**
     * Returns encoded ID for provided Entity
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return string
     */
    public function encode(AbstractEntityInterface $entity): string
    {
        // Just plain ID
        return $entity->getID();
    }

    /**
     * Returns decoded ID for provided Entity name
     *
     * @param string $entityName
     * @param string $value
     *
     * @return string
     */
    public function decode(string $entityName, string $value): string
    {
        // No conversion
        return $value;
    }
}
