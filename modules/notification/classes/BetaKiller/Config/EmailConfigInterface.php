<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface EmailConfigInterface
{
    public function getHost(): string;

    public function getPort(): int;

    public function useEncryption(): bool;

    public function getUsername(): ?string;

    public function getPassword(): ?string;

    public function getTimeout(): int;

    public function getFromEmail(): string;
    public function getFromName(): string;

    public function getDomain(): string;
}
