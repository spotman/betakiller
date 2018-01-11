<?php
namespace BetaKiller\Url;


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
     * @throws \BetaKiller\Url\UrlParameterException
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
        return $this->id;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }
}
