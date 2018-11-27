<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class AggregateLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \BetaKiller\I18n\I18nKeysLoaderInterface[]
     */
    private $loaders;

    /**
     * AggregateLoader constructor.
     *
     * @param \BetaKiller\I18n\I18nKeysLoaderInterface[] $loaders
     */
    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Returns keys
     *
     * @return \BetaKiller\Model\I18nKeyInterface[]
     */
    public function loadI18nKeys(): array
    {
        $data = [];

        foreach ($this->loaders as $loader) {
            $data[] = $loader->loadI18nKeys();
        }

        return \array_merge(...$data);
    }

    private function addLoader(I18nKeysLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }
}
