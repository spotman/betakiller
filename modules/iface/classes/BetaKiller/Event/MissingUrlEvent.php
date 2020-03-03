<?php
namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class MissingUrlEvent implements EventMessageInterface
{
    /**
     * @var UrlElementInterface|null
     */
    private $parentModel;

    /**
     * @var null|string
     */
    private $redirectToUrl;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * @var UriInterface
     */
    private $missedUri;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string|null
     */
    private $referrer;

    /**
     * UrlDispatchedEvent constructor.
     *
     * @param UriInterface                             $missedUri
     * @param \BetaKiller\Url\UrlElementInterface|null $parentModel
     * @param null|string                              $redirectTo
     * @param string                                   $ip
     * @param string|null                              $referrer
     */
    public function __construct(
        UriInterface $missedUri,
        ?UrlElementInterface $parentModel,
        ?string $redirectTo,
        string $ip,
        ?string $referrer
    ) {
        $this->parentModel   = $parentModel;
        $this->redirectToUrl = $redirectTo;
        $this->missedUri     = $missedUri;
        $this->ip            = $ip;
        $this->referrer      = $referrer;
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return UriInterface
     */
    public function getMissedUri(): UriInterface
    {
        return $this->missedUri;
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface|null
     */
    public function getParentModel(): ?UrlElementInterface
    {
        return $this->parentModel;
    }

    /**
     * @return string|null
     */
    public function getHttpReferer(): ?string
    {
        return $this->referrer;
    }

    /**
     * @return null|string
     */
    public function getRedirectToUrl(): ?string
    {
        return $this->redirectToUrl;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ip;
    }

    /**
     * Must return true if message requires at least one handler to be processed
     *
     * @return bool
     */
    public function handlersRequired(): bool
    {
        return true;
    }
}
