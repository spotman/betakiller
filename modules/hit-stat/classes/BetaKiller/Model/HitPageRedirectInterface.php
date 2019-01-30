<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface HitPageRedirectInterface extends DispatchableEntityInterface
{
    public const URL_KEY = 'id';

    public function getUrl(): string;

    public function setUrl(string $value): HitPageRedirectInterface;
}
