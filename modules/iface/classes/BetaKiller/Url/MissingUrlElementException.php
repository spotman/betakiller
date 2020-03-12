<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Url\Container\UrlContainerInterface;

class MissingUrlElementException extends UrlElementException
{
    /**
     * @var \BetaKiller\Url\UrlElementInterface|null
     */
    private $parentElement;

    /**
     * @var bool
     */
    private $redirect;

    /**
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $params;

    /**
     * MissingUrlElementException constructor.
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Url\UrlElementInterface|null        $parentElement
     * @param bool|null                                       $redirectToParent
     * @param \Throwable|null                                 $previous
     */
    public function __construct(
        UrlContainerInterface $params,
        ?UrlElementInterface $parentElement,
        ?bool $redirectToParent = null,
        ?\Throwable $previous = null
    ) {
        $this->params        = $params;
        $this->parentElement = $parentElement;
        $this->redirect      = (bool)$redirectToParent;

        parent::__construct('', null, 404, $previous);
    }

    /**
     * @return \BetaKiller\Url\UrlElementInterface|null
     */
    public function getParentUrlElement(): ?UrlElementInterface
    {
        return $this->parentElement;
    }

    /**
     * @return bool
     */
    public function getRedirectToParent(): bool
    {
        return $this->redirect;
    }

    /**
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getUrlContainer(): UrlContainerInterface
    {
        return $this->params;
    }
}
