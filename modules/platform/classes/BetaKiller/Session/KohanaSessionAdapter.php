<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Exception\NotImplementedHttpException;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class KohanaSessionAdapter implements SessionInterface, SessionIdentifierAwareInterface
{
    /**
     * @var \Kohana_Session
     */
    private $session;

    /**
     * KohanaSessionAdapter constructor.
     *
     * @param \Kohana_Session $session
     */
    public function __construct(\Kohana_Session $session)
    {
        $this->session = $session;
    }

    /**
     * Retrieve the session identifier.
     *
     * This feature was added in 1.1.0 to allow the session persistence to be
     * stateless. Previously, persistence implementations had to store the
     * session identifier between calls to initializeSessionFromRequest() and
     * persistSession(). When SessionInterface implementations also implement
     * this method, the persistence implementation no longer needs to store it.
     *
     * This method will become a part of the SessionInterface in 2.0.0.
     *
     * @since 1.1.0
     */
    public function getId(): string
    {
        return $this->session->id();
    }

    /**
     * Serialize the session data to an array for storage purposes.
     */
    public function toArray(): array
    {
        return $this->session->as_array();
    }

    /**
     * Retrieve a value from the session.
     *
     * @param string $name
     * @param mixed  $default Default value to return if $name does not exist.
     *
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    /**
     * Whether or not the container has the given key.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->session->has($name);
    }

    /**
     * Set a value within the session.
     *
     * Values MUST be serializable in any format; we recommend ensuring the
     * values are JSON serializable for greatest portability.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Remove a value from the session.
     *
     * @param string $name
     */
    public function unset(string $name): void
    {
        $this->session->delete($name);
    }

    /**
     * Clear all values.
     */
    public function clear(): void
    {
        throw new NotImplementedHttpException;
    }

    /**
     * Does the session contain changes? If not, the middleware handling
     * session persistence may not need to do more work.
     */
    public function hasChanged(): bool
    {
        throw new NotImplementedHttpException;
    }

    /**
     * Regenerate the session.
     *
     * This can be done to prevent session fixation. When executed, it SHOULD
     * return a new instance; that instance should always return true for
     * isRegenerated().
     *
     * An example of where this WOULD NOT return a new instance is within the
     * shipped LazySession, where instead it would return itself, after
     * internally re-setting the proxied session.
     */
    public function regenerate(): SessionInterface
    {
        throw new NotImplementedHttpException;
    }

    /**
     * Method to determine if the session was regenerated; should return
     * true if the instance was produced via regenerate().
     */
    public function isRegenerated(): bool
    {
        throw new NotImplementedHttpException;
    }

    public function persist(): void
    {
        $this->session->write();
    }
}
