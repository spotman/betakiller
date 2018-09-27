<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\IFace\Exception\IFaceException;

class MissingUrlElementException extends IFaceException
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
     */
    public function __construct(?UrlElementInterface $parentElement, ?bool $redirectToParent = null)
    {
        $this->parentElement = $parentElement;
        $this->redirect      = (bool)$redirectToParent;

        parent::__construct();
    }

    /**
     * @return \BetaKiller\Url\IFaceModelInterface|null
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
