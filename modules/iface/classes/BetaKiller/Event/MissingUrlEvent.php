<?php
namespace BetaKiller\Event;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\MessageBus\EventMessageInterface;

class MissingUrlEvent implements EventMessageInterface
{
    /**
     * @var string
     */
    private $missedUrl;

    /**
     * @var string
     */
    private $httpReferer;

    /**
     * @var IFaceModelInterface|null
     */
    private $parentModel;

    /**
     * @var null|string
     */
    private $redirectToUrl;

    /**
     * @var string
     */
    private $ip;

    /**
     * UrlDispatchedEvent constructor.
     *
     * @param string                   $url
     * @param IFaceModelInterface|null $parentModel
     * @param string                   $ip
     * @param string                   $httpReferer
     * @param null|string              $redirectTo
     */
    public function __construct(
        string $url,
        ?IFaceModelInterface $parentModel,
        string $ip,
        ?string $httpReferer,
        ?string $redirectTo = null
    ) {
        $this->missedUrl     = $url;
        $this->httpReferer   = $httpReferer;
        $this->parentModel   = $parentModel;
        $this->redirectToUrl = $redirectTo;
        $this->ip            = $ip;
    }

    /**
     * @return string
     */
    public function getMissedUrl(): string
    {
        return $this->missedUrl;
    }

    /**
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     */
    public function getParentModel(): ?IFaceModelInterface
    {
        return $this->parentModel;
    }

    /**
     * @return string|null
     */
    public function getHttpReferer(): ?string
    {
        return $this->httpReferer;
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
