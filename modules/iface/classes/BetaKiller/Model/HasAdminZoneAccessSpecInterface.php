<?php
namespace BetaKiller\Model;

interface HasAdminZoneAccessSpecInterface
{
    public function isAdminZoneAccessAllowed(): bool;
}
