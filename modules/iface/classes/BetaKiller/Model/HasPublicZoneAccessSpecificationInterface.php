<?php
namespace BetaKiller\Model;

interface HasPublicZoneAccessSpecificationInterface
{
    public function isPublicZoneAccessAllowed(): bool;
}
