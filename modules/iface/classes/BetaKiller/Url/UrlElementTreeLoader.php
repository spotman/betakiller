<?php

declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Url\ModelProvider\UrlElementProviderDatabase;
use BetaKiller\Url\ModelProvider\UrlElementProviderXmlConfig;

use function in_array;

readonly class UrlElementTreeLoader
{
    /**
     * UrlElementTreeLoader constructor.
     *
     * @param \BetaKiller\Url\ModelProvider\UrlElementProviderDatabase  $databaseProvider
     * @param \BetaKiller\Url\ModelProvider\UrlElementProviderXmlConfig $xmlProvider
     * @param \BetaKiller\Env\AppEnvInterface                           $appEnv
     */
    public function __construct(
        private UrlElementProviderDatabase $databaseProvider,
        private UrlElementProviderXmlConfig $xmlProvider,
        private AppEnvInterface $appEnv
    ) {
    }

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree *
     *
     * @return void
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function loadInto(UrlElementTreeInterface $tree): void
    {
//        $this->logger->debug('Loading URL elements tree from providers');

        /** @var \BetaKiller\Url\ModelProvider\UrlElementProviderInterface[] $sources */
        $sources = [
            $this->xmlProvider,
        ];

        // TODO Remove this hack after resolving spotman/betakiller#35
//        if (!$this->appEnv->inTestingMode()) {
//            $sources[] = $this->databaseProvider;
//        }

        $envMode = $this->appEnv->getModeName();

        foreach ($sources as $provider) {
            foreach ($provider->getAll() as $urlElement) {
                // Skip adding Url Elements which are not allowed in the current env
                if (!$this->isSatisfiedByEnv($urlElement, $envMode)) {
                    continue;
                }

                $tree->add($urlElement, true); // No overwriting allowed
            }
        }
    }

    private function isSatisfiedByEnv(UrlElementInterface $urlElement, string $envMode): bool
    {
        if (!$urlElement->hasEnvironmentRestrictions()) {
            return true;
        }

        return in_array($envMode, $urlElement->getAllowedEnvironments(), true);
    }
}
