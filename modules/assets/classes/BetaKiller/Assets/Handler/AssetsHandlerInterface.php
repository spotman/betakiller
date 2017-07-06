<?php
namespace BetaKiller\Assets\Handler;

use BetaKiller\Assets\Provider\AssetsProviderInterface;

interface AssetsHandlerInterface
{
    public function update(AssetsProviderInterface $provider, $model, array $postData): void;
}
