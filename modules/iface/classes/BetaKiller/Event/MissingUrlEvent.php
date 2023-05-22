<?php
namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\UriInterface;

final class MissingUrlEvent implements EventMessageInterface
{
    /**
     * @var UrlElementInterface|null
     */
    private ?UrlElementInterface $parentModel;

    /**
     * @var null|string
     */
    private ?string $redirectToUrl;

    /**
     * @var UriInterface
     */
    private UriInterface $missedUri;

    /**
     * @var string
     */
    private string $ip;

    /**
     * @var string|null
     */
    private ?string $referrer;

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
        UriInterface         $missedUri,
        ?UrlElementInterface $parentModel,
        ?string              $redirectTo,
        string               $ip,
        ?string              $referrer
    ) {
        $this->parentModel   = $parentModel;
        $this->redirectToUrl = $redirectTo;
        $this->missedUri     = $missedUri;
        $this->ip            = $ip;
        $this->referrer      = $referrer;
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
}
