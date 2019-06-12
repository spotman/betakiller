<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use LogicException;
use function base64_decode;
use function base64_encode;
use function explode;
use function random_bytes;
use function sodium_crypto_secretbox;
use function sodium_crypto_secretbox_open;

class Encryption
{
    private const NONCE_DELIMITER = '^';

    /**
     * @param string $data
     * @param string $key
     *
     * @return string
     */
    public function encrypt(string $data, string $key): string
    {
        $key = $this->decodeKey($key);

        $nonce  = random_bytes(\SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($data, $nonce, $key);

        return base64_encode($nonce).self::NONCE_DELIMITER.base64_encode($cipher);
    }

    /**
     * @param string $content
     * @param string $key
     *
     * @return string
     */
    public function decrypt(string $content, string $key): string
    {
        $key = $this->decodeKey($key);

        [$nonce, $text] = explode(self::NONCE_DELIMITER, $content);

        $data = sodium_crypto_secretbox_open(base64_decode($text), base64_decode($nonce), $key);

        if ($data === false) {
            throw new LogicException('Bad ciphertext');
        }

        return $data;
    }

    public function generateKey(): string
    {
        return base64_encode(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
    }

    private function decodeKey(string $key): string
    {
        $key = base64_decode($key);

        if (!$key) {
            throw new LogicException('Key must be in BASE64 encoding');
        }

        return $key;
    }
}
