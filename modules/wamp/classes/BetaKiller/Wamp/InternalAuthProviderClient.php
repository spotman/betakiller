<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Thruway\Authentication\AbstractAuthProviderClient;

class InternalAuthProviderClient extends AbstractAuthProviderClient
{
    public const METHOD_NAME = 'internal';

    /**
     * @return mixed
     */
    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }

    /**
     * Process Authenticate message
     *
     * @param mixed $signature
     * @param mixed $extra
     *
     * @return array
     */
    public function processAuthenticate($signature, $extra = null): array
    {
        if ($signature === getenv('APP_REVISION')) {
            $authDetails = [
                'authmethod' => self::METHOD_NAME,
                'authrole'   => 'user',
            ];

            return ['SUCCESS', $authDetails];
        }

        return ['FAILURE'];
    }
}
