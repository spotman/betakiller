<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

final class UserNameUrlParameter extends AbstractRawUrlParameter
{
    /**
     * @var string
     */
    private $userName;

    /**
     * @inheritDoc
     */
    protected function importUriValue(string $value): void
    {
        $this->userName = $value;
    }

    /**
     * @inheritDoc
     */
    public function exportUriValue(): string
    {
        return $this->userName;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->userName;
    }
}
