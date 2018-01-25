<?php
declare(strict_types=1);

namespace BetaKiller\IFace;


use BetaKiller\ModuleInitializerInterface;

class ModuleInitializer implements ModuleInitializerInterface
{
    /**
     * @Inject
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase
     */
    private $databaseProvider;

    /**
     * @Inject
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig
     */
    private $xmlProvider;

    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function init(): void
    {
        /** @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderInterface[] $sources */
        $sources = [
            $this->xmlProvider,
            $this->databaseProvider,
        ];

        foreach ($sources as $provider) {
            foreach ($provider->getAll() as $ifaceModel) {
                $this->tree->add($ifaceModel);
            }
        }

        // TODO Cache tree and get it from cache if exists
    }
}
