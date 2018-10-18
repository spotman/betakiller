<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\HttpDriverInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Session\SessionInterface;

class DebugBarHttpDriver implements HttpDriverInterface
{
    /**
     * @var string[]
     */
    private $headers = [];

    /**
     * @var \Zend\Expressive\Session\SessionInterface
     */
    private $session;

    /**
     * DebugBarHttpDriver constructor.
     *
     * @param \Zend\Expressive\Session\SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function applyHeaders(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * Sets HTTP headers
     *
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = \array_merge($this->headers, $headers);
    }

    /**
     * Checks if the session is started
     *
     * @return boolean
     */
    public function isSessionStarted(): bool
    {
        // Always started
        return true;
    }

    /**
     * Sets a value in the session
     *
     * @param string $name
     * @param string $value
     */
    public function setSessionValue($name, $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Checks if a value is in the session
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasSessionValue($name): bool
    {
        return $this->session->has($name);
    }

    /**
     * Returns a value from the session
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getSessionValue($name)
    {
        return $this->session->get($name);
    }

    /**
     * Deletes a value from the session
     *
     * @param string $name
     */
    public function deleteSessionValue($name): void
    {
        $this->session->unset($name);
    }
}
