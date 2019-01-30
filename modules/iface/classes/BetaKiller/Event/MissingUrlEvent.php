<?php
namespace BetaKiller\Event;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Url\UrlElementInterface;
use Psr\Http\Message\ServerRequestInterface;

class MissingUrlEvent implements EventMessageInterface
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
     * UrlDispatchedEvent constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Url\UrlElementInterface|null $parentModel
     * @param null|string                              $redirectTo
     */
    public function __construct(
        ServerRequestInterface $request,
        ?UrlElementInterface $parentModel,
        ?string $redirectTo
    ) {
        $this->parentModel   = $parentModel;
        $this->redirectToUrl = $redirectTo;
        $this->request       = $request;
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getMissedUrl(): string
    {
        return ServerRequestHelper::getUrl($this->request);
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
        return ServerRequestHelper::getHttpReferrer($this->request);
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
        return ServerRequestHelper::getIpAddress($this->request);
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

    /**
     * Must return true if message requires processing in external message queue (instead of internal queue)
     *
     * @return bool
     */
    public function isExternal(): bool
    {
        // Only internal processing
        return false;
    }
}
