<?php
declare(strict_types=1);


use BetaKiller\ModuleInitializerInterface;

final class OrmModuleInitializer implements ModuleInitializerInterface
{
    private OrmFactoryInterface $factory;

    public function __construct(OrmFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function initModule(): void
    {
        ORM::setModelFactory($this->factory);
    }
}
