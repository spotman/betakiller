<?php
declare(strict_types=1);

namespace BetaKiller\Security;

interface EncryptionInterface
{
    /**
     * @param string $data
     * @param string $key
     *
     * @return string
     */
    public function encrypt(string $data, string $key): string;

    /**
     * @param string $content
     * @param string $key
     *
     * @return string
     */
    public function decrypt(string $content, string $key): string;

    /**
     * @return string
     */
    public function generateKey(): string;
}
