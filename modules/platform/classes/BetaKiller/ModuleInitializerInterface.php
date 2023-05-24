<?php
namespace BetaKiller;

interface ModuleInitializerInterface
{
    /**
     * Array of other ModuleInitializer` class names (FQN)
     *
     * @return string[]
     */
    public static function getDependencies(): array;

    public function initModule(): void;
}
