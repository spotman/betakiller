<?php
namespace BetaKiller\Model;

interface HasPersonalZoneAccessSpecificationInterface
{
    public function isPersonalZoneAccessAllowed(): bool;
}
