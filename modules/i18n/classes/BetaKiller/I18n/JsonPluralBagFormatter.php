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
        // Empty string means empty array
        $data = $packedPluralString
            ? \json_decode($packedPluralString, true)
            : [];

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
     * @param string $packedString
     *
     * @return bool
     */
    public function isFormatted(string $packedString): bool
    {
        return str_starts_with($packedString, '{') && str_ends_with($packedString, '}');
    }
}
