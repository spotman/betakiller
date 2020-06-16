<?php
namespace BetaKiller\Event;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class UrlDispatchedEvent implements EventMessageInterface
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * UrlDispatchedEvent constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
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
    public function getUrl(): string
    {
        return ServerRequestHelper::getUrl($this->request);
    }

    /**
     * @return string|null
     */
    public function getHttpReferer(): ?string
    {
        return ServerRequestHelper::getHttpReferrer($this->request);
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return ServerRequestHelper::getIpAddress($this->request);
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getUrlContainer(): UrlContainerInterface
    {
        return ServerRequestHelper::getUrlContainer($this->request);
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
