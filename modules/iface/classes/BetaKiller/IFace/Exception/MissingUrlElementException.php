<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Exception;

use BetaKiller\Url\UrlElementInterface;

class MissingUrlElementException extends IFaceException
{
    /**
     * @var \BetaKiller\Url\UrlElementInterface|null
     */
    private $parentElement;

    /**
     * @var string|null
     */
    private $redirectTo;

    /**
     * MissingUrlElementException constructor.
     *
     * @param \BetaKiller\Url\UrlElementInterface|null $parentElement
     * @param string                                   $redirectTo
     */
    public function __construct(?UrlElementInterface $parentElement, ?string $redirectTo = null)
    {
        $this->parentElement = $parentElement;
        $this->redirectTo    = $redirectTo;

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
     * @return string
     */
    public function getRedirectTo(): ?string
    {
        return $this->redirectTo;
    }
}
