<?php
declare(strict_types=1);

namespace BetaKiller\Model;

trait EnumBasedEntityTrait
{
    public static function getModelName(): string
    {
        $className = static::class;

        // Try namespaces first
        $pos = strrpos($className, '\\');

        if ($pos === false) {
            throw new \LogicException(sprintf('Wrong naming for enum-based Entity %s', $className));
        }

        return substr($className, $pos + 1);
    }

    public function hasID(): bool
    {
        return true;
    }

    public function getID(): string
    {
        return (string)$this->value;
    }
}
