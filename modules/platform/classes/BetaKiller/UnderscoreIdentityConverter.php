<?php

declare(strict_types=1);

namespace BetaKiller;

use BetaKiller\Model\AbstractEntityInterface;

class UnderscoreIdentityConverter implements IdentityConverterInterface
{
    /**
     * Returns encoded ID for provided Entity
     *
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function encode(AbstractEntityInterface $entity): string
    {
        $id = $entity->getID();

        if (!is_numeric($id)) {
            throw new Exception('Entity ":name" has non-numeric ID ":id" and can not be converted', [
                ':name' => $entity::getModelName(),
                ':id'   => $id,
            ]);
        }

        // Make conversion to int to mimic hashids logic
        return sprintf('_%d_', (int)$id);
    }

    /**
     * Returns decoded ID for provided Entity name
     *
     * @param string $entityName
     * @param string $value
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function decode(string $entityName, string $value): string
    {
        // Check for underscores
        if (!str_starts_with($value, '_') || !str_ends_with($value, '_')) {
            throw new Exception('Entity ":name" ID must be surrounded by "underscore" symbols but ":value" provided', [
                ':value' => $value,
                ':name'  => $entityName,
            ]);
        }

        $result = trim($value, "_\r\n\0\x0B");

        if (!$result) {
            throw new Exception('Can not decode hashed ID ":id" for entity ":name"', [
                ':id'   => $value,
                ':name' => $entityName,
            ]);
        }

        return $result;
    }
}
