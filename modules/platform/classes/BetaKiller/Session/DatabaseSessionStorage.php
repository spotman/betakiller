<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionInterface as ZendSessionInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    /**
     * Generate a session data instance based on the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request): ZendSessionInterface
    {
        $cookies = $request->getCookieParams();
        $sessionCookie = $cookies[Auth::SESSION_COOKIE] ?? '';
        $parts = explode(Auth::SESSION_COOKIE_DELIMITER, $sessionCookie, 2);
        $sid = \array_pop($parts);

        return $this->factory($sid);
    }

    /**
     * Persist the session data instance.
     *
     * Persists the session data, returning a response instance with any
     * artifacts required to return to the client.
     *
     * @param \Zend\Expressive\Session\SessionInterface $session
     * @param \Psr\Http\Message\ResponseInterface       $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function persistSession(ZendSessionInterface $session, ResponseInterface $response): ResponseInterface
    {
        if (!$session instanceof KohanaSessionAdapter) {
            throw new \RuntimeException('Session object must be instance of KohanaSessionAdapter');
        }

        $session->persist();

        return $response;
    }

    /**
     * @param string $id
     *
     * @return \BetaKiller\Session\SessionInterface
     */
    public function getByID(string $id): SessionInterface
    {
        return $this->factory($id);
    }

    private function factory(?string $id): SessionInterface
    {
        $config = [
            'name' => Auth::SESSION_COOKIE,
        ];

        $kohanaSession = new \Session_Database($config, $id);

        return new KohanaSessionAdapter($kohanaSession);
    }
}
