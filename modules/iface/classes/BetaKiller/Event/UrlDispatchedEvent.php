<?php
namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;

class UrlDispatchedEvent implements EventMessageInterface
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $httpReferer;

    /**
     * @var string
     */
    public $ip;

    /**
     * UrlDispatchedEvent constructor.
     *
     * @param string $url
     * @param string $ip
     * @param string $httpReferer
     */
    public function __construct(string $url, string $ip, ?string $httpReferer)
    {
        $this->url         = $url;
        $this->ip          = $ip;
        $this->httpReferer = $httpReferer;
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
}
