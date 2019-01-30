<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\IFace\Exception\UrlElementException;

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
     * MissingUrlElementException constructor.
     *
     * @param \BetaKiller\Url\UrlElementInterface|null $parentElement
     * @param bool|null                                $redirectToParent
     * @param \Throwable|null                          $previous
     */
    public function __construct(
        ?UrlElementInterface $parentElement,
        ?bool $redirectToParent = null,
        ?\Throwable $previous = null
    ) {
        $this->parentElement = $parentElement;
        $this->redirect      = (bool)$redirectToParent;

        parent::__construct('', null, 0, $previous);
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
}
