<?php
namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Url\UrlContainerInterface;

class UrlDispatchedEvent implements EventMessageInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $httpReferer;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    private $params;

    /**
     * UrlDispatchedEvent constructor.
     *
     * @param string                                $url
     * @param \BetaKiller\Url\UrlContainerInterface $params
     * @param string                                $ip
     * @param string                                $httpReferer
     */
    public function __construct(string $url, UrlContainerInterface $params, string $ip, ?string $httpReferer)
    {
        $this->url         = $url;
        $this->ip          = $ip;
        $this->httpReferer = $httpReferer;
        $this->params      = $params;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getHttpReferer(): string
    {
        return $this->httpReferer;
    }

    /**
     * @return string
     */
    public function getIp(): string
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
        return false;
    }

    /**
     * @return \BetaKiller\Url\UrlContainerInterface
     */
    public function getUrlContainer(): UrlContainerInterface
    {
        return $this->params;
    }
}
