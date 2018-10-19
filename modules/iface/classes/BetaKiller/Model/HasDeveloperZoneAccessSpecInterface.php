<?php
namespace BetaKiller\Model;

interface HasDeveloperZoneAccessSpecInterface
{
    public function isDeveloperZoneAccessAllowed(): bool;
}
