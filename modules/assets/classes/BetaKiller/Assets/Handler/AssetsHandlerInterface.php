<?php
namespace BetaKiller\Assets\Handler;

use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Model\UserInterface;

interface AssetsHandlerInterface
{
    public function update(AssetsProviderInterface $provider, $model, array $postData, UserInterface $user): void;
}
