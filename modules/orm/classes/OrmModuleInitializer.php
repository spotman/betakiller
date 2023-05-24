<?php
declare(strict_types=1);


use BetaKiller\ModuleInitializerInterface;

final class OrmModuleInitializer implements ModuleInitializerInterface
{
    private OrmFactoryInterface $factory;

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    public function __construct(OrmFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function initModule(): void
    {
        ORM::setModelFactory($this->factory);
    }
}
