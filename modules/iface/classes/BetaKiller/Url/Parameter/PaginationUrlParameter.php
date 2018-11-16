<?php
namespace BetaKiller\Url\Parameter;

class PaginationUrlParameter extends AbstractRawUrlParameter
{
    public const URI_PREFIX = 'page-';

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * Process uri and set internal state
     *
     * @param string $uriValue
     *
     * @throws \BetaKiller\Url\Parameter\UrlParameterException
     */
    public function importUriValue(string $uriValue): void
    {
        $page = (int)str_replace(static::URI_PREFIX, '', $uriValue);

        if (!$page) {
            throw new UrlParameterException('Incorrect UrlParameter uri: :value', [':value' => $uriValue]);
        }

        $this->pageNumber = $page;
    }

    /**
     * Returns composed uri for current state
     *
     * @return string
     */
    public function exportUriValue(): string
    {
        return static::URI_PREFIX.$this->pageNumber;
    }

    public function getValue(): int
    {
        return $this->pageNumber;
    }
}
