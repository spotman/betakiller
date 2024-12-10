<?php

declare(strict_types=1);

namespace BetaKiller\Url;

/**
 * @method static \BetaKiller\Url\ZoneInterface public ()
 * @method static \BetaKiller\Url\ZoneInterface personal()
 * @method static \BetaKiller\Url\ZoneInterface preview()
 * @method static \BetaKiller\Url\ZoneInterface admin()
 * @method static \BetaKiller\Url\ZoneInterface developer()
 */
readonly class Zone implements ZoneInterface
{
    final public static function __callStatic(string $name, array $arguments): ZoneInterface
    {
        return self::factory($name);
    }

    final protected static function factory(string $value): ZoneInterface
    {
        return new static($value);
    }

    final protected function __construct(protected string $value)
    {
    }

    /**
     * @inheritDoc
     */
    final public function getName(): string
    {
        return $this->value;
    }
}
