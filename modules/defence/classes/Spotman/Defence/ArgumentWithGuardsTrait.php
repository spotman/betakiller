<?php
declare(strict_types=1);

namespace Spotman\Defence;

trait ArgumentWithGuardsTrait
{
    protected function checkGuardIsAllowed(GuardInterface $guard): void
    {
        $type    = $this->getType();
        $allowed = $guard->getArgumentTypes();

        if (!\in_array($type, $allowed, true)) {
            throw new \DomainException(sprintf(
                '"%s" may be applied to these argument types only: "%s"',
                \get_class($guard),
                \implode('", "', $allowed)
            ));
        }
    }
}
