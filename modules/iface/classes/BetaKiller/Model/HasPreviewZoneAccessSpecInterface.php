<?php
namespace BetaKiller\Model;

interface HasPreviewZoneAccessSpecInterface
{
    public function isPreviewZoneAccessAllowed(): bool;
}
