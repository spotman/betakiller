<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

readonly class UserInfo
{
    public function __construct(
        public string  $ip,
        public ?string $email,
        public ?string $phone = null,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $role = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
    ) {
    }
}
