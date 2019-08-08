<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\TokenInterface;
use BetaKiller\Model\UserInterface;
use DateTimeImmutable;
use LogicException;
use Zend\Expressive\Session\SessionInterface;

class SessionHelper
{
    public const CREATED_AT   = 'created_at';
    public const PERSISTENT   = 'persistent';
    public const AUTH_USER_ID = 'auth_user';
    public const ORIGIN_URL   = 'origin_url';
    public const TOKEN_HASH   = 'token';

    public static function transferData(SessionInterface $from, SessionInterface $to): void
    {
        foreach ($from->toArray() as $key => $value) {
            // Skip existing keys
            if (!$to->has($key)) {
                $to->set($key, $value);
            }
        }
    }

    public static function setCreatedAt(SessionInterface $session, DateTimeImmutable $createdAt): void
    {
        $session->set(self::CREATED_AT, $createdAt->getTimestamp());
    }

    public static function getCreatedAt(SessionInterface $session): DateTimeImmutable
    {
        $ts = $session->get(self::CREATED_AT);

        return DateTimeHelper::createDateTimeFromTimestamp($ts);
    }

    public static function markAsPersistent(SessionInterface $session): void
    {
        $session->set(self::PERSISTENT, true);
    }

    public static function isPersistent(SessionInterface $session): bool
    {
        return (bool)$session->get(self::PERSISTENT);
    }

    public static function setUserID(SessionInterface $session, UserInterface $user): void
    {
        if ($user instanceof GuestUserInterface) {
            throw new LogicException('Session user can not be a guest but real user only');
        }

        $session->set(self::AUTH_USER_ID, $user->getID());
    }

    /**
     * Null means guest user
     *
     * @param \Zend\Expressive\Session\SessionInterface $session
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

    public static function getOriginUrl(SessionInterface $session): ?string
    {
        return $session->get(self::ORIGIN_URL);
    }

    public static function setOriginUrl(SessionInterface $session, string $url): void
    {
        $session->set(self::ORIGIN_URL, $url);
    }

    public static function getTokenHash(SessionInterface $session): ?string
    {
        return $session->get(self::TOKEN_HASH);
    }

    public static function setTokenHash(SessionInterface $session, TokenInterface $token): void
    {
        $session->set(self::TOKEN_HASH, $token->getValue());
    }

    public static function checkToken(SessionInterface $session): void
    {
        $tokenHash = static::getTokenHash($session);

        if (!$tokenHash) {
            throw new AccessDeniedException('Session token is required for further processing');
        }
    }
}
