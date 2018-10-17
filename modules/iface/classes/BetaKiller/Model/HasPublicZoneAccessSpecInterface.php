<?php
namespace BetaKiller\Model;

interface HasPublicZoneAccessSpecInterface
{
    public function isPublicZoneAccessAllowed(): bool;
}
