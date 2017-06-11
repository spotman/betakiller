<?php
namespace BetaKiller\Model;

interface EntityWithPreviewModeInterface
{
    public function isPreviewNeeded(): bool;
}
