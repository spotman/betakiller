<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class JsonPluralBagFormatter implements PluralBagFormatterInterface
{
    /**
     * @var \BetaKiller\I18n\PluralBagFactoryInterface
     */
    private $bagFactory;

    /**
     * JsonPluralBagFormatter constructor.
     *
     * @param \BetaKiller\I18n\PluralBagFactoryInterface $bagFactory
     */
    public function __construct(PluralBagFactoryInterface $bagFactory)
    {
        $this->bagFactory = $bagFactory;
    }

    /**
     * @param string $packedPluralString
     *
     * @return \BetaKiller\I18n\PluralBagInterface
     */
    public function parse(string $packedPluralString): PluralBagInterface
    {
        $data = \json_decode($packedPluralString, true);

        if (!\is_array($data)) {
            throw new I18nException('Incorrect plural bag string, ":value"', [
                ':value' => $packedPluralString,
            ]);
        }

        return $this->bagFactory->create($data);
    }

    /**
     * @param \BetaKiller\I18n\PluralBagInterface $bag
     *
     * @return string
     */
    public function compile(PluralBagInterface $bag): string
    {
        return \json_encode($bag->getAll());
    }

    /**
     * Returns true if provided string is packed with current formatter
     *
     * @param string $str
     *
     * @return bool
     */
    public function isFormatted(string $str): bool
    {
        return \mb_strstr($str, '{') === 0
            && \mb_strrpos($str, '}', -1) === \mb_strlen($str) - 1;
    }
}
