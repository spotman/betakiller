<?php
declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Model\AbstractEntityInterface;

class FakeIdentityConverter implements IdentityConverterInterface
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
        // Just plain ID with underscores
        return '_'.$entity->getID().'_';
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
        // Remove underscores
        return trim($value, '_');
    }
}
