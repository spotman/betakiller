<?php
declare(strict_types=1);

namespace BetaKiller\Url\Parameter;

class IdUrlParameter extends AbstractRawUrlParameter
{
    /**
     * @var string
     */
    private string $id;

    /**
     * Process uri and set internal state
     *
     * @param string $uriValue
     *
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    protected function importUriValue(string $uriValue): void
    {
        if (!$uriValue) {
            throw new UrlParameterException('Incorrect IdUrlParameter uri: :value', [':value' => $uriValue]);
        }

        $this->id = $uriValue;
    }

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->id;
    }
}
