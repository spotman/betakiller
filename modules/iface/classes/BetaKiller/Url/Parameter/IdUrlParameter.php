<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

class IdUrlParameter extends AbstractRawUrlParameter
{
    /**
     * @var int
     */
    private $id;

    /**
     * Process uri and set internal state
     *
     * @param string $uriValue
     *
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public function importUriValue(string $uriValue): void
    {
        $id = (int)$uriValue;

        if (!$id) {
            throw new UrlParameterException('Incorrect IdUrlParameter uri: :value', [':value' => $uriValue]);
        }

        $this->id = $id;
    }

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string
    {
        return (string)$this->id;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->id;
    }
}
