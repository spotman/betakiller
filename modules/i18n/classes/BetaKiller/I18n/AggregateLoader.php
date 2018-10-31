<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

class AggregateLoader implements LoaderInterface
{
    /**
     * @var \BetaKiller\I18n\LoaderInterface[]
     */
    private $loaders;

    /**
     * AggregateLoader constructor.
     *
     * @param \BetaKiller\I18n\LoaderInterface[] $loaders
     */
    public function __construct(array $loaders)
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array
    {
        $data = [];

        foreach ($this->loaders as $loader) {
            $data[] = $loader->load($locale);
        }

        return \array_merge(...$data);
    }

    private function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }
}
