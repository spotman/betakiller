<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

interface PasswordHasherInterface
{
    /**
     * Returns hashed password string
     *
     * @param string $password
     *
     * @return string
     */
    public function proceed(string $password): string;
}
