<?php
namespace BetaKiller\Model;

interface HasAdminZoneAccessSpecificationInterface
{
    public function isAdminZoneAccessAllowed(): bool;
}
