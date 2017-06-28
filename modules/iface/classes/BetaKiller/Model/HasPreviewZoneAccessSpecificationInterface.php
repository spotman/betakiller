<?php
namespace BetaKiller\Model;

interface HasPreviewZoneAccessSpecificationInterface
{
    public function isPreviewZoneAccessAllowed(): bool;
}
