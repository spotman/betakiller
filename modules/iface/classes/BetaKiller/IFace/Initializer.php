<?php
declare(strict_types=1);

namespace BetaKiller\IFace;


use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase;
use BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig;
use BetaKiller\ModuleInitializerInterface;

class Initializer implements ModuleInitializerInterface
{
    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase
     */
    private $databaseProvider;

    /**
     * @var \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig
     */
    private $xmlProvider;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * Initializer constructor.
     *
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderDatabase $databaseProvider
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfig $xmlProvider
     * @param \BetaKiller\IFace\IFaceModelTree $tree
     */
    public function __construct(
        IFaceModelProviderDatabase $databaseProvider,
        IFaceModelProviderXmlConfig $xmlProvider,
        IFaceModelTree $tree
    ) {
        $this->databaseProvider = $databaseProvider;
        $this->xmlProvider      = $xmlProvider;
        $this->tree             = $tree;
    }


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
                $this->tree->add($ifaceModel); // Allow overwriting
            }
        }

        $this->tree->validate();

        // TODO Cache tree and get it from cache if exists
    }
}
