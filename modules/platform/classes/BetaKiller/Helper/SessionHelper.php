<?php

declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Session\SessionCause;
use DateTimeImmutable;
use LogicException;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Mezzio\Session\SessionInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SessionHelper
{
    public const AUTH_USER_ID       = 'auth_user_id';
    public const DEBUG              = 'debug';
    public const VERIFICATION_TOKEN = 'token';

    private const SERVICE_KEY_PREFIX = '_';

    public static function makeServiceKey(string $name): string
    {
        return self::SERVICE_KEY_PREFIX.$name;
    }

    public static function importData(array $data, SessionInterface $to): void
    {
        foreach ($data as $key => $value) {
            // Skip existing keys
            if (!$to->has($key)) {
                $to->set($key, $value);
            }
        }
    }

    public static function transferData(SessionInterface $from, SessionInterface $to): void
    {
        self::importData($from->toArray(), $to);
    }

    public static function getId(SessionInterface $session): string
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new LogicException();
        }

        return $session->getId();
    }

    public static function isEmpty(SessionInterface $session): bool
    {
        // Filter service keys
        return empty(array_filter(array_keys($session->toArray()), fn(string $key) => !str_starts_with($key, self::SERVICE_KEY_PREFIX)));
    }

    public static function setUserID(SessionInterface $session, UserInterface $user): void
    {
        if ($user instanceof GuestUserInterface) {
            throw new LogicException('Session user can not be a guest but real user only');
        }

        $session->set(self::AUTH_USER_ID, $user->getID());
    }

    public static function hasUserID(SessionInterface $session): bool
    {
        return $session->has(self::AUTH_USER_ID);
    }

    /**
     * Null means guest user
     *
     * @param \Mezzio\Session\SessionInterface $session
     *
     * @return string|null
     */
    public static function getUserID(SessionInterface $session): ?string
    {
        return $session->get(self::AUTH_USER_ID);
    }

    public static function removeUserID(SessionInterface $session): void
    {
        if ($session->has(self::AUTH_USER_ID)) {
            $session->unset(self::AUTH_USER_ID);
        }
    }

    public static function getVerificationToken(SessionInterface $session): ?string
    {
        return $session->get(self::VERIFICATION_TOKEN);
    }

    public static function setVerificationToken(SessionInterface $session, TokenInterface $token): void
    {
        $session->set(self::VERIFICATION_TOKEN, $token->getValue());
    }

    public static function checkVerificationTokenExists(SessionInterface $session): void
    {
        $tokenHash = static::getVerificationToken($session);

        if (!$tokenHash) {
            throw new AccessDeniedException('Session token is required for further processing');
        }
    }

    public static function enableDebug(SessionInterface $session): void
    {
        $session->set(self::DEBUG, true);
    }

    public static function disableDebug(SessionInterface $session): void
    {
        $session->set(self::DEBUG, false);
    }

    public static function hasDebugDefined(SessionInterface $session): bool
    {
        return $session->has(self::DEBUG);
    }

    public static function isDebugEnabled(SessionInterface $session): ?bool
    {
        return (bool)$session->get(self::DEBUG);
    }

    public static function hasCause(SessionInterface $session): bool
    {
        return $session->has(self::getCauseKey());
    }

    public static function getCause(SessionInterface $session): SessionCause
    {
        $raw = $session->get(self::getCauseKey());

        return SessionCause::fromCodename($raw);
    }

    public static function setCause(SessionInterface $session, SessionCause $value): void
    {
        $session->set(self::getCauseKey(), $value->getCodename());
    }

    public static function hasUserAgentHash(SessionInterface $session): bool
    {
        return $session->has(self::getUserAgentHashKey());

    }

    public static function getUserAgentHash(SessionInterface $session): string
    {
        return $session->get(self::getUserAgentHashKey());
    }

    public static function setUserAgentHash(SessionInterface $session, string $value): void
    {
        $session->set(self::getUserAgentHashKey(), $value);
    }

    private static function getUserAgentHashKey(): string
    {
        return self::makeServiceKey('ua');
    }

    private static function getCauseKey(): string
    {
        return self::makeServiceKey('cause');
    }
}
