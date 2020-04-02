<?php
declare(strict_types=1);

namespace BetaKiller\Security;

use BetaKiller\Exception\SecurityException;
use BetaKiller\Helper\ActionRequestHelper;
use BetaKiller\Helper\ServerRequestHelper;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Defence\ArgumentsDefinitionProviderInterface;
use Spotman\Defence\DefinitionBuilderInterface;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class CsrfService implements ArgumentsDefinitionProviderInterface
{
    public const SESSION_TOKENS_KEY = 'csrf';
    public const REQUEST_TOKEN_KEY  = 'csrf';

    /**
     * @var \BetaKiller\Security\Encryption
     */
    private $encryption;

    /**
     * CsrfService constructor.
     *
     * @param \BetaKiller\Security\Encryption $encryption
     */
    public function __construct(Encryption $encryption)
    {
        $this->encryption = $encryption;
    }

    public function createRequestToken(ServerRequestInterface $request): string
    {
        $session = ServerRequestHelper::getSession($request);

        return $this->createToken($session);
    }

    public function createToken(SessionInterface $session): string
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new LogicException('Session must implement SessionIdentifierAwareInterface');
        }

        $token = $this->encryption->generateKey();

        $key = $this->makeSessionTokenName($token);

        if ($session->has($key)) {
            throw new LogicException('Duplicate CSRF token ":value" in session ":id"', [
                ':id'    => $session->getId(),
                ':value' => $token,
            ]);
        }

        // Store token in session
        $session->set($key, true);

        return $token;
    }

    public function checkSessionToken(SessionInterface $session, string $token): void
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new LogicException('Session must implement SessionIdentifierAwareInterface');
        }

        $key = $this->makeSessionTokenName($token);

        if (!$session->has($key)) {
            throw new SecurityException('Missing CSRF token ":value" in session ":id"', [
                ':id'    => $session->getId(),
                ':value' => $token,
            ]);
        }
    }

    public function clearSessionToken(SessionInterface $session, string $token): void
    {
        $key = $this->makeSessionTokenName($token);

        // Remove one-time token
        $session->unset($key);
    }

    public function checkRequestToken(ServerRequestInterface $request, string $token): void
    {
        $session = ServerRequestHelper::getSession($request);

        $this->checkSessionToken($session, $token);
    }

    public function clearRequestToken(ServerRequestInterface $request, string $token): void
    {
        $session = ServerRequestHelper::getSession($request);

        $this->clearSessionToken($session, $token);
    }

    public function checkActionToken(ServerRequestInterface $request): void
    {
        $token = $this->getActionToken($request);

        $this->checkRequestToken($request, $token);
    }

    public function clearActionToken(ServerRequestInterface $request): void
    {
        $token = $this->getActionToken($request);

        $this->clearRequestToken($request, $token);
    }

    /**
     * @param \Spotman\Defence\DefinitionBuilderInterface $builder
     */
    public function addArgumentsDefinition(DefinitionBuilderInterface $builder): void
    {
        $builder->string(self::REQUEST_TOKEN_KEY);
    }

    private function makeSessionTokenName(string $hash): string
    {
        return self::SESSION_TOKENS_KEY.'.'.$hash;
    }

    private function getActionToken(ServerRequestInterface $request): string
    {
        return ActionRequestHelper::postArguments($request)->getString(self::REQUEST_TOKEN_KEY);
    }
}
