<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Auth\LoginIFace;
use BetaKiller\Url\BeforeProcessingInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHttpErrorIFace extends AbstractIFace implements BeforeProcessingInterface
{
    protected $exception;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function beforeProcessing(ServerRequestInterface $request): void
    {
        // No caching for error pages
        $this->setExpiresInPast();
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        return [
            'login_url' => LoginIFace::URL,
            'is_guest'  => ServerRequestHelper::isGuest($request),
        ];
    }

    public function setException(\Throwable $e): self
    {
        $this->exception = $e;

        return $this;
    }
}
