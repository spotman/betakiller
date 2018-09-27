<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Exception\NotImplementedHttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    /**
     * Generate a session data instance based on the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        throw new NotImplementedHttpException();
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
    public function persistSession(SessionInterface $session, ResponseInterface $response): ResponseInterface
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
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function getByID(string $id): SessionInterface
    {
        $kohanaSession = new \Session_Database([], $id);

        return new KohanaSessionAdapter($kohanaSession);
    }
}
