<?php
namespace BetaKiller\Model;

interface HasPersonalZoneAccessSpecInterface
{
    public function isPersonalZoneAccessAllowed(): bool;
}
